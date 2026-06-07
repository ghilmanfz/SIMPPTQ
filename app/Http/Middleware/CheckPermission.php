<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckPermission
{
    /**
     * Pastikan user yang sedang login memiliki permission yang dibutuhkan route.
     * Mendukung "salah satu dari" beberapa permission dengan pemisah '|'.
     * Contoh: ->middleware('permission:santri_manage')
     *         ->middleware('permission:leave_apply|leave_approve')
     */
    public function handle(Request $request, Closure $next, string $permission): Response
    {
        $user = $request->user();

        if ($user) {
            foreach (explode('|', $permission) as $key) {
                if ($user->hasPermissionTo(trim($key))) {
                    return $next($request);
                }
            }
        }

        abort(403, 'Anda tidak memiliki hak akses untuk membuka halaman ini.');
    }
}
