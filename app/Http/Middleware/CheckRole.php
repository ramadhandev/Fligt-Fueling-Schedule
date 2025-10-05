<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class CheckRole
{
    public function handle(Request $request, Closure $next, string $role): Response
    {
        // Gunakan Auth facade
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();
        
        // Check role
        if ($role === 'CRS' && !$this->isCRS($user)) {
            abort(403, 'Unauthorized access for CRS section.');
        }
        
        if ($role === 'Pengawas' && !$this->isPengawas($user)) {
            abort(403, 'Unauthorized access for Pengawas section.');
        }
        
        if ($role === 'CRO' && !$this->isCRO($user)) {
            abort(403, 'Unauthorized access for CRO section.');
        }

        return $next($request);
    }

    private function isCRS($user)
    {
        return $user->role === 'CRS';
    }

    private function isPengawas($user)
    {
        return $user->role === 'Pengawas';
    }

    private function isCRO($user)
    {
        return $user->role === 'CRO';
    }
}