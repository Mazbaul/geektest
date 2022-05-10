<?php

namespace App\Repositories;

use App\Interfaces\UserInterface;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

class UserRepository implements UserInterface
{
    public function login(array $userData): bool
    {
        return auth()->attempt($userData);
    }

    public function logoutApi(): void
    {
        auth()->user()->token()->revoke();
    }

    public function getAuthUser(): User
    {
        return auth()->user();
    }

    public function getUserById(int $userId): User
    {
        return User::findOrFail($userId);
    }

    public function getAllUsers(): Collection
    {
        return User::all();
    }

    public function updateUser(int $userId, array $newDetails): int
    {
        return User::whereId($userId)->update($newDetails);
    }
}
