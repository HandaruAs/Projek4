<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

class Simulation extends Model
{
    protected $connection = 'mongodb';
    protected $collection = 'simulations';

    protected $fillable = [
        'commodity_id',
        'commodity_name',
        'wilayah',
        'scenario',
        'simulated_price',
        'simulation_date',
        'parameters',
        'result',
    ];

    protected $casts = [
        'simulated_price' => 'float',
        'simulation_date' => 'datetime',
        'parameters'      => 'array',
        'result'          => 'array',
        'created_at'      => 'datetime',
        'updated_at'      => 'datetime',
    ];

    // Relasi ke Commodity
    public function commodity()
    {
        return $this->belongsTo(Commodity::class, 'commodity_id');
    }
}
