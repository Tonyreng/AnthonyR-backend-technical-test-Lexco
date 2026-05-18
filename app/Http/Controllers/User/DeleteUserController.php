<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\User\DeleteUserService;
use Illuminate\Http\JsonResponse;

class DeleteUserController extends Controller
{
    public function __invoke(User $user, DeleteUserService $deleteUserService): JsonResponse
    {
        $deleteUserService->handle($user, request()->user());

        return response()->json(status: 204);
    }
}
