<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

class Commodity extends Model
{
    protected $connection = 'mongodb';

    protected $collection = 'commodities';

    protected $fillable = [
        'name',
        'category_id'
    ];
}