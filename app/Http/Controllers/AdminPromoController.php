<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Jobs\SendPromoEmail;
use App\Models\Promo;
use Illuminate\Support\Facades\Mail;
use App\Mail\PromoActiveMail;
use App\Models\User;

class AdminPromoController extends Controller
{
    public function index()
    {
        $promos = Promo::orderBy('starts_at','desc')->paginate(20);
        return view('admin.promo.index', compact('promos'));
    }

    public function create()
    {
        return view('admin.promo.form', ['promo' => new Promo()]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'type' => 'required|in:discount,package',
            'value' => 'required|numeric|min:0',
            'starts_at' => 'nullable|date',
            'ends_at' => 'nullable|date',
            'is_active' => 'nullable|boolean',
        ]);
        $data['is_active'] = $request->has('is_active');
        Promo::create($data);
        $promo = Promo::latest()->first();
        // jika promo aktif dan mulai sekarang, kirim notifikasi email ke users via queue job
        if($promo->is_active && (!$promo->starts_at || $promo->starts_at <= now())){
            $users = User::whereNotNull('email')->where('email','!=','')->cursor();
            foreach($users as $u){
                SendPromoEmail::dispatch($u->id, $promo);
            }
        }
        return redirect()->route('admin.promo.index')->with('success','Promo dibuat.');
    }

    public function edit($id)
    {
        $promo = Promo::findOrFail($id);
        return view('admin.promo.form', compact('promo'));
    }

    public function update(Request $request, $id)
    {
        $promo = Promo::findOrFail($id);
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'type' => 'required|in:discount,package',
            'value' => 'required|numeric|min:0',
            'starts_at' => 'nullable|date',
            'ends_at' => 'nullable|date',
            'is_active' => 'nullable|boolean',
        ]);
        $data['is_active'] = $request->has('is_active');
        $promo->update($data);
        // jika promo aktif dan mulai sekarang, kirim notifikasi email via queue job
        if($promo->is_active && (!$promo->starts_at || $promo->starts_at <= now())){
            $users = User::whereNotNull('email')->where('email','!=','')->cursor();
            foreach($users as $u){
                SendPromoEmail::dispatch($u->id, $promo);
            }
        }
        return redirect()->route('admin.promo.index')->with('success','Promo diperbarui.');
    }

    public function destroy($id)
    {
        $promo = Promo::findOrFail($id);
        $promo->delete();
        return redirect()->route('admin.promo.index')->with('success','Promo dihapus.');
    }
}
