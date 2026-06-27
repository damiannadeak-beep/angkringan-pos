<?php

namespace App\Http\Controllers;

use App\Models\Menu;

class PublicController extends Controller
{
    public function home() {
        return view('public.home'); // Buat view landing page sederhana nanti
    }

    public function katalog() {
        // Menampilkan semua menu yang tersedia (Read-only)
        $menus = Menu::where('is_available', true)->get();
        $promos = \App\Models\Promo::with('menus')->where('is_active', true)
            ->where(function($q) {
                $q->whereNull('starts_at')->orWhere('starts_at', '<=', now());
            })
            ->where(function($q) {
                $q->whereNull('ends_at')->orWhere('ends_at', '>=', now());
            })
            ->get();

        $promoMenuIds = [];
        foreach($promos as $promo) {
            if($promo->type == 'package') {
                foreach($promo->menus as $pm) {
                    $promoMenuIds[] = $pm->id;
                }
            }
        }

        return view('public.katalog', compact('menus', 'promos', 'promoMenuIds'));
    }

    public function lokasi() {
        return view('public.lokasi');
    }

    public function kontak() {
        return view('public.kontak');
    }
}