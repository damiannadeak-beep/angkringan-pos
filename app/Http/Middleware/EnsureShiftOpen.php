<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureShiftOpen
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Only apply to users with 'kasir' role
        if (auth()->check() && auth()->user()->hasRole('kasir')) {
            $userId = auth()->id();
            $userShift = auth()->user()->shift ?? 'pagi';

            // Check if THIS kasir has their own open shift (= shift owner / Kasir 1)
            $myShift = \App\Models\KasirShift::where('user_id', $userId)
                ->where('status', 'open')
                ->first();

            // Check if ANOTHER kasir on the same shift group has an open shift
            $teamShift = \App\Models\KasirShift::where('status', 'open')
                ->where('user_id', '!=', $userId)
                ->whereHas('user', function($q) use ($userShift) {
                    $q->where('shift', $userShift);
                })
                ->first();

            if (!$myShift && !$teamShift) {
                // No one has opened a shift yet → force this kasir to open one (become Kasir 1)
                // Allow: buka shift form, logout, absensi
                if (!$request->routeIs('kasir.shift.buka') && !$request->routeIs('kasir.shift.storeBuka') && !$request->routeIs('logout') && !$request->routeIs('kasir.absensi.*')) {
                    return redirect()->route('kasir.shift.buka')->with('warning', 'Anda harus membuka laci kasir (Modal Awal) sebelum memulai shift.');
                }
            } elseif (!$myShift && $teamShift) {
                // Team member already opened a shift → this kasir is Kasir 2 (piggyback)
                // Allow access to POS directly, but block buka shift form
                if ($request->routeIs('kasir.shift.buka') || $request->routeIs('kasir.shift.storeBuka')) {
                    return redirect()->route('kasir.pos');
                }
                // Also block Tutup Shift for Kasir 2 (they don't own the drawer)
                if ($request->routeIs('kasir.shift.tutup') || $request->routeIs('kasir.shift.storeTutup')) {
                    return redirect()->route('kasir.pos')->with('error', 'Hanya kasir yang membuka laci (Kasir 1) yang bisa menutup shift.');
                }
            } elseif ($myShift) {
                // This kasir IS the shift owner (Kasir 1)
                // Block buka form since they already have an open shift
                if ($request->routeIs('kasir.shift.buka') || $request->routeIs('kasir.shift.storeBuka')) {
                    return redirect()->route('kasir.pos');
                }
            }
        }

        return $next($request);
    }
}

