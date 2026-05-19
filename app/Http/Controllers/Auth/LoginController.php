<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Services\Auth\LoginUserService;
use Illuminate\Http\JsonResponse;

class LoginController extends Controller
{
    /**
     * Authenticate a user and return the active session user.
     *
     * @param LoginRequest $request
     * @param LoginUserService $loginUserService
     * @return JsonResponse
     * @author OpenCode
     * @since 2026/05
     */
    public function __invoke(LoginRequest $request, LoginUserService $loginUserService): JsonResponse
    {
        $user = $loginUserService->handle($request);

        if ($user === null) {
            return response()->json([
                'message' => 'Invalid credentials',
            ], 401);
        }

        return response()->json([
            'data' => [
                'user' => $user,
            ],
            'message' => 'User authenticated successfully',
        ]);
    }
}
