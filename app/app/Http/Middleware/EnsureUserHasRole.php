<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureUserHasRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next, $role)
    {
        if ($role === 'USER' && $request->user()->organisation_id !== null) {
            return response(['message' => 'You are not allowed to access this resource.'], 403);
        }
        if ($role === 'ORGANISER' && $request->user()->organisation_id === null) {
            return response(['message' => 'You are not allowed to access this resource.'], 403);
        }
        return $next($request);
    }
}
