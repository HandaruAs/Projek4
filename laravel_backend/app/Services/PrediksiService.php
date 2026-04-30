<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PrediksiService
{
    private string $baseUrl;
    private string $apiKey;

    public function __construct()
    {
        $this->baseUrl = config('services.flask.url');
        $this->apiKey  = config('services.flask.key');
    }

    private function headers(): array
    {
        return ['X-API-Key' => $this->apiKey];
    }

    // Ambil daftar komoditas dari Flask
    public static function getCommodities(): array
    {
        $instance = new self();
        try {
            $res = Http::withHeaders($instance->headers())
                ->timeout(10)
                ->get("{$instance->baseUrl}/api/external/komoditas");
            return $res->successful ? $res->json : [];
        } catch (\Exception $e) {
            Log::error('PrediksiService::getCommodities - ' . $e->getMessage());
            return [];
        }
    }

    // Generate prediksi dari Flask
    public function generate(string $komoditas, int $steps = 30): array
    {
        try {
            $res = Http::withHeaders($this->headers())
                ->timeout(60)
                ->get("{$this->baseUrl}/api/external/prediksi/" . rawurlencode($komoditas), [
                    'steps' => $steps,
                ]);

            if (!$res->successful) {
                throw new \Exception($res->json('error', 'Flask error'));
            }

            return $res->json();

        } catch (\Exception $e) {
            Log::error('PrediksiService::generate - ' . $e->getMessage());
            throw new \Exception('Gagal ambil prediksi dari Flask: ' . $e->getMessage());
        }
    }

    // Ambil rekomendasi dari Flask
    public function rekomendasi(string $komoditas, float $konsumsi = 1.0): array
    {
        try {
            $res = Http::withHeaders($this->headers())
                ->timeout(60)
                ->post("{$this->baseUrl}/api/external/rekomendasi", [
                    'komoditas' => $komoditas,
                    'konsumsi'  => $konsumsi,
                ]);

            if (!$res->successful()) {
                throw new \Exception($res->json('error', 'Flask error'));
            }

            return $res->json();

        } catch (\Exception $e) {
            Log::error('PrediksiService::rekomendasi - ' . $e->getMessage());
            throw new \Exception('Gagal ambil rekomendasi dari Flask: ' . $e->getMessage());
        }
    }
}
