"""
PanganWatch Jember — Flask + MongoDB + Holt-Winters Time Series
Koleksi MongoDB: price_histories, commodities, categories, predictions, users, simulations
"""

import os, warnings, hashlib, secrets
import bcrypt
from datetime import datetime, timedelta
from functools import wraps

import numpy as np
import pandas as pd
from bson import ObjectId
from flask import Flask, render_template, request, jsonify, session, redirect, url_for
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

from routes.ai_route import ai_bp
app.register_blueprint(ai_bp)

FLASK_API_KEY = os.getenv("FLASK_API_KEY", "")


def api_key_required(f):
    @wraps(f)
    def decorated(*args, **kwargs):
        key = request.headers.get("X-API-Key") or request.args.get("api_key")
        if not key or key != FLASK_API_KEY:
            return jsonify({"error": "Unauthorized"}), 401
        return f(*args, **kwargs)
    return decorated


MONGO_URI = os.getenv("MONGO_URI", "mongodb://localhost:27017/")
DB_NAME   = os.getenv("DB_NAME", "monitoring_harga_pangan")

# ═══════════════════════════════════════════════════════════════════
# DATABASE CONNECTION
# ═══════════════════════════════════════════════════════════════════
client = MongoClient(MONGO_URI)
db     = client[DB_NAME]

col_price      = db["price_histories"]
col_commodity  = db["commodities"]
col_category   = db["categories"]
col_prediction = db["predictions"]
col_user       = db["users"]
col_simulation = db["simulations"]

col_price.create_index([("commodity_name", ASCENDING), ("date", ASCENDING)])
col_price.create_index([("category", ASCENDING), ("date", ASCENDING)])
col_prediction.create_index([("commodity_name", ASCENDING), ("created_at", DESCENDING)])


# ═══════════════════════════════════════════════════════════════════
# AUTH HELPERS
# ═══════════════════════════════════════════════════════════════════
def hash_pw(pw: str) -> str:
    return bcrypt.hashpw(pw.encode(), bcrypt.gensalt()).decode()


def verify_pw(plain: str, stored: str) -> bool:
    if not plain or not stored:
        return False
    if stored.startswith(("$2y$", "$2b$", "$2a$")):
        try:
            normalized = stored.replace("$2y$", "$2b$").encode()
            return bcrypt.checkpw(plain.encode(), normalized)
        except Exception:
            return False
    if len(stored) == 64:
        return hashlib.sha256(plain.encode()).hexdigest() == stored
    return stored == plain


def login_required(f):
    @wraps(f)
    def decorated(*args, **kwargs):
        if not session.get("user_id"):
            return redirect(url_for("login_page"))
        return f(*args, **kwargs)
    return decorated


def admin_required(f):
    @wraps(f)
    def decorated(*args, **kwargs):
        if not session.get("user_id"):
            return redirect(url_for("login_page"))
        if session.get("role") != "admin":
            return jsonify({"error": "Akses ditolak"}), 403
        return f(*args, **kwargs)
    return decorated


# ═══════════════════════════════════════════════════════════════════
# DATA HELPERS
# ═══════════════════════════════════════════════════════════════════
def get_komoditas_list() -> list[str]:
    return sorted(col_price.distinct("commodity_name"))


def get_categories_list() -> list[str]:
    return sorted(col_price.distinct("category"))


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
        cutoff = datetime.utcnow() - timedelta(days=days)
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
# ML: HOLT-WINTERS EXPONENTIAL SMOOTHING
# ═══════════════════════════════════════════════════════════════════
def hw_forecast(series: pd.Series, steps: int = 30):
    """
    Model utama: Holt-Winters Triple Exponential Smoothing
    - trend="add"         : komponen tren aditif
    - seasonal="add"      : komponen musiman aditif
    - seasonal_periods=7  : pola mingguan (7 hari)

    Syarat: HAS_HW=True dan data >= 30 hari.
    Jika tidak terpenuhi → fallback ke regresi linear (numpy polyfit).

    Return:
        fc_values : array hasil prediksi N hari ke depan
        ci        : dict {"lower": [...], "upper": [...]} confidence interval ±1.5σ
                    atau None jika pakai fallback
    """
    if HAS_HW and len(series) >= 30:
        try:
            m = ExponentialSmoothing(
                series,
                trend="add",
                seasonal="add",
                seasonal_periods=7,
                initialization_method="estimated",
            )
            f = m.fit(optimized=True, use_brute=False)
            fc  = f.forecast(steps)
            std = float(np.std(f.resid.dropna()))
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


def compute_accuracy(series: pd.Series) -> dict:
    """
    Evaluasi akurasi model dengan metode walk-forward 80/20 split:
    - 80% data awal  → training
    - 20% data akhir → testing (maks 30 hari)

    Metrik yang dihitung:
    - MAE  : Mean Absolute Error (satuan Rupiah)
    - MAPE : Mean Absolute Percentage Error (%)
    - RMSE : Root Mean Squared Error
    - Accuracy = 100 - MAPE
    """
    if len(series) < 60:
        return {
            "accuracy": None,
            "mae": None,
            "mape": None,
            "rmse": None,
            "note": "Data kurang dari 60 hari",
        }

    split = int(len(series) * 0.8)
    train = series.iloc[:split]
    test  = series.iloc[split : split + 30]

    try:
        if HAS_HW and len(train) >= 30:
            m = ExponentialSmoothing(
                train,
                trend="add",
                seasonal="add",
                seasonal_periods=7,
                initialization_method="estimated",
            )
            pred = m.fit(optimized=True, use_brute=False).forecast(len(test)).values
        else:
            y    = train.values[-30:]
            coef = np.polyfit(np.arange(len(y)), y, 1)
            pred = np.polyval(coef, np.arange(len(y), len(y) + len(test)))

        actual = test.values
        mae    = float(np.mean(np.abs(actual - pred)))
        mape   = float(np.mean(np.abs((actual - pred) / (actual + 1e-9))) * 100)
        rmse   = float(np.sqrt(np.mean((actual - pred) ** 2)))

        return {
            "accuracy": round(max(0, 100 - mape), 1),
            "mae":      round(mae, 0),
            "mape":     round(mape, 2),
            "rmse":     round(rmse, 0),
            "note":     "Holt-Winters, walk-forward 80/20 split",
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
# SISTEM REKOMENDASI PEMBELIAN (Rule-Based Scoring)
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

    # ── Scoring ──────────────────────────────────────────────────
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

    # ── Mapping skor → label rekomendasi ─────────────────────────
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

    # ── Alasan teks otomatis ──────────────────────────────────────
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


def _status(d7, dp):
    if d7 > 5 or dp > 5:    return "naik_signifikan"
    if d7 > 2 or dp > 2:    return "naik"
    if d7 < -5 or dp < -5:  return "turun_signifikan"
    if d7 < -2 or dp < -2:  return "turun"
    return "stabil"


# ═══════════════════════════════════════════════════════════════════
# AUTH ROUTES
# ═══════════════════════════════════════════════════════════════════
@app.route("/login", methods=["GET"])
def login_page():
    if session.get("user_id"):
        return redirect(url_for("index"))
    return render_template("login.html")


@app.route("/api/auth/login", methods=["POST"])
def api_login():
    body     = request.get_json()
    email    = body.get("email", "").strip().lower()
    password = body.get("password", "")

    if not email or not password:
        return jsonify({"error": "Email dan password wajib diisi"}), 400

    user = col_user.find_one({"email": email})
    if not user:
        return jsonify({"error": "Email atau password salah"}), 401
    if not user.get("is_active", True):
        return jsonify({"error": "Akun tidak aktif"}), 403
    if not verify_pw(password, user.get("password", "")):
        return jsonify({"error": "Email atau password salah"}), 401

    session["user_id"]  = str(user["_id"])
    session["username"] = user.get("name") or user.get("username") or email.split("@")[0]
    session["email"]    = email
    session["role"]     = user.get("role", "user")
    return jsonify({"role": session["role"], "username": session["username"]})


@app.route("/api/auth/register", methods=["POST"])
def api_register():
    body     = request.get_json()
    email    = body.get("email", "").strip().lower()
    password = body.get("password", "")
    nama     = body.get("nama", "").strip()

    if not email or not password:
        return jsonify({"error": "Email dan password wajib diisi"}), 400
    if len(password) < 6:
        return jsonify({"error": "Password minimal 6 karakter"}), 400
    if col_user.find_one({"email": email}):
        return jsonify({"error": "Email sudah terdaftar"}), 409

    doc = {
        "name":       nama or email.split("@")[0],
        "email":      email,
        "password":   hash_pw(password),
        "role":       "user",
        "is_active":  True,
        "created_at": datetime.utcnow(),
        "updated_at": datetime.utcnow(),
    }
    result = col_user.insert_one(doc)
    session["user_id"]  = str(result.inserted_id)
    session["username"] = doc["name"]
    session["email"]    = email
    session["role"]     = "user"
    return jsonify({"role": "user", "username": doc["name"]}), 201


@app.route("/api/auth/logout", methods=["POST"])
def api_logout():
    session.clear()
    return jsonify({"ok": True})


@app.route("/api/auth/me")
def api_me():
    if not session.get("user_id"):
        return jsonify({"logged_in": False})
    return jsonify({
        "logged_in": True,
        "username":  session.get("username"),
        "email":     session.get("email", ""),
        "role":      session.get("role"),
        "user_id":   session.get("user_id"),
    })


# ═══════════════════════════════════════════════════════════════════
# PAGE ROUTES
# ═══════════════════════════════════════════════════════════════════
@app.route("/")
def index():
    komoditas_list = get_komoditas_list()
    categories     = get_categories_list()
    total_records  = col_price.count_documents({})
    return render_template(
        "index.html",
        komoditas_list=komoditas_list,
        categories=categories,
        total_records=total_records,
    )


@app.route("/admin")
@login_required
def admin():
    if session.get("role") != "admin":
        return redirect(url_for("user_page"))
    return render_template("admin.html", komoditas_list=get_komoditas_list())


@app.route("/user")
@login_required
def user_page():
    return render_template("user.html", komoditas_list=get_komoditas_list())


# ═══════════════════════════════════════════════════════════════════
# API: KOMODITAS & KATEGORI
# ═══════════════════════════════════════════════════════════════════
@app.route("/api/komoditas")
def api_komoditas():
    return jsonify(get_komoditas_list())


@app.route("/api/categories")
def api_categories():
    cats = []
    for cat in get_categories_list():
        koms = sorted(col_price.distinct("commodity_name", {"category": cat}))
        cats.append({"category": cat, "komoditas": koms})
    return jsonify(cats)


# ═══════════════════════════════════════════════════════════════════
# API: HISTORIS
# ═══════════════════════════════════════════════════════════════════
@app.route("/api/historis/<komoditas>")
@login_required
def api_historis(komoditas):
    days = int(request.args.get("days", 90))
    s    = get_series(komoditas, days=days)
    if s.empty:
        return jsonify({"error": "Data tidak ditemukan"}), 404

    stats = {
        "min":         round(float(s.min())),
        "max":         round(float(s.max())),
        "mean":        round(float(s.mean())),
        "std":         round(float(s.std())),
        "latest":      round(float(s.iloc[-1])),
        "latest_date": s.index[-1].strftime("%Y-%m-%d"),
    }
    return jsonify({
        "tanggal": s.index.strftime("%Y-%m-%d").tolist(),
        "harga":   [round(float(v)) for v in s.values],
        "satuan":  get_satuan(komoditas),
        "stats":   stats,
    })


# ═══════════════════════════════════════════════════════════════════
# API: PREDIKSI
# ═══════════════════════════════════════════════════════════════════
@app.route("/api/prediksi/<komoditas>")
def api_prediksi(komoditas):
    steps     = int(request.args.get("steps", 30))
    use_cache = request.args.get("cache", "1") == "1"
    sat       = get_satuan(komoditas)

    if use_cache:
        cached = col_prediction.find_one(
            {"commodity_name": komoditas, "steps": steps},
            sort=[("created_at", DESCENDING)],
        )
        if cached:
            age = (datetime.utcnow() - cached["created_at"]).total_seconds()
            if age < 86400:
                return jsonify(cached["payload"])

    s = get_series(komoditas)
    if s.empty:
        return jsonify({"error": "Data tidak ditemukan"}), 404

    fc, ci  = hw_forecast(s, steps)
    acc     = compute_accuracy(s)
    today   = s.index[-1]
    dates   = [(today + timedelta(days=i + 1)).strftime("%Y-%m-%d") for i in range(steps)]

    payload = {
        "tanggal_pred":    dates,
        "forecast":        [round(float(v)) for v in fc],
        "ci_lower":        [round(float(v)) for v in ci["lower"]] if ci else None,
        "ci_upper":        [round(float(v)) for v in ci["upper"]] if ci else None,
        "accuracy":        acc,
        "satuan":          sat,
        "harga_terakhir":  round(float(s.iloc[-1])),
        "tanggal_terakhir": s.index[-1].strftime("%Y-%m-%d"),
        "kategori":        get_category(komoditas),
    }
    return jsonify(payload)


# ═══════════════════════════════════════════════════════════════════
# API: REKOMENDASI (user)
# ═══════════════════════════════════════════════════════════════════
@app.route("/api/rekomendasi", methods=["POST"])
@login_required
def api_rekomendasi():
    body      = request.get_json()
    komoditas = body.get("komoditas", "")
    konsumsi  = float(body.get("konsumsi", 1))
    user_id   = session.get("user_id")

    s = get_series(komoditas)
    if s.empty:
        return jsonify({"error": "Data tidak ditemukan"}), 404

    fc, ci = hw_forecast(s, 30)
    sat    = get_satuan(komoditas)
    rek    = buat_rekomendasi(s, fc, konsumsi, sat)

    h30   = s.iloc[-30:]
    today = s.index[-1]
    pred_dates = [
        (today + timedelta(days=i + 1)).strftime("%Y-%m-%d") for i in range(14)
    ]

    rek["chart"] = {
        "hist_tanggal": h30.index.strftime("%Y-%m-%d").tolist(),
        "hist_harga":   [round(float(v)) for v in h30.values],
        "pred_tanggal": pred_dates,
        "pred_harga":   [round(float(v)) for v in fc[:14]],
        "ci_lower":     [round(float(v)) for v in ci["lower"][:14]] if ci else None,
        "ci_upper":     [round(float(v)) for v in ci["upper"][:14]] if ci else None,
        "hist_avg": (
            round(float(s.iloc[-90:].mean())) if len(s) >= 90
            else round(float(s.mean()))
        ),
    }

    col_simulation.insert_one({
        "user_id":        user_id,
        "username":       session.get("username"),
        "komoditas":      komoditas,
        "konsumsi":       konsumsi,
        "satuan":         sat,
        "rekomendasi":    rek["rekomendasi"],
        "skor":           rek["skor"],
        "harga_kini":     rek["harga_kini"],
        "harga_7hari":    rek["harga_7hari"],
        "budget_sekarang":rek["budget_sekarang"],
        "budget_7hari":   rek["budget_7hari"],
        "delta_pct_7":    rek["delta_pct_7"],
        "alasan":         rek["alasan"],
        "created_at":     datetime.utcnow(),
    })

    return jsonify(rek)


# ═══════════════════════════════════════════════════════════════════
# API: DASHBOARD (admin)
# ═══════════════════════════════════════════════════════════════════
@app.route("/api/dashboard")
@login_required
def api_dashboard():
    result = []
    for k in get_komoditas_list():
        s = get_series(k, days=60)
        if len(s) < 7:
            continue
        h  = float(s.iloc[-1])
        h7 = float(s.iloc[-7])
        d7 = (h - h7) / h7 * 100
        fc, _ = hw_forecast(s, 7)
        dp = (float(np.mean(fc)) - h) / h * 100
        result.append({
            "komoditas":  k,
            "kategori":   get_category(k),
            "satuan":     get_satuan(k),
            "harga_kini": round(h),
            "delta_7":    round(d7, 1),
            "pred_7hari": round(float(np.mean(fc))),
            "delta_pred": round(dp, 1),
            "status":     _status(d7, dp),
        })
    return jsonify(result)


# ═══════════════════════════════════════════════════════════════════
# API: STATS ADMIN
# ═══════════════════════════════════════════════════════════════════
@app.route("/api/admin/stats")
@login_required
def api_admin_stats():
    total_records   = col_price.count_documents({})
    total_komoditas = len(get_komoditas_list())
    total_users     = col_user.count_documents({})
    total_active    = col_user.count_documents({"is_active": True})
    total_sim       = col_simulation.count_documents({})
    total_pred      = col_prediction.count_documents({})

    pipeline = [
        {"$group": {"_id": "$komoditas", "count": {"$sum": 1}}},
        {"$sort":  {"count": -1}},
        {"$limit": 5},
    ]
    top_komoditas = [
        {"komoditas": d["_id"], "count": d["count"]}
        for d in col_simulation.aggregate(pipeline)
    ]

    latest      = col_price.find_one({}, sort=[("date", DESCENDING)])
    latest_date = latest["date"].strftime("%Y-%m-%d") if latest else "-"

    return jsonify({
        "total_records":    total_records,
        "total_komoditas":  total_komoditas,
        "total_users":      total_users,
        "total_active":     total_active,
        "total_simulasi":   total_sim,
        "total_prediksi":   total_pred,
        "latest_date":      latest_date,
        "top_komoditas":    top_komoditas,
    })


# ═══════════════════════════════════════════════════════════════════
# API: RIWAYAT SIMULASI USER
# ═══════════════════════════════════════════════════════════════════
@app.route("/api/simulasi/riwayat")
@login_required
def api_riwayat():
    uid   = session.get("user_id")
    limit = int(request.args.get("limit", 10))
    docs  = list(
        col_simulation.find({"user_id": uid}, {"_id": 0, "user_id": 0})
        .sort("created_at", DESCENDING)
        .limit(limit)
    )
    for d in docs:
        if isinstance(d.get("created_at"), datetime):
            d["created_at"] = d["created_at"].strftime("%Y-%m-%d %H:%M")
    return jsonify(docs)


# ═══════════════════════════════════════════════════════════════════
# API: PRICE HISTORY RAW (admin)
# ═══════════════════════════════════════════════════════════════════
@app.route("/api/admin/price_histories")
@login_required
def api_price_histories():
    komoditas = request.args.get("komoditas", "")
    limit     = int(request.args.get("limit", 100))
    page      = int(request.args.get("page", 1))
    skip      = (page - 1) * limit

    query = {}
    if komoditas:
        query["commodity_name"] = komoditas

    total = col_price.count_documents(query)
    docs  = list(
        col_price.find(query, {"_id": 0, "commodity_id": 0, "category_id": 0})
        .sort("date", DESCENDING)
        .skip(skip)
        .limit(limit)
    )
    for d in docs:
        if isinstance(d.get("date"), datetime):
            d["date"] = d["date"].strftime("%Y-%m-%d")
        if isinstance(d.get("created_at"), datetime):
            d["created_at"] = d["created_at"].strftime("%Y-%m-%d %H:%M")

    return jsonify({"total": total, "page": page, "limit": limit, "data": docs})


# ═══════════════════════════════════════════════════════════════════
# API: KELOLA KOMODITAS (admin CRUD)
# ═══════════════════════════════════════════════════════════════════
@app.route("/api/admin/komoditas_detail")
@login_required
def api_komoditas_detail():
    pipeline = [
        {"$sort": {"date": -1}},
        {
            "$group": {
                "_id":       "$commodity_name",
                "harga_kini":{"$first": "$harga_sekarang"},
                "satuan":    {"$first": "$satuan"},
                "category":  {"$first": "$category"},
                "last_date": {"$first": "$date"},
                "total_rec": {"$sum": 1},
            }
        },
        {"$sort": {"_id": 1}},
    ]
    docs   = list(col_price.aggregate(pipeline))
    result = []
    for d in docs:
        result.append({
            "nama":      d["_id"],
            "kategori":  d.get("category", "—"),
            "satuan":    d.get("satuan", "kg"),
            "harga_kini":round(float(d.get("harga_kini", 0))),
            "last_date": d["last_date"].strftime("%Y-%m-%d") if d.get("last_date") else "—",
            "total_rec": d.get("total_rec", 0),
        })
    return jsonify(result)


@app.route("/api/admin/run_prediksi", methods=["POST"])
@login_required
def api_run_prediksi():
    if session.get("role") != "admin":
        return jsonify({"error": "Akses ditolak"}), 403

    body  = request.get_json()
    k     = body.get("komoditas", "")
    steps = int(body.get("steps", 30))

    s = get_series(k)
    if s.empty:
        return jsonify({"error": "Data tidak ditemukan"}), 404

    fc, ci = hw_forecast(s, steps)
    acc    = compute_accuracy(s)
    today  = s.index[-1]
    dates  = [(today + timedelta(days=i + 1)).strftime("%Y-%m-%d") for i in range(steps)]

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
        "created_at":     datetime.utcnow(),
        "created_by":     session.get("username"),
        "status":         "completed",
        "accuracy_mae":   acc.get("mae"),
        "accuracy_rmse":  acc.get("rmse"),
        "accuracy_mape":  acc.get("mape"),
        "payload":        payload,
    })
    return jsonify({"ok": True, "accuracy": acc, "steps": steps, "komoditas": k})


@app.route("/api/admin/prediction_logs")
@login_required
def api_prediction_logs():
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
                d["created_at"].strftime("%b %d, %Y %H:%M")
                if d.get("created_at") else "—"
            ),
        })
    return jsonify(result)


@app.route("/api/admin/users_list")
@login_required
def api_users_list():
    if session.get("role") != "admin":
        return jsonify({"error": "Akses ditolak"}), 403

    docs   = list(col_user.find({}, {"password": 0}).sort("created_at", DESCENDING))
    result = []
    for d in docs:
        result.append({
            "id":        str(d["_id"]),
            "name":      d.get("name") or d.get("nama") or d.get("username") or "—",
            "email":     d.get("email", "—"),
            "role":      d.get("role", "user"),
            "is_active": d.get("is_active", True),
            "created_at": (
                d["created_at"].strftime("%Y-%m-%d %H:%M")
                if d.get("created_at") else "—"
            ),
        })
    return jsonify(result)


# ═══════════════════════════════════════════════════════════════════
# API: ENDPOINT UNTUK LARAVEL
# ═══════════════════════════════════════════════════════════════════
@app.route("/api/external/komoditas")
@api_key_required
def api_external_komoditas():
    return jsonify(get_komoditas_list())


@app.route("/api/external/prediksi/<komoditas>")
@api_key_required
def api_external_prediksi(komoditas):
    steps  = int(request.args.get("steps", 30))
    cached = col_prediction.find_one(
        {"commodity_name": komoditas, "steps": steps},
        sort=[("created_at", DESCENDING)],
    )
    if cached:
        age = (datetime.utcnow() - cached["created_at"]).total_seconds()
        if age < 86400:
            payload = cached["payload"]
            payload["from_cache"] = True
            return jsonify(payload)

    s = get_series(komoditas)
    if s.empty:
        return jsonify({"error": f"Komoditas '{komoditas}' tidak ditemukan"}), 404

    fc, ci = hw_forecast(s, steps)
    acc    = compute_accuracy(s)
    today  = s.index[-1]
    dates  = [(today + timedelta(days=i + 1)).strftime("%Y-%m-%d") for i in range(steps)]

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
        "created_at":     datetime.utcnow(),
        "created_by":     "laravel_api",
        "payload":        payload,
    })
    return jsonify(payload)


@app.route("/api/external/rekomendasi", methods=["POST"])
@api_key_required
def api_external_rekomendasi():
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

    h30   = s.iloc[-30:]
    today = s.index[-1]
    pred_dates = [
        (today + timedelta(days=i + 1)).strftime("%Y-%m-%d") for i in range(14)
    ]

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


# ═══════════════════════════════════════════════════════════════════
# INISIALISASI ADMIN OTOMATIS
# ═══════════════════════════════════════════════════════════════════
def auto_seed_admin():
    """Buat akun admin pertama kali jika belum ada di database."""
    if col_user.count_documents({"role": "admin"}) == 0:
        username    = os.getenv("ADMIN_USERNAME", "admin")
        password    = os.getenv("ADMIN_PASSWORD", "admin123")
        email_admin = os.getenv("ADMIN_EMAIL", "admin@gmail.com")
        col_user.insert_one({
            "name":       "Administrator",
            "email":      email_admin,
            "password":   hash_pw(password),
            "role":       "admin",
            "is_active":  True,
            "created_at": datetime.utcnow(),
            "updated_at": datetime.utcnow(),
        })
        print("\n" + "=" * 50)
        print("  Akun admin dibuat otomatis!")
        print(f"  Username : {username}")
        print(f"  Password : {password}")
        print(f"  URL      : http://localhost:5001/login")
        print("=" * 50 + "\n")
    else:
        print("  Akun admin sudah ada di database.")


if __name__ == "__main__":
    auto_seed_admin()
    app.run(debug=True, port=5001)