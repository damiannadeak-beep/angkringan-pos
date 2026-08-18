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

    /**
     * Scope: hanya promo yang aktif dan dalam periode berlaku.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true)
            ->where(fn($q) => $q->whereNull('starts_at')->orWhere('starts_at', '<=', now()))
            ->where(fn($q) => $q->whereNull('ends_at')->orWhere('ends_at', '>=', now()));
    }

    public function menus()
    {
        return $this->belongsToMany(Menu::class, 'promo_menu')->withPivot('jumlah');
    }
}
