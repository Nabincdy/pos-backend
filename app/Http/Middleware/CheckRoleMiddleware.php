<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class CheckRoleMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        collect($request->user()->role->permissions->pluck('title'))->each(function ($title) {
            Gate::define($title, function () {
                return true;
            });
        });

        return $next($request);
    }
}
