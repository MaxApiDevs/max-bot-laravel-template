<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureMaxDebugEnabled
{
    public function handle(Request $request, Closure $next): Response
    {
        abort_unless((bool) config('max.debug_ui.enabled', true), 404);

        return $next($request);
    }
}
