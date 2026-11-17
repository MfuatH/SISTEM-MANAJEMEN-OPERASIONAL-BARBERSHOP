<?php

namespace Tests\Feature;

use App\Models\IncomeEntry;
use App\Models\Service;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Tests\TestCase;

class BarberIncomeTest extends TestCase
{
    use DatabaseTransactions;

    public function test_two_services_create_two_entries_and_retry_keeps_the_total_exact(): void
    {
        $firstService = Service::factory()->create(['name' => 'Layanan A', 'current_price' => '35000']);
        $secondService = Service::factory()->create(['name' => 'Layanan B', 'current_price' => '15000']);
        $this->startBarberSession();
        $first = $this->entryPayload($firstService) + ['customer_name' => ' Andi ', 'barber_name' => 'Diubah'];
        $this->post(route('barber.entries.store'), $first)->assertRedirect(route('barber.dashboard'));
        $second = $this->entryPayload($secondService) + ['customer_name' => '  '];
        $this->post(route('barber.entries.store'), $second)->assertRedirect(route('barber.dashboard'));
        $this->post(route('barber.entries.store'), $first)->assertRedirect(route('barber.dashboard'));

        $entries = IncomeEntry::query()->whereIn('request_key', [$first['request_key'], $second['request_key']])->get()
            ->keyBy('request_key');

        $this->assertCount(2, $entries);
        $this->assertSame(0, 35_000 + 15_000 - (int) $entries->sum('amount'));
        $this->assertSame('Bayu', $entries[$first['request_key']]->barber_name);
        $this->assertSame('Andi', $entries[$first['request_key']]->customer_name);
        $this->assertNull($entries[$second['request_key']]->customer_name);
        $this->assertTrue($entries[$first['request_key']]->service->is($firstService));

        $this->get(route('barber.dashboard'))->assertOk()
            ->assertSee('Andi')->assertSee('Layanan A')->assertSee('Rp35.000')
            ->assertSee('Layanan B')->assertSee('Rp15.000');
    }

    public function test_a_changed_catalog_price_requires_review_and_preserves_earlier_snapshots(): void
    {
        $service = Service::factory()->create(['name' => 'Layanan A', 'current_price' => '35000']);
        $this->startBarberSession();
        $this->travelTo(Carbon::parse('2026-09-30 16:59:59.123456', 'UTC'));

        $prior = $this->entryPayload($service);
        $this->post(route('barber.entries.store'), $prior)->assertRedirect(route('barber.dashboard'));
        $stale = $this->entryPayload($service);
        $service->update(['name' => 'Layanan A Baru', 'current_price' => '40000']);

        $this->post(route('barber.entries.store'), $stale)
            ->assertRedirect(route('barber.entry-form'))
            ->assertSessionHasErrors('quoted_price');
        $this->assertDatabaseMissing('income_entries', ['request_key' => $stale['request_key']]);
        $this->get(route('barber.entry-form'))->assertOk()->assertSee('Harga layanan berubah.')->assertSee('Rp40.000');
        $fresh = $this->entryPayload($service);
        $this->assertNotSame($stale['request_key'], $fresh['request_key']);
        $this->post(route('barber.entries.store'), $fresh)->assertRedirect(route('barber.dashboard'));

        $original = IncomeEntry::query()->where('request_key', $prior['request_key'])->sole();
        $this->assertSame('35000', $original->amount);
        $this->assertSame('Layanan A', $original->service_name);
        $this->assertSame('2026-09-30 16:59:59.123456', $original->recorded_at->format('Y-m-d H:i:s.u'));
        $this->assertDatabaseHas('income_entries', ['request_key' => $fresh['request_key'], 'amount' => '40000']);
    }

    public function test_archive_blocks_new_entries_but_allows_retry_of_a_saved_entry(): void
    {
        $service = Service::factory()->create();
        $this->startBarberSession();
        $saved = $this->entryPayload($service);
        $this->post(route('barber.entries.store'), $saved)->assertRedirect(route('barber.dashboard'));
        $new = $this->entryPayload($service);
        $service->forceFill(['archived_at' => now()])->save();

        $this->post(route('barber.entries.store'), $new)
            ->assertRedirect(route('barber.entry-form'))->assertSessionHasErrors('service_id');
        $this->post(route('barber.entries.store'), $saved)->assertRedirect(route('barber.dashboard'));
        $this->assertDatabaseMissing('income_entries', ['request_key' => $new['request_key']]);
        $this->assertSame(1, IncomeEntry::query()->where('request_key', $saved['request_key'])->count());
    }

    public function test_a_used_form_rejects_a_different_payload(): void
    {
        $service = Service::factory()->create();
        $this->startBarberSession();
        $payload = $this->entryPayload($service) + ['customer_name' => 'Andi'];

        $this->post(route('barber.entries.store'), $payload)->assertRedirect(route('barber.dashboard'));
        $this->post(route('barber.entries.store'), array_replace($payload, ['customer_name' => 'Ari']))
            ->assertRedirect(route('barber.entry-form'))->assertSessionHasErrors('request_key');

        $this->assertSame('Andi', IncomeEntry::query()->where('request_key', $payload['request_key'])->sole()->customer_name);
    }

    public function test_sessions_with_the_same_name_have_separate_entries_and_form_keys(): void
    {
        $service = Service::factory()->create();
        $firstSession = $this->startBarberSession();
        $first = $this->entryPayload($service) + ['customer_name' => 'Pelanggan Pertama'];
        $this->post(route('barber.entries.store'), $first)->assertRedirect(route('barber.dashboard'));

        $this->withCookie(config('session.cookie'), Str::random(40));
        $secondSession = $this->startBarberSession();
        $this->assertNotSame($firstSession, $secondSession);
        $this->get(route('barber.dashboard'))->assertOk()
            ->assertSee('Belum ada pencatatan.')->assertDontSee('Pelanggan Pertama');
        $this->post(route('barber.entries.store'), $first)
            ->assertRedirect(route('barber.entry-form'))->assertSessionHasErrors('request_key');
        $second = $this->entryPayload($service) + ['customer_name' => 'Pelanggan Kedua'];
        $this->post(route('barber.entries.store'), $second)->assertRedirect(route('barber.dashboard'));
        $this->get(route('barber.dashboard'))->assertOk()
            ->assertSee('Pelanggan Kedua')->assertDontSee('Pelanggan Pertama');

        $this->withCookie(config('session.cookie'), $firstSession);
        $this->get(route('barber.dashboard'))->assertOk()
            ->assertSee('Pelanggan Pertama')->assertDontSee('Pelanggan Kedua');
        $this->post(route('barber.session.destroy'))->assertRedirect(route('home'));
        $this->withCookie(config('session.cookie'), session()->getId());
        $this->startBarberSession();
        $this->get(route('barber.dashboard'))->assertOk()->assertSee('Belum ada pencatatan.');
        $this->assertDatabaseHas('income_entries', ['request_key' => $first['request_key']]);
    }

    public function test_validation_rejects_multiple_services_and_invalid_customer_names(): void
    {
        $service = Service::factory()->create();
        $this->startBarberSession();
        $form = $this->entryPayload($service);
        $payload = array_replace($form, ['service_id' => [$service->id], 'customer_name' => str_repeat('A', 121)]);

        $this->post(route('barber.entries.store'), $payload)
            ->assertRedirect(route('barber.entry-form'))->assertSessionHasErrors(['service_id', 'customer_name']);
        $this->get(route('barber.entry-form'))->assertOk();
        $this->assertDatabaseMissing('income_entries', ['request_key' => $form['request_key']]);
    }

    public function test_store_requires_a_guest_barber_session(): void
    {
        $this->post(route('barber.entries.store'))->assertRedirect(route('home'));
        $this->actingAs(User::factory()->create())
            ->post(route('barber.entries.store'))->assertRedirect(route('owner.home'));
    }

    private function startBarberSession(): string
    {
        $this->post(route('barber.session.store'), ['name' => 'Bayu'])->assertRedirect(route('barber.dashboard'));
        $sessionId = session()->getId();
        $this->withCookie(config('session.cookie'), $sessionId);

        return $sessionId;
    }

    private function entryPayload(Service $service): array
    {
        $form = $this->get(route('barber.entry-form'))->assertOk();

        return [
            'request_key' => $form->viewData('requestKey'),
            'request_signature' => $form->viewData('requestSignature'),
            'service_id' => $service->id,
            'quoted_price' => $service->current_price,
        ];
    }
}
