<?php

namespace App\Models;

use MongoDB\Laravel\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, Notifiable;

    protected $connection = 'mongodb';
    protected $collection = 'users';

    /**
     * Semua user beroperasi di wilayah Jember.
     *
     * Role:
     *   - "admin"      → akses penuh di Laravel (CRUD, training ML, lihat prediksi)
     *   - "petugas"    → input data harga & stok via Flutter
     *   - "masyarakat" → read-only via Flutter (monitoring harga)
     *
     * Struktur dokumen MongoDB:
     * {
     *   _id: ObjectId,
     *   name: "Budi Santoso",
     *   email: "budi@email.com",
     *   password: "hashed",
     *   role: "petugas",
     *   created_at: ...,
     *   updated_at: ...
     * }
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password'          => 'hashed',
        'created_at'        => 'datetime',
        'updated_at'        => 'datetime',
    ];

    const ROLE_ADMIN      = 'admin';
    const ROLE_PETUGAS    = 'petugas';
    const ROLE_MASYARAKAT = 'masyarakat';

    // ── Helper Role ──────────────────────────────────────────

    public function isAdmin(): bool
    {
        return $this->role === self::ROLE_ADMIN;
    }

    public function isPetugas(): bool
    {
        return $this->role === self::ROLE_PETUGAS;
    }

    public function isMasyarakat(): bool
    {
        return $this->role === self::ROLE_MASYARAKAT;
    }

    // ── Relasi ───────────────────────────────────────────────

    public function trainingJobs()
    {
        return $this->hasMany(TrainingJob::class, 'triggered_by');
    }
}
