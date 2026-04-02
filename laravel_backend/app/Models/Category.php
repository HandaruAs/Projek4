<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

class Category extends Model
{
    protected $connection = 'mongodb';
    protected $collection = 'categories';

    /**
     * Kategori komoditas pangan di Jember.
     * Contoh: "Beras & Serealia", "Sayuran", "Bumbu", "Minyak & Lemak"
     *
     * Struktur dokumen MongoDB:
     * {
     *   _id: ObjectId,
     *   name: "Beras & Serealia",   // unique index
     *   created_at: ...,
     *   updated_at: ...
     * }
     */
    protected $fillable = [
        'name',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function commodities()
    {
        return $this->hasMany(Commodity::class, 'category_id');
    }
}
