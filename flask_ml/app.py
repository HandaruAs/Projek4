"""
SIMOPANG Flask ML Service — Pure ML/Prediction API
Koleksi MongoDB: price_histories, predictions

Endpoint yang tersedia (semua butuh X-API-Key header):
  GET  /api/external/komoditas               → daftar nama komoditas
  GET  /api/external/prediksi/<komoditas>    → prediksi + akurasi (cache 24 jam)
  POST /api/external/rekomendasi             → rekomendasi beli/tunda
  POST /api/admin/run_prediksi               → paksa regenerasi prediksi
  GET  /api/admin/prediction_logs            → riwayat log prediksi

CHANGELOG (fixes):
  [FIX 1] Bug 1 — tanggal_pred sekarang dimulai dari hari ini (datetime.now()),
          bukan dari tanggal data terakhir di DB. Ini mencegah prediksi tampil
          mundur ke masa lalu ketika data tidak up-to-date.

  [FIX 2] Bug 2 — default steps dinaikkan ke 60 di semua endpoint prediksi,
          dan steps yang dikirim Laravel sekarang benar-benar dipakai.
          Cache juga mempertimbangkan steps agar tidak salah serve.

  [FIX 3] Bug 3 — threshold minimum data untuk compute_accuracy diturunkan
          dari 60 ke 30 hari agar komoditas seperti BERAS MERAH (110 dokumen
          tapi mungkin rentang < 60 hari kalender) tetap bisa dihitung MAPE-nya.
          Walk-forward split disesuaikan: min(20, len*0.2) hari untuk test.

  [FIX 4] Support steps 7 / 14 / 30 / 60 / 90 hari (sesuai pilihan UI admin).
          Default steps dinaikkan ke 90. hw_forecast dan compute_accuracy
          sudah handle semua nilai tersebut secara otomatis.
"""

import os, warnings, secrets
from datetime import datetime, timedelta
from functools import wraps
from zoneinfo import ZoneInfo

WIB = ZoneInfo("Asia/Jakarta")

import numpy as np
import pandas as pd
from bson import ObjectId
from flask import Flask, request, jsonify
from pymongo import MongoClient, ASCENDING, DESCENDING
from dotenv import load_dotenv

warnings.filterwarnings("ignore")
load_dotenv()

try:
    from statsmodels.tsa.holtwinters import ExponentialSmoothing
    HAS_HW = True
except ImportError:
    HAS_HW = False

# ═══════════════════════════════════════════════════════════════════
# CONFIG
# ═══════════════════════════════════════════════════════════════════
app = Flask(__name__)
app.secret_key = os.getenv("SECRET_KEY", secrets.token_hex(32))

FLASK_API_KEY = os.getenv("FLASK_API_KEY", "")

MONGO_URI = os.getenv("MONGO_URI", "mongodb://localhost:27017/")
DB_NAME   = os.getenv("DB_NAME", "monitoring_harga_pangan")

from routes.ai_route import ai_bp
app.register_blueprint(ai_bp)

def api_key_required(f):
    @wraps(f)
    def decorated(*args, **kwargs):
        key = request.headers.get("X-API-Key") or request.args.get("api_key")
        if not key or key != FLASK_API_KEY:
            return jsonify({"error": "Unauthorized"}), 401
        return f(*args, **kwargs)
    return decorated


# ═══════════════════════════════════════════════════════════════════
# DATABASE CONNECTION
# ═══════════════════════════════════════════════════════════════════
client = MongoClient(MONGO_URI)
db     = client[DB_NAME]

col_price      = db["price_histories"]
col_prediction = db["predictions"]

col_price.create_index([("commodity_name", ASCENDING), ("date", ASCENDING)])
col_price.create_index([("category", ASCENDING), ("date", ASCENDING)])
col_prediction.create_index([("commodity_name", ASCENDING), ("created_at", DESCENDING)])


# ═══════════════════════════════════════════════════════════════════
# DATA HELPERS
# ═══════════════════════════════════════════════════════════════════
def get_komoditas_list() -> list[str]:
    return sorted(col_price.distinct("commodity_name"))


def get_series(commodity_name: str, days: int = None) -> pd.Series:
    """
    Ambil data harga dari MongoDB, lalu:
    1. Konversi ke datetime index
    2. Resample harian (1 titik per hari = rata-rata)
    3. Forward-fill hari yang kosong (ffill)
    Hasil: pd.Series siap pakai untuk model prediksi
    """
    query = {"commodity_name": commodity_name}
    if days:
        cutoff = datetime.now(WIB) - timedelta(days=days)
        query["date"] = {"$gte": cutoff}

    cursor = col_price.find(
        query, {"date": 1, "harga_sekarang": 1, "_id": 0}
    ).sort("date", ASCENDING)

    records = list(cursor)
    if not records:
        return pd.Series(dtype=float)

    df = pd.DataFrame(records)
    df["date"] = pd.to_datetime(df["date"])
    df = df.set_index("date")["harga_sekarang"]
    df = pd.to_numeric(df, errors="coerce").dropna()
    df = df[df > 0]
    df = df.resample("D").mean().ffill()
    return df


def get_satuan(commodity_name: str) -> str:
    doc = col_price.find_one({"commodity_name": commodity_name}, {"satuan": 1})
    return doc.get("satuan", "kg") if doc else "kg"


def get_category(commodity_name: str) -> str:
    doc = col_price.find_one({"commodity_name": commodity_name}, {"category": 1})
    return doc.get("category", "") if doc else ""


# ═══════════════════════════════════════════════════════════════════
# [FIX 1] HELPER: generate tanggal prediksi mulai dari HARI INI
# ═══════════════════════════════════════════════════════════════════
def _generate_pred_dates(steps: int) -> list[str]:
    """
    [FIX 1] Tanggal prediksi selalu dimulai dari hari ini (UTC),
    bukan dari tanggal data terakhir di DB.

    Sebelumnya: today = s.index[-1]  → bisa mundur ke masa lalu
    Sekarang  : today = datetime.utcnow().date()  → selalu hari ini
    """
    today = datetime.now(WIB).date()
    return [(today + timedelta(days=i + 1)).strftime("%Y-%m-%d") for i in range(steps)]


# ═══════════════════════════════════════════════════════════════════
# ML: OUTLIER REMOVAL
# ═══════════════════════════════════════════════════════════════════
def _remove_outliers(series: pd.Series, window: int = 7, threshold: float = 2.5) -> pd.Series:
    """
    CRISP-DM — Data Preparation:
    Bersihkan spike harga ekstrem menggunakan rolling median.
    Nilai yang jauh > threshold × std dari median rolling diganti dengan median rolling.
    Berguna untuk komoditas volatile seperti tomat, cabai, dll.
    """
    rolling_median = series.rolling(window, center=True, min_periods=1).median()
    rolling_std    = series.rolling(window, center=True, min_periods=1).std().fillna(0)
    mask    = np.abs(series - rolling_median) > threshold * rolling_std
    cleaned = series.copy()
    cleaned[mask] = rolling_median[mask]
    return cleaned


# ═══════════════════════════════════════════════════════════════════
# ML: HOLT-WINTERS AUTO-CONFIG
# ═══════════════════════════════════════════════════════════════════
def _fit_best_hw(train: pd.Series):
    """
    CRISP-DM — Modeling:
    Coba beberapa konfigurasi Holt-Winters, pilih yang menghasilkan AIC terkecil.
    Konfigurasi yang dicoba (dari paling kompleks ke paling sederhana):
      1. Trend + Seasonal 7 hari  (pola mingguan)
      2. Trend + Seasonal 30 hari (pola bulanan)
      3. Trend saja, tanpa seasonal
      4. Tanpa trend dan seasonal  (Simple Exponential Smoothing)
    Fallback: None → caller akan gunakan regresi linear.
    """
    best_model = None
    best_aic   = float("inf")

    configs = [
        {"trend": "add", "seasonal": "add",  "seasonal_periods": 7},
        {"trend": "add", "seasonal": "add",  "seasonal_periods": 30},
    ]

    for cfg in configs:
        try:
            m = ExponentialSmoothing(
                train,
                trend=cfg["trend"],
                seasonal=cfg["seasonal"],
                seasonal_periods=cfg.get("seasonal_periods"),
                initialization_method="estimated",
            )
            fitted = m.fit(optimized=True, use_brute=False)
            if fitted.aic < best_aic:
                best_aic   = fitted.aic
                best_model = fitted
        except Exception:
            continue

    return best_model


# ═══════════════════════════════════════════════════════════════════
# ML: HOLT-WINTERS FORECAST
# ═══════════════════════════════════════════════════════════════════
def hw_forecast(series: pd.Series, steps: int = 90):
    """
    CRISP-DM — Modeling:
    Model utama: Holt-Winters Triple Exponential Smoothing
    - Auto-select konfigurasi terbaik via _fit_best_hw()
    - Confidence interval ±1.5σ dari residual

    Syarat: HAS_HW=True dan data >= 30 hari.
    Jika tidak terpenuhi → fallback ke regresi linear (numpy polyfit).

    Return:
        fc_values : array hasil prediksi N hari ke depan
        ci        : dict {"lower": [...], "upper": [...]} confidence interval
                    atau None jika pakai fallback
    """
    if HAS_HW and len(series) >= 30:
        try:
            fitted = _fit_best_hw(series)
            if fitted is not None:
                fc  = fitted.forecast(steps)
                std = float(np.std(fitted.resid.dropna()))
                return fc.values, {
                    "lower": (fc - 1.5 * std).tolist(),
                    "upper": (fc + 1.5 * std).tolist(),
                }
        except Exception:
            pass

    # Fallback: regresi linear sederhana
    y    = series.values[-30:] if len(series) >= 30 else series.values
    coef = np.polyfit(np.arange(len(y)), y, 1)
    fc   = np.polyval(coef, np.arange(len(y), len(y) + steps))
    return fc, None


# ═══════════════════════════════════════════════════════════════════
# ML: EVALUASI AKURASI (CRISP-DM — Evaluation)
# ═══════════════════════════════════════════════════════════════════
def compute_accuracy(series: pd.Series) -> dict:
    """
    CRISP-DM — Evaluation:
    Evaluasi akurasi model dengan metode walk-forward split:
    - Min data: 30 hari (diturunkan dari 60 agar komoditas dengan data
      terbatas seperti BERAS MERAH tetap bisa dievaluasi)                [FIX 3]
    - Split: 80% train / 20% test (maks 20 hari untuk test)
    - Outlier removal sebelum evaluasi
    - MAPE robust: hanya hitung di baris actual > 100

    Metrik yang dihitung:
    - MAE      : Mean Absolute Error (satuan Rupiah)
    - MAPE     : Mean Absolute Percentage Error (%)
    - RMSE     : Root Mean Squared Error
    - Accuracy : 100 - MAPE  (diclamp ke 0 jika MAPE > 100)
    """
    # [FIX 3] threshold diturunkan dari 60 ke 30
    if len(series) < 30:
        return {
            "accuracy": None,
            "mae": None,
            "mape": None,
            "rmse": None,
            "note": "Data kurang dari 30 hari",
        }

    # Step 1: bersihkan outlier
    series = _remove_outliers(series)

    split    = int(len(series) * 0.8)
    train    = series.iloc[:split]
    # [FIX 3+4] test window: maks 30 hari (cukup untuk evaluasi sampai 90 hari)
    test_len = min(30, len(series) - split)
    test     = series.iloc[split : split + test_len]

    if len(test) == 0:
        return {
            "accuracy": None,
            "mae": None,
            "mape": None,
            "rmse": None,
            "note": "Test set kosong setelah split",
        }

    try:
        if HAS_HW and len(train) >= 30:
            # Step 2: pilih konfigurasi HW terbaik
            fitted = _fit_best_hw(train)
            if fitted is not None:
                pred = fitted.forecast(len(test)).values
            else:
                raise ValueError("Semua konfigurasi HW gagal, pakai fallback")
        else:
            y    = train.values[-30:] if len(train) >= 30 else train.values
            coef = np.polyfit(np.arange(len(y)), y, 1)
            pred = np.polyval(coef, np.arange(len(y), len(y) + len(test)))

        actual = test.values
        mae    = float(np.mean(np.abs(actual - pred)))
        rmse   = float(np.sqrt(np.mean((actual - pred) ** 2)))

        # Step 3: MAPE robust
        mask = actual > 100
        if mask.sum() == 0:
            mask = np.ones(len(actual), dtype=bool)
        mape = float(
            np.mean(np.abs((actual[mask] - pred[mask]) / actual[mask])) * 100
        )

        return {
            "accuracy": round(max(0, 100 - mape), 1),
            "mae":      round(mae, 0),
            "mape":     round(mape, 2),
            "rmse":     round(rmse, 0),
            "note":     "Holt-Winters auto-config + outlier removal, walk-forward 80/20 split",
        }
    except Exception as e:
        return {
            "accuracy": None,
            "mae": None,
            "mape": None,
            "rmse": None,
            "note": str(e),
        }


# ═══════════════════════════════════════════════════════════════════
# ML: REKOMENDASI PEMBELIAN (Rule-Based Scoring)
# ═══════════════════════════════════════════════════════════════════
def buat_rekomendasi(series: pd.Series, fc_vals, konsumsi: float, satuan: str) -> dict:
    """
    Sistem scoring berbasis aturan (bukan ML) untuk rekomendasi beli/tunda.
    Skor dimulai dari 50, lalu diubah berdasarkan:
    - Prediksi harga 7 & 30 hari ke depan
    - Perbandingan harga saat ini vs historis (7/30/90/365 hari)
    - Volatilitas harga (standar deviasi relatif)

    Mapping skor → rekomendasi:
      0–30  : BELI SEKARANG
      31–50 : BELI SEGERA
      51–68 : TUNGGU DULU
      69–100: TUNDA PEMBELIAN
    """
    h_kini = float(series.iloc[-1])
    fc_arr = [float(v) for v in fc_vals]

    h_7d  = float(np.mean(fc_arr[:7]))
    h_30d = float(np.mean(fc_arr[:30]))
    h_d7  = float(fc_arr[6])  if len(fc_arr) > 6  else h_kini
    h_d14 = float(fc_arr[13]) if len(fc_arr) > 13 else h_kini
    h_d30 = float(fc_arr[29]) if len(fc_arr) > 29 else h_kini

    def _avg(s, w):
        return float(np.mean(s.iloc[-w:])) if len(s) >= w else float(np.mean(s))

    def _std(s, w):
        return float(s.iloc[-w:].std()) if len(s) >= w else float(s.std())

    h_7avg   = _avg(series, 7)
    h_30avg  = _avg(series, 30)
    h_90avg  = _avg(series, 90)
    h_365avg = _avg(series, 365)

    vol_90  = _std(series, 90)  / h_kini * 100 if h_kini else 0
    vol_365 = _std(series, 365) / h_kini * 100 if h_kini else 0
    vol     = vol_90 if vol_90 > 0.1 else vol_365

    d_7hist   = (h_kini - h_7avg)   / h_7avg   * 100 if h_7avg   else 0
    d_30hist  = (h_kini - h_30avg)  / h_30avg  * 100 if h_30avg  else 0
    d_90hist  = (h_kini - h_90avg)  / h_90avg  * 100 if h_90avg  else 0
    d_365hist = (h_kini - h_365avg) / h_365avg * 100 if h_365avg else 0

    d_d7  = (h_d7  - h_kini) / h_kini * 100 if h_kini else 0
    d_d14 = (h_d14 - h_kini) / h_kini * 100 if h_kini else 0
    d_d30 = (h_d30 - h_kini) / h_kini * 100 if h_kini else 0
    d_7d  = (h_7d  - h_kini) / h_kini * 100 if h_kini else 0

    skor = 50
    if d_d30 > 5:    skor -= 25
    elif d_d30 > 2:  skor -= 12
    elif d_d30 < -5: skor += 25
    elif d_d30 < -2: skor += 12

    if d_d7 > 3:    skor -= 10
    elif d_d7 < -3: skor += 10

    if d_365hist > 5:    skor -= 10
    elif d_365hist < -5: skor += 8

    if d_90hist > 5:    skor -= 8
    elif d_90hist < -5: skor += 6

    if vol > 3:   skor -= 8
    elif vol > 1: skor -= 3

    skor = max(0, min(100, skor))

    if skor <= 30:
        rek, warna, emoji, headline = (
            "BELI SEKARANG", "buy", "🛒",
            "Harga diprediksi naik dalam 30 hari ke depan — beli sekarang lebih hemat",
        )
    elif skor <= 50:
        rek, warna, emoji, headline = (
            "BELI SEGERA", "buy_soon", "⚡",
            "Tren harga cenderung naik — beli dalam waktu dekat lebih disarankan",
        )
    elif skor <= 68:
        rek, warna, emoji, headline = (
            "TUNGGU DULU", "wait", "⏳",
            "Ada indikasi harga akan turun — pertimbangkan tunggu beberapa hari",
        )
    else:
        rek, warna, emoji, headline = (
            "TUNDA PEMBELIAN", "hold", "📉",
            "Harga diprediksi turun dalam 30 hari ke depan — tunda jika stok masih ada",
        )

    alasan = []
    if abs(d_d30) >= 0.05:
        arah = "naik" if d_d30 > 0 else "turun"
        alasan.append(
            f"Harga diprediksi {arah} {abs(d_d30):.2f}% dalam 30 hari "
            f"(Rp {h_kini:,.0f} → Rp {h_d30:,.0f})"
        )
    elif abs(d_365hist) >= 0.5:
        arah = "naik" if d_365hist > 0 else "turun"
        alasan.append(
            f"Harga {arah} {abs(d_365hist):.1f}% vs rata-rata setahun lalu "
            f"(Rp {h_365avg:,.0f}) — saat ini Rp {h_kini:,.0f}"
        )
    else:
        alasan.append(
            f"Harga stabil di kisaran Rp {int(series.min()):,.0f}–Rp {int(series.max()):,.0f} "
            f"dalam setahun terakhir — waktu beli tidak terlalu kritis"
        )

    selisih_budget = konsumsi * (h_d30 - h_kini)
    if abs(selisih_budget) >= 100:
        if selisih_budget > 0:
            alasan.append(
                f"Beli sekarang hemat ~Rp {abs(selisih_budget):,.0f} vs beli 30 hari lagi "
                f"({konsumsi} {satuan}/minggu: Rp {konsumsi*h_kini:,.0f} → Rp {konsumsi*h_d30:,.0f})"
            )
        else:
            alasan.append(
                f"Tunggu 30 hari bisa hemat ~Rp {abs(selisih_budget):,.0f} "
                f"({konsumsi} {satuan}/minggu: Rp {konsumsi*h_kini:,.0f} → Rp {konsumsi*h_d30:,.0f})"
            )
    else:
        alasan.append(
            f"Selisih harga sangat kecil (~Rp {abs(selisih_budget):,.0f}/minggu) — "
            f"beli kapan saja tidak masalah"
        )

    return {
        "rekomendasi":      rek,
        "warna":            warna,
        "emoji":            emoji,
        "headline":         headline,
        "alasan":           alasan,
        "skor":             skor,
        "harga_kini":       round(h_kini),
        "harga_7hari":      round(h_7d),
        "harga_30hari_avg": round(h_30avg),
        "volatilitas":      round(vol, 2),
        "budget_sekarang":  round(konsumsi * h_kini),
        "budget_7hari":     round(konsumsi * h_7d),
        "konsumsi":         konsumsi,
        "satuan":           satuan,
        "delta_pct_7":      round(d_7d, 2),
        "delta_pct_30":     round(d_d30, 2),
    }


# ═══════════════════════════════════════════════════════════════════
# API ENDPOINTS
# ═══════════════════════════════════════════════════════════════════
@app.route("/api/external/komoditas")
@api_key_required
def api_external_komoditas():
    """Daftar semua nama komoditas yang tersedia di database."""
    return jsonify(get_komoditas_list())


@app.route("/api/external/prediksi/<komoditas>")
@api_key_required
def api_external_prediksi(komoditas):
    """
    Prediksi harga N hari ke depan untuk satu komoditas.
    Cache 24 jam — jika ada prediksi yang masih fresh, langsung return tanpa recompute.
    Query param: steps (default 60)                                     [FIX 2]

    [FIX 1] tanggal_pred sekarang dimulai dari hari ini, bukan tanggal data terakhir.
    [FIX 2] default steps = 60 (naik dari 30).
    [FIX 4] steps valid: 7 / 14 / 30 / 60 / 90. Default 90.
    """
    # [FIX 4] default steps dinaikkan ke 90, clamp ke pilihan valid
    VALID_STEPS = {7, 14, 30, 60, 90}
    steps = int(request.args.get("steps", 90))
    if steps not in VALID_STEPS:
        steps = min(VALID_STEPS, key=lambda x: abs(x - steps))  # snap ke nilai terdekat
    cached = col_prediction.find_one(
        {"commodity_name": komoditas, "steps": steps},
        sort=[("created_at", DESCENDING)],
    )
    if cached:
        age = (datetime.now(WIB) - cached["created_at"].replace(tzinfo=WIB)).total_seconds()
        if age < 86400:
            payload = cached["payload"]
            payload["from_cache"] = True
            return jsonify(payload)

    s = get_series(komoditas)
    if s.empty:
        return jsonify({"error": f"Komoditas '{komoditas}' tidak ditemukan"}), 404

    fc, ci = hw_forecast(s, steps)
    acc    = compute_accuracy(s)

    # [FIX 1] tanggal dimulai dari hari ini, bukan s.index[-1]
    dates = _generate_pred_dates(steps)

    payload = {
        "komoditas":        komoditas,
        "tanggal_pred":     dates,
        "forecast":         [round(float(v)) for v in fc],
        "ci_lower":         [round(float(v)) for v in ci["lower"]] if ci else None,
        "ci_upper":         [round(float(v)) for v in ci["upper"]] if ci else None,
        "accuracy":         acc,
        "satuan":           get_satuan(komoditas),
        "harga_terakhir":   round(float(s.iloc[-1])),
        "tanggal_terakhir": s.index[-1].strftime("%Y-%m-%d"),
        "kategori":         get_category(komoditas),
        "from_cache":       False,
    }

    col_prediction.insert_one({
        "commodity_name": komoditas,
        "steps":          steps,
        "created_at":     datetime.now(WIB),
        "created_by":     "laravel_api",
        "payload":        payload,
    })
    return jsonify(payload)


@app.route("/api/external/rekomendasi", methods=["POST"])
@api_key_required
def api_external_rekomendasi():
    """
    Rekomendasi beli/tunda berdasarkan prediksi harga + konsumsi user.
    Body JSON: { "komoditas": str, "konsumsi": float }
    """
    body      = request.get_json()
    komoditas = body.get("komoditas", "")
    konsumsi  = float(body.get("konsumsi", 1))

    if not komoditas:
        return jsonify({"error": "Field 'komoditas' wajib diisi"}), 400

    s = get_series(komoditas)
    if s.empty:
        return jsonify({"error": f"Komoditas '{komoditas}' tidak ditemukan"}), 404

    fc, ci = hw_forecast(s, 30)
    sat    = get_satuan(komoditas)
    rek    = buat_rekomendasi(s, fc, konsumsi, sat)

    h30 = s.iloc[-30:]

    # [FIX 1] pred_dates dari hari ini
    pred_dates = _generate_pred_dates(14)

    rek["chart"] = {
        "hist_tanggal": h30.index.strftime("%Y-%m-%d").tolist(),
        "hist_harga":   [round(float(v)) for v in h30.values],
        "pred_tanggal": pred_dates,
        "pred_harga":   [round(float(v)) for v in fc[:14]],
        "ci_lower":     [round(float(v)) for v in ci["lower"][:14]] if ci else None,
        "ci_upper":     [round(float(v)) for v in ci["upper"][:14]] if ci else None,
    }
    rek["komoditas"] = komoditas
    return jsonify(rek)


@app.route("/api/admin/run_prediksi", methods=["POST"])
@api_key_required
def api_run_prediksi():
    """
    Paksa regenerasi prediksi untuk satu komoditas (hapus cache lama).
    Body JSON: { "komoditas": str, "steps": int }
    Dipanggil dari Laravel saat admin klik 'Generate Prediksi'.

    [FIX 1] tanggal_pred sekarang dimulai dari hari ini.
    [FIX 2] default steps = 60 (naik dari 30).
    [FIX 4] steps valid: 7 / 14 / 30 / 60 / 90. Default 90.
    """
    body  = request.get_json()
    k     = body.get("komoditas", "")
    # [FIX 4] default steps 90, clamp ke nilai valid
    VALID_STEPS = {7, 14, 30, 60, 90}
    steps = int(body.get("steps", 90))
    if steps not in VALID_STEPS:
        steps = min(VALID_STEPS, key=lambda x: abs(x - steps))

    if not k:
        return jsonify({"error": "Field 'komoditas' wajib diisi"}), 400

    s = get_series(k)
    if s.empty:
        return jsonify({"error": "Data tidak ditemukan"}), 404

    fc, ci = hw_forecast(s, steps)
    acc    = compute_accuracy(s)

    # [FIX 1] tanggal dimulai dari hari ini, bukan s.index[-1]
    dates = _generate_pred_dates(steps)

    payload = {
        "tanggal_pred":     dates,
        "forecast":         [round(float(v)) for v in fc],
        "ci_lower":         [round(float(v)) for v in ci["lower"]] if ci else None,
        "ci_upper":         [round(float(v)) for v in ci["upper"]] if ci else None,
        "accuracy":         acc,
        "satuan":           get_satuan(k),
        "harga_terakhir":   round(float(s.iloc[-1])),
        "tanggal_terakhir": s.index[-1].strftime("%Y-%m-%d"),
        "kategori":         get_category(k),
    }

    col_prediction.delete_many({"commodity_name": k, "steps": steps})
    col_prediction.insert_one({
        "commodity_name": k,
        "steps":          steps,
        "created_at":     datetime.now(WIB),
        "created_by":     "admin",
        "status":         "completed",
        "accuracy_mae":   acc.get("mae"),
        "accuracy_rmse":  acc.get("rmse"),
        "accuracy_mape":  acc.get("mape"),
        "payload":        payload,
    })
    return jsonify({"ok": True, "accuracy": acc, "steps": steps, "komoditas": k})


@app.route("/api/admin/prediction_logs")
@api_key_required
def api_prediction_logs():
    """
    Riwayat log prediksi (tanpa payload) untuk ditampilkan di tabel admin Laravel.
    Query param: limit (default 20)
    """
    limit = int(request.args.get("limit", 20))
    docs  = list(
        col_prediction.find({}, {"payload": 0})
        .sort("created_at", DESCENDING)
        .limit(limit)
    )
    result = []
    for d in docs:
        result.append({
            "id":            str(d["_id"]),
            "commodity":     d.get("commodity_name", "—"),
            "steps":         d.get("steps", 0),
            "status":        d.get("status", "completed"),
            "accuracy_mae":  d.get("accuracy_mae"),
            "accuracy_rmse": d.get("accuracy_rmse"),
            "accuracy_mape": d.get("accuracy_mape"),
            "created_by":    d.get("created_by", "system"),
            "created_at": (
                d["created_at"].astimezone(WIB).strftime("%b %d, %Y %H:%M")
                if d.get("created_at") else "—"
            ),
        })
    return jsonify(result)


# ═══════════════════════════════════════════════════════════════════
# ENTRY POINT
# ═══════════════════════════════════════════════════════════════════
if __name__ == "__main__":
    app.run(debug=True, port=5001)