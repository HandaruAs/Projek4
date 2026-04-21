<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

class Komoditas extends Model
{
    protected $connection = 'mongodb';
    protected $collection = 'commodities';
    protected $table = 'commodities';
    protected $guarded = [];
}