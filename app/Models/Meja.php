<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Meja extends Model
{
    protected $table = 'meja';
    protected $guarded = [];

    public function pesanan()
    {
        return $this->hasMany(Pesanan::class, 'id_meja');
    }
}