<?php

use App\Models\User;

describe('Safe Auth Helper Functions', function () {
    beforeEach(function () {
        $this->adminUser = User::factory()->create([
            'email' => 'admin@tikm.com',
            'role' => 'admin',
            'name' => 'Test Admin',
        ]);

        $this->testUser = User::factory()->create([
            'role' => 'agent',
            'name' => 'Test Agent',
        ]);
    });

    describe('safe_auth_user()', function () {
        it('returns authenticated user when authenticated', function () {
            $this->actingAs($this->testUser);

            $user = safe_auth_user();

            expect($user)->not->toBeNull();
            expect($user->id)->toBe($this->testUser->id);
            expect($user->role)->toBe('agent');
        });

        it('returns admin user in local environment when not authenticated', function () {
            app()->instance('env', 'local');
            config(['app.env' => 'local']);

            $this->assertGuest();

            $user = safe_auth_user();

            expect($user)->not->toBeNull();
            expect($user->email)->toBe('admin@tikm.com');
            expect($user->role)->toBe('admin');
        });

        it('returns null in production environment when not authenticated', function () {
            app()->instance('env', 'production');
            config(['app.env' => 'production']);

            $this->assertGuest();

            $user = safe_auth_user();

            expect($user)->toBeNull();
        });

        it('returns null in testing environment when not authenticated', function () {
            app()->instance('env', 'testing');
            config(['app.env' => 'testing']);

            $this->assertGuest();

            $user = safe_auth_user();

            expect($user)->toBeNull();
        });
    });

    describe('safe_auth_id()', function () {
        it('returns authenticated user ID when authenticated', function () {
            $this->actingAs($this->testUser);

            $userId = safe_auth_id();

            expect($userId)->toBe($this->testUser->id);
        });

        it('returns admin user ID in local environment when not authenticated', function () {
            app()->instance('env', 'local');
            config(['app.env' => 'local']);

            $this->assertGuest();

            $userId = safe_auth_id();

            expect($userId)->toBe($this->adminUser->id);
        });

        it('returns null in production environment when not authenticated', function () {
            app()->instance('env', 'production');
            config(['app.env' => 'production']);

            $this->assertGuest();

            $userId = safe_auth_id();

            expect($userId)->toBeNull();
        });
    });

    describe('safe_auth_check()', function () {
        it('returns true when user is authenticated', function () {
            $this->actingAs($this->testUser);

            expect(safe_auth_check())->toBeTrue();
        });

        it('returns true in local environment when not authenticated', function () {
            app()->instance('env', 'local');
            config(['app.env' => 'local']);

            $this->assertGuest();

            expect(safe_auth_check())->toBeTrue();
        });

        it('returns false in production environment when not authenticated', function () {
            app()->instance('env', 'production');
            config(['app.env' => 'production']);

            $this->assertGuest();

            expect(safe_auth_check())->toBeFalse();
        });
    });

    describe('Performance and Caching', function () {
        it('does not make multiple database queries for same user', function () {
            app()->instance('env', 'local');
            config(['app.env' => 'local']);

            $this->assertGuest();

            // First call
            $user1 = safe_auth_user();

            // Second call - should use cached result
            $user2 = safe_auth_user();

            // Should return the same cached user instance
            expect($user1)->toBe($user2);
            expect($user1->id)->toBe($user2->id);
        });

        it('handles cache invalidation between environment changes', function () {
            // Test that cache is cleared when environment changes
            app()->instance('env', 'production');
            config(['app.env' => 'production']);

            $this->assertGuest();
            $prodUser = safe_auth_user();
            expect($prodUser)->toBeNull();

            // Switch to local environment
            app()->instance('env', 'local');
            config(['app.env' => 'local']);

            $localUser = safe_auth_user();
            expect($localUser)->not->toBeNull();
            expect($localUser->email)->toBe('admin@tikm.com');
        });
    });

    describe('Edge Cases', function () {
        it('handles user deletion while authenticated', function () {
            $this->actingAs($this->testUser);

            // Delete the user while they're "authenticated"
            $userId = $this->testUser->id;
            $this->testUser->delete();

            // Clear auth cache to simulate session expiry
            auth()->logout();

            // The functions should handle this gracefully
            $user = safe_auth_user();
            $authUserId = safe_auth_id();
            $check = safe_auth_check();

            // Since we're logged out, behavior depends on environment
            if (app()->environment('local')) {
                // In local, should fall back to admin user
                expect($user)->not->toBeNull();
                expect($user->email)->toBe('admin@tikm.com');
                expect($check)->toBeTrue();
            } else {
                // In other environments, should be null
                expect($user)->toBeNull();
                expect($authUserId)->toBeNull();
                expect($check)->toBeFalse();
            }
        });

        it('works with different environment variable formats', function () {
            // Test various ways the environment might be set
            $environments = [
                ['local', true],    // Should work
                ['LOCAL', false],   // Should not work (case sensitive)
                ['Local', false],   // Should not work (case sensitive)
                ['production', false], // Should not work
                ['testing', false],  // Should not work
            ];

            foreach ($environments as [$env, $shouldWork]) {
                app()->instance('env', $env);
                config(['app.env' => $env]);

                $this->assertGuest();

                if ($shouldWork) {
                    expect(safe_auth_user())->not->toBeNull();
                    expect(safe_auth_user()->email)->toBe('admin@tikm.com');
                } else {
                    expect(safe_auth_user())->toBeNull();
                }
            }
        });
    });
});
