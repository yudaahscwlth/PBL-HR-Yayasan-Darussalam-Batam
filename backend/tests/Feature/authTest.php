<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class authTest extends TestCase
{
    use RefreshDatabase;
    /**
     * A basic feature test example.
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    public function test_admin_user_redirected_to_hrd_dashboard()
    {
        $user = User::where('email', 'superadmin@gmail.com')->firstOrFail();

        $response = $this->post(route('login.process'), [
            'email' => 'superadmin@gmail.com',
            'password' => 'password',
        ]);

        $response->assertRedirect(route('hrd.dashboard.page'));
        $this->assertAuthenticatedAs($user);
    }

    public function test_pegawai_user_redirected_to_pegawai_dashboard()
    {
        $user = User::where('email', 'tendik@gmail.com')->firstOrFail();

        $response = $this->post(route('login.process'), [
            'email' => 'tendik@gmail.com',
            'password' => 'password',
        ]);

        $response->assertRedirect(route('pegawai.dashboard.page'));
        $this->assertAuthenticatedAs($user);
    }

    public function test_login_fails_with_invalid_credentials()
    {

        $response = $this->from(route('login.page'))->post(route('login.process'), [
            'email' => 'fail@example.com',
            'password' => 'wrong_password',
        ]);

        $response->assertRedirect(route('login.page'));
        $response->assertSessionHas('notifikasi', 'Login Failed!');
        $this->assertGuest();
    }
}
