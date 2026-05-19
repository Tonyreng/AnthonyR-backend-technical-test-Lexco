<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\User\DeleteUserService;
use Illuminate\Http\JsonResponse;

class DeleteUserController extends Controller
{
    /**
     * Delete an existing managed user.
     *
     * @param User $user
     * @param DeleteUserService $deleteUserService
     * @return JsonResponse
     * @author OpenCode
     * @since 2026/05
     */
    public function __invoke(User $user, DeleteUserService $deleteUserService): JsonResponse
    {
        $deleteUserService->handle($user, request()->user());

        return response()->json(status: 204);
    }
}
