<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\Permission\Traits\HasRoles; // Wajib dipanggil untuk Spatie
use Illuminate\Contracts\Auth\MustVerifyEmail;
use NotificationChannels\WebPush\HasPushSubscriptions;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasFactory, Notifiable, HasRoles, HasPushSubscriptions;

    protected $fillable = [
        'name',
        'email',
        'no_hp',
        'password',
        'shift',
        'foto',
        'google_id',
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