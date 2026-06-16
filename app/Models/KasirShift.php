<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KasirShift extends Model
{
    protected $fillable = [
        'user_id', 'waktu_buka', 'waktu_tutup', 'modal_awal',
        'uang_fisik_aktual', 'total_pemasukan_tunai', 'total_pengeluaran',
        'selisih', 'status'
    ];

    protected $casts = [
        'waktu_buka' => 'datetime',
        'waktu_tutup' => 'datetime',
        'modal_awal' => 'decimal:2',
        'uang_fisik_aktual' => 'decimal:2',
        'total_pemasukan_tunai' => 'decimal:2',
        'total_pengeluaran' => 'decimal:2',
        'selisih' => 'decimal:2',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
