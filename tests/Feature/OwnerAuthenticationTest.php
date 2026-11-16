<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class OwnerAuthenticationTest extends TestCase
{
    use DatabaseTransactions;

    public function test_owner_logs_in_with_username_and_can_log_out(): void
    {
        $owner = User::factory()->create(['username' => 'pemilik']);

        $this->post(route('owner.login.store'), ['username' => ' PEMILIK ', 'password' => 'password'])
            ->assertRedirect(route('owner.home'));
        $this->assertAuthenticatedAs($owner);
        $this->get(route('owner.home'))->assertOk()->assertSee($owner->name);

        $this->post(route('owner.logout'))->assertRedirect(route('login'));
        $this->assertGuest();
    }

    public function test_guest_cannot_open_the_owner_area(): void
    {
        $this->withSession(['barber_name' => 'Kapster Uji'])
            ->get(route('owner.home'))->assertRedirect(route('login'));
    }

    public function test_wrong_password_is_rejected(): void
    {
        $owner = User::factory()->create();

        $this->from(route('login'))
            ->post(route('owner.login.store'), ['username' => $owner->username, 'password' => 'salah'])
            ->assertRedirect(route('login'))
            ->assertSessionHasErrors('username');
        $this->assertGuest();
    }

    public function test_disabled_owner_cannot_log_in_or_keep_using_a_session(): void
    {
        $owner = User::factory()->create(['disabled_at' => now()]);

        $this->post(route('owner.login.store'), ['username' => $owner->username, 'password' => 'password'])
            ->assertSessionHasErrors('username');
        $this->assertGuest();

        $this->actingAs($owner)->get(route('owner.home'))->assertRedirect(route('login'));
        $this->assertGuest();
    }

    public function test_csrf_is_required_for_login_and_logout(): void
    {
        $middleware = new class($this->app, $this->app['encrypter']) extends PreventRequestForgery
        {
            protected function runningUnitTests(): bool
            {
                return false;
            }
        };
        $this->app->instance(PreventRequestForgery::class, $middleware);

        $this->post(route('owner.login.store'))->assertStatus(419);

        $owner = User::factory()->create();
        $this->actingAs($owner)->post(route('owner.logout'))->assertStatus(419);
    }

    public function test_database_enforces_unique_usernames_case_insensitively(): void
    {
        User::factory()->create(['username' => 'pemilik']);

        $this->expectException(UniqueConstraintViolationException::class);
        User::factory()->create(['username' => 'PEMILIK']);
    }

    public function test_owner_can_be_provisioned_without_email_from_the_console(): void
    {
        $this->artisan('owner:create', ['username' => 'Pemilik', '--name' => 'Owner Uji'])
            ->expectsQuestion('Kata sandi Owner', 'contoh-kata-sandi')
            ->expectsQuestion('Ulangi kata sandi Owner', 'contoh-kata-sandi')
            ->assertSuccessful();

        $owner = User::where('username', 'pemilik')->firstOrFail();
        $this->assertTrue(Hash::check('contoh-kata-sandi', $owner->password));
        $this->assertSame('Owner Uji', $owner->name);
    }

    public function test_login_attempts_are_rate_limited(): void
    {
        for ($attempt = 0; $attempt < 10; $attempt++) {
            $this->postJson(route('owner.login.store'), ['username' => 'pemilik', 'password' => 'salah'])
                ->assertUnprocessable();
        }

        $this->postJson(route('owner.login.store'), ['username' => 'pemilik', 'password' => 'salah'])
            ->assertTooManyRequests();
    }
}
