<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

class Prediction extends Model
{
    protected $connection = 'mongodb';
    protected $collection = 'predictions';

    /**
     * Hasil prediksi harga dari model LSTM di Flask untuk komoditas di Jember.
     *
     * Struktur dokumen MongoDB:
     * {
     *   _id: ObjectId,
     *   training_job_id: ObjectId,
     *   commodity_id: ObjectId,
     *   commodity_name: "Beras Medium",
     *   predicted_at: ISODate(...),
     *
     *   // Array hasil prediksi per hari ke depan
     *   results: [
     *     { date: "2024-02-01", predicted_price: 13000, predicted_stok: 480 },
     *     { date: "2024-02-02", predicted_price: 13200, predicted_stok: 460 },
     *     ...
     *   ],
     *
     *   // Metrik performa model dari Flask
     *   metrics: {
     *     mae: 120.5,    // Mean Absolute Error
     *     rmse: 145.2,   // Root Mean Squared Error
     *     mape: 1.02,    // Mean Absolute Percentage Error (%)
     *   },
     *
     *   horizon_days: 7,
     *   created_at: ...,
     *   updated_at: ...
     * }
     */
    protected $fillable = [
        'training_job_id',
        'commodity_id',
        'commodity_name',
        'predicted_at',
        'results',
        'metrics',
        'horizon_days',
    ];

    protected $casts = [
        'predicted_at' => 'datetime',
        'results'      => 'array',
        'metrics'      => 'array',
        'horizon_days' => 'integer',
        'created_at'   => 'datetime',
        'updated_at'   => 'datetime',
    ];

    // ── Relasi ───────────────────────────────────────────────

    public function commodity()
    {
        return $this->belongsTo(Commodity::class, 'commodity_id');
    }

    public function trainingJob()
    {
        return $this->belongsTo(TrainingJob::class, 'training_job_id');
    }

    // ── Scopes ───────────────────────────────────────────────

    public function scopeLatestByCommodity($query, string $commodityId)
    {
        return $query->where('commodity_id', $commodityId)
                     ->orderBy('predicted_at', 'desc');
    }
}
