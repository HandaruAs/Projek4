<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

class Commodity extends Model
{
    protected $connection = 'mongodb';
    protected $collection = 'commodities';

    /**
     * Master data komoditas pangan di Jember.
     * Contoh: Beras Medium, Bawang Merah, Cabai Rawit, Minyak Goreng
     *
     * Struktur dokumen MongoDB:
     * {
     *   _id: ObjectId,
     *   name: "Beras Medium",   // unique index
     *   category_id: ObjectId,
     *   unit: "kg",             // satuan harga (kg, liter, dll)
     *   stok_unit: "kuintal",   // satuan stok
     *   description: "...",
     *   created_at: ...,
     *   updated_at: ...
     * }
     */
    protected $fillable = [
        'name',
        'category_id',
        'unit',
        'stok_unit',
        'description',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    public function priceHistories()
    {
        return $this->hasMany(PriceHistory::class, 'commodity_id');
    }

    public function predictions()
    {
        return $this->hasMany(Prediction::class, 'commodity_id');
    }

    public function trainingJobs()
    {
        return $this->hasMany(TrainingJob::class, 'commodity_id');
    }
}
