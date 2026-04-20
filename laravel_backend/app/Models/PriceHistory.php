<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

class PriceHistory extends Model
{
    protected $connection = 'mongodb';
    protected $collection = 'price_histories';

    protected $fillable = [
        'commodity_id',
        'commodity_name',
        'category',
        'category_id',
        'date',
        'satuan',
        'harga_lama',
        'harga_sekarang',
        'selisih',
        'persen',
        'source',
    ];

    protected $casts = [
        'harga_lama'     => 'float',
        'harga_sekarang' => 'float',
        'selisih'        => 'float',
        'persen'         => 'float',
        'date'           => 'datetime',
        'created_at'     => 'datetime',
        'updated_at'     => 'datetime',
    ];

    public function commodity()
    {
        return $this->belongsTo(Commodity::class, 'commodity_id');
    }

    public function scopeByCommodity($query, string $commodityId)
    {
        return $query->where('commodity_id', $commodityId);
    }

    public function scopeDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('date', [$startDate, $endDate]);
    }

    public function scopeForTraining($query, string $commodityId)
    {
        return $query
            ->where('commodity_id', $commodityId)
            ->orderBy('date', 'asc')
            ->select(['date', 'harga_sekarang']);
    }
}
