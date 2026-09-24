<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Temporary stand-in for real authentication: every request acts as the first user.
 * Replace with the `auth:sanctum` middleware once authentication is added.
 */
class ActAsDefaultUser
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        Auth::setUser(User::query()->oldest('id')->firstOrFail());

        return $next($request);
    }
}
