<?php

namespace Tests\Feature;

use App\Models\Expense;
use App\Models\IncomeTransaction;
use App\Models\Product;
use App\Models\Service;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Str;
use Illuminate\Testing\TestResponse;
use OpenSpout\Common\Entity\Cell\NumericCell;
use OpenSpout\Common\Entity\Cell\StringCell;
use OpenSpout\Common\Entity\Row;
use OpenSpout\Reader\XLSX\Reader;
use Tests\TestCase;
use ZipArchive;

class OwnerRecapTest extends TestCase
{
    use DatabaseTransactions;

    public function test_recap_combines_distinct_items_across_months_and_exports_the_same_period_totals(): void
    {
        $owner = User::factory()->create();
        $this->actingAs($owner);

        $september = $this->transaction('2026-09-30 16:59:59.999999');
        $this->service($september, 'Potong', '35000');
        $this->service($september, 'Keramas', '15000');
        $product = Product::create(['name' => 'Hair Tonic', 'sale_price' => '17500']);
        $september->productSales()->create([
            'product_id' => $product->id, 'product_name' => $product->name, 'unit_price' => '17500', 'quantity' => 2,
        ]);
        $expense = $this->expense($owner, '2026-09-30', 'Perlengkapan', '20000');

        $october = $this->transaction('2026-09-30 17:00:00.000000');
        $this->service($october, 'Layanan Oktober', '40000');
        $this->expense($owner, '2026-10-01', 'Kebersihan Oktober', '5000');

        $this->get(route('owner.home'))->assertOk()->assertSee(route('owner.recap.index'));
        $history = $this->get(route('owner.recap.index'))->assertOk()
            ->assertSee('Seluruh periode')->assertSee('Layanan Oktober');
        $this->assertSame(6, $history->viewData('items')->total());
        $this->assertSame(['income' => '125000', 'expenses' => '25000', 'net' => '100000'], $history->viewData('summary'));

        $filters = ['month' => '2026-09'];
        $page = $this->get(route('owner.recap.index', $filters))->assertOk()
            ->assertSee('September 2026')->assertSee('Rp85.000')->assertSee('Rp20.000')->assertSee('Rp65.000')
            ->assertSee(Expense::reference($expense->id))->assertDontSee('Layanan Oktober')->assertDontSee('Kebersihan Oktober');
        $this->assertSame(4, $page->viewData('items')->total());
        $this->assertSame('105000', $page->viewData('detailTotal'));

        $workbook = $this->readWorkbook($this->get(route('owner.recap.export', $filters))
            ->assertDownload('rekap-2026-09.xlsx'));
        $rows = $workbook['Rekap'];
        $this->assertCount(6, $rows);
        $items = array_slice($rows, 1, 4);
        foreach ($items as $item) {
            $this->assertSame('2026-09-30', $item->cells[0]->getValue());
        }

        $this->assertCount(3, array_filter($items, fn (Row $item) => $item->cells[3]->getValue() === (string) $september->id));
        $this->assertSame(105000, $rows[5]->cells[7]->getValue());
        $this->assertSame(
            [85000, 20000, 65000],
            $this->summaryAmounts($workbook['Ringkasan']),
        );
        $this->assertSame(
            0,
            35_000 + 15_000 + 2 * 17_500 - 20_000 - $workbook['Ringkasan'][3]->cells[1]->getValue(),
        );
    }

    public function test_filter_limits_details_and_export_without_removing_the_other_transaction_type_from_the_summary(): void
    {
        $owner = User::factory()->create();
        $this->actingAs($owner);
        $transaction = $this->transaction('2026-09-20 00:00:00');
        $this->service($transaction, 'Layanan 100%_uji', '35000');
        for ($record = 0; $record < 26; $record++) {
            $this->expense($owner, '2026-09-21', '=Harga 100%_uji', '1000');
        }
        $this->expense($owner, '2026-09-21', 'Harga 100Xuji', '2000');

        $filters = ['month' => '2026-09', 'type' => 'OUT', 'q' => '%_'];
        $page = $this->get(route('owner.recap.index', $filters))->assertOk()
            ->assertSee('Berikutnya')->assertDontSee('Harga 100Xuji')->assertDontSee('Layanan 100%_uji');
        $this->assertSame(25, $page->viewData('items')->count());
        $this->assertSame(26, $page->viewData('items')->total());
        $this->assertSame('26000', $page->viewData('detailTotal'));
        $this->assertSame(['income' => '35000', 'expenses' => '28000', 'net' => '7000'], $page->viewData('summary'));

        $workbook = $this->readWorkbook($this->get(route('owner.recap.export', $filters + ['page' => 2])));
        $this->assertCount(28, $workbook['Rekap']);
        $this->assertSame('=Harga 100%_uji', $workbook['Rekap'][1]->cells[6]->getValue());
        $this->assertSame(26000, $workbook['Rekap'][27]->cells[7]->getValue());
        $this->assertSame([35000, 28000, 7000], $this->summaryAmounts($workbook['Ringkasan']));
    }

    public function test_search_accepts_income_transaction_and_expense_references(): void
    {
        $owner = User::factory()->create();
        $this->actingAs($owner);
        $transaction = $this->transaction('2026-09-20 00:00:00');
        $this->service($transaction, 'Potong', '35000');
        $this->service($transaction, 'Keramas', '15000');
        $expense = $this->expense($owner, '2026-09-21', 'Perlengkapan', '1000');

        $income = $this->get(route('owner.recap.index', ['type' => 'IN', 'q' => $transaction->id]))->assertOk();
        $this->assertSame(2, $income->viewData('items')->total());

        $spending = $this->get(route('owner.recap.index', ['q' => Expense::reference($expense->id)]))->assertOk();
        $this->assertSame(1, $spending->viewData('items')->total());
        $this->assertSame('OUT', $spending->viewData('items')->items()[0]->entry_type);
    }

    public function test_empty_and_negative_periods_keep_signed_exact_amounts_on_screen_and_in_xlsx(): void
    {
        $owner = User::factory()->create();
        $this->actingAs($owner);
        $this->expense($owner, '2026-09-30', 'Sewa', '999999999999999');
        $this->expense($owner, '2026-10-01', 'Sewa pertama', '999999999999999');
        $this->expense($owner, '2026-10-01', 'Sewa kedua', '999999999999999');

        $this->get(route('owner.recap.index', ['month' => '2026-10']))->assertOk()
            ->assertSee('-Rp1.999.999.999.999.998');
        $big = $this->readWorkbook($this->get(route('owner.recap.export', ['month' => '2026-10'])));
        $this->assertSame([0, '1999999999999998', '-1999999999999998'], $this->summaryAmounts($big['Ringkasan']));
        $this->assertInstanceOf(StringCell::class, $big['Ringkasan'][3]->cells[1]);

        $negative = $this->readWorkbook($this->get(route('owner.recap.export', ['month' => '2026-09'])));
        $this->assertSame(-999999999999999, $negative['Ringkasan'][3]->cells[1]->getValue());
        $this->assertInstanceOf(NumericCell::class, $negative['Ringkasan'][3]->cells[1]);

        foreach (['2026-11', '1000-01', '9999-12'] as $month) {
            $empty = $this->get(route('owner.recap.index', ['month' => $month]))->assertOk()
                ->assertSee('Belum ada transaksi yang sesuai.');
            $this->assertSame(['income' => '0', 'expenses' => '0', 'net' => '0'], $empty->viewData('summary'));
        }

        $emptyExport = $this->readWorkbook($this->get(route('owner.recap.export', ['month' => '2026-11'])));
        $this->assertSame([0, 0, 0], $this->summaryAmounts($emptyExport['Ringkasan']));
    }

    public function test_recap_and_export_require_an_active_owner_and_valid_filters(): void
    {
        $this->get(route('owner.recap.index'))->assertRedirect(route('login'));
        $this->withSession(['barber_name' => 'Bayu'])->get(route('owner.recap.export'))->assertRedirect(route('login'));
        $this->actingAs(User::factory()->create(['disabled_at' => now()]))
            ->get(route('owner.recap.index'))->assertRedirect(route('login'));

        $this->actingAs(User::factory()->create());
        $this->from(route('owner.recap.index'))
            ->get(route('owner.recap.index', ['month' => '2026-13', 'type' => 'other']))
            ->assertRedirect(route('owner.recap.index'))->assertSessionHasErrors(['month', 'type']);
    }

    private function transaction(string $recordedAt): IncomeTransaction
    {
        return IncomeTransaction::create([
            'barber_name' => 'Bayu',
            'recorded_at' => $recordedAt,
            'session_scope' => hash('sha256', 'owner recap fixture', true),
            'request_key' => (string) Str::uuid(),
            'request_hash' => hash('sha256', $recordedAt, true),
        ]);
    }

    private function service(IncomeTransaction $transaction, string $name, string $amount): void
    {
        $service = Service::factory()->create(['name' => $name, 'current_price' => $amount]);
        $transaction->entries()->create([
            'service_id' => $service->id, 'service_name' => $service->name, 'amount' => $service->current_price,
        ]);
    }

    private function expense(User $owner, string $date, string $description, string $amount): Expense
    {
        return Expense::create([
            'recorded_by_user_id' => $owner->id,
            'incurred_on' => $date,
            'description' => $description,
            'amount' => $amount,
            'created_at' => now('UTC'),
            'request_key' => (string) Str::uuid(),
            'request_hash' => hash('sha256', $description, true),
        ]);
    }

    private function summaryAmounts(array $rows): array
    {
        return array_map(fn (Row $row) => $row->cells[1]->getValue(), array_slice($rows, 1, 3));
    }

    /** @return array<string, list<Row>> */
    private function readWorkbook(TestResponse $response): array
    {
        $response->assertOk()->assertHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $path = tempnam(sys_get_temp_dir(), 'recap-xlsx-');
        $this->assertNotFalse($path);
        $reader = new Reader;

        try {
            file_put_contents($path, $response->streamedContent());
            $zip = new ZipArchive;
            $this->assertTrue($zip->open($path));
            try {
                $worksheet = $zip->getFromName('xl/worksheets/sheet1.xml');
                $this->assertNotFalse($worksheet);
                $this->assertDoesNotMatchRegularExpression('/<f(?:\s|>)/', $worksheet);
            } finally {
                $zip->close();
            }

            $reader->open($path);
            $sheets = [];
            foreach ($reader->getSheetIterator() as $sheet) {
                $sheets[$sheet->getName()] = iterator_to_array($sheet->getRowIterator(), false);
            }

            return $sheets;
        } finally {
            $reader->close();
            unlink($path);
        }
    }
}
