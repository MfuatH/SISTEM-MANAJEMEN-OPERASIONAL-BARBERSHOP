<?php

namespace App\Services;

use OpenSpout\Common\Entity\Cell\StringCell;
use OpenSpout\Common\Entity\Row;

class OwnerRecapWorkbook
{
    public function __construct(private readonly OwnerReportWorkbook $workbook) {}

    public function write(OwnerRecapReport $report): void
    {
        $summary = $report->summary();
        $this->workbook->write(
            'Rekap',
            ['Tanggal (WIB)', 'Jenis', 'Kategori', 'ID referensi', 'Kapster', 'Pelanggan', 'Rincian', 'Nominal (Rp)'],
            [18, 18, 23, 20, 22, 22, 42, 22],
            OwnerRecapReport::DETAIL_TOTAL_LABEL,
            $report->query()->cursor()->map(fn (object $item): array => [
                'cells' => [
                    new StringCell($item->business_date),
                    new StringCell(OwnerRecapReport::TYPES[$item->entry_type]),
                    new StringCell(OwnerRecapReport::category($item->item_type)),
                    new StringCell(OwnerRecapReport::reference($item)),
                    new StringCell($item->barber_name ?? ''),
                    new StringCell($item->customer_name ?? ''),
                    new StringCell($item->label),
                ],
                'amount' => (string) $item->amount,
            ]),
            [
                Row::fromValues(['Periode (WIB)', $report->periodLabel()]),
                new Row([new StringCell('Total pemasukan (Rp)'), $this->workbook->amountCell($summary['income'])]),
                new Row([new StringCell('Total pengeluaran (Rp)'), $this->workbook->amountCell($summary['expenses'])]),
                new Row([new StringCell('Hasil bersih tercatat (Rp)'), $this->workbook->amountCell($summary['net'])]),
            ],
            footerTotals: $report->detailTotals(),
        );
    }
}
