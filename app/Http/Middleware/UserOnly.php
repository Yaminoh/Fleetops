<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class UserOnly
{
    public function handle(Request $request, Closure $next): Response
    {
        abort_unless($request->user()?->status === 'active', 403, 'This account is inactive.');

        if ($request->user()?->role === 'Admin') {
            return redirect('/dashboard');
        }

        abort_unless($request->user() && $request->user()->role !== 'Admin', 403, 'User access only.');

        return $next($request);
    }
}
