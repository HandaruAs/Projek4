<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

class Prediction extends Model
{
    protected $connection = 'mongodb';
    protected $collection = 'predictions';

    /**
     * Struktur dokumen MongoDB — sinkron dengan Flask col_prediction:
     * {
     *   _id: ObjectId,
     *   commodity_id: "...",
     *   commodity_name: "Beras Medium",
     *   predicted_at: ISODate,
     *   horizon_days: 30,
     *   current_price: 13500,
     *   satuan: "kg",            ← DITAMBAH (Flask: satuan)
     *   kategori: "Beras",       ← DITAMBAH (Flask: kategori)
     *
     *   // Payload sinkron Flask:
     *   tanggal_pred: ["2024-05-01", ...],   ← DITAMBAH
     *   forecast:     [13200, 13250, ...],   ← DITAMBAH
     *   ci_lower:     [13000, 13050, ...],   ← DITAMBAH
     *   ci_upper:     [13400, 13450, ...],   ← DITAMBAH
     *
     *   // Array detail (untuk show/export di Laravel)
     *   results: [
     *     { date: "2024-05-01", predicted_price: 13200, lower: 13000, upper: 13400 },
     *     ...
     *   ],
     *
     *   metrics: {
     *     mae: 120, rmse: 145, mape: 1.02, accuracy: 98.98,
     *     recommendation_score: 70,
     *     score: 70,                ← alias (blade pakai $metrics['score'])
     *     recommendation: "BELI SEGERA",
     *     warna: "buy_soon",
     *     emoji: "⚡",
     *     delta_pct_7: 1.2,
     *     delta_pct_30: 3.5,
     *     harga_7hari: 13200,
     *     harga_30hari: 13500,
     *   },
     *   created_at: ...,
     *   updated_at: ...,
     * }
     */
    protected $fillable = [
        'commodity_id',
        'commodity_name',
        'predicted_at',
        'horizon_days',
        'current_price',
        'satuan',
        'kategori',
        // Payload array (sinkron Flask)
        'tanggal_pred',
        'forecast',
        'ci_lower',
        'ci_upper',
        // Detail per baris (untuk show & export)
        'results',
        'metrics',
        // Legacy (masih bisa ada di data lama)
        'recommendation_score',
    ];

    protected $primaryKey = 'id';
    public $incrementing  = false;
    protected $keyType    = 'string';

    protected $casts = [
        'predicted_at'         => 'datetime',
        'results'              => 'array',
        'metrics'              => 'array',
        'tanggal_pred'         => 'array',
        'forecast'             => 'array',
        'ci_lower'             => 'array',
        'ci_upper'             => 'array',
        'horizon_days'         => 'integer',
        'current_price'        => 'decimal:2',
        'recommendation_score' => 'integer',
        'created_at'           => 'datetime',
        'updated_at'           => 'datetime',
    ];



    public function commodity()
    {
        return $this->belongsTo(Commodity::class, 'commodity_id', '_id');
    }

    /**
     * Scope: prediksi terbaru untuk satu commodity_id.
     * Sinkron Flask: col_prediction.find_one(sort=[("created_at", DESCENDING)])
     */
    public function scopeLatestByCommodity($query, string $commodityId)
    {
        return $query->where('commodity_id', $commodityId)
                     ->orderBy('predicted_at', 'desc');
    }

    /**
     * Accessor: ambil harga_terakhir (alias Flask: payload.harga_terakhir)
     */
    public function getHargaTerakhirAttribute(): ?int
    {
        return $this->current_price ? (int) $this->current_price : null;
    }

    /**
     * Accessor: rekomendasi dari metrics
     */
    public function getRekomendasiAttribute(): ?string
    {
        return $this->metrics['recommendation'] ?? null;
    }

    /**
     * Accessor: accuracy dari metrics
     */
    public function getAccuracyAttribute(): ?float
    {
        return $this->metrics['accuracy'] ?? null;
    }
}
