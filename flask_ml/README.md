# PanganWatch Jember 🌾
Prediksi Harga Bahan Pokok — Flask + MongoDB + Holt-Winters

## Struktur MongoDB
```
monitoring_harga_pangan/
├── price_histories    ← data harga harian (sumber utama)
├── commodities        ← master komoditas
├── categories         ← master kategori
├── predictions        ← cache hasil prediksi (auto-fill oleh app)
├── simulations        ← riwayat simulasi user (auto-fill oleh app)
└── users              ← akun login
```

## Struktur Dokumen price_histories
```json
{
  "commodity_name": "Beras Premium",
  "category": "BERAS",
  "date": ISODate("2020-01-01"),
  "satuan": "kg",
  "harga_lama": 14876,
  "harga_sekarang": 14850,
  "selisih": -26,
  "persen": -0.18,
  "source": "siskaperbapo.jatimprov.go.id"
}
```

## Setup & Jalankan
```bash
# 1. Clone / extract project
cd pangan_app

# 2. Install dependencies
pip install -r requirements.txt

# 3. Konfigurasi .env
cp .env.example .env
# Edit .env: isi MONGO_URI dan DB_NAME

# 4. Buat akun admin pertama
# Jalankan app dulu, lalu:
curl -X POST http://localhost:5000/api/seed_admin \
  -H "Content-Type: application/json" \
  -d '{"username":"admin","password":"admin123","nama":"Administrator"}'
# HAPUS route /api/seed_admin dari app.py setelah ini!

# 5. Jalankan
python app.py
# → http://localhost:5000
```

## API Endpoints
| Method | Endpoint | Auth | Keterangan |
|---|---|---|---|
| GET | `/` | — | Landing page |
| GET | `/login` | — | Halaman login/register |
| GET | `/admin` | Admin | Dashboard admin |
| GET | `/user` | User | Halaman rekomendasi |
| POST | `/api/auth/login` | — | Login |
| POST | `/api/auth/register` | — | Register user baru |
| POST | `/api/auth/logout` | — | Logout |
| GET | `/api/auth/me` | — | Cek sesi login |
| GET | `/api/komoditas` | — | List komoditas |
| GET | `/api/categories` | — | List kategori + komoditas |
| GET | `/api/historis/<nama>?days=90` | Login | Data historis |
| GET | `/api/prediksi/<nama>?steps=30&cache=1` | Login | Forecast + akurasi |
| POST | `/api/rekomendasi` | Login | Rekomendasi beli |
| GET | `/api/dashboard` | Admin | Ringkasan semua komoditas |
| GET | `/api/admin/stats` | Admin | Stats total DB |
| GET | `/api/admin/price_histories` | Admin | Browse raw data |
| GET | `/api/simulasi/riwayat` | Login | Riwayat simulasi user |

## Logika Prediksi
- **Model**: Holt-Winters Exponential Smoothing (trend=add, seasonal=add, period=7)
- **Validasi**: Walk-forward 80/20 split
- **Cache**: Hasil prediksi disimpan di koleksi `predictions`, dipakai ulang selama < 24 jam
- **Fallback**: Jika data < 30 hari, gunakan linear trend

## Logika Rekomendasi
**Skor Tunggu (0–100)**:
- `≤ 35` → 🛒 **BELI SEKARANG**
- `36–55` → ⚡ **BELI SEGERA**
- `56–70` → ⏳ **TUNGGU DULU**
- `> 70` → 📉 **TUNDA PEMBELIAN**

Faktor: delta harga 7 hari, vs rata-rata 30 hari, volatilitas

## Production Deploy
```bash
# Gunicorn
gunicorn -w 4 -b 0.0.0.0:5000 app:app

# Atau dengan systemd / Docker
```
