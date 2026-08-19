<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Menu extends Model
{
    protected $table = 'menu';
    protected $fillable = ['nama_menu', 'harga', 'stok', 'image', 'kategori', 'is_available', 'variants_json', 'deskripsi'];

    protected $casts = [
        'is_available' => 'boolean',
        'stok' => 'integer',
        'harga' => 'float',
    ];

    protected $appends = ['image_url'];

    public function getImageUrlAttribute(): ?string
    {
        if (!$this->image) {
            return 'https://placehold.co/600x450/e9ecef/6c757d?text=Belum+Ada+Foto';
        }
        if (str_starts_with($this->image, 'http://') || str_starts_with($this->image, 'https://')) {
            return $this->image;
        }
        $path = str_contains($this->image, '/') ? $this->image : 'menus/' . $this->image;
        if (str_starts_with($path, 'storage/')) {
            $path = substr($path, 8);
        }

        // 1. Cek file persis
        if (file_exists(public_path('storage/' . $path)) || \Illuminate\Support\Facades\Storage::disk('public')->exists($path)) {
            return asset('storage/' . $path);
        }

        // 2. Jika nama file sedikit berbeda (misal typo karakter di DB), cari file yang memiliki awalan timestamp yang sama
        $filename = basename($path);
        if (preg_match('/^(\d+)_[a-f0-9]/i', $filename, $m)) {
            $prefix = $m[1];
            $dir = dirname($path);
            $dir = ($dir === '.' || $dir === '') ? 'menus' : $dir;

            $files = \Illuminate\Support\Facades\Storage::disk('public')->files($dir);
            foreach ($files as $file) {
                if (str_starts_with(basename($file), $prefix . '_')) {
                    $this->attributes['image'] = $file;
                    try {
                        $this->saveQuietly();
                    } catch (\Throwable $e) {}
                    return asset('storage/' . $file);
                }
            }
        }

        return 'https://placehold.co/600x450/e9ecef/6c757d?text=Belum+Ada+Foto';
    }

    public function detail_pesanan()
    {
        return $this->hasMany(DetailPesanan::class, 'id_menu');
    }

    public function bahans()
    {
        return $this->belongsToMany(Bahan::class, 'bahan_menu', 'menu_id', 'bahan_id')
                    ->withPivot('jumlah_dibutuhkan')
                    ->withTimestamps();
    }
}