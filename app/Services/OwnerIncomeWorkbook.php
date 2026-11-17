<?php

namespace App\Services;

use Brick\Math\BigInteger;
use OpenSpout\Common\Entity\Cell\NumericCell;
use OpenSpout\Common\Entity\Cell\StringCell;
use OpenSpout\Common\Entity\Row;
use OpenSpout\Writer\XLSX\Writer;

class OwnerIncomeWorkbook
{
    public function write(OwnerIncomeReport $report): void
    {
        $writer = new Writer;
        $writer->openToFile('php://output');

        try {
            $sheet = $writer->getCurrentSheet();
            $sheet->setName('Pemasukan');
            $sheet->setColumnWidth(14, 1, 8);
            $sheet->setColumnWidth(22, 2, 3, 4, 5, 9, 10);
            $sheet->setColumnWidth(28, 6, 7);
            $writer->addRow(Row::fromValues([
                'ID transaksi', 'Waktu (WIB)', 'Kapster', 'Pelanggan', 'Jenis', 'Item',
                'Produk dipakai (riwayat)', 'Jumlah', 'Harga satuan (Rp)', 'Nilai (Rp)',
            ]));

            $total = BigInteger::zero();
            foreach ($report->query()->cursor() as $item) {
                $amount = (string) $item->amount;
                $total = $total->plus($amount);
                $writer->addRow(new Row([
                    new StringCell((string) $item->transaction_id),
                    new StringCell(OwnerIncomeReport::localTime($item->recorded_at)->format('Y-m-d H:i:s')),
                    new StringCell($item->barber_name),
                    new StringCell($item->customer_name ?? ''),
                    new StringCell(OwnerIncomeReport::itemType($item->item_type)),
                    new StringCell($item->item_name),
                    new StringCell($item->used_product_name ?? ''),
                    new NumericCell((int) $item->quantity),
                    $this->amountCell((string) $item->unit_price),
                    $this->amountCell($amount),
                ]));
            }

            $writer->addRow(new Row([
                0 => new StringCell($report->search === '' ? 'Total bulan berjalan' : 'Total hasil pencarian'),
                9 => $this->amountCell((string) $total),
            ]));
        } finally {
            $writer->close();
        }
    }

    private function amountCell(string $amount): NumericCell|StringCell
    {
        // Excel numeric cells retain up to 15 significant digits.
        return strlen($amount) <= 15 ? new NumericCell((int) $amount) : new StringCell($amount);
    }
}
