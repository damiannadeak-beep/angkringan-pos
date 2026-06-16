<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Meja extends Model
{
    protected $table = 'meja';
    protected $fillable = ['nama_meja_atau_nomor', 'is_available', 'qr_code'];

    public function pesanan()
    {
        return $this->hasMany(Pesanan::class, 'id_meja');
    }
}