<?php

namespace App\Services;

use App\Models\Expense;
use OpenSpout\Common\Entity\Cell\StringCell;

class OwnerExpenseWorkbook
{
    public function __construct(private readonly OwnerReportWorkbook $workbook) {}

    public function write(OwnerExpenseReport $report): void
    {
        $this->workbook->write(
            'Pengeluaran',
            ['ID pengeluaran', 'Tanggal (WIB)', 'Deskripsi', 'Jumlah (Rp)'],
            [23, 18, 42, 23],
            $report->search === '' ? 'Total bulan berjalan' : 'Total hasil pencarian',
            $report->query()->cursor()->map(fn (object $item): array => [
                'cells' => [
                    new StringCell(Expense::reference($item->id)),
                    new StringCell($item->incurred_on),
                    new StringCell($item->description),
                ],
                'amount' => (string) $item->amount,
            ]),
        );
    }
}
