<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

class Commodity extends Model
{
    protected $connection = 'mongodb';
    protected $collection = 'commodities';

        protected $fillable = [
        'name',
'category',   // <-- pastikan ini ada, bukan         'category_id'
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

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
