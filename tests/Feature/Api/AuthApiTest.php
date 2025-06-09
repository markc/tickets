<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create([
        'email' => 'test@example.com',
        'password' => bcrypt('password'),
        'role' => 'customer',
    ]);
});

test('user can login via API', function () {
    $response = $this->postJson('/api/auth/login', [
        'email' => 'test@example.com',
        'password' => 'password',
        'device_name' => 'mobile_app',
    ]);

    $response->assertOk()
        ->assertJsonStructure([
            'user' => [
                'id',
                'name',
                'email',
                'role',
                'avatar_url',
            ],
            'token',
            'expires_at',
        ]);

    expect($response->json('user.email'))->toBe('test@example.com');
    expect($response->json('token'))->toBeString();
});

test('user cannot login with invalid credentials', function () {
    $response = $this->postJson('/api/auth/login', [
        'email' => 'test@example.com',
        'password' => 'wrong-password',
        'device_name' => 'mobile_app',
    ]);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['email']);
});

test('login requires device name', function () {
    $response = $this->postJson('/api/auth/login', [
        'email' => 'test@example.com',
        'password' => 'password',
    ]);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['device_name']);
});

test('authenticated user can get profile', function () {
    Sanctum::actingAs($this->user);

    $response = $this->getJson('/api/auth/user');

    $response->assertOk()
        ->assertJsonStructure([
            'user' => [
                'id',
                'name',
                'email',
                'role',
                'avatar_url',
                'offices',
            ],
        ]);

    expect($response->json('user.email'))->toBe('test@example.com');
});

test('user can logout', function () {
    $tokenResult = $this->user->createToken('mobile_app');
    $token = $tokenResult->plainTextToken;

    $response = $this->withHeader('Authorization', 'Bearer '.$token)
        ->postJson('/api/auth/logout');

    $response->assertOk()
        ->assertJson([
            'message' => 'Successfully logged out',
        ]);

    // Verify token is deleted from database
    $this->assertDatabaseMissing('personal_access_tokens', [
        'id' => $tokenResult->accessToken->id,
    ]);
});

test('user can logout from all devices', function () {
    $tokenResult1 = $this->user->createToken('mobile_app');
    $tokenResult2 = $this->user->createToken('web_app');

    $response = $this->withHeader('Authorization', 'Bearer '.$tokenResult1->plainTextToken)
        ->postJson('/api/auth/logout-all');

    $response->assertOk()
        ->assertJson([
            'message' => 'All sessions terminated',
        ]);

    // Verify both tokens are deleted from database
    $this->assertDatabaseMissing('personal_access_tokens', [
        'id' => $tokenResult1->accessToken->id,
    ]);

    $this->assertDatabaseMissing('personal_access_tokens', [
        'id' => $tokenResult2->accessToken->id,
    ]);
});

test('user can refresh token', function () {
    $tokenResult = $this->user->createToken('mobile_app');
    $token = $tokenResult->plainTextToken;

    $response = $this->withHeader('Authorization', 'Bearer '.$token)
        ->postJson('/api/auth/refresh');

    $response->assertOk()
        ->assertJsonStructure([
            'token',
            'expires_at',
        ]);

    $newToken = $response->json('token');
    expect($newToken)->toBeString();
    expect($newToken)->not->toBe($token);

    // Verify old token is deleted from database
    $this->assertDatabaseMissing('personal_access_tokens', [
        'id' => $tokenResult->accessToken->id,
    ]);

    // Verify new token works
    $this->withHeader('Authorization', 'Bearer '.$newToken)
        ->getJson('/api/auth/user')
        ->assertOk();
});

test('unauthenticated requests are rejected', function () {
    $this->getJson('/api/auth/user')
        ->assertUnauthorized();

    $this->postJson('/api/auth/logout')
        ->assertUnauthorized();

    $this->getJson('/api/tickets')
        ->assertUnauthorized();
});

test('API status endpoint is public', function () {
    $response = $this->getJson('/api/status');

    $response->assertOk()
        ->assertJsonStructure([
            'api',
            'version',
            'status',
            'documentation',
        ]);
});

test('API health check requires authentication', function () {
    $this->getJson('/api/health')
        ->assertUnauthorized();

    Sanctum::actingAs($this->user);

    $response = $this->getJson('/api/health');

    $response->assertOk()
        ->assertJsonStructure([
            'status',
            'timestamp',
            'version',
        ]);
});
