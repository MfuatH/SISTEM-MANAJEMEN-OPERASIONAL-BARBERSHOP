<?php

namespace Tests\Feature;

use App\Models\Service;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class BarberSessionTest extends TestCase
{
    use DatabaseTransactions;

    public function test_a_guest_can_start_a_session_with_a_text_name(): void
    {
        $userCount = User::query()->count();

        $this->get(route('home'))->assertOk()->assertSee('Nama Kapster')->assertSee('Masuk Owner');

        $this->post(route('barber.session.store'), ['name' => '  Bayu  '])
            ->assertRedirect(route('barber.dashboard'))
            ->assertSessionHas('barber_name', 'Bayu');

        $this->assertGuest();
        $this->assertSame($userCount, User::query()->count());
        $this->get(route('barber.dashboard'))->assertOk()->assertSee('Bayu')->assertSee('Tambah');
    }

    #[DataProvider('invalidNames')]
    public function test_invalid_names_do_not_start_a_session(mixed $name): void
    {
        $this->from(route('home'))
            ->post(route('barber.session.store'), ['name' => $name])
            ->assertRedirect(route('home'))
            ->assertSessionHasErrors('name')
            ->assertSessionMissing('barber_name');
    }

    public static function invalidNames(): array
    {
        return [['   '], [str_repeat('A', 121)], [['Bayu']]];
    }

    public function test_dashboard_and_entry_form_require_a_barber_session(): void
    {
        $this->get(route('barber.dashboard'))->assertRedirect(route('home'));
        $this->get(route('barber.entry-form'))->assertRedirect(route('home'));
    }

    public function test_a_barber_cannot_access_owner_routes_and_can_end_the_session(): void
    {
        $this->withSession(['barber_name' => 'Bayu'])
            ->get(route('owner.home'))->assertRedirect(route('login'));
        $this->get(route('owner.services.index'))->assertRedirect(route('login'));

        $this->post(route('barber.session.destroy'))
            ->assertRedirect(route('home'))
            ->assertSessionMissing('barber_name');
        $this->get(route('barber.dashboard'))->assertRedirect(route('home'));
    }

    public function test_starting_a_session_with_the_same_name_replaces_the_previous_context(): void
    {
        $this->post(route('barber.session.store'), ['name' => 'Bayu'])
            ->assertSessionHas('barber_name', 'Bayu');
        $previousSession = $this->app['session']->getId();

        $this->withSession(['prior_context' => 'previous session'])
            ->post(route('barber.session.store'), ['name' => 'Bayu'])
            ->assertSessionHas('barber_name', 'Bayu')
            ->assertSessionMissing('prior_context');

        $this->assertNotSame($previousSession, $this->app['session']->getId());
    }

    public function test_owner_login_clears_the_barber_context(): void
    {
        $owner = User::factory()->create();

        $this->withSession(['barber_name' => 'Bayu'])
            ->post(route('owner.login.store'), ['username' => $owner->username, 'password' => 'password'])
            ->assertRedirect(route('owner.home'))
            ->assertSessionMissing('barber_name');
        $this->assertAuthenticatedAs($owner);

        $this->get(route('barber.dashboard'))->assertRedirect(route('owner.home'));
        $this->post(route('barber.session.store'), ['name' => 'Bayu'])->assertRedirect(route('owner.home'));
    }

    public function test_entry_form_previews_prices_from_active_services_only(): void
    {
        Service::factory()->create(['name' => 'Layanan A', 'current_price' => 35000]);
        Service::factory()->create(['name' => 'Layanan B', 'current_price' => 15000, 'archived_at' => now()]);

        $this->withSession(['barber_name' => 'Bayu'])
            ->get(route('barber.entry-form'))->assertOk()
            ->assertSee('Pilih layanan')
            ->assertSee('Layanan A')
            ->assertSee('data-price="Rp35.000"', false)
            ->assertDontSee('Layanan B');
    }
}
