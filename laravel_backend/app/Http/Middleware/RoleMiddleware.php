<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, string $role): Response
    {
        $user = Auth::guard('web')->user();

        if (!$user) {
            return redirect('/login');
        }

        if ($user->role !== $role) {
            abort(403, 'Access Denied');
        }

        return $next($request);
    }
}
