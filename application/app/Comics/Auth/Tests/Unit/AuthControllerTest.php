<?php

namespace App\Comics\Auth\Tests\Unit;

use Tests\TestCase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Response;
use Mockery;

class AuthControllerTest extends TestCase
{
    public function test_login_returns_unauthorized_when_credentials_invalid(): void
    {
        $this->withoutMiddleware(\Illuminate\Routing\Middleware\ThrottleRequests::class);

        Auth::shouldReceive('attempt')
            ->once()
            ->with(['email' => 'test@test.com', 'password' => 'password'])
            ->andReturn(false);

        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'test@test.com',
            'password' => 'password',
        ]);

        $response->assertStatus(Response::HTTP_UNAUTHORIZED)
            ->assertJson(['message' => 'Unauthorized']);
    }

    public function test_forgot_password_returns_same_message_for_non_existing_email(): void
    {
        DB::shouldReceive('table')
            ->with('users')
            ->andReturnSelf();

        DB::shouldReceive('where')
            ->with('email', 'nonexistent@test.com')
            ->andReturnSelf();

        DB::shouldReceive('exists')
            ->once()
            ->andReturn(false);

        $response = $this->postJson('/api/v1/auth/forgot-password', [
            'email' => 'nonexistent@test.com',
        ]);

        $response->assertStatus(Response::HTTP_OK)
            ->assertJson(['message' => 'If the email exists, a reset token has been sent.']);
    }

    public function test_forgot_password_creates_token_for_existing_email(): void
    {
        $email = 'user@test.com';

        DB::shouldReceive('table->where->exists')
            ->once()
            ->andReturn(true);

        DB::shouldReceive('table->where->delete')
            ->once()
            ->andReturn(0);

        DB::shouldReceive('table->insert')
            ->once()
            ->with(Mockery::subset([
                'email' => $email,
            ]));
        
        Log::shouldReceive('info')
            ->once()
            ->with(Mockery::pattern("/Password reset token for {$email}/"));

        $response = $this->postJson('/api/v1/auth/forgot-password', [
            'email' => $email,
        ]);

        $response->assertStatus(Response::HTTP_OK)
            ->assertJson(['message' => 'If the email exists, a reset token has been sent.']);
    }

    public function test_reset_password_returns_error_for_invalid_token(): void
    {
        DB::shouldReceive('table->where->first')
            ->once()
            ->andReturn((object) [
                'email' => 'user@test.com',
                'token' => Hash::make('valid-token'),
            ]);

        $response = $this->postJson('/api/v1/auth/reset-password', [
            'email' => 'user@test.com',
            'token' => 'wrong-token',
            'password' => 'newpassword',
            'password_confirmation' => 'newpassword',
        ]);

        $response->assertStatus(Response::HTTP_BAD_REQUEST)
            ->assertJson(['message' => 'Invalid token.']);
    }
}