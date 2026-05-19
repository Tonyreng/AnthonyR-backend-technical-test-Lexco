<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\IndexUserRequest;
use App\Services\User\ListUsersService;
use Illuminate\Http\JsonResponse;

class ListUsersController extends Controller
{
    /**
     * Retrieve paginated managed users.
     *
     * @param IndexUserRequest $request
     * @param ListUsersService $listUsersService
     * @return JsonResponse
     * @author OpenCode
     * @since 2026/05
     */
    public function __invoke(IndexUserRequest $request, ListUsersService $listUsersService): JsonResponse
    {
        $users = $listUsersService->handle($request->validated());

        return response()->json([
            'data' => $users->items(),
            'meta' => [
                'current_page' => $users->currentPage(),
                'per_page' => $users->perPage(),
                'total' => $users->total(),
                'last_page' => $users->lastPage(),
            ],
            'message' => 'Users retrieved successfully',
        ]);
    }
}
