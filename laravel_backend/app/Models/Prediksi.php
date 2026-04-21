<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

class Prediksi extends Model
{
    protected $connection = 'mongodb';
    protected $collection = 'predictions';
    protected $table = 'predictions';
    protected $guarded = [];
}