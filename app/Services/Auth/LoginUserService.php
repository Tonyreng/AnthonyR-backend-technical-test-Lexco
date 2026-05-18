<?php

namespace App\Services\Auth;

use App\Http\Requests\Auth\LoginRequest;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class LoginUserService
{
    public function handle(LoginRequest $request): ?User
    {
        $credentials = $request->safe()->only(['email', 'password']);

        if (! Auth::guard('web')->attempt($credentials)) {
            return null;
        }

        $request->session()->regenerate();

        return Auth::guard('web')->user()?->fresh();
    }
}
