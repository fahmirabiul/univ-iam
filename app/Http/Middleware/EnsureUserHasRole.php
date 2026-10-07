<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserHasRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  ...$roles
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (! $user) {
            abort(Response::HTTP_FORBIDDEN, 'Akses ditolak: Anda tidak memiliki izin untuk mengakses halaman ini.');
        }

        // Super administrator has global access
        if ($user->isSuperAdmin()) {
            return $next($request);
        }

        // Check if user is an administrator and 'admin' / 'admin_sdm' is in requested roles
        if ($user->is_admin && (in_array('admin', $roles, true) || in_array('admin_sdm', $roles, true))) {
            return $next($request);
        }

        if (! $user->hasAnyRole($roles)) {
            abort(Response::HTTP_FORBIDDEN, 'Akses ditolak: Anda tidak memiliki izin untuk mengakses halaman ini.');
        }

        return $next($request);
    }
}
