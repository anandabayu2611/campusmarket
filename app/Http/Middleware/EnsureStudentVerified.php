<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Pasang pada route transaksi (jual/beli/chat/dst) pada sprint berikutnya:
 *   Route::middleware(['auth', 'student.verified'])->...
 */
class EnsureStudentVerified
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user || (! $user->isStaff() && ! $user->isStudentVerified())) {
            return redirect()
                ->route('profile.show')
                ->with('warning', 'Fitur ini hanya untuk mahasiswa yang sudah terverifikasi.');
        }

        return $next($request);
    }
}
