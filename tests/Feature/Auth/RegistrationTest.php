<?php

namespace Tests\Feature\Auth;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Admin registration is not supported in this application.
 * Admin accounts are created via seeders/artisan commands.
 * These tests verify that the registration route does not exist.
 */
class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_screen_is_not_accessible(): void
    {
        // Admin self-registration is disabled in this application.
        $response = $this->get('/register');

        // Should return 404 (route does not exist)
        $response->assertStatus(404);
    }
}
