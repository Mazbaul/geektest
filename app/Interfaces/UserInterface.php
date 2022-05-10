<?php

namespace App\Interfaces;

use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

interface UserInterface
{
    public function login(array $userData): bool;
    public function logoutApi(): void;
    public function getAuthUser(): User;
    public function getUserById(int $userId): User;
    public function getAllUsers(): Collection;
    public function updateUser(int $userId, array $newDetails): int;
}
