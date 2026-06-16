<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Bahan extends Model
{
    use HasFactory;

    protected $fillable = ['nama_bahan', 'satuan', 'stok', 'harga_beli'];

    public function menus()
    {
        return $this->belongsToMany(Menu::class, 'bahan_menu', 'bahan_id', 'menu_id')
                    ->withPivot('jumlah_dibutuhkan')
                    ->withTimestamps();
    }
}
