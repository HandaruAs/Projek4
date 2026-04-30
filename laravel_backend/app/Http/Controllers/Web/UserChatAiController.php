<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Commodity;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;

class UserChatAiController extends Controller
{
    private string $flaskUrl;

    public function __construct()
    {
        $this->flaskUrl = env('FLASK_ML_URL', 'http://127.0.0.1:5000');
    }

    // ── Halaman Chat AI ──────────────────────────────────────────
    public function index()
    {
        return view('user.chatai');
    }

    // ── GET Daftar Komoditas dari DB ─────────────────────────────
    // GET /chatai/komoditas
    // Dipanggil dari blade step 4 untuk mengisi carousel
    public function komoditas()
    {
        $komoditas = Commodity::orderBy('name')
            ->pluck('name')
            ->filter()
            ->values()
            ->toArray();

        return response()->json([
            'success'   => true,
            'komoditas' => $komoditas,
            'total'     => count($komoditas),
        ]);
    }

    // ── Generate Rekomendasi Wizard ──────────────────────────────
    // POST /chatai/rekomendasi
    public function rekomendasi(Request $request)
    {
        $request->validate([
            'periode'   => 'required|string',
            'anggota'   => 'required|string',
            'budget'    => 'required|string',
            'komoditas' => 'required|array|min:1',
            'prioritas' => 'required|string',
        ]);

        $periode   = $request->periode;
        $anggota   = $request->anggota;
        $budget    = $request->budget;
        $komoditas = implode(', ', $request->komoditas);
        $prioritas = $request->prioritas;

        $prompt = "Buatkan rekomendasi belanja pangan untuk kondisi berikut:
- Jangka waktu: {$periode}
- Jumlah anggota keluarga: {$anggota}
- Budget total: {$budget}
- Komoditas yang dibutuhkan: {$komoditas}
- Prioritas: {$prioritas}

Berikan rekomendasi dalam format berikut (Bahasa Indonesia, ramah dan informatif):

1. **Ringkasan Alokasi Budget** — tabel alokasi per komoditas dengan estimasi harga dan jumlah yang disarankan (format: | Komoditas | Jumlah | Estimasi Harga |). Sesuaikan dengan anggota keluarga dan periode waktu.

2. **Tips Belanja Cerdas** — 3 tips spesifik sesuai kondisi mereka.

3. **Peringatan Harga** — komoditas yang sedang fluktuatif dan perlu diperhatikan.

4. **Saran Substitusi** — alternatif lebih hemat jika ada komoditas mahal.

Gunakan emoji secukupnya. Jawaban praktis dan langsung bisa diterapkan. Maksimal 400 kata.";

        return $this->tanyaFlask($prompt);
    }

    // ── Follow Up Questions ──────────────────────────────────────
    // POST /chatai/followup
    public function followup(Request $request)
    {
        $request->validate([
            'action'    => 'required|string',
            'komoditas' => 'nullable|array',
        ]);

        $action    = $request->action;
        $komoditas = implode(', ', $request->komoditas ?? []);

        $prompts = [
            'cheapNow' =>
                'Berdasarkan kondisi pasar pangan Indonesia saat ini, komoditas pangan apa yang sedang harganya terjangkau atau turun? ' .
                'Info singkat per komoditas (beras, telur, cabai, bawang, daging ayam, dll) dan tips memanfaatkannya. ' .
                'Bahasa Indonesia, ringkas, dengan emoji.',

            'storageTips' =>
                "Berikan tips menyimpan stok bahan pangan seperti {$komoditas} agar lebih tahan lama dan tidak cepat rusak. " .
                'Bahasa Indonesia, praktis, dengan emoji.',
        ];

        if (!isset($prompts[$action])) {
            return response()->json(['success' => false, 'error' => 'Action tidak dikenali'], 400);
        }

        return $this->tanyaFlask($prompts[$action]);
    }

    // ── Helper: Kirim prompt ke Flask → Groq ────────────────────
    private function tanyaFlask(string $prompt)
    {
        $endpoint = "{$this->flaskUrl}/api/ai/chat";

        try {
            $response = Http::timeout(60)
                ->withOptions(['verify' => false])
                ->withHeaders(['Content-Type' => 'application/json'])
                ->post($endpoint, [
                    'pertanyaan' => $prompt,
                    'history'    => [],
                ]);

            Log::info('[ChatAI] Flask response', [
                'status' => $response->status(),
                'body'   => substr($response->body(), 0, 500),
            ]);

            if ($response->successful()) {
                $data  = $response->json();
                $reply = $data['jawaban']
                    ?? $data['reply']
                    ?? $data['response']
                    ?? $data['message']
                    ?? $data['answer']
                    ?? null;

                if (!$reply) {
                    Log::warning('[ChatAI] Key jawaban tidak ditemukan', ['data' => $data]);
                    return response()->json([
                        'success' => false,
                        'error'   => 'Format response Flask tidak dikenali.',
                    ], 500);
                }

                return response()->json(['success' => true, 'reply' => $reply]);
            }

            Log::error('[ChatAI] Flask error', [
                'status' => $response->status(),
                'body'   => $response->body(),
            ]);

            return response()->json([
                'success' => false,
                'error'   => "Flask merespons status {$response->status()}.",
            ], 500);

        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            Log::error('[ChatAI] Koneksi gagal', ['url' => $endpoint, 'error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'error'   => "Tidak bisa terhubung ke Flask di {$endpoint}.",
            ], 503);

        } catch (\Exception $e) {
            Log::error('[ChatAI] Exception', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'error'   => 'Kesalahan: ' . $e->getMessage(),
            ], 500);
        }
    }
}
