"""
holt_winters.py  —  Baca data dari MongoDB, jalankan Holt-Winters, simpan JSON.

Letakkan file ini di:  <laravel_root>/scripts/holt_winters.py

Install dependensi Python:
    pip install statsmodels pandas numpy pymongo python-dotenv

Usage (dipanggil otomatis oleh Laravel):
    python3 scripts/holt_winters.py <commodity_id> <steps> <trend> <seasonal>
                                    <seasonal_periods> <damped:0|1> <output_file>
"""

import sys, json, os
from datetime import datetime, timedelta

import numpy as np
import pandas as pd
from statsmodels.tsa.holtwinters import ExponentialSmoothing
from pymongo import MongoClient
from dotenv import dotenv_values

# ── 1. Parse argumen ────────────────────────────────────────────────────────
if len(sys.argv) != 8:
    print(json.dumps({"error": "Butuh 7 argumen: commodity_id steps trend seasonal seasonal_periods damped output_file"}))
    sys.exit(1)

commodity_id     = sys.argv[1]          # string MongoDB ObjectId
steps            = int(sys.argv[2])
trend_type       = sys.argv[3] if sys.argv[3] != 'none' else None
seasonal_type    = sys.argv[4] if sys.argv[4] != 'none' else None
seasonal_periods = int(sys.argv[5])
damped           = bool(int(sys.argv[6]))
output_file      = sys.argv[7]

# ── 2. Baca .env Laravel untuk koneksi MongoDB ──────────────────────────────
base_path = os.path.dirname(os.path.dirname(os.path.abspath(__file__)))
env = dotenv_values(os.path.join(base_path, '.env'))

MONGO_HOST = env.get('DB_HOST', '127.0.0.1')
MONGO_PORT = int(env.get('DB_PORT', 27017))
MONGO_DB   = env.get('DB_DATABASE', 'monitoring_harga_pangan')
MONGO_USER = env.get('DB_USERNAME', '')
MONGO_PASS = env.get('DB_PASSWORD', '')

# Build URI
if MONGO_USER and MONGO_PASS:
    uri = f"mongodb://{MONGO_USER}:{MONGO_PASS}@{MONGO_HOST}:{MONGO_PORT}/{MONGO_DB}"
else:
    uri = f"mongodb://{MONGO_HOST}:{MONGO_PORT}/"

client = MongoClient(uri)
db     = client[MONGO_DB]

# ── 3. Ambil data historis dari collection price_histories ──────────────────
# Cocok dengan PriceHistory model Laravel (field: date, harga_sekarang)
from bson import ObjectId

try:
    oid = ObjectId(commodity_id)
except Exception:
    oid = commodity_id  # fallback jika bukan ObjectId valid

cursor = db['price_histories'].find(
    {"commodity_id": oid},
    {"date": 1, "harga_sekarang": 1, "_id": 0}
).sort("date", 1)

records = list(cursor)

if not records:
    print(json.dumps({"error": f"Tidak ada data price_histories untuk commodity_id={commodity_id}"}))
    sys.exit(1)

# ── 4. Bangun DataFrame ──────────────────────────────────────────────────────
df = pd.DataFrame(records)
df.rename(columns={"date": "ds", "harga_sekarang": "y"}, inplace=True)
df['ds'] = pd.to_datetime(df['ds'])
df = df.dropna(subset=['ds', 'y'])
df = df.sort_values('ds').drop_duplicates('ds')
df = df.set_index('ds').asfreq('D')
df['y'] = df['y'].interpolate(method='time')

min_required = seasonal_periods * 2
if len(df) < min_required:
    print(json.dumps({
        "error": f"Data tidak cukup. Dibutuhkan {min_required} baris, tersedia {len(df)}."
    }))
    sys.exit(1)

# ── 5. Train / Test split 80% / 20% ─────────────────────────────────────────
split_idx = int(len(df) * 0.8)
train = df['y'].iloc[:split_idx]
test  = df['y'].iloc[split_idx:]

# ── 6. Fit Holt-Winters pada data train → evaluasi ───────────────────────────
model_train = ExponentialSmoothing(
    train,
    trend=trend_type,
    seasonal=seasonal_type,
    seasonal_periods=seasonal_periods if seasonal_type else None,
    damped_trend=(damped if trend_type else False),
    initialization_method='estimated',
)
fitted_train = model_train.fit(optimized=True, use_brute=True)
test_pred    = fitted_train.forecast(len(test))

mae  = float(np.mean(np.abs(test.values - test_pred.values)))
rmse = float(np.sqrt(np.mean((test.values - test_pred.values) ** 2)))
mask = test.values != 0
mape = float(np.mean(np.abs(
    (test.values[mask] - test_pred.values[mask]) / test.values[mask]
)) * 100) if mask.any() else 0.0

# ── 7. Fit ulang pada SEMUA data → forecast ke depan ────────────────────────
model_full = ExponentialSmoothing(
    df['y'],
    trend=trend_type,
    seasonal=seasonal_type,
    seasonal_periods=seasonal_periods if seasonal_type else None,
    damped_trend=(damped if trend_type else False),
    initialization_method='estimated',
)
full_fitted   = model_full.fit(optimized=True, use_brute=True)
forecast_vals = full_fitted.forecast(steps)

# Confidence interval ±1.96 * std_residual * sqrt(h)
residual_std  = float(full_fitted.resid.std())
z = 1.96
last_date     = df.index[-1]

forecast_list = []
for i, (val) in enumerate(forecast_vals):
    ci = z * residual_std * (i + 1) ** 0.5
    ds = last_date + timedelta(days=i + 1)
    forecast_list.append({
        "date":            ds.strftime('%Y-%m-%d'),
        "predicted_price": round(float(val), 2),       # sesuai field di Prediction model
        "lower":           round(float(val) - ci, 2),
        "upper":           round(float(val) + ci, 2),
    })

# ── 8. Tulis output JSON ─────────────────────────────────────────────────────
os.makedirs(os.path.dirname(output_file), exist_ok=True)

output = {
    "commodity_id":     str(commodity_id),
    "steps":            steps,
    "trend":            trend_type or "none",
    "seasonal":         seasonal_type or "none",
    "seasonal_periods": seasonal_periods,
    "damped":           damped,
    "mae":              round(mae,  4),
    "rmse":             round(rmse, 4),
    "mape":             round(mape, 4),
    "alpha":            round(float(full_fitted.params.get('smoothing_level', 0)), 4),
    "beta":             round(float(full_fitted.params.get('smoothing_trend', 0)), 4),
    "gamma":            round(float(full_fitted.params.get('smoothing_seasonal', 0)), 4),
    "generated_at":     datetime.now().isoformat(),
    "forecast":         forecast_list,
}

with open(output_file, 'w') as f:
    json.dump(output, f, indent=2)

# Cetak ringkasan (dibaca Laravel untuk log)
print(json.dumps({
    "ok":   True,
    "mae":  output["mae"],
    "rmse": output["rmse"],
    "mape": output["mape"],
}))

sys.exit(0)