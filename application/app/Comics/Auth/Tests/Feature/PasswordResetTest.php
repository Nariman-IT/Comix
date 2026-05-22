<?php

namespace App\Comics\Auth\Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Response;

class PasswordResetTest extends TestCase
{
    use RefreshDatabase;

    private string $email = 'test@example.com';

    protected function setUp(): void
    {
        parent::setUp();

        User::create([
            'name' => 'Test User',
            'email' => $this->email,
            'password' => Hash::make('oldpassword'),
        ]);
    }

    public function test_forgot_password_returns_ok_for_existing_email(): void
    {
        Log::spy();

        $response = $this->postJson('/api/v1/auth/forgot-password', [
            'email' => $this->email,
        ]);

        $response->assertStatus(Response::HTTP_OK)
            ->assertJson(['message' => 'If the email exists, a reset token has been sent.']);

        $this->assertDatabaseHas('password_reset_tokens', [
            'email' => $this->email,
        ]);

        Log::shouldHaveReceived('info')->once();
    }

    public function test_forgot_password_returns_ok_for_non_existing_email(): void
    {
        $response = $this->postJson('/api/v1/auth/forgot-password', [
            'email' => 'nonexistent@example.com',
        ]);

        $response->assertStatus(Response::HTTP_OK)
            ->assertJson(['message' => 'If the email exists, a reset token has been sent.']);

        $this->assertDatabaseMissing('password_reset_tokens', [
            'email' => 'nonexistent@example.com',
        ]);
    }

    public function test_forgot_password_requires_email(): void
    {
        $response = $this->postJson('/api/v1/auth/forgot-password', []);

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJsonValidationErrors(['email']);
    }

    public function test_forgot_password_requires_valid_email(): void
    {
        $response = $this->postJson('/api/v1/auth/forgot-password', [
            'email' => 'not-an-email',
        ]);

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJsonValidationErrors(['email']);
    }

    public function test_forgot_password_replaces_old_token(): void
    {
        DB::table('password_reset_tokens')->insert([
            'email' => $this->email,
            'token' => Hash::make('old-token'),
            'created_at' => now(),
        ]);

        $this->assertDatabaseCount('password_reset_tokens', 1);

        $this->postJson('/api/v1/auth/forgot-password', [
            'email' => $this->email,
        ]);

        $this->assertDatabaseCount('password_reset_tokens', 1);
        $this->assertDatabaseMissing('password_reset_tokens', [
            'token' => Hash::make('old-token'),
        ]);
    }

    public function test_reset_password_changes_password(): void
    {
        $token = 'valid-reset-token';

        DB::table('password_reset_tokens')->insert([
            'email' => $this->email,
            'token' => Hash::make($token),
            'created_at' => now(),
        ]);

        $response = $this->postJson('/api/v1/auth/reset-password', [
            'email' => $this->email,
            'token' => $token,
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123',
        ]);

        $response->assertStatus(Response::HTTP_OK)
            ->assertJson(['message' => 'Password has been reset.']);

        $user = User::where('email', $this->email)->first();
        $this->assertTrue(Hash::check('newpassword123', $user->password));

        $this->assertDatabaseMissing('password_reset_tokens', [
            'email' => $this->email,
        ]);
    }

    public function test_reset_password_returns_error_for_invalid_token(): void
    {
        DB::table('password_reset_tokens')->insert([
            'email' => $this->email,
            'token' => Hash::make('correct-token'),
            'created_at' => now(),
        ]);

        $response = $this->postJson('/api/v1/auth/reset-password', [
            'email' => $this->email,
            'token' => 'wrong-token',
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123',
        ]);

        $response->assertStatus(Response::HTTP_BAD_REQUEST)
            ->assertJson(['message' => 'Invalid token.']);
    }

    public function test_reset_password_returns_error_for_non_existing_email(): void
    {
        $response = $this->postJson('/api/v1/auth/reset-password', [
            'email' => 'nonexistent@example.com',
            'token' => 'some-token',
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123',
        ]);

        $response->assertStatus(Response::HTTP_BAD_REQUEST)
            ->assertJson(['message' => 'Invalid token.']);
    }

    public function test_reset_password_requires_all_fields(): void
    {
        $response = $this->postJson('/api/v1/auth/reset-password', []);

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJsonValidationErrors(['email', 'token', 'password']);
    }

    public function test_reset_password_requires_password_confirmation(): void
    {
        $response = $this->postJson('/api/v1/auth/reset-password', [
            'email' => $this->email,
            'token' => 'some-token',
            'password' => 'newpassword123',
        ]);

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJsonValidationErrors(['password']);
    }
}