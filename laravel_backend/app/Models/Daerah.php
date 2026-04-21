<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

class Daerah extends Model
{
    protected $connection = 'mongodb';
    protected $collection = 'categories';
    protected $table = 'categories';
    protected $guarded = [];
}