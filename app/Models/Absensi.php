<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Absensi extends Model
{
    protected $fillable = ['user_id', 'shift', 'tanggal', 'jam_masuk', 'jam_keluar', 'status', 'latitude', 'longitude', 'jarak_meter'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
