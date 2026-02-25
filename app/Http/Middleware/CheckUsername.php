<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckUsername
{
    public function handle(Request $request, Closure $next): Response
    {
        // If the browser doesn't have a username saved, kick them to login
        if (!session()->has('username')) {
            return redirect('/login');
        }

        return $next($request);
    }
}