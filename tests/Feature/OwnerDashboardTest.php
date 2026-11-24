<?php

namespace Tests\Feature;

use App\Models\Expense;
use App\Models\IncomeTransaction;
use App\Models\Product;
use App\Models\Service;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Tests\TestCase;

class OwnerDashboardTest extends TestCase
{
    use DatabaseTransactions;

    public function test_dashboard_uses_current_wib_month_for_totals_and_shows_zero_filled_days_through_today(): void
    {
        $this->travelTo(Carbon::parse('2026-11-02 17:00:00', 'UTC'));
        $owner = User::factory()->create();
        $this->actingAs($owner);

        $this->service($this->transaction('2026-10-31 16:59:59.999999'), '70000');
        $firstDay = $this->transaction('2026-10-31 17:00:00.000000');
        $this->service($firstDay, '35000');
        $this->service($firstDay, '15000');
        $product = Product::create(['name' => 'Hair Tonic', 'sale_price' => '17500']);
        $firstDay->productSales()->create([
            'product_id' => $product->id, 'product_name' => $product->name, 'unit_price' => '17500', 'quantity' => 2,
        ]);
        $this->service($this->transaction('2026-11-30 17:00:00.000000'), '12000');
        $this->expense($owner, '2026-11-02', '20000');
        $this->expense($owner, '2026-11-04', '5000');
        $this->expense($owner, '2026-12-01', '9000');

        $dashboard = $this->get(route('owner.home', ['month' => '2026-10']))->assertOk()
            ->assertSee('November 2026')->assertSee('Grafik harian')->assertSee('03/11/2026')
            ->assertSee('Rp85.000')->assertSee('Rp25.000')->assertSee('Rp60.000')->assertDontSee('04/11/2026')
            ->assertSee('Total hasil bersih bulan berjalan')->assertSee('Garis (Line Chart)')
            ->assertSee('name="dashboard-chart-type" value="bar" checked', false)
            ->assertSee('data-series="income" aria-pressed="true"', false)
            ->assertSee('data-series="expenses" aria-pressed="true"', false)
            ->assertSee('js/owner-dashboard.js')
            ->assertSee(route('owner.income.index'))->assertSee(route('owner.expenses.index'))->assertSee(route('owner.recap.index'));
        $this->assertSame(['income' => '85000', 'expenses' => '25000', 'net' => '60000'], $dashboard->viewData('totals'));
        $this->assertSame([
            ['date' => '2026-11-01', 'income' => '85000', 'expenses' => '0'],
            ['date' => '2026-11-02', 'income' => '0', 'expenses' => '20000'],
            ['date' => '2026-11-03', 'income' => '0', 'expenses' => '0'],
        ], $dashboard->viewData('days'));

        $recap = $this->get(route('owner.recap.index', ['month' => '2026-11']))->assertOk();
        $this->assertSame($dashboard->viewData('totals'), $recap->viewData('summary'));
    }

    public function test_month_rollover_starts_a_zero_series_and_keeps_previous_month_in_recap(): void
    {
        $owner = User::factory()->create();
        $this->actingAs($owner);
        $this->service($this->transaction('2026-11-30 16:59:59.999999'), '35000');
        $this->expense($owner, '2026-11-30', '5000');

        $this->travelTo(Carbon::parse('2026-11-30 16:59:59.999999', 'UTC'));
        $november = $this->get(route('owner.home'))->assertOk()->assertSee('November 2026');
        $this->assertCount(30, $november->viewData('days'));
        $this->assertSame(
            ['date' => '2026-11-30', 'income' => '35000', 'expenses' => '5000'],
            $november->viewData('days')[29],
        );

        $this->travelTo(Carbon::parse('2026-11-30 17:00:00.000000', 'UTC'));
        $december = $this->get(route('owner.home'))->assertOk()
            ->assertSee('Desember 2026')->assertSee('Rp0')->assertDontSee('30/11/2026');
        $this->assertSame(['income' => '0', 'expenses' => '0', 'net' => '0'], $december->viewData('totals'));
        $this->assertSame([['date' => '2026-12-01', 'income' => '0', 'expenses' => '0']], $december->viewData('days'));

        $recap = $this->get(route('owner.recap.index', ['month' => '2026-11']))->assertOk();
        $this->assertSame(['income' => '35000', 'expenses' => '5000', 'net' => '30000'], $recap->viewData('summary'));

        $this->expense($owner, '2026-12-01', '5000');
        $negative = $this->get(route('owner.home'))->assertOk()->assertSee('-Rp5.000');
        $this->assertSame(['income' => '0', 'expenses' => '5000', 'net' => '-5000'], $negative->viewData('totals'));
    }

    public function test_owner_pages_share_navigation_and_only_income_opens_catalog_dialogs(): void
    {
        $this->actingAs(User::factory()->create());
        $routes = ['owner.home', 'owner.income.index', 'owner.expenses.index', 'owner.recap.index'];

        foreach ($routes as $route) {
            $page = $this->get(route($route))->assertOk()
                ->assertSee('aria-label="Menu Owner"', false)
                ->assertSee('aria-current="page"', false);

            foreach ($routes as $menuRoute) {
                $page->assertSee('href="'.route($menuRoute).'"', false);
            }

            if ($route === 'owner.income.index') {
                $page->assertSee('id="owner-catalog-dialog"', false)
                    ->assertSee('data-catalog-url="'.route('owner.services.index', absolute: false).'"', false)
                    ->assertSee('data-catalog-url="'.route('owner.products.index', absolute: false).'"', false)
                    ->assertSee('aria-haspopup="dialog"', false);
            } else {
                $page->assertDontSee('data-catalog-url=', false)->assertDontSee('id="owner-catalog-dialog"', false);
            }
        }

        foreach (['owner.services.index', 'owner.products.index'] as $catalog) {
            $this->get(route($catalog), ['Sec-Fetch-Dest' => 'iframe'])->assertOk()
                ->assertDontSee('Kembali ke Pemasukan')->assertDontSee('aria-label="Menu Owner"', false);
        }
    }

    private function transaction(string $recordedAt): IncomeTransaction
    {
        return IncomeTransaction::create([
            'barber_name' => 'Bayu',
            'recorded_at' => $recordedAt,
            'session_scope' => hash('sha256', 'owner dashboard fixture', true),
            'request_key' => (string) Str::uuid(),
            'request_hash' => hash('sha256', $recordedAt, true),
        ]);
    }

    private function service(IncomeTransaction $transaction, string $amount): void
    {
        $service = Service::factory()->create(['current_price' => $amount]);
        $transaction->entries()->create([
            'service_id' => $service->id, 'service_name' => $service->name, 'amount' => $service->current_price,
        ]);
    }

    private function expense(User $owner, string $date, string $amount): void
    {
        Expense::create([
            'recorded_by_user_id' => $owner->id,
            'incurred_on' => $date,
            'description' => 'Perlengkapan',
            'amount' => $amount,
            'created_at' => now('UTC'),
            'request_key' => (string) Str::uuid(),
            'request_hash' => hash('sha256', $date, true),
        ]);
    }
}
