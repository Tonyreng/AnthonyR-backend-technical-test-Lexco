<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MeController extends Controller
{
    /**
     * Retrieve the currently authenticated user.
     *
     * @param Request $request
     * @return JsonResponse
     * @author OpenCode
     * @since 2026/05
     */
    public function __invoke(Request $request): JsonResponse
    {
        return response()->json([
            'data' => [
                'user' => $request->user(),
            ],
            'message' => 'Authenticated user retrieved successfully',
        ]);
    }
}
