<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateKasirRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->hasRole('pemilik');
    }

    public function rules(): array
    {
        $kasirId = $this->route('id');

        return [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $kasirId,
            'no_hp' => 'nullable|string|max:15',
            'shift' => 'required|in:pagi,malam',
            'password' => 'nullable|string|min:8|confirmed',
        ];
    }
}
