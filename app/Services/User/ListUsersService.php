<?php

namespace App\Services\User;

use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ListUsersService
{
    public function handle(array $filters): LengthAwarePaginator
    {
        $perPage = (int) ($filters['per_page'] ?? 10);
        $search = $filters['search'] ?? null;
        $role = $filters['role'] ?? null;

        return User::query()
            ->when($search, function ($query, string $search) {
                $query->where(function ($userQuery) use ($search): void {
                    $userQuery
                        ->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->when($role, fn ($query, string $role) => $query->where('role', $role))
            ->orderByDesc('created_at')
            ->paginate($perPage)
            ->withQueryString();
    }
}
