<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Http\Resources\UserResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use App\Interfaces\UserInterface;

class UserController extends Controller
{
    private UserInterface $userRepository;

    public function __construct(UserInterface $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    public function login(LoginRequest $request): UserResource|JsonResponse
    {
        $userData = $request->only(['email', 'password']);

        if ($this->userRepository->login($userData)) {
            $user = auth()->user();
            $user->token = $user->createToken('P2P-Wallet')->accessToken;

            return new UserResource($user);
        } else {
            return response()->json(['message' => 'Invalid email or password'], Response::HTTP_UNAUTHORIZED);
        }
    }
    public function logout(): JsonResponse
    {
        $this->userRepository->logoutApi();

        return response()->json(null, Response::HTTP_NO_CONTENT);
    }
}
