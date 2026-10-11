<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminOnly
{
    public function handle(Request $request, Closure $next): Response
    {
        abort_unless($request->user()?->status === 'active', 403, 'This account is inactive.');

        if ($request->user() && $request->user()->role !== 'Admin' && $request->isMethod('GET')) {
            $page = trim($request->path(), '/');
            if (in_array($page, ['dashboard', 'vehicles', 'reservations', 'drivers', 'fuel-logs', 'cost-analytics', 'driver-analytics', 'routes', 'reports', 'settings', 'usermanagement', 'notifications'], true)) {
                return redirect('/users/'.$page);
            }
        }

        abort_unless($request->user()?->role === 'Admin', 403, 'Only administrators can perform this action.');

        return $next($request);
    }
}
