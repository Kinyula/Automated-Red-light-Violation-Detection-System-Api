<?php

namespace App\Http\Middleware\Api\Auth;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

class OnlineStatusMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check()) {
            $user = $request->user();
            $cacheKey = 'user_is_online_' . $user->id;

            // Update both cache and database
            Cache::put($cacheKey, true, now()->addMinutes(5));
            $user->update([
                'online_status' => 'online',
                'last_activity' => now()
            ]);
        }

        return $next($request);
    }
}
