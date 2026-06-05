<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles; // Wajib dipanggil untuk Spatie

class User extends Authenticatable
{
    use Notifiable, HasRoles;

    protected $fillable = [
        'name',
        'email',
        'no_hp',
        'password',
        'shift',
        'foto',
    ];

    // Jika user adalah konsumen
    public function pesanan_konsumen()
    {
        return $this->hasMany(Pesanan::class, 'id_konsumen');
    }

    // Jika user adalah kasir
    public function pesanan_kasir()
    {
        return $this->hasMany(Pesanan::class, 'id_kasir');
    }
}