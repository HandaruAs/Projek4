<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

class Prediction extends Model
{
    protected $connection = 'mongodb';
    protected $collection = 'predictions';

    /**
     * Skema dokumen MongoDB — IDENTIK dengan yang dibuat Flask (api_run_prediksi & api_external_prediksi):
     *
     * {
     *   _id:            ObjectId,
     *   commodity_name: "Beras Medium",
     *   steps:          30,
     *   created_at:     ISODate,
     *   created_by:     "zaiful" | "laravel_api",
     *   status:         "completed",
     *   accuracy_mae:   120,       ← flat, bukan nested
     *   accuracy_rmse:  145,       ← flat
     *   accuracy_mape:  1.02,      ← flat
     *   payload: {
     *     komoditas:        "Beras Medium",
     *     tanggal_pred:     ["2024-05-01", ...],
     *     forecast:         [13200, ...],
     *     ci_lower:         [13000, ...] | null,
     *     ci_upper:         [13400, ...] | null,
     *     accuracy: {
     *       accuracy: 98.1,
     *       mae:      120,
     *       mape:     1.02,
     *       rmse:     145,
     *       note:     "Holt-Winters, walk-forward 80/20 split"
     *     },
     *     satuan:           "kg",
     *     harga_terakhir:   13500,
     *     tanggal_terakhir: "2024-04-30",
     *     kategori:         "BERAS",
     *     from_cache:       false
     *   }
     * }
     */
    protected $fillable = [
        'commodity_name',
        'steps',
        'created_at',
        'created_by',
        'status',
        'accuracy_mae',
        'accuracy_rmse',
        'accuracy_mape',
        'payload',
    ];

    protected $primaryKey = 'id';
    public $incrementing  = false;
    protected $keyType    = 'string';

    protected $casts = [
        'created_at'    => 'datetime',
        'updated_at'    => 'datetime',
        'steps'         => 'integer',
        'accuracy_mae'  => 'float',
        'accuracy_rmse' => 'float',
        'accuracy_mape' => 'float',
        // 'payload' => 'array', // JANGAN dicast — MongoDB driver sudah decode sebagai array native
    ];

    // ──────────────────────────────────────────────────────────────
    // ACCESSORS — shortcut ke dalam payload agar blade tetap ringkas
    // ──────────────────────────────────────────────────────────────

    /** Alias: predicted_at → created_at */
    public function getPredictedAtAttribute()
    {
        return $this->created_at;
    }

    /** Alias: horizon_days → steps */
    public function getHorizonDaysAttribute(): int
    {
        return (int) ($this->steps ?? 0);
    }

    /** Harga terakhir dari payload */
    public function getCurrentPriceAttribute()
    {
        return $this->payload['harga_terakhir'] ?? null;
    }

    /** Satuan dari payload */
    public function getSatuanAttribute(): string
    {
        return $this->payload['satuan'] ?? 'kg';
    }

    /** Kategori dari payload */
    public function getKategoriAttribute(): string
    {
        return $this->payload['kategori'] ?? '';
    }

    /** Array forecast dari payload */
    public function getForecastAttribute(): array
    {
        return $this->payload['forecast'] ?? [];
    }

    /** Array tanggal prediksi dari payload */
    public function getTanggalPredAttribute(): array
    {
        return $this->payload['tanggal_pred'] ?? [];
    }

    /** CI lower dari payload */
    public function getCiLowerAttribute(): ?array
    {
        return $this->payload['ci_lower'] ?? null;
    }

    /** CI upper dari payload */
    public function getCiUpperAttribute(): ?array
    {
        return $this->payload['ci_upper'] ?? null;
    }

    /**
     * metrics → payload.accuracy
     * Blade $prediction->metrics['mape'] tetap bekerja.
     */
    public function getMetricsAttribute(): array
    {
        return $this->payload['accuracy'] ?? [];
    }

    /**
     * results → dibangun on-the-fly dari payload arrays.
     * Blade $prediction->results tetap bekerja.
     */
    public function getResultsAttribute(): array
    {
        $tanggal  = $this->payload['tanggal_pred'] ?? [];
        $forecast = $this->payload['forecast']     ?? [];
        $ciLower  = $this->payload['ci_lower']     ?? [];
        $ciUpper  = $this->payload['ci_upper']     ?? [];

        $results = [];
        foreach ($tanggal as $i => $tgl) {
            $results[] = [
                'date'            => $tgl,
                'predicted_price' => $forecast[$i] ?? 0,
                'lower'           => is_array($ciLower) ? ($ciLower[$i] ?? null) : null,
                'upper'           => is_array($ciUpper) ? ($ciUpper[$i] ?? null) : null,
            ];
        }
        return $results;
    }

    // ──────────────────────────────────────────────────────────────
    // SCOPES
    // ──────────────────────────────────────────────────────────────

    public function scopeLatestByCommodity($query, string $commodityName)
    {
        return $query->where('commodity_name', $commodityName)
                     ->orderBy('created_at', 'desc');
    }
}