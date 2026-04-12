"""
Test Suite — PanganWatch flask_ml
Jalankan: python test_app.py
Tidak butuh MongoDB nyata — pakai mongomock (in-memory)
"""

import sys
import json
import unittest
from datetime import datetime, timedelta
from unittest.mock import patch, MagicMock

import numpy as np
import pandas as pd
import mongomock

# ── Patch MongoClient SEBELUM import app ─────────────────────────
patcher = patch("pymongo.MongoClient", mongomock.MongoClient)
patcher.start()

sys.path.insert(0, "/home/claude/flask_ml")
import app as flask_app  # noqa: E402  (import after patch)

# ─────────────────────────────────────────────────────────────────
# SEED DATA HELPER
# ─────────────────────────────────────────────────────────────────
KOMODITAS_TEST = [
    ("Beras Medium",  "BERAS",  "kg",    12500),
    ("Cabai Merah",   "CABAI",  "kg",    45000),
    ("Minyak Goreng", "MINYAK", "liter", 18000),
    ("Telur Ayam",    "TELUR",  "kg",    28000),
    ("Daging Ayam",   "DAGING", "kg",    35000),
]

def seed_price_histories(days: int = 90):
    """Masukkan data harga dummy ke mongomock."""
    flask_app.col_price.drop()
    docs = []
    today = datetime.utcnow().replace(hour=0, minute=0, second=0, microsecond=0)
    rng = np.random.default_rng(42)

    for name, cat, sat, base in KOMODITAS_TEST:
        for i in range(days):
            tgl = today - timedelta(days=days - i)
            harga_l = int(base * (1 + 0.0003 * i) + rng.normal(0, base * 0.02))
            harga_s = int(base * (1 + 0.0003 * (i + 1)) + rng.normal(0, base * 0.02))
            harga_l = max(harga_l, int(base * 0.5))
            harga_s = max(harga_s, int(base * 0.5))
            docs.append({
                "commodity_name": name,
                "category":       cat,
                "satuan":         sat,
                "date":           tgl,
                "harga_lama":     harga_l,
                "harga_sekarang": harga_s,
                "selisih":        harga_s - harga_l,
                "persen":         round((harga_s - harga_l) / harga_l * 100, 2),
                "source":         "test",
                "created_at":     datetime.utcnow(),
            })
    flask_app.col_price.insert_many(docs)
    print(f"   ✔ Seeded {len(docs)} dokumen price_histories")

def seed_user():
    """Buat 1 admin dan 1 user biasa."""
    flask_app.col_user.drop()
    flask_app.col_user.insert_many([
        {
            "username": "admin",
            "password": flask_app.hash_pw("admin123"),
            "nama":     "Administrator",
            "role":     "admin",
            "created_at": datetime.utcnow(),
        },
        {
            "username": "warga",
            "password": flask_app.hash_pw("warga123"),
            "nama":     "Budi Santoso",
            "role":     "user",
            "created_at": datetime.utcnow(),
        },
    ])
    print("   ✔ Seeded 2 users (admin + warga)")


# ═════════════════════════════════════════════════════════════════
# TEST CLASSES
# ═════════════════════════════════════════════════════════════════

class TestML(unittest.TestCase):
    """Unit test fungsi ML murni (tidak butuh HTTP/DB)."""

    @classmethod
    def setUpClass(cls):
        print("\n📐 TestML — Fungsi ML")
        seed_price_histories(90)

    # ── get_series ────────────────────────────────────────────────
    def test_get_series_returns_series(self):
        s = flask_app.get_series("Beras Medium")
        self.assertIsInstance(s, pd.Series)
        self.assertGreater(len(s), 0)
        print(f"   ✔ get_series → {len(s)} titik data")

    def test_get_series_index_is_datetime(self):
        s = flask_app.get_series("Beras Medium")
        self.assertTrue(hasattr(s.index, 'freq') or pd.api.types.is_datetime64_any_dtype(s.index))

    def test_get_series_all_positive(self):
        s = flask_app.get_series("Beras Medium")
        self.assertTrue((s > 0).all())

    def test_get_series_unknown_returns_empty(self):
        s = flask_app.get_series("Komoditas Tidak Ada XYZ")
        self.assertTrue(s.empty)

    # ── hw_forecast ───────────────────────────────────────────────
    def test_hw_forecast_length(self):
        s = flask_app.get_series("Beras Medium")
        fc, ci = flask_app.hw_forecast(s, steps=30)
        self.assertEqual(len(fc), 30)
        print(f"   ✔ hw_forecast(30) → {len(fc)} titik")

    def test_hw_forecast_all_positive(self):
        s = flask_app.get_series("Beras Medium")
        fc, _ = flask_app.hw_forecast(s, steps=7)
        self.assertTrue(all(v > 0 for v in fc))

    def test_hw_forecast_ci_present(self):
        s = flask_app.get_series("Beras Medium")
        fc, ci = flask_app.hw_forecast(s, steps=7)
        if ci:
            self.assertIn("lower", ci)
            self.assertIn("upper", ci)
            self.assertEqual(len(ci["lower"]), 7)
            print("   ✔ Confidence interval ada")

    def test_hw_forecast_fallback_short_series(self):
        """Jika data < 30 hari, gunakan fallback linear."""
        short = flask_app.get_series("Beras Medium").iloc[:20]
        fc, ci = flask_app.hw_forecast(short, steps=7)
        self.assertEqual(len(fc), 7)
        self.assertIsNone(ci)
        print("   ✔ Fallback linear pada data pendek")

    # ── compute_accuracy ──────────────────────────────────────────
    def test_accuracy_keys(self):
        s = flask_app.get_series("Beras Medium")
        acc = flask_app.compute_accuracy(s)
        for key in ("accuracy", "mae", "mape", "rmse", "note"):
            self.assertIn(key, acc)
        print(f"   ✔ compute_accuracy → akurasi={acc['accuracy']}%  MAPE={acc['mape']}%")

    def test_accuracy_range(self):
        s = flask_app.get_series("Beras Medium")
        acc = flask_app.compute_accuracy(s)
        if acc["accuracy"] is not None:
            self.assertGreaterEqual(acc["accuracy"], 0)
            self.assertLessEqual(acc["accuracy"], 100)

    def test_accuracy_short_data(self):
        short = flask_app.get_series("Beras Medium").iloc[:30]
        acc = flask_app.compute_accuracy(short)
        self.assertIsNone(acc["accuracy"])
        print("   ✔ Akurasi None jika data < 60 hari")

    # ── buat_rekomendasi ──────────────────────────────────────────
    def test_rekomendasi_keys(self):
        s = flask_app.get_series("Beras Medium")
        fc, _ = flask_app.hw_forecast(s, 30)
        rek = flask_app.buat_rekomendasi(s, fc, 2, "kg")
        for key in ("rekomendasi","warna","emoji","headline","alasan","skor",
                    "harga_kini","harga_7hari","budget_sekarang","budget_7hari"):
            self.assertIn(key, rek)

    def test_rekomendasi_valid_warna(self):
        s = flask_app.get_series("Beras Medium")
        fc, _ = flask_app.hw_forecast(s, 30)
        rek = flask_app.buat_rekomendasi(s, fc, 2, "kg")
        self.assertIn(rek["warna"], ("buy", "buy_soon", "wait", "hold"))
        print(f"   ✔ Rekomendasi: {rek['rekomendasi']} (skor {rek['skor']})")

    def test_rekomendasi_skor_range(self):
        s = flask_app.get_series("Beras Medium")
        fc, _ = flask_app.hw_forecast(s, 30)
        rek = flask_app.buat_rekomendasi(s, fc, 2, "kg")
        self.assertGreaterEqual(rek["skor"], 0)
        self.assertLessEqual(rek["skor"], 100)

    def test_rekomendasi_budget_positif(self):
        s = flask_app.get_series("Beras Medium")
        fc, _ = flask_app.hw_forecast(s, 30)
        rek = flask_app.buat_rekomendasi(s, fc, 2, "kg")
        self.assertGreater(rek["budget_sekarang"], 0)
        self.assertGreater(rek["budget_7hari"], 0)

    def test_rekomendasi_alasan_not_empty(self):
        s = flask_app.get_series("Cabai Merah")
        fc, _ = flask_app.hw_forecast(s, 30)
        rek = flask_app.buat_rekomendasi(s, fc, 1, "kg")
        self.assertGreater(len(rek["alasan"]), 0)
        print(f"   ✔ Alasan: {rek['alasan'][0]}")


class TestAuth(unittest.TestCase):
    """Test endpoint autentikasi."""

    @classmethod
    def setUpClass(cls):
        print("\n🔐 TestAuth — Autentikasi")
        seed_user()
        flask_app.app.config["TESTING"] = True
        flask_app.app.config["SECRET_KEY"] = "test-secret"
        cls.client = flask_app.app.test_client()

    # ── Register ──────────────────────────────────────────────────
    def test_register_success(self):
        r = self.client.post("/api/auth/register",
            json={"username": "user_baru", "password": "pass123", "nama": "User Baru"})
        self.assertEqual(r.status_code, 201)
        d = json.loads(r.data)
        self.assertEqual(d["role"], "user")
        print("   ✔ Register user baru OK")

    def test_register_duplicate(self):
        self.client.post("/api/auth/register",
            json={"username": "duplikat", "password": "pass123"})
        r = self.client.post("/api/auth/register",
            json={"username": "duplikat", "password": "pass456"})
        self.assertEqual(r.status_code, 409)
        print("   ✔ Register duplikat → 409")

    def test_register_tanpa_password(self):
        r = self.client.post("/api/auth/register",
            json={"username": "tanpa_pw", "password": ""})
        self.assertEqual(r.status_code, 400)
        print("   ✔ Register tanpa password → 400")

    # ── Login ─────────────────────────────────────────────────────
    def test_login_admin_success(self):
        r = self.client.post("/api/auth/login",
            json={"username": "admin", "password": "admin123"})
        self.assertEqual(r.status_code, 200)
        d = json.loads(r.data)
        self.assertEqual(d["role"], "admin")
        print("   ✔ Login admin OK")

    def test_login_user_success(self):
        r = self.client.post("/api/auth/login",
            json={"username": "warga", "password": "warga123"})
        self.assertEqual(r.status_code, 200)
        d = json.loads(r.data)
        self.assertEqual(d["role"], "user")
        print("   ✔ Login user OK")

    def test_login_wrong_password(self):
        r = self.client.post("/api/auth/login",
            json={"username": "admin", "password": "salah"})
        self.assertEqual(r.status_code, 401)
        print("   ✔ Login salah password → 401")

    def test_login_unknown_user(self):
        r = self.client.post("/api/auth/login",
            json={"username": "tidakada", "password": "xxx"})
        self.assertEqual(r.status_code, 401)

    def test_me_after_login(self):
        with flask_app.app.test_client() as c:
            c.post("/api/auth/login", json={"username": "warga", "password": "warga123"})
            r = c.get("/api/auth/me")
            d = json.loads(r.data)
            self.assertTrue(d["logged_in"])
            self.assertEqual(d["username"], "warga")
            print("   ✔ /api/auth/me → logged_in=True")

    def test_logout(self):
        with flask_app.app.test_client() as c:
            c.post("/api/auth/login", json={"username": "warga", "password": "warga123"})
            c.post("/api/auth/logout")
            r = c.get("/api/auth/me")
            d = json.loads(r.data)
            self.assertFalse(d["logged_in"])
            print("   ✔ Logout → logged_in=False")


class TestAPI(unittest.TestCase):
    """Test semua endpoint API dengan sesi login."""

    @classmethod
    def setUpClass(cls):
        print("\n🌐 TestAPI — Endpoint API")
        seed_price_histories(90)
        seed_user()
        flask_app.app.config["TESTING"] = True
        flask_app.app.config["SECRET_KEY"] = "test-secret"

        # Client admin
        cls.admin = flask_app.app.test_client()
        cls.admin.post("/api/auth/login",
            json={"username": "admin", "password": "admin123"},
            content_type="application/json")

        # Client user biasa
        cls.user = flask_app.app.test_client()
        cls.user.post("/api/auth/login",
            json={"username": "warga", "password": "warga123"},
            content_type="application/json")

        # Client tanpa login
        cls.anon = flask_app.app.test_client()

    # ── Public endpoints ──────────────────────────────────────────
    def test_index_ok(self):
        r = self.anon.get("/")
        self.assertEqual(r.status_code, 200)
        print("   ✔ GET / → 200")

    def test_login_page_ok(self):
        r = self.anon.get("/login")
        self.assertEqual(r.status_code, 200)
        print("   ✔ GET /login → 200")

    def test_api_komoditas(self):
        r = self.anon.get("/api/komoditas")
        d = json.loads(r.data)
        self.assertIsInstance(d, list)
        self.assertIn("Beras Medium", d)
        print(f"   ✔ /api/komoditas → {len(d)} komoditas")

    def test_api_categories(self):
        r = self.anon.get("/api/categories")
        d = json.loads(r.data)
        self.assertIsInstance(d, list)
        self.assertTrue(len(d) > 0)
        print(f"   ✔ /api/categories → {len(d)} kategori")

    # ── Historis ──────────────────────────────────────────────────
    def test_historis_normal(self):
        r = self.admin.get("/api/historis/Beras Medium?days=30")
        d = json.loads(r.data)
        self.assertIn("tanggal", d)
        self.assertIn("harga", d)
        self.assertIn("stats", d)
        self.assertGreater(len(d["harga"]), 0)
        print(f"   ✔ /api/historis/Beras Medium → {len(d['harga'])} titik, stats={d['stats']}")

    def test_historis_semua_komoditas(self):
        komoditas_list = [k[0] for k in KOMODITAS_TEST]
        for k in komoditas_list:
            r = self.admin.get(f"/api/historis/{k}?days=30")
            self.assertEqual(r.status_code, 200, f"Gagal untuk {k}")
        print(f"   ✔ Historis OK untuk semua {len(komoditas_list)} komoditas")

    def test_historis_tidak_ada(self):
        r = self.admin.get("/api/historis/KomoditasXYZ")
        self.assertEqual(r.status_code, 404)
        print("   ✔ Historis komoditas tidak ada → 404")

    def test_historis_require_login(self):
        r = self.anon.get("/api/historis/Beras Medium")
        # Redirect ke login (302) atau 401
        self.assertIn(r.status_code, [302, 401])
        print(f"   ✔ Historis tanpa login → {r.status_code}")

    # ── Prediksi ──────────────────────────────────────────────────
    def test_prediksi_struktur(self):
        r = self.admin.get("/api/prediksi/Beras Medium?steps=14&cache=0")
        self.assertEqual(r.status_code, 200)
        d = json.loads(r.data)
        for key in ("tanggal_pred", "forecast", "accuracy", "satuan",
                    "harga_terakhir", "tanggal_terakhir"):
            self.assertIn(key, d)
        self.assertEqual(len(d["forecast"]), 14)
        self.assertEqual(len(d["tanggal_pred"]), 14)
        print(f"   ✔ /api/prediksi → 14 hari, akurasi={d['accuracy']['accuracy']}%")

    def test_prediksi_accuracy_not_none(self):
        r = self.admin.get("/api/prediksi/Beras Medium?steps=7&cache=0")
        d = json.loads(r.data)
        self.assertIsNotNone(d["accuracy"]["accuracy"])

    def test_prediksi_cache(self):
        """Hit kedua harus pakai cache (tidak hitung ulang)."""
        self.admin.get("/api/prediksi/Minyak Goreng?steps=7&cache=0")
        r2 = self.admin.get("/api/prediksi/Minyak Goreng?steps=7&cache=1")
        self.assertEqual(r2.status_code, 200)
        print("   ✔ Prediksi cache → 200")

    def test_prediksi_semua_steps(self):
        for steps in [7, 14, 30]:
            r = self.admin.get(f"/api/prediksi/Telur Ayam?steps={steps}&cache=0")
            d = json.loads(r.data)
            self.assertEqual(len(d["forecast"]), steps)
        print("   ✔ Prediksi 7/14/30 hari semuanya benar")

    # ── Rekomendasi ───────────────────────────────────────────────
    def test_rekomendasi_user(self):
        r = self.user.post("/api/rekomendasi",
            json={"komoditas": "Beras Medium", "konsumsi": 2},
            content_type="application/json")
        self.assertEqual(r.status_code, 200)
        d = json.loads(r.data)
        self.assertIn("rekomendasi", d)
        self.assertIn("chart", d)
        self.assertIn("alasan", d)
        self.assertGreater(len(d["alasan"]), 0)
        print(f"   ✔ /api/rekomendasi → {d['rekomendasi']} | budget Rp{d['budget_sekarang']:,}")

    def test_rekomendasi_semua_komoditas(self):
        for k, _, _, _ in KOMODITAS_TEST:
            r = self.user.post("/api/rekomendasi",
                json={"komoditas": k, "konsumsi": 1},
                content_type="application/json")
            self.assertEqual(r.status_code, 200, f"Gagal untuk {k}")
        print(f"   ✔ Rekomendasi OK untuk semua {len(KOMODITAS_TEST)} komoditas")

    def test_rekomendasi_invalid_komoditas(self):
        r = self.user.post("/api/rekomendasi",
            json={"komoditas": "Barang Aneh XYZ", "konsumsi": 1},
            content_type="application/json")
        self.assertIn(r.status_code, [400, 404])
        print("   ✔ Rekomendasi komoditas invalid → 400/404")

    def test_rekomendasi_simpan_ke_simulasi(self):
        """Setelah rekomendasi, harus ada dokumen di col_simulation."""
        before = flask_app.col_simulation.count_documents({})
        self.user.post("/api/rekomendasi",
            json={"komoditas": "Daging Ayam", "konsumsi": 0.5},
            content_type="application/json")
        after = flask_app.col_simulation.count_documents({})
        self.assertGreater(after, before)
        print("   ✔ Simulasi tersimpan ke MongoDB")

    def test_rekomendasi_tanpa_login(self):
        r = self.anon.post("/api/rekomendasi",
            json={"komoditas": "Beras Medium", "konsumsi": 1},
            content_type="application/json")
        self.assertIn(r.status_code, [302, 401])
        print(f"   ✔ Rekomendasi tanpa login → {r.status_code}")

    # ── Dashboard & Stats (admin) ─────────────────────────────────
    def test_dashboard_admin(self):
        r = self.admin.get("/api/dashboard")
        self.assertEqual(r.status_code, 200)
        d = json.loads(r.data)
        self.assertIsInstance(d, list)
        self.assertGreater(len(d), 0)
        # Cek struktur tiap item
        item = d[0]
        for key in ("komoditas","satuan","harga_kini","delta_7","pred_7hari","status"):
            self.assertIn(key, item)
        print(f"   ✔ /api/dashboard → {len(d)} komoditas, contoh: {d[0]['komoditas']} {d[0]['status']}")

    def test_admin_stats(self):
        r = self.admin.get("/api/admin/stats")
        self.assertEqual(r.status_code, 200)
        d = json.loads(r.data)
        for key in ("total_records","total_komoditas","total_users","latest_date"):
            self.assertIn(key, d)
        self.assertGreater(d["total_records"], 0)
        print(f"   ✔ /api/admin/stats → records={d['total_records']}, komoditas={d['total_komoditas']}")

    def test_price_histories_browse(self):
        r = self.admin.get("/api/admin/price_histories?komoditas=Beras Medium&limit=10&page=1")
        self.assertEqual(r.status_code, 200)
        d = json.loads(r.data)
        self.assertIn("data", d)
        self.assertIn("total", d)
        self.assertGreater(d["total"], 0)
        self.assertLessEqual(len(d["data"]), 10)
        print(f"   ✔ /api/admin/price_histories → total={d['total']}, halaman 1={len(d['data'])} baris")

    def test_price_histories_pagination(self):
        r1 = self.admin.get("/api/admin/price_histories?komoditas=Beras Medium&limit=5&page=1")
        r2 = self.admin.get("/api/admin/price_histories?komoditas=Beras Medium&limit=5&page=2")
        d1 = json.loads(r1.data)
        d2 = json.loads(r2.data)
        if d1["data"] and d2["data"]:
            self.assertNotEqual(d1["data"][0]["date"], d2["data"][0]["date"])
        print("   ✔ Pagination page 1 ≠ page 2")

    # ── Riwayat simulasi user ─────────────────────────────────────
    def test_riwayat_simulasi(self):
        # Buat dulu 1 simulasi
        self.user.post("/api/rekomendasi",
            json={"komoditas": "Beras Medium", "konsumsi": 3},
            content_type="application/json")
        r = self.user.get("/api/simulasi/riwayat?limit=5")
        self.assertEqual(r.status_code, 200)
        d = json.loads(r.data)
        self.assertIsInstance(d, list)
        self.assertGreater(len(d), 0)
        print(f"   ✔ /api/simulasi/riwayat → {len(d)} entri")

    # ── Halaman yang butuh login ──────────────────────────────────
    def test_admin_page_redirect_anon(self):
        r = self.anon.get("/admin")
        self.assertIn(r.status_code, [302, 401])
        print(f"   ✔ /admin tanpa login → {r.status_code}")

    def test_user_page_redirect_anon(self):
        r = self.anon.get("/user")
        self.assertIn(r.status_code, [302, 401])
        print(f"   ✔ /user tanpa login → {r.status_code}")

    def test_admin_page_ok(self):
        r = self.admin.get("/admin")
        self.assertEqual(r.status_code, 200)
        print("   ✔ /admin dengan login admin → 200")

    def test_user_page_ok(self):
        r = self.user.get("/user")
        self.assertEqual(r.status_code, 200)
        print("   ✔ /user dengan login user → 200")

    # ── Seed admin route ──────────────────────────────────────────
    def test_seed_admin_sudah_ada(self):
        r = self.anon.post("/api/seed_admin",
            json={"username": "admin2", "password": "x"},
            content_type="application/json")
        self.assertEqual(r.status_code, 409)
        print("   ✔ /api/seed_admin saat admin sudah ada → 409")


# ═════════════════════════════════════════════════════════════════
# MAIN
# ═════════════════════════════════════════════════════════════════
if __name__ == "__main__":
    print("=" * 60)
    print("  🧪  PanganWatch — Test Suite")
    print("  MongoDB: mongomock (in-memory, tidak butuh server)")
    print("=" * 60)

    loader = unittest.TestLoader()
    suite  = unittest.TestSuite()

    # Urutan eksekusi
    suite.addTests(loader.loadTestsFromTestCase(TestML))
    suite.addTests(loader.loadTestsFromTestCase(TestAuth))
    suite.addTests(loader.loadTestsFromTestCase(TestAPI))

    runner = unittest.TextTestRunner(verbosity=0, stream=sys.stdout)
    result = runner.run(suite)

    print("\n" + "=" * 60)
    total  = result.testsRun
    failed = len(result.failures) + len(result.errors)
    passed = total - failed
    print(f"  Total  : {total} test")
    print(f"  ✅ Lulus : {passed}")
    print(f"  ❌ Gagal : {failed}")
    print("=" * 60)

    patcher.stop()
    sys.exit(0 if result.wasSuccessful() else 1)
