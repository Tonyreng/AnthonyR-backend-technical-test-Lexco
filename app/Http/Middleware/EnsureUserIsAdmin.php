<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EnsureUserIsAdmin
{
    /**
     * Ensure the authenticated user has the admin role.
     *
     * @param Request $request
     * @param Closure $next
     * @return mixed
     * @author OpenCode
     * @since 2026/05
     */
    public function handle(Request $request, Closure $next)
    {
        if ($request->user()?->role !== 'admin') {
            return response()->json([
                'message' => 'Forbidden.',
            ], 403);
        }

        return $next($request);
    }
}
