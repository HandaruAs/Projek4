<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

class PriceHistory extends Model
{
    protected $connection = 'mongodb';
    protected $collection = 'price_histories';

    /**
     * Data historis harga & stok komoditas di Jember.
     * Diinput oleh admin/petugas dan dipakai sebagai training data LSTM di Flask.
     *
     * Struktur dokumen MongoDB:
     * {
     *   _id: ObjectId,
     *   commodity_id: ObjectId,
     *   commodity_name: "Beras Medium",
     *   date: ISODate("2024-01-01"),
     *   price: 12500,       // harga dalam rupiah
     *   stok: 500,          // jumlah stok (sesuai stok_unit di commodity)
     *   created_at: ...,
     *   updated_at: ...
     * }
     *
     * Indexes di MongoDB:
     *   - { commodity_id, date }  compound index
     *   - { date }
     *   - { commodity_name }
     */
    protected $fillable = [
        'commodity_id',
        'commodity_name',
        'date',
        'price',
        'stok',
    ];

    protected $casts = [
        'price'      => 'float',
        'stok'       => 'float',
        'date'       => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // ── Relasi ───────────────────────────────────────────────

    public function commodity()
    {
        return $this->belongsTo(Commodity::class, 'commodity_id');
    }

    // ── Scopes ───────────────────────────────────────────────

    public function scopeByCommodity($query, string $commodityId)
    {
        return $query->where('commodity_id', $commodityId);
    }

    public function scopeDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('date', [$startDate, $endDate]);
    }

    /**
     * Scope khusus untuk mengambil data training yang dikirim ke Flask.
     * LSTM butuh data urut ascending by date.
     */
    public function scopeForTraining($query, string $commodityId)
    {
        return $query
            ->where('commodity_id', $commodityId)
            ->orderBy('date', 'asc')
            ->select(['date', 'price', 'stok']);
    }
}
