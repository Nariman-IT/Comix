<?php

namespace App\Comics\Auth\Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Response;

class LoginTest extends TestCase
{
    use RefreshDatabase;

    private string $password = 'password123';

    protected function setUp(): void
    {
        parent::setUp();

        User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => Hash::make($this->password),
        ]);
    }

    public function test_user_can_login(): void
    {
        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'test@example.com',
            'password' => $this->password,
        ]);

        $response->assertStatus(Response::HTTP_OK)
            ->assertJsonStructure(['token', 'token_type'])
            ->assertJsonPath('token_type', 'Bearer');

        $this->assertDatabaseHas('personal_access_tokens', [
            'tokenable_type' => User::class,
            'name' => 'auth_token',
        ]);
    }

    public function test_login_returns_401_for_wrong_password(): void
    {
        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'test@example.com',
            'password' => 'wrongpassword',
        ]);

        $response->assertStatus(Response::HTTP_UNAUTHORIZED)
            ->assertJson(['message' => 'Unauthorized']);
    }

    public function test_login_returns_401_for_non_existing_user(): void
    {
        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'nonexistent@example.com',
            'password' => $this->password,
        ]);

        $response->assertStatus(Response::HTTP_UNAUTHORIZED)
            ->assertJson(['message' => 'Unauthorized']);
    }

    public function test_login_requires_email(): void
    {
        $response = $this->postJson('/api/v1/auth/login', [
            'password' => $this->password,
        ]);

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJsonValidationErrors(['email']);
    }

    public function test_login_requires_password(): void
    {
        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'test@example.com',
        ]);

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJsonValidationErrors(['password']);
    }

    public function test_login_deletes_old_tokens_and_creates_new(): void
    {
        $user = User::where('email', 'test@example.com')->first();
        
        $user->createToken('auth_token');
        $this->assertDatabaseCount('personal_access_tokens', 1);

        $this->postJson('/api/v1/auth/login', [
            'email' => 'test@example.com',
            'password' => $this->password,
        ]);

        $this->assertDatabaseCount('personal_access_tokens', 1);
    }
}