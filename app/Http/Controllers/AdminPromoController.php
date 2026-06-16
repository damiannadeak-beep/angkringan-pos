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
        $allMenus = \App\Models\Menu::all();
        return view('admin.promo.form', ['promo' => new Promo(), 'allMenus' => $allMenus]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'type' => 'required|in:discount,package',
            'discount_type' => 'nullable|in:percentage,nominal',
            'value' => 'required|numeric|min:0',
            'starts_at' => 'nullable|date',
            'ends_at' => 'nullable|date',
            'days' => 'nullable|array',
            'days.*' => 'string'
        ]);
        $data['is_active'] = $request->has('is_active');
        $promo = Promo::create($data);

        // Sync Paket Menus
        if ($promo->type === 'package' && $request->has('package_menus')) {
            $menus = $request->input('package_menus');
            $qtys = $request->input('package_qty');
            $syncData = [];
            foreach($menus as $index => $menuId) {
                if(!empty($menuId)) {
                    $syncData[$menuId] = ['jumlah' => $qtys[$index] ?? 1];
                }
            }
            $promo->menus()->sync($syncData);
        }

        // jika promo aktif dan mulai sekarang, kirim notifikasi email ke users via queue job
        if($promo->is_active && (!$promo->starts_at || $promo->starts_at <= now())){
            $users = User::whereNotNull('email')->where('email','!=','')->cursor();
            foreach($users as $u){
                SendPromoEmail::dispatch($u->id, $promo);
            }
        }
        
        \App\Models\ActivityLog::create([
            'user_id' => auth()->id(),
            'action' => 'Create Promo',
            'description' => 'Membuat promo baru: ' . $promo->title
        ]);

        return redirect()->route('admin.promo.index')->with('success','Promo dibuat.');
    }

    public function edit($id)
    {
        $promo = Promo::findOrFail($id);
        $allMenus = \App\Models\Menu::all();
        return view('admin.promo.form', compact('promo', 'allMenus'));
    }

    public function update(Request $request, $id)
    {
        $promo = Promo::findOrFail($id);
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'type' => 'required|in:discount,package',
            'discount_type' => 'nullable|in:percentage,nominal',
            'value' => 'required|numeric|min:0',
            'starts_at' => 'nullable|date',
            'ends_at' => 'nullable|date',
            'days' => 'nullable|array',
            'days.*' => 'string'
        ]);
        $data['is_active'] = $request->has('is_active');
        // Jika type diganti dari package ke discount, kosongkan days? Tidak perlu. Tapi kosongkan menu
        if ($data['type'] === 'discount') {
            $promo->menus()->detach();
        }

        $promo->update($data);

        // Sync Paket Menus
        if ($promo->type === 'package' && $request->has('package_menus')) {
            $menus = $request->input('package_menus');
            $qtys = $request->input('package_qty');
            $syncData = [];
            foreach($menus as $index => $menuId) {
                if(!empty($menuId)) {
                    $syncData[$menuId] = ['jumlah' => $qtys[$index] ?? 1];
                }
            }
            $promo->menus()->sync($syncData);
        }

        // jika promo aktif dan mulai sekarang, kirim notifikasi email via queue job
        if($promo->is_active && (!$promo->starts_at || $promo->starts_at <= now())){
            $users = User::whereNotNull('email')->where('email','!=','')->cursor();
            foreach($users as $u){
                SendPromoEmail::dispatch($u->id, $promo);
            }
        }
        
        \App\Models\ActivityLog::create([
            'user_id' => auth()->id(),
            'action' => 'Update Promo',
            'description' => 'Memperbarui promo: ' . $promo->title
        ]);

        return redirect()->route('admin.promo.index')->with('success','Promo diperbarui.');
    }

    public function destroy($id)
    {
        $promo = Promo::findOrFail($id);
        $promo->delete();
        return redirect()->route('admin.promo.index')->with('success','Promo dihapus.');
    }
}
