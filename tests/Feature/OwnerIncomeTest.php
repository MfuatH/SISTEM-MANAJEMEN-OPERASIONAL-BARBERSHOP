<?php

namespace Tests\Feature;

use App\Models\IncomeTransaction;
use App\Models\Product;
use App\Models\Service;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Illuminate\Testing\TestResponse;
use OpenSpout\Common\Entity\Cell\NumericCell;
use OpenSpout\Common\Entity\Cell\StringCell;
use OpenSpout\Common\Entity\Row;
use OpenSpout\Reader\XLSX\Reader;
use Tests\TestCase;
use ZipArchive;

class OwnerIncomeTest extends TestCase
{
    use DatabaseTransactions;

    public function test_owner_sees_and_exports_the_same_current_month_items_and_totals(): void
    {
        $this->travelTo(Carbon::parse('2026-11-17 06:00:00', 'UTC'));
        $this->actingAs(User::factory()->create());
        $mixed = $this->transaction('2026-10-31 17:00:00.000000', '=1+1');
        $this->addService($mixed, 'Layanan A', '35000');
        $this->addService($mixed, 'Layanan B', '15000');
        $this->addSale($mixed, 'Hair Tonic', '15000', 2);
        $this->addService($this->transaction('2026-10-31 16:59:59.999999'), 'Bulan lalu', '40000');
        $this->addService($this->transaction('2026-11-30 17:00:00.000000'), 'Bulan depan', '12000');

        $this->get(route('owner.home'))->assertSee(route('owner.income.index'));
        $page = $this->get(route('owner.income.index'))->assertOk()->assertSee('November 2026')
            ->assertSee('01/11/2026 00:00')->assertSee('Rp80.000')
            ->assertDontSee('Bulan lalu')->assertDontSee('Bulan depan');
        $this->assertSame(3, $page->viewData('items')->total());
        $this->assertSame('80000', $page->viewData('total'));

        $rows = $this->readWorkbook($this->get(route('owner.income.export'))
            ->assertDownload('pemasukan-2026-11.xlsx'));
        $this->assertCount(5, $rows);
        $items = array_slice($rows, 1, 3);
        foreach ($items as $item) {
            $this->assertSame((string) $mixed->id, $item->cells[0]->getValue());
            $this->assertSame('2026-11-01 00:00:00', $item->cells[1]->getValue());
            $this->assertSame('=1+1', $item->cells[3]->getValue());
            $this->assertInstanceOf(NumericCell::class, $item->cells[9]);
        }

        $this->assertSame(0, 35_000 + 15_000 + 2 * 15_000 - array_sum(array_map(fn (Row $row) => $row->cells[9]->getValue(), $items)));
        $this->assertSame(80000, $rows[4]->cells[9]->getValue());

        $search = ['q' => 'Hair Tonic'];
        $searched = $this->get(route('owner.income.index', $search))->assertOk()
            ->assertSee(route('owner.income.export', $search))->assertSee('Rp30.000')->assertDontSee('Layanan A');
        $this->assertSame(1, $searched->viewData('items')->total());
        $filtered = $this->readWorkbook($this->get(route('owner.income.export', $search)));
        $this->assertCount(3, $filtered);
        $this->assertSame('Hair Tonic', $filtered[1]->cells[5]->getValue());
        $this->assertSame(2, $filtered[1]->cells[7]->getValue());
        $this->assertSame(30000, $filtered[2]->cells[9]->getValue());
    }

    public function test_month_rollover_uses_wib_boundaries_and_empty_month_exports_zero(): void
    {
        $this->actingAs(User::factory()->create());
        $this->addService($this->transaction('2026-11-30 16:59:59.999999'), 'Akhir hari WIB', '35000');
        $this->addService($this->transaction('2026-11-30 17:00:00.000000'), 'Awal bulan WIB', '15000');

        $this->travelTo(Carbon::parse('2026-11-30 16:59:59.999999', 'UTC'));
        $this->get(route('owner.income.index'))->assertOk()->assertSee('Akhir hari WIB')->assertDontSee('Awal bulan WIB');
        $this->travelTo(Carbon::parse('2026-11-30 17:00:00.000000', 'UTC'));
        $this->get(route('owner.income.index'))->assertOk()->assertSee('Desember 2026')
            ->assertSee('Awal bulan WIB')->assertDontSee('Akhir hari WIB');

        $this->travelTo(Carbon::parse('2027-01-01', 'UTC'));
        $this->get(route('owner.income.index'))->assertOk()->assertSee('Belum ada pemasukan bulan ini.')->assertSee('Rp0');
        $rows = $this->readWorkbook($this->get(route('owner.income.export'))
            ->assertDownload('pemasukan-2027-01.xlsx'));
        $this->assertCount(2, $rows);
        $this->assertSame(0, $rows[1]->cells[9]->getValue());
    }

    public function test_export_includes_matches_beyond_the_displayed_page_and_escapes_search_wildcards(): void
    {
        $this->travelTo(Carbon::parse('2026-11-17', 'UTC'));
        $this->actingAs(User::factory()->create());
        $transaction = $this->transaction('2026-11-15 08:00:00');
        $service = Service::factory()->create(['name' => 'Layanan 100%_Uji', 'current_price' => '1000']);
        $transaction->entries()->createMany(array_fill(0, 26, [
            'service_id' => $service->id,
            'service_name' => $service->name,
            'amount' => $service->current_price,
        ]));
        $this->addService($transaction, 'Layanan 100XUji', '1000');

        $filters = ['q' => '%_'];
        $page = $this->get(route('owner.income.index', $filters))->assertOk()
            ->assertSee('Rp26.000')->assertSee('Berikutnya')->assertDontSee('Layanan 100XUji');
        $this->assertSame(25, $page->viewData('items')->count());
        $this->assertSame(26, $page->viewData('items')->total());
        $this->get(route('owner.income.index', $filters + ['page' => 2]))->assertOk()->assertSee('Rp26.000');
        $rows = $this->readWorkbook($this->get(route('owner.income.export', $filters + ['page' => 2])));
        $this->assertCount(28, $rows);
        $this->assertSame(26000, $rows[27]->cells[9]->getValue());

        $byId = $this->get(route('owner.income.index', ['q' => $transaction->id]))->assertOk();
        $this->assertSame(27, $byId->viewData('items')->total());
    }

    public function test_export_retains_digits_for_an_amount_exceeding_excel_numeric_precision(): void
    {
        $this->travelTo(Carbon::parse('2026-11-17', 'UTC'));
        $this->actingAs(User::factory()->create());
        $this->addSale($this->transaction('2026-11-15 08:00:00'), 'Produk Uji', '999999999999999', 1000);

        $this->get(route('owner.income.index'))->assertOk()->assertSee('Rp999.999.999.999.999.000');
        $rows = $this->readWorkbook($this->get(route('owner.income.export')));
        $this->assertInstanceOf(StringCell::class, $rows[1]->cells[9]);
        $this->assertSame('999999999999999000', $rows[1]->cells[9]->getValue());
        $this->assertSame('999999999999999000', $rows[2]->cells[9]->getValue());
    }

    public function test_pemasukan_and_export_require_owner_access(): void
    {
        $this->get(route('owner.income.index'))->assertRedirect(route('login'));
        $this->withSession(['barber_name' => 'Bayu'])->get(route('owner.income.export'))->assertRedirect(route('login'));
        $this->actingAs(User::factory()->create(['disabled_at' => now()]))
            ->get(route('owner.income.index'))->assertRedirect(route('login'));
    }

    private function transaction(string $recordedAt, ?string $customerName = null): IncomeTransaction
    {
        return IncomeTransaction::create([
            'barber_name' => 'Bayu',
            'customer_name' => $customerName,
            'recorded_at' => $recordedAt,
            'session_scope' => hash('sha256', 'owner income fixture', true),
            'request_key' => (string) Str::uuid(),
            'request_hash' => hash('sha256', $recordedAt, true),
        ]);
    }

    private function addService(IncomeTransaction $transaction, string $name, string $price): void
    {
        $service = Service::factory()->create(['name' => $name, 'current_price' => $price]);
        $transaction->entries()->create(['service_id' => $service->id, 'service_name' => $name, 'amount' => $price]);
    }

    private function addSale(IncomeTransaction $transaction, string $name, string $price, int $quantity): void
    {
        $product = Product::create(['name' => $name, 'sale_price' => $price]);
        $transaction->productSales()->create([
            'product_id' => $product->id, 'product_name' => $name, 'unit_price' => $price, 'quantity' => $quantity,
        ]);
    }

    /** @return list<Row> */
    private function readWorkbook(TestResponse $response): array
    {
        $response->assertOk()->assertHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $path = tempnam(sys_get_temp_dir(), 'income-xlsx-');
        $this->assertNotFalse($path);
        $reader = new Reader;
        try {
            file_put_contents($path, $response->streamedContent());
            // Verify the actual sheet XML because the reader infers formulas from strings beginning with '='.
            $zip = new ZipArchive;
            $this->assertTrue($zip->open($path));
            try {
                $worksheet = simplexml_load_string($zip->getFromName('xl/worksheets/sheet1.xml'));
                $this->assertNotFalse($worksheet);
                $worksheet->registerXPathNamespace('m', 'http://schemas.openxmlformats.org/spreadsheetml/2006/main');
                $this->assertSame([], $worksheet->xpath('//m:f'));
            } finally {
                $zip->close();
            }

            $reader->open($path);
            $rows = [];
            foreach ($reader->getSheetIterator() as $sheet) {
                foreach ($sheet->getRowIterator() as $row) {
                    $rows[] = $row;
                }
            }

            return $rows;
        } finally {
            $reader->close();
            unlink($path);
        }
    }
}
