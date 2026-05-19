<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterRequest;
use App\Services\Auth\RegisterUserService;
use Illuminate\Http\JsonResponse;

class RegisterController extends Controller
{
    /**
     * Register a new user and start an authenticated session.
     *
     * @param RegisterRequest $request
     * @param RegisterUserService $registerUserService
     * @return JsonResponse
     * @author OpenCode
     * @since 2026/05
     */
    public function __invoke(RegisterRequest $request, RegisterUserService $registerUserService): JsonResponse
    {
        $user = $registerUserService->handle($request->validated());

        return response()->json([
            'data' => [
                'user' => $user,
            ],
            'message' => 'User registered successfully',
        ], 201);
    }
}
