<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterRequest;
use App\Services\Auth\RegisterUserService;
use Illuminate\Http\JsonResponse;

class RegisterController extends Controller
{
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
