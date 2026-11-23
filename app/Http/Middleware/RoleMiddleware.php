<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next, string $role): Response
    {
        $user = $request->user();

        if ($user && $role === 'staff' && $user->role === 'admin' && $request->routeIs('staff.dashboard')) {
            return redirect()->route('admin.dashboard', [
                'current_team' => $request->route('current_team'),
            ]);
        }

        if (! Auth::check() || $user?->role !== $role) {
            abort(403, 'Unauthorized access.');
        }

        return $next($request);
    }
}   
