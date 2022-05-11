<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use App\Models\User;
use Tests\TestCase;

class ApiUserControllerTest extends TestCase
{
    public function test_login_validation()
    {
        $this->json('POST', 'api/login')
            ->assertInvalid([
                'email' => 'The email field is required.',
                'password' => 'The password field is required.',
            ]);
    }

    public function test_login_invalid_email_password()
    {
        $user = User::factory()->create(['password' => bcrypt('123456')]);
        $this->json('POST', 'api/login', ['email' => $user->email, 'password' => 'abcxyz'])->assertUnauthorized();
    }

    public function test_login_successful()
    {
        $user = User::factory()->create(['password' => bcrypt('123456')]);
        $response = $this->json('POST', 'api/login', ['email' => $user->email, 'password' => '123456']);
        $response->assertOk();
        $this->assertArrayHasKey('token', $response->json()['data']);

        $this->assertAuthenticated();
    }


    public function test_logout_successful()
    {
        $user = User::factory()->create(['password' => bcrypt('123456')]);
        $response = $this->json('POST', 'api/login', ['email' => $user->email, 'password' => '123456']);
        $response->assertOk();
        $token = $response->json()['data']['token'];

        $this->json('POST', 'api/logout', [], ['Authorization' => 'Bearer '.$token])->assertNoContent();
    }
}
