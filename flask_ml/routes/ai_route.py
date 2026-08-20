from flask import Blueprint, request, jsonify
from pymongo import MongoClient, DESCENDING
from groq import Groq
from datetime import datetime, timedelta
from dotenv import load_dotenv
import traceback
import os

load_dotenv()

ai_bp = Blueprint('ai', __name__)

# ── Koneksi MongoDB ──────────────────────────────────────────
client = MongoClient(os.getenv("MONGO_URI", "mongodb://localhost:27017/"))
db = client[os.getenv("DB_NAME", "monitoring_harga_pangan")]

# ── Koneksi Groq AI ──────────────────────────────────────────
groq_client = Groq(api_key=os.getenv("GROQ_API_KEY"))


# ════════════════════════════════════════════════════════════
#  HELPER: Ambil data harga dari MongoDB
# ════════════════════════════════════════════════════════════

def get_data_harga(limit=60):
    """Ambil data harga terbaru dari price_histories"""
    data = list(
        db.price_histories.find(
            {},
            {
                "_id": 0,
                "commodity_name": 1,
                "category": 1,
                "date": 1,
                "satuan": 1,
                "harga_lama": 1,
                "harga_sekarang": 1,
                "selisih": 1,
                "persen": 1,
                "source": 1,
            }
        )
        .sort("date", DESCENDING)
        .limit(limit)
    )
    return data


def get_ringkasan_harga():
    """Buat ringkasan agregat harga per komoditas (lebih efisien untuk context AI)"""
    pipeline = [
        {"$sort": {"date": -1}},
        {
            "$group": {
                "_id": "$commodity_name",
                "category": {"$first": "$category"},
                "harga_terbaru": {"$first": "$harga_sekarang"},
                "harga_sebelumnya": {"$first": "$harga_lama"},
                "satuan": {"$first": "$satuan"},
                "selisih": {"$first": "$selisih"},
                "persen": {"$first": "$persen"},
                "tanggal_update": {"$first": "$date"},
            }
        },
        {"$sort": {"_id": 1}},
    ]
    return list(db.price_histories.aggregate(pipeline))


def get_data_7_hari():
    """Ambil data 7 hari terakhir untuk analisis tren"""
    tujuh_hari_lalu = datetime.now() - timedelta(days=7)
    data = list(
        db.price_histories.find(
            {"date": {"$gte": tujuh_hari_lalu}},
            {"_id": 0, "commodity_name": 1, "category": 1,
             "date": 1, "harga_sekarang": 1, "persen": 1}
        )
        .sort("date", DESCENDING)
        .limit(100)
    )
    return data


def format_context_ringkasan(ringkasan):
    """Format ringkasan harga jadi teks untuk context AI"""
    if not ringkasan:
        return "Tidak ada data harga tersedia."

    lines = ["=== RINGKASAN HARGA KOMODITAS TERKINI ===\n"]
    for item in ringkasan:
        tanggal = ""
        if item.get("tanggal_update"):
            try:
                tanggal = item["tanggal_update"].strftime("%d %b %Y")
            except Exception:
                tanggal = str(item["tanggal_update"])[:10]

        status = "NAIK ↑" if (item.get("selisih") or 0) > 0 else (
            "TURUN ↓" if (item.get("selisih") or 0) < 0 else "STABIL →"
        )

        lines.append(
            f"• {item['_id']} ({item.get('category', '-')})\n"
            f"  Harga: Rp {item.get('harga_terbaru', 0):,}/{item.get('satuan', 'kg')}\n"
            f"  Perubahan: {status} Rp {abs(item.get('selisih') or 0):,} ({float(item.get('persen') or 0):.2f}%)\n"
            f"  Update: {tanggal}\n"
        )

    return "\n".join(lines)


def format_context_tren(data_7_hari):
    """Format data tren 7 hari untuk context AI"""
    if not data_7_hari:
        return "Tidak ada data tren 7 hari terakhir."

    lines = ["=== TREN HARGA 7 HARI TERAKHIR ===\n"]
    for item in data_7_hari[:30]:
        tanggal = ""
        try:
            tanggal = item["date"].strftime("%d %b %Y")
        except Exception:
            tanggal = str(item.get("date", ""))[:10]

        lines.append(
            f"• {item.get('commodity_name', '-')} | "
            f"Rp {item.get('harga_sekarang', 0):,} | "
            f"{float(item.get('persen') or 0):.2f}% | {tanggal}"
        )

    return "\n".join(lines)


# ════════════════════════════════════════════════════════════
#  HELPER: Bangun system prompt
# ════════════════════════════════════════════════════════════

def build_system_prompt(context_ringkasan, context_tren):
    return f"""Kamu adalah asisten AI bernama "SimoBot" untuk aplikasi SIMOPANG 
(Sistem Monitoring Pangan) di Jember, Jawa Timur.

Tugasmu adalah menganalisis data harga pangan dan memberikan insight yang berguna 
bagi masyarakat dan pemerintah daerah Jember.

Selalu jawab dalam Bahasa Indonesia yang ramah dan mudah dipahami.
Gunakan data di bawah sebagai referensi utama jawabanmu.
Jika data tidak tersedia untuk pertanyaan tertentu, sampaikan dengan jujur.

{context_ringkasan}

{context_tren}

Panduan menjawab:
- Sebutkan angka harga secara spesifik jika relevan
- Berikan analisis singkat dan actionable
- Gunakan emoji secukupnya agar lebih menarik
- Maksimal 3-4 paragraf per jawaban
"""


# ════════════════════════════════════════════════════════════
#  ENDPOINT: Chat AI utama
#  POST /api/ai/chat
#  Body: {{ "pertanyaan": "...", "history": [...] }}
# ════════════════════════════════════════════════════════════

@ai_bp.route('/api/ai/chat', methods=['POST'])
def chat():
    try:
        body = request.get_json()
        pertanyaan = body.get("pertanyaan", "").strip()
        history    = body.get("history", [])

        if not pertanyaan:
            return jsonify({"success": False, "error": "Pertanyaan tidak boleh kosong"}), 400

        # Ambil data dari MongoDB
        ringkasan    = get_ringkasan_harga()
        data_7_hari  = get_data_7_hari()

        # Format context
        ctx_ringkasan = format_context_ringkasan(ringkasan)
        ctx_tren      = format_context_tren(data_7_hari)

        # Bangun messages untuk Groq
        messages = [
            {"role": "system", "content": build_system_prompt(ctx_ringkasan, ctx_tren)}
        ]

        # Tambahkan history percakapan (maks 6 pesan terakhir)
        for msg in history[-6:]:
            if msg.get("role") in ("user", "assistant"):
                messages.append({"role": msg["role"], "content": msg["content"]})

        # Tambahkan pertanyaan saat ini
        messages.append({"role": "user", "content": pertanyaan})

        # Kirim ke Groq
        response = groq_client.chat.completions.create(
            model="llama-3.3-70b-versatile",
            messages=messages,
            temperature=0.7,
            max_tokens=1024,
        )

        jawaban = response.choices[0].message.content

        return jsonify({
            "success": True,
            "jawaban": jawaban,
            "pertanyaan": pertanyaan,
            "timestamp": datetime.now().isoformat(),
        })

    except Exception as e:
        traceback.print_exc()  # TAMBAHAN: print detail error ke terminal
        return jsonify({"success": False, "error": str(e)}), 500


# ════════════════════════════════════════════════════════════
#  ENDPOINT: Pertanyaan siap pakai (dari sistem Laravel)
#  GET /api/ai/pertanyaan-sistem
# ════════════════════════════════════════════════════════════

@ai_bp.route('/api/ai/pertanyaan-sistem', methods=['GET'])
def pertanyaan_sistem():
    pertanyaan = [
        {
            "id": 1,
            "kategori": "Harga Terkini",
            "icon": "💰",
            "teks": "Komoditas apa yang mengalami kenaikan harga tertinggi saat ini?"
        },
        {
            "id": 2,
            "kategori": "Tren Mingguan",
            "icon": "📈",
            "teks": "Bagaimana tren harga pangan di Jember dalam 7 hari terakhir?"
        },
        {
            "id": 3,
            "kategori": "Rekomendasi",
            "icon": "💡",
            "teks": "Komoditas apa yang harganya paling stabil untuk dikonsumsi?"
        },
        {
            "id": 4,
            "kategori": "Analisis Penurunan",
            "icon": "📉",
            "teks": "Komoditas apa yang mengalami penurunan harga terbesar minggu ini?"
        },
        {
            "id": 5,
            "kategori": "Kategori Beras",
            "icon": "🌾",
            "teks": "Bagaimana kondisi harga beras saat ini dan apakah masih terjangkau?"
        },
        {
            "id": 6,
            "kategori": "Perbandingan",
            "icon": "⚖️",
            "teks": "Bandingkan harga komoditas sayuran dan daging minggu ini!"
        },
    ]
    return jsonify({"success": True, "data": pertanyaan})


# ════════════════════════════════════════════════════════════
#  ENDPOINT: Jawab pertanyaan sistem langsung (by ID)
#  POST /api/ai/tanya-sistem
#  Body: {{ "pertanyaan_id": 1 }}
# ════════════════════════════════════════════════════════════

@ai_bp.route('/api/ai/tanya-sistem', methods=['POST'])
def tanya_sistem():
    try:
        body = request.get_json()
        pertanyaan_id = body.get("pertanyaan_id")

        peta_pertanyaan = {
            1: "Komoditas apa yang mengalami kenaikan harga tertinggi saat ini? Sebutkan nama komoditas, harga, dan persentase kenaikannya.",
            2: "Bagaimana tren harga pangan di Jember dalam 7 hari terakhir? Berikan analisis singkat.",
            3: "Komoditas apa yang harganya paling stabil untuk dikonsumsi? Berikan rekomendasi beserta alasannya.",
            4: "Komoditas apa yang mengalami penurunan harga terbesar minggu ini? Sebutkan detailnya.",
            5: "Bagaimana kondisi harga beras saat ini? Apakah masih terjangkau untuk masyarakat Jember?",
            6: "Bandingkan harga komoditas sayuran dan daging minggu ini! Mana yang lebih terjangkau?",
        }

        pertanyaan = peta_pertanyaan.get(pertanyaan_id)
        if not pertanyaan:
            return jsonify({"success": False, "error": "ID pertanyaan tidak valid"}), 400

        ringkasan   = get_ringkasan_harga()
        data_7_hari = get_data_7_hari()

        ctx_ringkasan = format_context_ringkasan(ringkasan)
        ctx_tren      = format_context_tren(data_7_hari)

        messages = [
            {"role": "system", "content": build_system_prompt(ctx_ringkasan, ctx_tren)},
            {"role": "user",   "content": pertanyaan},
        ]

        response = groq_client.chat.completions.create(
            model="llama-3.3-70b-versatile",
            messages=messages,
            temperature=0.7,
            max_tokens=1024,
        )

        jawaban = response.choices[0].message.content

        return jsonify({
            "success": True,
            "pertanyaan_id": pertanyaan_id,
            "pertanyaan": pertanyaan,
            "jawaban": jawaban,
            "timestamp": datetime.now().isoformat(),
        })

    except Exception as e:
        traceback.print_exc()  # TAMBAHAN: print detail error ke terminal
        return jsonify({"success": False, "error": str(e)}), 500


# ════════════════════════════════════════════════════════════
#  ENDPOINT: Cek status AI & koneksi
#  GET /api/ai/status
# ════════════════════════════════════════════════════════════

@ai_bp.route('/api/ai/status', methods=['GET'])
def status():
    try:
        jumlah_data = db.price_histories.count_documents({})
        return jsonify({
            "success": True,
            "status": "online",
            "model": "llama-3.3-70b-versatile (Groq)",
            "total_data_harga": jumlah_data,
            "timestamp": datetime.now().isoformat(),
        })
    except Exception as e:
        traceback.print_exc()  # TAMBAHAN: print detail error ke terminal
        return jsonify({"success": False, "status": "error", "error": str(e)}), 500