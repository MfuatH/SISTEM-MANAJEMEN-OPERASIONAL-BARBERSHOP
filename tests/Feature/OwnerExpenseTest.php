<?php

namespace Tests\Feature;

use App\Models\Expense;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Illuminate\Testing\TestResponse;
use OpenSpout\Common\Entity\Cell\NumericCell;
use OpenSpout\Common\Entity\Row;
use OpenSpout\Reader\XLSX\Reader;
use Tests\TestCase;
use ZipArchive;

class OwnerExpenseTest extends TestCase
{
    use DatabaseTransactions;

    public function test_owner_records_one_expense_per_submission_key_with_a_system_reference(): void
    {
        $this->travelTo(Carbon::parse('2026-10-31 17:00:00', 'UTC'));
        $owner = User::factory()->create();
        $this->actingAs($owner);

        $form = $this->get(route('owner.expenses.create'))->assertOk()
            ->assertSee('value="2026-11-01"', false);
        $payload = [
            'id' => 123,
            'recorded_by_user_id' => User::factory()->create()->id,
            'incurred_on' => '2026-11-01',
            'description' => 'Perlengkapan kebersihan',
            'amount' => '75000',
            'request_key' => $form->viewData('requestKey'),
        ];

        $this->post(route('owner.expenses.store'), $payload)->assertRedirect(route('owner.expenses.index'));
        $expense = Expense::query()->where('recorded_by_user_id', $owner->id)->sole();
        $reference = Expense::reference($expense->id);
        $this->assertSame('EXP-'.$expense->id, $reference);
        $this->assertSame($owner->id, $expense->recordedBy->id);
        $this->assertSame('2026-10-31 17:00:00', $expense->created_at->format('Y-m-d H:i:s'));
        $this->assertSame('2026-11-01', $expense->incurred_on);
        $this->assertSame('75000', $expense->amount);

        $this->post(route('owner.expenses.store'), array_replace($payload, ['amount' => 75000]))
            ->assertRedirect(route('owner.expenses.index'))
            ->assertSessionHas('status', 'Pengeluaran '.$reference.' tersimpan.');
        $this->post(route('owner.expenses.store'), array_replace($payload, ['amount' => '120000']))
            ->assertRedirect(route('owner.expenses.create'))
            ->assertSessionHasErrors('request_key');

        $this->assertSame(1, Expense::query()->where('recorded_by_user_id', $owner->id)->count());
        $this->assertSame('75000', $expense->refresh()->amount);
        $this->get(route('owner.expenses.index'))->assertOk()->assertSee($reference)->assertSee('Rp75.000');

        $otherOwner = User::factory()->create();
        $this->actingAs($otherOwner)->post(route('owner.expenses.store'), $payload)
            ->assertRedirect(route('owner.expenses.index'));
        $this->assertSame(1, Expense::query()->where('recorded_by_user_id', $otherOwner->id)->count());
    }

    public function test_invalid_fields_leave_no_expense(): void
    {
        $owner = User::factory()->create();
        $this->actingAs($owner);
        $payload = [
            'incurred_on' => '2026-11-30',
            'description' => 'Perlengkapan',
            'amount' => '1000',
            'request_key' => (string) Str::uuid(),
        ];

        foreach ([
            ['incurred_on' => '2026-11-31'],
            ['incurred_on' => '0999-01-01'],
            ['description' => ' '],
            ['description' => str_repeat('a', 256)],
            ['amount' => '0'],
            ['amount' => '-1'],
            ['amount' => '1.5'],
            ['amount' => '1000000000000000'],
            ['request_key' => 'invalid'],
        ] as $invalid) {
            $this->post(route('owner.expenses.store'), array_replace($payload, $invalid))
                ->assertRedirect(route('owner.expenses.create'))
                ->assertSessionHasErrors(array_keys($invalid));
        }

        $this->assertFalse(Expense::query()->where('recorded_by_user_id', $owner->id)->exists());
    }

    public function test_month_rollover_search_and_excel_match_the_same_expense_amounts(): void
    {
        $owner = User::factory()->create();
        $this->actingAs($owner);
        $this->expense($owner, '2026-10-31', 'Perlengkapan Oktober', '8000');
        $first = $this->expense($owner, '2026-11-01', 'Perlengkapan 100Xbersih', '75000');
        $second = $this->expense($owner, '2026-11-30', '=100%_bersih', '100000');
        $this->expense($owner, '2026-12-01', 'Perlengkapan Desember', '9000');

        $this->travelTo(Carbon::parse('2026-10-31 16:59:59.999999', 'UTC'));
        $this->get(route('owner.expenses.index'))->assertOk()
            ->assertSee('Perlengkapan Oktober')->assertDontSee($first->description);

        $this->travelTo(Carbon::parse('2026-10-31 17:00:00', 'UTC'));
        $this->get(route('owner.home'))->assertOk()->assertSee(route('owner.expenses.index'));
        $page = $this->get(route('owner.expenses.index'))->assertOk()
            ->assertSee('November 2026')->assertSee('Rp175.000')
            ->assertDontSee('Perlengkapan Oktober')->assertDontSee('Perlengkapan Desember');
        $this->assertSame('175000', $page->viewData('total'));

        $rows = $this->readWorkbook($this->get(route('owner.expenses.export'))->assertDownload('pengeluaran-2026-11.xlsx'));
        $this->assertCount(4, $rows);
        $this->assertSame(Expense::reference($second->id), $rows[1]->cells[0]->getValue());
        $this->assertSame('2026-11-30', $rows[1]->cells[1]->getValue());
        $this->assertSame($second->description, $rows[1]->cells[2]->getValue());
        $this->assertInstanceOf(NumericCell::class, $rows[1]->cells[3]);
        $this->assertSame(0, 75_000 + 100_000 - ($rows[1]->cells[3]->getValue() + $rows[2]->cells[3]->getValue()));
        $this->assertSame((int) $page->viewData('total'), $rows[3]->cells[3]->getValue());

        $search = ['q' => '%_'];
        $this->get(route('owner.expenses.index', $search))->assertOk()
            ->assertSee(route('owner.expenses.export', $search))->assertSee('Rp100.000')
            ->assertDontSee($first->description);
        $filtered = $this->readWorkbook($this->get(route('owner.expenses.export', $search)));
        $this->assertCount(3, $filtered);
        $this->assertSame(100000, $filtered[2]->cells[3]->getValue());

        foreach ([$first->id, Expense::reference($first->id)] as $identifier) {
            $this->get(route('owner.expenses.index', ['q' => $identifier]))->assertOk()
                ->assertSee($first->description)->assertDontSee($second->description);
        }

        $this->travelTo(Carbon::parse('2026-11-30 17:00:00', 'UTC'));
        $this->get(route('owner.expenses.index'))->assertOk()
            ->assertSee('Desember 2026')->assertSee('Perlengkapan Desember')->assertDontSee($first->description);
        $this->assertTrue($first->fresh()->exists);

        $this->travelTo(Carbon::parse('2027-01-01', 'UTC'));
        $this->get(route('owner.expenses.index'))->assertOk()
            ->assertSee('Belum ada pengeluaran bulan ini.')->assertSee('Rp0');
        $empty = $this->readWorkbook($this->get(route('owner.expenses.export'))->assertDownload('pengeluaran-2027-01.xlsx'));
        $this->assertCount(2, $empty);
        $this->assertSame(0, $empty[1]->cells[3]->getValue());
    }

    public function test_expense_routes_require_an_enabled_owner_account(): void
    {
        $this->get(route('owner.expenses.index'))->assertRedirect(route('login'));
        $this->withSession(['barber_name' => 'Bayu'])
            ->get(route('owner.expenses.export'))->assertRedirect(route('login'));
        $this->get(route('owner.expenses.create'))->assertRedirect(route('login'));
        $this->post(route('owner.expenses.store'))->assertRedirect(route('login'));

        $this->actingAs(User::factory()->create(['disabled_at' => now()]))
            ->post(route('owner.expenses.store'))->assertRedirect(route('login'));
    }

    private function expense(User $owner, string $date, string $description, string $amount): Expense
    {
        return Expense::query()->create([
            'recorded_by_user_id' => $owner->id,
            'incurred_on' => $date,
            'description' => $description,
            'amount' => $amount,
            'created_at' => now('UTC'),
            'request_key' => (string) Str::uuid(),
            'request_hash' => hash('sha256', $description, true),
        ]);
    }

    /** @return list<Row> */
    private function readWorkbook(TestResponse $response): array
    {
        $response->assertOk()->assertHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $path = tempnam(sys_get_temp_dir(), 'expense-xlsx-');
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
