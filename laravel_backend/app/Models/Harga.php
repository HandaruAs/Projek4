<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

class Harga extends Model
{
    protected $connection = 'mongodb';
    protected $collection = 'price_histories';
    protected $table = 'price_histories'; // tambahkan ini
    protected $guarded = [];
}