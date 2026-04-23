<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Item extends Model
{
    protected $fillable = [
        'category_id',
        'nama_barang',
        'kode_barang',
        'stok',
        'harga',
        'satuan',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }
}
