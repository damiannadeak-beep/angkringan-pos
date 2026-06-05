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

        if(!empty($data['password'])){
            $kasir->password = Hash::make($data['password']);
        }
        $kasir->name = $data['name'];
        $kasir->email = $data['email'];
        $kasir->no_hp = $data['no_hp'] ?? $kasir->no_hp;
        $kasir->shift = $data['shift'];
        $kasir->save();

        return redirect()->route('admin.kasir.index')->with('success','Akun kasir diperbarui.');
    }

    public function destroy($id)
    {
        $kasir = User::findOrFail($id);
        $kasir->removeRole('kasir');
        $kasir->delete();
        return redirect()->route('admin.kasir.index')->with('success','Akun kasir dihapus.');
    }
}
