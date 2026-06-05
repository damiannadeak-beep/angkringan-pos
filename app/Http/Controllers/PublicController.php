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
        return view('public.katalog', compact('menus'));
    }

    public function lokasi() {
        return view('public.lokasi');
    }

    public function kontak() {
        return view('public.kontak');
    }
}