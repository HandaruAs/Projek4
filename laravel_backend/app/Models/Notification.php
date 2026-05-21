<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

class Notification extends Model
{
    protected $connection = 'mongodb';
    protected $collection = 'notifications';

    protected $fillable = [
        'user_id',    // null = broadcast ke semua user
        'title',
        'body',
        'type',       // price_alert | prediction | simulation
        'commodity',  // nama komoditas, nullable
        'meta',       // data tambahan (tabIndex, dll)
        'is_read_by', // array of user_id yang sudah baca
        'created_at',
    ];

    protected $casts = [
        'meta'       => 'array',
        'is_read_by' => 'array',
    ];
}