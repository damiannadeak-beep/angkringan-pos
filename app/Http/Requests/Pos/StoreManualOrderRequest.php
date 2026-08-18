<?php

namespace App\Http\Requests\Pos;

use Illuminate\Foundation\Http\FormRequest;

class StoreManualOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->hasRole('kasir');
    }

    public function rules(): array
    {
        return [
            'id_meja' => 'required|exists:meja,id',
            'tipe_pesanan' => 'required|in:dine_in,takeaway',
            'pembayaran_langsung' => 'required|boolean',
            'metode_pembayaran' => 'nullable|in:cash,qris,pending',
            'items' => 'required|array',
            'items.*.id_menu' => 'required|exists:menu,id',
            'items.*.jumlah' => 'required|integer|min:1',
            'items.*.catatan' => 'nullable|string|max:255',
            'items.*.variants' => 'nullable|array',
            'promo_id' => 'nullable|exists:promos,id',
        ];
    }
}
