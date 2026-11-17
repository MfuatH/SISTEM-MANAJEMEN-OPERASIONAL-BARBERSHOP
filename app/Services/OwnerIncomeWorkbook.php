<?php

namespace App\Services;

use OpenSpout\Common\Entity\Cell\NumericCell;
use OpenSpout\Common\Entity\Cell\StringCell;

class OwnerIncomeWorkbook
{
    public function __construct(private readonly OwnerReportWorkbook $workbook) {}

    public function write(OwnerIncomeReport $report): void
    {
        $this->workbook->write(
            'Pemasukan',
            [
                'ID transaksi', 'Waktu (WIB)', 'Kapster', 'Pelanggan', 'Jenis', 'Item',
                'Produk dipakai (riwayat)', 'Jumlah', 'Harga satuan (Rp)', 'Nilai (Rp)',
            ],
            [14, 22, 22, 22, 22, 28, 28, 14, 22, 22],
            $report->search === '' ? 'Total bulan berjalan' : 'Total hasil pencarian',
            $report->query()->cursor()->map(fn (object $item): array => [
                'cells' => [
                    new StringCell((string) $item->transaction_id),
                    new StringCell(OwnerIncomeReport::localTime($item->recorded_at)->format('Y-m-d H:i:s')),
                    new StringCell($item->barber_name),
                    new StringCell($item->customer_name ?? ''),
                    new StringCell(OwnerIncomeReport::itemType($item->item_type)),
                    new StringCell($item->item_name),
                    new StringCell($item->used_product_name ?? ''),
                    new NumericCell((int) $item->quantity),
                    $this->workbook->amountCell((string) $item->unit_price),
                ],
                'amount' => (string) $item->amount,
            ]),
        );
    }
}
