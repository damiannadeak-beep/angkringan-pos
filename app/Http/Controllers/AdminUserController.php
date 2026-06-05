<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;

class AdminUserController extends Controller
{
    public function index()
    {
        // Menampilkan semua user kecuali kasir & pemilik (karena sudah ada menu khusus & pengaturan)
        $users = User::with('roles')
            ->whereDoesntHave('roles', function($query) {
                $query->whereIn('name', ['kasir', 'pemilik']);
            })
            ->orderBy('created_at', 'desc')
            ->paginate(20);
        return view('admin.user.index', compact('users'));
    }

    // Bisa ditambahkan store/update/destroy sesuai kebutuhan
}
