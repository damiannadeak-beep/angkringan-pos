<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AdminKasirController extends Controller
{
    public function index()
    {
        $kasirs = User::whereHas('roles', function($q){ $q->where('name','kasir'); })->paginate(20);
        return view('admin.kasir.index', compact('kasirs'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'shift' => 'required|in:pagi,malam',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'shift' => $data['shift'],
            'password' => Hash::make($data['password']),
        ]);

        $user->assignRole('kasir');

        return redirect()->route('admin.kasir.index')->with('success', 'Akun kasir berhasil dibuat.');
    }

    public function edit($id)
    {
        $kasir = User::findOrFail($id);
        return view('admin.kasir.edit', compact('kasir'));
    }

    public function update(Request $request, $id)
    {
        $kasir = User::findOrFail($id);
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,'.$kasir->id,
            'no_hp' => 'nullable|string|max:15',
            'shift' => 'required|in:pagi,malam',
            'password' => 'nullable|string|min:8|confirmed',
        ]);

        if (!empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }

        $kasir->fill($data)->save();

        return redirect()->route('admin.kasir.index')->with('success','Akun kasir diperbarui.');
    }

    public function destroy($id)
    {
        $kasir = User::findOrFail($id);

        // Cegah hapus kasir yang masih punya shift aktif
        $hasOpenShift = \App\Models\KasirShift::where('user_id', $kasir->id)
            ->where('status', 'open')->exists();

        if ($hasOpenShift) {
            return redirect()->route('admin.kasir.index')
                ->with('error', 'Kasir ini masih memiliki shift aktif. Tutup shift terlebih dahulu.');
        }

        $kasir->removeRole('kasir');
        $kasir->delete();
        return redirect()->route('admin.kasir.index')->with('success','Akun kasir dihapus.');
    }
}
