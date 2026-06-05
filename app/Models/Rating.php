<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Rating extends Model
{
    // Table name is plural 'ratings' (migration creates 'ratings')
    protected $table = 'ratings';
    protected $guarded = [];

    public function pesanan()
    {
        return $this->belongsTo(Pesanan::class, 'id_pesanan');
    }

    public function konsumen()
    {
        return $this->belongsTo(User::class, 'id_konsumen');
    }
}