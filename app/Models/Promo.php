<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Promo extends Model
{
    protected $table = 'promos';
    protected $fillable = ['title', 'description', 'type', 'discount_type', 'value', 'starts_at', 'ends_at', 'is_active', 'days'];

    protected $casts = [
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'days' => 'array',
    ];

    public function menus()
    {
        return $this->belongsToMany(Menu::class, 'promo_menu')->withPivot('jumlah');
    }
}
