<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

class Category extends Model
{
    protected $connection = 'mongodb';
    protected $collection = 'categories';
    protected $primaryKey = '_id';
    public $incrementing  = false;
    protected $keyType    = 'string';
        
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
