<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\StoreUserRequest;
use App\Services\User\CreateUserService;
use Illuminate\Http\JsonResponse;

class CreateUserController extends Controller
{
    public function __invoke(StoreUserRequest $request, CreateUserService $createUserService): JsonResponse
    {
        $user = $createUserService->handle($request->validated());

        return response()->json([
            'data' => $user,
            'message' => 'User created successfully',
        ], 201);
    }
}
