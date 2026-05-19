<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\UpdateUserRequest;
use App\Models\User;
use App\Services\User\UpdateUserService;
use Illuminate\Http\JsonResponse;

class UpdateUserController extends Controller
{
    /**
     * Update an existing managed user.
     *
     * @param UpdateUserRequest $request
     * @param User $user
     * @param UpdateUserService $updateUserService
     * @return JsonResponse
     * @author OpenCode
     * @since 2026/05
     */
    public function __invoke(UpdateUserRequest $request, User $user, UpdateUserService $updateUserService): JsonResponse
    {
        $updatedUser = $updateUserService->handle($user, $request->validated(), $request->user());

        return response()->json([
            'data' => $updatedUser,
            'message' => 'User updated successfully',
        ]);
    }
}
