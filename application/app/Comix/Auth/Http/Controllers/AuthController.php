<?php

namespace App\Comix\Auth\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Symfony\Component\HttpFoundation\Response;
use App\Models\User;
use App\Comix\Auth\Http\Requests\RegisterRequest;
use App\Comix\Auth\Http\Requests\LoginRequest;
use App\Comix\Auth\Http\Requests\ForgotPasswordRequest;
use App\Comix\Auth\Http\Requests\ResetPasswordRequest;
use App\Comix\Auth\Http\Resources\UserResource;
use App\Comix\Auth\Dto\ResetPasswordDto;
use App\Comix\Auth\Dto\RegisterDto;


class AuthController
{
    public function register(RegisterRequest $request): JsonResponse
    {
        $dto = new RegisterDto(
            name: $request->validated('name'),
            email: $request->validated('email'),
            password: $request->validated('password'),
        );

        $user = User::create([
            'name' => $dto->name,
            'email' => $dto->email,
            'password' => Hash::make($dto->password),
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'user' => new UserResource(resource: $user),
            'token' => $token,
            'token_type' => 'Bearer'
        ], Response::HTTP_CREATED);
    }

    public function login(LoginRequest $request): JsonResponse
    {
        $credentials = $request->validated();

        if (!auth()->attempt($credentials)) {
            return response()->json([
                'message' => 'Unauthorized'
            ], Response::HTTP_UNAUTHORIZED);
        }

        $user = auth()->user();
        $user->tokens()->where('name', 'auth_token')->delete();
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'token' => $token,
            'token_type' => 'Bearer'
        ], Response::HTTP_OK);
    }

    public function forgot(ForgotPasswordRequest $request): JsonResponse
    {
        $email = $request->validated('email');
        $exists = DB::table('users')->where('email', $email)->exists();

        if (!$exists) {
            return response()->json([
                'message' => 'If the email exists, a reset token has been sent.'
            ], Response::HTTP_OK);
        }

        $token = Str::random(60);

        DB::table('password_reset_tokens')->where('email', $email)->delete();
        DB::table('password_reset_tokens')->insert([
            'email' => $email,
            'token' => Hash::make($token),
            'created_at' => now(),
        ]);

        // Отправляем токен (заглушка потом заменить на Mailpit)
        Log::info("Password reset token for {$email}: {$token}");

        return response()->json([
            'message' => 'If the email exists, a reset token has been sent.'
        ], Response::HTTP_OK);
    }

    public function reset(ResetPasswordRequest $request): JsonResponse
    {
        $dto = new ResetPasswordDto(
            email: $request->validated('email'),
            token: $request->validated('token'),
            password: $request->validated('password'),
        );
        
        $record = DB::table('password_reset_tokens')
            ->where('email', $dto->email)
            ->first();

        if (!$record || !Hash::check($dto->token, $record->token)) {
            return response()->json([
                'message' => 'Invalid token.'
            ], Response::HTTP_BAD_REQUEST);
        }

        $user = User::where('email', $dto->email)->first();
        $user->update(['password' => Hash::make($dto->password)]);

        DB::table('password_reset_tokens')->where('email', $dto->email)->delete();

        return response()->json([
            'message' => 'Password has been reset.'
        ], Response::HTTP_OK);
    }
}
