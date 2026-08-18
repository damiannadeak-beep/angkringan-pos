<?php

namespace App\Services;

use App\Models\User;
use App\Models\KasirShift;
use Illuminate\Support\Facades\Hash;

class KasirService
{
    /**
     * Ambil daftar kasir terpaginasi.
     */
    public function getPaginatedKasirs(int $perPage = 20)
    {
        return User::whereHas('roles', function ($q) {
            $q->where('name', 'kasir');
        })->paginate($perPage);
    }

    /**
     * Buat akun kasir baru.
     */
    public function createKasir(array $data): User
    {
        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'shift' => $data['shift'],
            'password' => Hash::make($data['password']),
        ]);

        $user->assignRole('kasir');

        return $user;
    }

    /**
     * Update akun kasir.
     */
    public function updateKasir(User $kasir, array $data): User
    {
        if (!empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }

        $kasir->fill($data)->save();

        return $kasir;
    }

    /**
     * Hapus akun kasir jika tidak ada shift aktif.
     *
     * @throws \Exception Jika kasir memiliki shift aktif.
     */
    public function deleteKasir(User $kasir): void
    {
        $hasOpenShift = KasirShift::where('user_id', $kasir->id)
            ->where('status', 'open')
            ->exists();

        if ($hasOpenShift) {
            throw new \Exception('Kasir ini masih memiliki shift aktif. Tutup shift terlebih dahulu.');
        }

        $kasir->removeRole('kasir');
        $kasir->delete();
    }
}
