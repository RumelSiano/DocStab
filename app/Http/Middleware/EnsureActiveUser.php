<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureActiveUser
{
    public function handle(Request $request, Closure $next): Response
    {
        abort_unless($request->user(), 401, 'Please log in.');
        abort_unless($request->user()->is_active, 403, 'This account is disabled.');
        abort_unless(in_array($request->user()->role, ['requestor', 'sta', 'admin'], true), 403);

        return $next($request);
    }
}
