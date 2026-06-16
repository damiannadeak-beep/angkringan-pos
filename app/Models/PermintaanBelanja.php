<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PermintaanBelanja extends Model
{
    protected $fillable = [
        'user_id',
        'nama_barang',
        'sisa_stok',
        'jumlah_diminta',
        'catatan',
        'status',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
