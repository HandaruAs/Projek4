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
        $this->baseUrl = config('services.flask.url', env('FLASK_API_URL', 'http://127.0.0.1:5001'));
        $this->apiKey  = config('services.flask.key', env('FLASK_API_KEY', ''));
    }

    private function headers(): array
    {
        return ['X-API-Key' => $this->apiKey];
    }

    /**
     * Ambil daftar komoditas dari Flask.
     * Return: array of string ['Beras Premium', 'Beras Medium', ...]
     */
    public static function getCommodities(): array
    {
        $instance = new self();
        try {
            $res = Http::withHeaders($instance->headers())
                ->timeout(10)
                ->get("{$instance->baseUrl}/api/external/komoditas");

            if (!$res->successful()) {
                Log::warning('PrediksiService::getCommodities - status ' . $res->status());
                return [];
            }

            $data = $res->json();

            // Normalisasi: apapun format Flask, return array of string
            return collect($data)->map(function ($item) {
                if (is_string($item)) return $item;
                if (is_array($item))  return $item['name'] ?? $item['nama'] ?? array_values($item)[0] ?? '';
                return (string) $item;
            })->filter()->values()->toArray();

        } catch (\Exception $e) {
            Log::error('PrediksiService::getCommodities - ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Generate prediksi dari Flask.
     * Parameter: nama komoditas (string), bukan ObjectId.
     */
    public function generate(string $komoditas, int $steps = 30): array
    {
        try {
            $res = Http::withHeaders($this->headers())
                ->timeout(60)
                ->get(
                    "{$this->baseUrl}/api/external/prediksi/" . rawurlencode($komoditas),
                    ['steps' => $steps]
                );

            if (!$res->successful()) {
                $error = $res->json('error', 'Flask error ' . $res->status());
                throw new \Exception($error);
            }

            return $res->json();

        } catch (\Exception $e) {
            Log::error('PrediksiService::generate - ' . $e->getMessage());
            throw new \Exception('Gagal ambil prediksi dari Flask: ' . $e->getMessage());
        }
    }

    /**
     * Ambil rekomendasi dari Flask.
     */
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
                $error = $res->json('error', 'Flask error ' . $res->status());
                throw new \Exception($error);
            }

            return $res->json();

        } catch (\Exception $e) {
            Log::error('PrediksiService::rekomendasi - ' . $e->getMessage());
            throw new \Exception('Gagal ambil rekomendasi dari Flask: ' . $e->getMessage());
        }
    }
}
