<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

class Commodity extends Model
{
    protected $connection = 'mongodb';
    protected $collection = 'commodities';
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

}
