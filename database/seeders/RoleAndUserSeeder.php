<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;

class RoleAndUserSeeder extends Seeder
{
    public function run()
    {
        // 1. Buat Role
        Role::create(['name' => 'pemilik']);
        Role::create(['name' => 'kasir']);
        Role::create(['name' => 'konsumen']);

        // 2. Buat Akun Pemilik (Admin) mengambil dari .env
        $pemilik = User::create([
            'name' => 'Admin Warung',
            'email' => env('ADMIN_EMAIL', 'admin@angkringan.com'), // Default fallback jika .env kosong
            'password' => Hash::make(env('ADMIN_PASSWORD', 'password123')),
        ]);
        $pemilik->assignRole('pemilik'); // Beri role pemilik


        // Note: Konsumen harus mendaftar lewat form Register, tidak dibuat oleh seeder.
    }
}