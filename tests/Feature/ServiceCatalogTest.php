<?php

namespace Tests\Feature;

use App\Models\Service;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class ServiceCatalogTest extends TestCase
{
    use DatabaseTransactions;

    public function test_owner_can_add_and_update_services(): void
    {
        $this->actingAs(User::factory()->create());

        $this->get(route('owner.home'))->assertSee('Katalog layanan');
        $this->get(route('owner.services.create'))->assertOk()->assertSee('Tambah layanan');

        $this->post(route('owner.services.store'), ['name' => 'Layanan A', 'current_price' => '35000'])
            ->assertRedirect(route('owner.services.index'));
        $service = Service::query()->where('name', 'Layanan A')->sole();
        $this->assertSame('35000', $service->current_price);

        $this->get(route('owner.services.edit', $service))->assertOk()->assertSee('Layanan A');
        $this->put(route('owner.services.update', $service), ['name' => 'Layanan A', 'current_price' => '40000'])
            ->assertRedirect(route('owner.services.index'));
        $this->assertSame('40000', $service->fresh()->current_price);
        $this->get(route('owner.services.index'))->assertSee('Rp40.000');
    }

    public function test_archiving_retains_the_service_and_excludes_it_from_the_active_list(): void
    {
        $owner = User::factory()->create();
        $active = Service::factory()->create(['name' => 'Layanan A', 'current_price' => 35000]);
        $archived = Service::factory()->create(['name' => 'Layanan B', 'current_price' => 15000]);

        $this->actingAs($owner)->patch(route('owner.services.archive', $archived))
            ->assertRedirect(route('owner.services.index'));
        $archivedAt = $archived->fresh()->archived_at;
        $this->assertNotNull($archivedAt);

        $this->patch(route('owner.services.archive', $archived))
            ->assertRedirect(route('owner.services.index'));
        $this->assertTrue($archivedAt->equalTo($archived->fresh()->archived_at));
        $this->get(route('owner.services.index'))->assertSee('Layanan B')->assertSee('Diarsipkan');

        $this->app['auth']->guard()->logout();
        $this->getJson(route('services.active'))->assertOk()
            ->assertJsonFragment(['id' => $active->id, 'name' => 'Layanan A', 'current_price' => '35000'])
            ->assertJsonMissing(['name' => 'Layanan B'])
            ->assertJsonMissingPath('data.0.archived_at');
    }

    public function test_guest_cannot_manage_services(): void
    {
        $service = Service::factory()->create(['name' => 'Layanan A', 'current_price' => 35000]);
        $input = ['name' => 'Layanan B', 'current_price' => '15000'];

        $this->withSession(['barber_name' => 'Kapster Uji']);
        $this->get(route('owner.services.index'))->assertRedirect(route('login'));
        $this->get(route('owner.services.create'))->assertRedirect(route('login'));
        $this->post(route('owner.services.store'), $input)->assertRedirect(route('login'));
        $this->get(route('owner.services.edit', $service))->assertRedirect(route('login'));
        $this->put(route('owner.services.update', $service), $input)->assertRedirect(route('login'));
        $this->patch(route('owner.services.archive', $service))->assertRedirect(route('login'));

        $this->assertDatabaseHas('services', ['id' => $service->id, 'name' => 'Layanan A', 'archived_at' => null]);
        $this->assertDatabaseMissing('services', ['name' => 'Layanan B']);
    }

    #[DataProvider('invalidPrices')]
    public function test_invalid_prices_are_rejected(mixed $price): void
    {
        $this->actingAs(User::factory()->create())
            ->postJson(route('owner.services.store'), ['name' => 'Layanan Uji', 'current_price' => $price])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('current_price');

        $this->assertDatabaseMissing('services', ['name' => 'Layanan Uji']);
    }

    public static function invalidPrices(): array
    {
        return [[0], [-1], ['35000.50'], ['1000000000000000'], [true]];
    }

    public function test_mysql_rejects_nonpositive_prices(): void
    {
        $this->expectException(QueryException::class);
        Service::factory()->create(['current_price' => 0]);
    }
}
