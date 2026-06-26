<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Menu extends Model
{
    protected $table = 'menu';
    protected $fillable = ['nama_menu', 'harga', 'stok', 'image', 'kategori', 'is_available', 'variants_json', 'deskripsi'];

    protected $casts = [
        'is_available' => 'boolean',
        'stok' => 'integer',
        'harga' => 'float',
    ];

    public function detail_pesanan()
    {
        return $this->hasMany(DetailPesanan::class, 'id_menu');
    }

    public function bahans()
    {
        return $this->belongsToMany(Bahan::class, 'bahan_menu', 'menu_id', 'bahan_id')
                    ->withPivot('jumlah_dibutuhkan')
                    ->withTimestamps();
    }
}