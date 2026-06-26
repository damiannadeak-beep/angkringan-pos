<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Laravel\Socialite\Facades\Socialite;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Exception;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class SocialAuthController extends Controller
{
    /**
     * Redirect the user to the Google authentication page.
     *
     * @return \Illuminate\Http\Response
     */
    public function redirectToGoogle()
    {
        return Socialite::driver('google')->redirect();
    }

    /**
     * Obtain the user information from Google.
     *
     * @return \Illuminate\Http\Response
     */
    public function handleGoogleCallback()
    {
        try {
            $googleUser = Socialite::driver('google')->user();
            
            // Cek apakah user dengan google_id ini sudah ada
            $user = User::where('google_id', $googleUser->getId())->first();

            if ($user) {
                // Jika sudah ada, langsung login
                Auth::login($user);
            } else {
                // Jika tidak ada google_id, cek apakah emailnya sudah terdaftar sebelumnya
                $existingUser = User::where('email', $googleUser->getEmail())->first();

                if ($existingUser) {
                    // Update user yang sudah ada dengan google_id
                    $existingUser->google_id = $googleUser->getId();
                    
                    // Jika butuh verify email, kita bisa otomatis set verified
                    if (!$existingUser->email_verified_at) {
                        $existingUser->email_verified_at = now();
                    }
                    $existingUser->save();
                    Auth::login($existingUser);
                } else {
                    // Buat user baru (Konsumen)
                    $newUser = User::create([
                        'name' => $googleUser->getName(),
                        'email' => $googleUser->getEmail(),
                        'google_id' => $googleUser->getId(),
                        'password' => Hash::make(Str::random(16)), // Random password karena login via google
                        'email_verified_at' => now(), // Otomatis terverifikasi karena dari Google
                    ]);

                    // Assign role konsumen secara default
                    $newUser->assignRole('konsumen');

                    Auth::login($newUser);
                }
            }

            return redirect()->intended('/home');
            
        } catch (Exception $e) {
            return redirect('/login')->with('error', 'Gagal login menggunakan Google. Silakan coba lagi.');
        }
    }
}
