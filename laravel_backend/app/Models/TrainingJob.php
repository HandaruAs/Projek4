<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

class TrainingJob extends Model
{
    protected $connection = 'mongodb';
    protected $collection = 'training_jobs';

    /**
     * Job pelatihan model LSTM di Flask.
     * Dicatat setiap kali admin/pemilik meminta training ulang.
     *
     * Struktur dokumen MongoDB:
     * {
     *   _id: ObjectId,
     *   commodity_id: ObjectId,
     *   triggered_by: ObjectId (user_id),
     *   status: "pending" | "running" | "completed" | "failed",
     *   started_at: ISODate(...),
     *   completed_at: ISODate(...),
     *   error_message: "...",
     *   training_config: {
     *     horizon_days: 7,
     *     epochs: 50,
     *     batch_size: 32,
     *   },
     *   created_at: ...,
     *   updated_at: ...
     * }
     */
    protected $fillable = [
        'commodity_id',
        'triggered_by',
        'status',
        'started_at',
        'completed_at',
        'error_message',
        'training_config',
    ];

    protected $casts = [
        'started_at'     => 'datetime',
        'completed_at'   => 'datetime',
        'training_config'=> 'array',
        'created_at'     => 'datetime',
        'updated_at'     => 'datetime',
    ];

    // ── Status Constants ─────────────────────────────────────
    const STATUS_PENDING   = 'pending';
    const STATUS_RUNNING   = 'running';
    const STATUS_COMPLETED = 'completed';
    const STATUS_FAILED    = 'failed';

    // ── Relasi ───────────────────────────────────────────────

    public function commodity()
    {
        return $this->belongsTo(Commodity::class, 'commodity_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'triggered_by');
    }

    public function predictions()
    {
        return $this->hasMany(Prediction::class, 'training_job_id');
    }
}

