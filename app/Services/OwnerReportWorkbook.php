<?php

namespace App\Services;

use Brick\Math\BigInteger;
use OpenSpout\Common\Entity\Cell;
use OpenSpout\Common\Entity\Cell\NumericCell;
use OpenSpout\Common\Entity\Cell\StringCell;
use OpenSpout\Common\Entity\Row;
use OpenSpout\Writer\XLSX\Writer;

class OwnerReportWorkbook
{
    /**
     * @param  list<string>  $headings
     * @param  list<int>  $widths
     * @param  iterable<array{cells: list<Cell>, amount: string}>  $rows
     */
    public function write(string $sheetName, array $headings, array $widths, string $totalLabel, iterable $rows): void
    {
        $writer = new Writer;
        $writer->openToFile('php://output');

        try {
            $sheet = $writer->getCurrentSheet();
            $sheet->setName($sheetName);
            foreach ($widths as $index => $width) {
                $sheet->setColumnWidth($width, $index + 1);
            }
            $writer->addRow(Row::fromValues($headings));

            $total = BigInteger::zero();
            foreach ($rows as $row) {
                $total = $total->plus($row['amount']);
                $writer->addRow(new Row([...$row['cells'], $this->amountCell($row['amount'])]));
            }

            $writer->addRow(new Row([
                0 => new StringCell($totalLabel),
                count($headings) - 1 => $this->amountCell((string) $total),
            ]));
        } finally {
            $writer->close();
        }
    }

    public function amountCell(string $amount): NumericCell|StringCell
    {
        // Excel numeric cells retain up to 15 significant digits.
        return strlen($amount) <= 15 ? new NumericCell((int) $amount) : new StringCell($amount);
    }
}
