# SIMOPANG — Sistem Monitoring & Prediksi Pangan 🌾📈

**SIMOPANG** adalah sistem monitoring dan prediksi harga bahan pangan pokok untuk Kabupaten Jember, dirancang untuk membantu masyarakat merencanakan belanja lebih cerdas berdasarkan:

- **Prediksi harga komoditas** menggunakan model time-series Holt-Winters
- **Evaluasi akurasi prediksi** dengan metrik MAE, MAPE, dan RMSE
- **Rekomendasi beli / tunda** berbasis rule-based scoring
- **Notifikasi real-time** saat prediksi baru tersedia
- Integrasi penuh antara **Flutter App**, **Laravel Backend**, dan **Flask ML Service**

![Laravel](https://img.shields.io/badge/Laravel-%5E12.x-red?logo=laravel)
![PHP](https://img.shields.io/badge/PHP-%5E8.2-blue?logo=php)
![Flutter](https://img.shields.io/badge/Flutter-%5E3.x-02569B?logo=flutter)
![Python](https://img.shields.io/badge/Python-%5E3.10-yellow?logo=python)
![Flask](https://img.shields.io/badge/Flask-3.x-black?logo=flask)
![MongoDB](https://img.shields.io/badge/MongoDB-Database-green?logo=mongodb)
![Repository](https://img.shields.io/badge/Repository-GitHub-black?logo=github)
![License](https://img.shields.io/badge/License-MIT-brightgreen)

---

## 📖 Tentang Projek

**SIMOPANG** adalah monorepo yang terdiri dari tiga komponen utama:

1. `flutter_app` — Aplikasi mobile Flutter sebagai antarmuka pengguna untuk memantau harga, melihat prediksi, simulasi belanja, dan menerima notifikasi.
2. `laravel_backend` — Backend Laravel sebagai API gateway, manajemen autentikasi, admin panel, dan penghubung ke Flask ML Service.
3. `flask_ml` — Server Flask (Python) yang menjalankan model **Holt-Winters Exponential Smoothing** untuk prediksi harga komoditas.

---

## ✨ Fitur Utama

- 📊 **Dashboard Harga**: Visualisasi harga komoditas terkini secara real-time.
- 🔮 **Prediksi Harga**: Forecast harga 7 / 14 / 30 / 60 / 90 hari ke depan.
- 📉 **Evaluasi Akurasi**: Menampilkan MAE, MAPE, dan RMSE hasil walk-forward split.
- 🛒 **Simulasi Belanja**: Estimasi biaya belanja berdasarkan prediksi harga.
- 🔔 **Notifikasi Real-time**: Push notification saat admin membuat prediksi baru.
- 🤖 **SimoBot (AI Chat)**: Asisten berbasis Groq LLaMA 3.3-70B untuk rekomendasi belanja.
- 📋 **Statistik Komoditas**: Grafik historis dan analisis tren harga.
- 🔐 **Autentikasi**: Sistem login aman berbasis JWT untuk pengguna dan admin.

---

## 🛠️ Tech Stack

| Layer       | Teknologi                                      |
|-------------|------------------------------------------------|
| Mobile      | Flutter 3.x (Dart)                             |
| Backend     | Laravel 12 (PHP 8.2)                           |
| Database    | MongoDB via `mongodb/laravel-mongodb`          |
| ML Service  | Flask 3.x + statsmodels (Holt-Winters)         |
| AI Chat     | Groq API (LLaMA 3.3-70B)                       |
| Auth        | JWT (`tymon/jwt-auth`)                         |
| HTTP Client | Dio (Flutter), Laravel `Http::` facade         |
| Language    | PHP ^8.2, Python ^3.10, Dart ^3.x              |

---

## 📁 Struktur Projek (Monorepo)

```
Projek4/                              ← Root repository ini
├── flutter_app/                      ← Aplikasi Mobile Flutter
│   ├── assets/images/
│   │   └── LOGO-2.png
│   ├── lib/
│   │   ├── models/                   # Data models
│   │   │   ├── commodity_model.dart
│   │   │   ├── market_model.dart
│   │   │   ├── price_latest_model.dart
│   │   │   ├── price_model.dart
│   │   │   └── user_model.dart
│   │   ├── providers/                # State management
│   │   │   ├── auth_provider.dart
│   │   │   ├── commodity_provider.dart
│   │   │   ├── price_provider.dart
│   │   │   └── theme_provider.dart
│   │   ├── screens/
│   │   │   ├── auth/                 # Autentikasi
│   │   │   │   ├── forgot_password_screen.dart
│   │   │   │   ├── login_screen.dart
│   │   │   │   ├── otp_verification_screen.dart
│   │   │   │   ├── register_screen.dart
│   │   │   │   └── reset_password_screen.dart
│   │   │   └── User/                 # Halaman utama user
│   │   │       ├── change_password_screen.dart
│   │   │       ├── chat_ai_screen.dart
│   │   │       ├── commodity_detail_screen.dart
│   │   │       ├── home_screen.dart
│   │   │       ├── main_screen.dart
│   │   │       ├── notification_screen.dart
│   │   │       ├── prediction_screen.dart
│   │   │       ├── profile_screen.dart
│   │   │       ├── settings_screen.dart
│   │   │       ├── simulation_screen.dart
│   │   │       └── statistics_screen.dart
│   │   ├── services/                 # API & storage service
│   │   │   ├── api_service.dart
│   │   │   ├── auth_service.dart
│   │   │   └── storage_service.dart
│   │   ├── widgets/                  # Reusable widgets
│   │   │   ├── commodity_card.dart
│   │   │   ├── custom_app_bar.dart
│   │   │   ├── loading_widget.dart
│   │   │   └── market_card.dart
│   │   ├── main.dart
│   │   └── router.dart
│   ├── .env
│   └── pubspec.yaml
│
├── laravel_backend/                  ← Backend Laravel (API + Admin Web)
│   ├── app/
│   │   ├── Console/
│   │   │   └── Commands/
│   │   │       └── SendweeklysimulationReminder.php
│   │   ├── Http/
│   │   │   ├── Controllers/
│   │   │   │   ├── Api/              # Controller API untuk Flutter
│   │   │   │   │   ├── AuthController.php
│   │   │   │   │   ├── CategoryController.php
│   │   │   │   │   ├── CommodityController.php
│   │   │   │   │   ├── PriceLatestController.php
│   │   │   │   │   └── StatisticsController.php
│   │   │   │   └── Web/              # Controller admin & user web
│   │   │   │       ├── AdminApiStatusController.php
│   │   │   │       ├── AdminController.php
│   │   │   │       ├── AuthController.php
│   │   │   │       ├── HargaController.php
│   │   │   │       ├── KomoditasController.php
│   │   │   │       ├── NotificationController.php
│   │   │   │       ├── PrediksiController.php
│   │   │   │       ├── SettingsController.php
│   │   │   │       ├── UserChatAiController.php
│   │   │   │       ├── UserController.php
│   │   │   │       ├── UserHargaController.php
│   │   │   │       ├── UserPrediksiController.php
│   │   │   │       ├── UserProfilController.php
│   │   │   │       └── UserSimulasiController.php
│   │   │   └── Middleware/
│   │   │       ├── ApiRoleMiddleware.php
│   │   │       └── RoleMiddleware.php
│   │   ├── Models/
│   │   │   ├── Category.php
│   │   │   ├── commodity.php
│   │   │   ├── Notification.php
│   │   │   ├── Prediction.php
│   │   │   ├── PriceHistory.php
│   │   │   ├── Simulation.php
│   │   │   └── User.php
│   │   └── Services/
│   │       ├── NotificationService.php
│   │       └── PrediksiService.php
│   ├── config/
│   │   ├── jwt.php
│   │   └── services.php              # Konfigurasi Flask URL & key
│   ├── database/
│   │   ├── seeders/
│   │   │   ├── CommoditySeeder.php
│   │   │   ├── DatabaseSeeder.php
│   │   │   └── PriceHistorySeeder.php
│   ├── resources/
│   │   ├── css/                      # Stylesheet
│   │   ├── js/                       # JS assets
│   │   └── views/
│   │       ├── admin/                # Halaman admin (Blade)
│   │       │   ├── prediksi/
│   │       │   ├── api-status.blade.php
│   │       │   ├── dashboard.blade.php
│   │       │   ├── harga.blade.php
│   │       │   ├── komoditas.blade.php
│   │       │   └── prediksi.blade.php
│   │       ├── auth/                 # Login, register, forgot password
│   │       ├── components/
│   │       ├── layouts/
│   │       └── user/                 # Halaman user (Blade)
│   │           ├── pdf/laporan.blade.php
│   │           ├── chatai.blade.php
│   │           ├── harga.blade.php
│   │           ├── prediksi.blade.php
│   │           ├── simulasi.blade.php
│   │           └── profil.blade.php
│   ├── routes/
│   │   ├── api.php                   # API routes (Flutter)
│   │   ├── console.php
│   │   └── web.php                   # Web routes (admin & user panel)
│   ├── .env.example
│   └── vite.config.js
│
├── flask_ml/                         ← Flask ML Service (Prediksi)
│   ├── data/raw/                     # Data mentah
│   ├── models/                       # Model terlatih
│   ├── routes/                       # Blueprint routes Flask
│   ├── static/                       # Static files
│   ├── templates/                    # Template HTML Flask
│   ├── app.py                        # Entry point Flask
│   ├── test_app.py                   # Unit test Flask
│   ├── scraping.ipynb                # Scraping harga dari SISKAPERBAPO
│   ├── cleaning_pangan_jember.ipynb  # Data cleaning & preparation
│   ├── pangan_makanan_jember_2021_2025.csv  # Dataset hasil scraping
│   ├── requirements.txt
│   └── .env.example
│
└── README.md
```

---

## 🔌 Endpoint Penting

### Flask ML Service (`flask_ml/app.py`)

Semua endpoint mewajibkan header: **`X-API-Key`**.

| Method | Endpoint                              | Deskripsi                                  |
|--------|---------------------------------------|--------------------------------------------|
| GET    | `/api/external/komoditas`             | List komoditas yang tersedia               |
| GET    | `/api/external/prediksi/<komoditas>`  | Forecast + akurasi (cache 24 jam)          |
| POST   | `/api/external/rekomendasi`           | Rekomendasi beli/tunda + chart data        |
| POST   | `/api/admin/run_prediksi`             | Hapus cache & regenerasi prediksi          |
| GET    | `/api/admin/prediction_logs`          | Riwayat prediksi untuk tabel admin         |

Parameter `steps` yang didukung: `7 | 14 | 30 | 60 | 90`

### Laravel Backend (`laravel_backend/routes/api.php`)

| Method | Endpoint                       | Deskripsi                          |
|--------|--------------------------------|------------------------------------|
| GET    | `/commodities`                 | Daftar komoditas                   |
| GET    | `/categories`                  | Daftar kategori                    |
| GET    | `/prices/latest`               | Harga terbaru semua komoditas      |
| GET    | `/predictions`                 | Semua prediksi tersimpan           |
| GET    | `/predictions/{komoditas}`     | Prediksi per komoditas             |
| POST   | `/predictions/generate`        | Trigger generate prediksi (admin)  |
| POST   | `/predictions/rekomendasi`     | Rekomendasi belanja                |
| GET    | `/notifications`               | Daftar notifikasi user             |
| GET    | `/notifications/unread-count`  | Jumlah notifikasi belum dibaca     |
| POST   | `/chatai/rekomendasi`          | Chat AI (SimoBot) - rekomendasi    |
| POST   | `/chatai/followup`             | Chat AI (SimoBot) - lanjutan       |

---

## 🚀 Persiapan Menjalankan Projek

Projek ini terdiri dari **tiga service** yang harus dijalankan secara bersamaan. Ikuti panduan di bawah secara berurutan.

### Prasyarat Global

Pastikan semua tools berikut sudah terinstall di sistem kamu:

- **PHP** >= 8.2
- **Composer**
- **Node.js** & NPM
- **MongoDB** (lokal atau MongoDB Atlas)
- **Python** >= 3.10
- **Flutter SDK** >= 3.x
- **Git**

---

### ⚙️ Bagian 1: Laravel Backend

#### Instalasi

```bash
# 1. Clone repositori ini
git clone https://github.com/HandaruAs/Projek4.git
cd Projek4

# 2. Masuk ke folder Laravel
cd laravel_backend

# 3. Install dependensi PHP
composer install

# 4. Salin file environment
cp .env.example .env

# 5. Generate application key
php artisan key:generate

# 6. Generate JWT secret
php artisan jwt:secret
```

#### Konfigurasi `.env`

Buka file `laravel_backend/.env` dan sesuaikan nilai berikut:

```env
# Koneksi MongoDB
DB_CONNECTION=mongodb
DB_HOST=127.0.0.1
DB_PORT=27017
DB_DATABASE=simopang
DB_USERNAME=          # Kosongkan jika tidak pakai autentikasi
DB_PASSWORD=          # Kosongkan jika tidak pakai autentikasi

# Koneksi ke Flask ML Service
FLASK_URL=http://localhost:5001
FLASK_API_KEY=your_secret_api_key_here

# Groq API (SimoBot)
GROQ_API_KEY=your_groq_api_key_here
```

> ⚠️ **Penting:** Nilai `FLASK_API_KEY` di sini **harus sama persis** dengan `FLASK_API_KEY` yang ada di `.env` milik `flask_ml`.

#### Menjalankan Server Laravel

```bash
# Dari dalam folder laravel_backend/
php artisan serve
```

Akses admin panel melalui: `http://localhost:8000`

---

### 🤖 Bagian 2: Flask ML Service (Setup Server)

Service ini adalah server Python yang menjalankan model **Holt-Winters Exponential Smoothing** dan menyediakan endpoint prediksi harga komoditas.

#### Instalasi

```bash
# Dari root repository, masuk ke folder Flask
cd flask_ml

# 1. Buat virtual environment Python
python -m venv venv

# 2. Aktifkan virtual environment
# Windows:
venv\Scripts\activate
# Linux/Mac:
source venv/bin/activate

# 3. Install semua dependensi Python
pip install -r requirements.txt
```

Library utama yang diinstall:

| Library       | Versi  | Fungsi                                          |
|---------------|--------|-------------------------------------------------|
| `Flask`       | 3.x    | Web framework untuk API server                  |
| `statsmodels` | latest | Model Holt-Winters Exponential Smoothing        |
| `pymongo`     | latest | Koneksi ke database MongoDB                     |
| `pandas`      | latest | Manipulasi data time-series                     |
| `numpy`       | latest | Komputasi numerik                               |
| `python-dotenv` | latest | Membaca konfigurasi dari file `.env`           |

#### Konfigurasi `.env`

```bash
# Salin file environment
cp .env.example .env
```

Buka file `flask_ml/.env` dan sesuaikan:

```env
# Koneksi MongoDB (sama dengan yang dipakai Laravel)
MONGO_URI=mongodb://127.0.0.1:27017
DB_NAME=simopang

# API Key untuk keamanan endpoint Flask
# Nilai ini HARUS SAMA dengan FLASK_API_KEY di .env milik laravel_backend
FLASK_API_KEY=your_secret_api_key_here
```

#### Menjalankan Flask Server

Pastikan virtual environment sudah aktif (ada `(venv)` di awal terminal kamu), lalu:

```bash
# Dari dalam folder flask_ml/
python app.py
```

Flask akan berjalan di: `http://localhost:5001`

> 💡 **Tips:** Biarkan terminal Flask tetap berjalan selama kamu menggunakan fitur prediksi di dashboard admin maupun aplikasi Flutter.

---

### 🕷️ Bagian 3: Scraping & Data Preparation

Dua notebook Jupyter ini dijalankan **sekali** untuk mengumpulkan dan membersihkan data historis harga sebelum Flask ML bisa digunakan. File-file ini berada di dalam folder `flask_ml/`.

> ⚠️ **Perhatian:** Scraping membutuhkan waktu **±2–3 jam** untuk mengumpulkan data 5 tahun (2021–2025). Jalankan sekali dan simpan hasilnya.

#### Prasyarat Tambahan

```bash
pip install selenium webdriver-manager beautifulsoup4 pandas numpy
```

Pastikan **Google Chrome** sudah terinstall di sistem kamu (Selenium menggunakan Chrome headless).

---

#### Step 1 — Scraping Data (`scraping.ipynb`)

Notebook ini mengambil data harga bahan pokok dari situs **SISKAPERBAPO Jawa Timur** menggunakan Selenium headless Chrome + BeautifulSoup. Situs membutuhkan JavaScript sehingga tidak bisa menggunakan `requests` biasa.

| Item         | Detail                                                                                |
|--------------|---------------------------------------------------------------------------------------|
| Sumber       | `https://siskaperbapo.jatimprov.go.id/harga/tabel`                                   |
| Wilayah      | Kabupaten Jember                                                                      |
| Periode      | 2021-01-01 s/d 2025-12-31 (per hari)                                                 |
| Metode       | Selenium headless Chrome + BeautifulSoup                                              |
| Delay        | 2 detik per tanggal (anti-block)                                                      |
| Output       | `pangan_makanan_jember_2021_2025.csv`                                                 |
| Kolom output | `tanggal`, `komoditas`, `satuan`, `harga_lama`, `harga_sekarang`, `selisih`, `persen`|
| Estimasi     | ±2–3 jam untuk 5 tahun data (~1826 hari)                                              |

Kategori komoditas yang di-scrape: BERAS, GULA, MINYAK GORENG, DAGING AYAM, DAGING SAPI, TELUR AYAM, IKAN SEGAR, IKAN ASIN, BAWANG, CABAI, KEDELAI, TAHU, TEMPE, TEPUNG TERIGU, JAGUNG, GARAM, KACANG TANAH, SUSU, SAYURAN, BUAH, dan lainnya.

```bash
# Masuk ke folder flask_ml
cd flask_ml

# Install dependensi scraping
pip install selenium webdriver-manager beautifulsoup4

# Jalankan notebook
jupyter notebook scraping.ipynb
```

> 💡 **Tips resume otomatis:** Jika scraping terhenti di tengah jalan (koneksi putus, crash, dll), cukup jalankan ulang — script membaca `progress.txt` dan **otomatis melanjutkan dari tanggal terakhir** tanpa mengulang dari awal.

> ⚠️ **Catatan:** Pastikan **Google Chrome** sudah terinstall. `webdriver-manager` akan mengunduh ChromeDriver yang sesuai secara otomatis.

---

#### Step 2 — Data Cleaning (`cleaning_pangan_jember.ipynb`)

Notebook ini membersihkan CSV hasil scraping sesuai metodologi **CRISP-DM Phase 3: Data Preparation**.

| Langkah | Tindakan                                                  |
|---------|-----------------------------------------------------------|
| 1       | Load & inspeksi awal                                      |
| 2       | Hapus baris kategori (bukan data observasi)              |
| 3       | Hapus duplikat                                            |
| 4       | Konversi kolom `tanggal` ke datetime                      |
| 5       | Konversi kolom `selisih` ke numerik                       |
| 6       | Bersihkan kolom `persen` (format `"0,18%"` → `0.0018`)   |
| 7       | Imputasi `harga_lama` = 0 dengan nilai `harga_sekarang`   |
| 8       | Ganti `harga_sekarang` = 0 dengan `NaN` (anomali)        |
| 9       | Filter & hapus komoditas non-pangan (semen, pupuk, dll)  |
| 10      | Bersihkan prefix `-` dari nama komoditas                  |
| 11      | Simpan hasil ke `pangan_jember_cleanfix.csv`              |

```bash
jupyter notebook cleaning_pangan_jember.ipynb
```

**Output akhir:** `pangan_jember_cleanfix.csv` — dataset siap pakai untuk diimport ke MongoDB `price_histories`.

---

#### Step 3 — Import ke MongoDB

Setelah cleaning selesai, import data ke MongoDB agar bisa dipakai Flask ML:

```python
import pandas as pd
from pymongo import MongoClient

client = MongoClient("mongodb://127.0.0.1:27017")
db = client["simopang"]

df = pd.read_csv("flask_ml/pangan_jember_cleanfix.csv", parse_dates=["tanggal"])
records = df.to_dict("records")

db["price_histories"].insert_many(records)
print(f"✅ {len(records)} baris berhasil diimport ke MongoDB collection 'price_histories'")
```

---

### 📱 Bagian 4: Flutter App

#### Instalasi

```bash
# Dari root repository, masuk ke folder Flutter
cd flutter_app

# Install dependensi
flutter pub get
```

#### Konfigurasi Base URL

Pastikan `BASE_URL` di dalam kode menunjuk ke Laravel backend:

```dart
// lib/services/api_service.dart (atau sesuai struktur kamu)
const String baseUrl = 'http://10.0.2.2:8000/api'; // Android Emulator
// const String baseUrl = 'http://localhost:8000/api'; // iOS Simulator / Web
```

#### Menjalankan Flutter App

```bash
# Dari dalam folder flutter_app/
flutter run
```

---

## 🔄 Alur Kerja Sistem

```
[Flutter App]  ──── request prediksi ────►  [Laravel Backend]
                                                    │
                                          cek cache MongoDB
                                                    │
                                    (cache miss) panggil Flask
                                                    ▼
                                          [Flask ML Service]
                                                    │
                                      ambil data price_histories
                                                    │
                                     jalankan Holt-Winters model
                                                    │
                                      simpan hasil ke predictions
                                                    ▼
                                          [Laravel Backend]
                                                    │
                                    kirim notifikasi ke pengguna
                                                    ▼
                                          [Flutter App]
                                                    │
                              tampilkan chart prediksi + badge notifikasi
```

---

## 🧠 Model & Evaluasi (Flask ML)

- **Model utama:** Holt-Winters Triple Exponential Smoothing (auto-config: trend + seasonal)
- **Fallback:** Regresi linear (`numpy.polyfit`) jika data tidak cukup
- **Metodologi:** CRISP-DM
- **Split evaluasi:** Walk-forward validation
- **Metrik:** MAE, MAPE (robust), RMSE
- **`steps` yang didukung:** 7 / 14 / 30 / 60 / 90
- **Caching:** Hasil prediksi di-cache 24 jam di MongoDB collection `predictions`

Collection MongoDB yang dipakai:

| Collection        | Keterangan                        |
|-------------------|-----------------------------------|
| `price_histories` | Data historis harga komoditas     |
| `predictions`     | Cache hasil prediksi              |
| `users`           | Data pengguna                     |
| `notifications`   | Notifikasi per pengguna           |

---

## 🔧 Troubleshooting Cepat

**1. 401 Unauthorized (Flask)**
> Pastikan `FLASK_API_KEY` di `laravel_backend/.env` sama persis dengan `FLASK_API_KEY` di `flask_ml/.env`.

**2. Data prediksi kosong / error**
> Pastikan collection `price_histories` untuk `commodity_name` ada di MongoDB dan field `date` bertipe valid.

**3. Flutter tidak connect ke Laravel**
> Gunakan `10.0.2.2` (bukan `localhost`) saat menjalankan di Android Emulator. Pastikan Laravel berjalan.

**4. Badge notifikasi tidak update**
> Cek polling interval di `NotificationApiService`. Pastikan endpoint `/notifications/unread-count` bisa diakses dan token JWT valid.

**5. SimoBot tidak merespons**
> Pastikan `GROQ_API_KEY` sudah diset di `laravel_backend/.env` dan valid.

---

## 📄 Lisensi

MIT License — lihat file [LICENSE](LICENSE) untuk detail.
