<?php

namespace Tests\Feature;

use App\Models\IncomeTransaction;
use App\Models\Product;
use App\Models\Service;
use App\Services\Forecasting\HistoryPackage;
use App\Services\Forecasting\MonthlyRevenueHistory;
use App\Services\OwnerFinancialEntries;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\TestCase;

class ForecastHistoryTest extends TestCase
{
    use DatabaseTransactions;

    private array $directories = [];

    protected function setUp(): void
    {
        parent::setUp();

        $this->travelTo(Carbon::parse('2026-10-14 07:00:00', 'UTC'));
    }

    protected function tearDown(): void
    {
        foreach ($this->directories as $directory) {
            foreach (['transactions.csv', 'transaction_items.csv', 'monthly_revenues.csv', 'manifest.json'] as $file) {
                unlink($directory.DIRECTORY_SEPARATOR.$file);
            }
            rmdir($directory);
        }

        parent::tearDown();
    }

    public function test_preview_reconciles_multi_item_amount_and_import_reuses_the_same_batch(): void
    {
        $package = $this->package('preview-and-import');

        $this->artisan('forecast:history-import', ['directory' => $package, '--preview' => true])
            ->expectsOutputToContain('Transaksi: 15 | Item: 17 | Nama pelanggan kosong: 14')
            ->expectsOutputToContain('277000')->assertSuccessful();
        $this->assertDatabaseCount('historical_imports', 0);
        $this->assertDatabaseCount('forecast_month_sources', 0);

        $this->artisan('forecast:history-import', ['directory' => $package, '--activate' => true])->assertSuccessful();
        $id = DB::table('historical_imports')->value('id');
        $this->artisan('forecast:history-import', ['directory' => $package, '--activate' => true])
            ->expectsOutput("Batch #$id tersimpan atau sudah tersedia.")->assertSuccessful();

        $this->assertDatabaseCount('historical_imports', 1);
        $this->assertDatabaseCount('historical_monthly_revenues', 3);
        $this->assertDatabaseCount('forecast_month_sources', 3);
        $this->assertDatabaseCount('income_transactions', 0);

        $window = app(MonthlyRevenueHistory::class)->window();
        $this->assertSame('available', $window['status']);
        $this->assertSame('synthetic', $window['data_kind']);
        $this->assertSame('2026-10', $window['target_month']);
        $this->assertSame(['2026-07', '2026-08', '2026-09'], array_column($window['actual'], 'month'));
        $this->assertSame(['0', '277000', '0'], array_column($window['actual'], 'amount'));
        $this->assertSame([0, 15, 0], array_column($window['actual'], 'transaction_count'));
    }

    public function test_invalid_package_hash_and_reconciliation_fail_before_persistence(): void
    {
        $badTotal = $this->package('wrong-total', amount: '277001');
        $this->artisan('forecast:history-import', ['directory' => $badTotal, '--activate' => true])
            ->expectsOutputToContain('Rekap bulanan tidak cocok')->assertFailed();

        $tampered = $this->package('tampered');
        file_put_contents(storage_path("app/private/imports/$tampered/transactions.csv"), 'tampered');
        $this->artisan('forecast:history-import', ['directory' => $tampered, '--preview' => true])
            ->expectsOutputToContain('SHA-256')->assertFailed();

        $this->assertDatabaseCount('historical_imports', 0);
        $this->assertDatabaseCount('forecast_month_sources', 0);
    }

    public function test_activation_conflict_rolls_back_batch_and_preceding_month_selection(): void
    {
        $history = app(MonthlyRevenueHistory::class);
        $history->selectSource('2026-08', 'application', null);
        $package = $this->package('conflict');

        $this->artisan('forecast:history-import', ['directory' => $package, '--activate' => true])
            ->expectsOutputToContain('2026-08-01 sudah memiliki sumber lain')->assertFailed();

        $this->assertDatabaseCount('historical_imports', 0);
        $this->assertDatabaseCount('historical_monthly_revenues', 0);
        $this->assertDatabaseMissing('forecast_month_sources', ['month_start' => '2026-07-01']);
        $this->assertDatabaseHas('forecast_month_sources', [
            'month_start' => '2026-08-01', 'source' => 'application', 'import_id' => null,
        ]);
    }

    public function test_source_selection_uses_either_import_or_operational_items_and_respects_wib_boundaries(): void
    {
        $history = app(MonthlyRevenueHistory::class);
        $importId = $history->import(HistoryPackage::fromDirectory($this->package('overlap')), true, null);
        $this->recordTransaction('2026-07-31 16:59:59.999999', withProduct: false);
        $this->recordTransaction('2026-07-31 17:00:00.000000', withProduct: true);
        $this->recordTransaction('2026-08-31 17:00:00.000000', withProduct: false);
        $this->assertSame('277000', $history->window()['actual'][1]['amount']);

        $this->artisan('forecast:month-source', ['month' => '2026-08', 'source' => 'application'])->assertSuccessful();
        $window = $history->window('2026-10');

        $this->assertSame('mixed', $window['data_kind']);
        $this->assertSame(['0', '48000', '0'], array_column($window['actual'], 'amount'));
        $this->assertSame([0, 1, 0], array_column($window['actual'], 'transaction_count'));
        $this->assertSame('application', $window['actual'][1]['source']);

        $this->artisan('forecast:month-source', ['month' => '2026-08', 'source' => 'import', '--import-id' => $importId])
            ->assertSuccessful();
        $this->assertSame('277000', $history->window()['actual'][1]['amount']);
        $this->assertDatabaseCount('forecast_month_sources', 3);
    }

    public function test_incomplete_and_current_months_cannot_fill_required_window(): void
    {
        $history = app(MonthlyRevenueHistory::class);
        $id = $history->import(HistoryPackage::fromDirectory($this->package('incomplete', complete: '0')), true, null);
        $history->selectSource('2026-08', 'import', $id);

        $this->assertSame('insufficient_data', $history->window()['status']);
        $this->assertSame(['2026-08'], $history->window()['missing_months']);
        $this->artisan('forecast:month-source', ['month' => '2026-10', 'source' => 'application'])->assertFailed();
        $this->assertSame(['2026-08', '2026-10'], $history->window('2026-11')['missing_months']);
    }

    public function test_demo_import_records_operational_items_and_reuses_the_same_batch(): void
    {
        $package = $this->package('demo-income');
        $this->artisan('forecast:history-import', ['directory' => $package, '--activate' => true])->assertSuccessful();
        $this->artisan('forecast:demo-import', ['directory' => $package])
            ->expectsOutputToContain('Transaksi demo baru: 15')->assertSuccessful();
        $this->artisan('forecast:demo-import', ['directory' => $package])
            ->expectsOutputToContain('Transaksi demo baru: 0')->assertSuccessful();
        $this->assertDatabaseCount('income_transactions', 15);
        $this->assertDatabaseCount('income_entries', 16);
        $this->assertDatabaseCount('income_product_sales', 1);
        $this->assertDatabaseHas('income_transactions', [
            'demo_import_id' => DB::table('historical_imports')->value('id'),
            'barber_name' => 'Bayu (Demo)',
            'recorded_at' => '2026-08-01 03:00:00',
            'customer_name' => null,
        ]);

        $month = CarbonImmutable::parse('2026-08-01', 'Asia/Jakarta');
        $this->assertSame('277000', (string) OwnerFinancialEntries::query($month)->sum('amount'));
        $result = app(MonthlyRevenueHistory::class)->window('2026-10');
        $this->assertSame('synthetic', $result['data_kind']);
        $this->assertSame('application', $result['actual'][1]['source']);
        $this->assertSame('277000', $result['actual'][1]['amount']);
    }

    public function test_closed_months_read_operational_income_without_explicit_source(): void
    {
        $this->recordTransaction('2026-06-30 17:00:00.000000', withProduct: false);
        $this->recordTransaction('2026-08-31 17:00:00.000000', withProduct: true);

        $window = app(MonthlyRevenueHistory::class)->window('2026-10');

        $this->assertSame(['2026-08'], $window['missing_months']);
        $this->assertSame(['2026-07', '2026-09'], array_column($window['actual'], 'month'));
        $this->assertSame(['18000', '48000'], array_column($window['actual'], 'amount'));
        $this->assertDatabaseCount('forecast_month_sources', 0);
    }

    private function package(string $version, string $amount = '277000', string $complete = '1'): string
    {
        $name = 'slice11-test-'.Str::uuid();
        $directory = storage_path("app/private/imports/$name");
        mkdir($directory, 0700);
        $this->directories[] = $directory;
        $transactions = "source_transaction_id,recorded_at_wib,barber_name,customer_name\n";
        $items = "source_transaction_id,item_number,item_type,item_name,quantity,unit_price,amount\n";
        for ($number = 1; $number <= 15; $number++) {
            $customer = $number === 1 ? 'Fiktif' : '';
            $transactions .= "TX-$number,2026-08-01 10:00:00,Bayu,$customer\n";
            $items .= $number === 15
                ? "TX-$number,1,product,Hair Tonic,1,15000,15000\n"
                : "TX-$number,1,service,Potong rambut,1,18000,18000\n";
        }
        $items .= "TX-1,2,service,Keramas,1,5000,5000\nTX-2,2,service,Keramas,1,5000,5000\n";
        $monthly = "month_start,revenue,transaction_count,complete\n2026-07-01,0,0,1\n"
            ."2026-08-01,$amount,15,$complete\n2026-09-01,0,0,1\n";

        $files = ['transactions.csv' => $transactions, 'transaction_items.csv' => $items, 'monthly_revenues.csv' => $monthly];
        foreach ($files as $file => $contents) {
            file_put_contents($directory.DIRECTORY_SEPARATOR.$file, $contents);
        }
        file_put_contents($directory.DIRECTORY_SEPARATOR.'manifest.json', json_encode([
            'schema_version' => 1,
            'dataset_version' => $version,
            'data_kind' => 'synthetic',
            'timezone' => 'Asia/Jakarta',
            'currency' => 'IDR',
            'coverage' => ['start' => '2026-07-01', 'end' => '2026-09-30'],
            'files' => array_map(fn (string $contents): string => hash('sha256', $contents), $files),
        ], JSON_THROW_ON_ERROR));

        return $name;
    }

    private function recordTransaction(string $date, bool $withProduct): void
    {
        $transaction = IncomeTransaction::create([
            'barber_name' => 'Bayu',
            'recorded_at' => $date,
            'session_scope' => hash('sha256', 'forecast history test', true),
            'request_key' => (string) Str::uuid(),
            'request_hash' => hash('sha256', $date, true),
        ]);
        $service = Service::factory()->create(['current_price' => '18000']);
        $transaction->entries()->create([
            'service_id' => $service->id, 'service_name' => $service->name, 'amount' => '18000',
        ]);
        if ($withProduct) {
            $product = Product::create(['name' => 'Hair Tonic', 'sale_price' => '15000']);
            $transaction->productSales()->create([
                'product_id' => $product->id, 'product_name' => $product->name, 'quantity' => 2, 'unit_price' => '15000',
            ]);
        }
    }
}
