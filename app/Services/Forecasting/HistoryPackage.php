<?php

namespace App\Services\Forecasting;

use App\Services\OwnerReport;
use Brick\Math\BigInteger;
use DateTimeImmutable;
use Generator;
use InvalidArgumentException;
use JsonException;

final readonly class HistoryPackage
{
    public const MAPPING_VERSION = 'csv-v1';

    private const HEADERS = [
        'transactions.csv' => ['source_transaction_id', 'recorded_at_wib', 'barber_name', 'customer_name'],
        'transaction_items.csv' => ['source_transaction_id', 'item_number', 'item_type', 'item_name', 'quantity', 'unit_price', 'amount'],
        'monthly_revenues.csv' => ['month_start', 'revenue', 'transaction_count', 'complete'],
    ];

    private function __construct(
        public string $version,
        public string $dataKind,
        public string $manifestHash,
        public array $months,
        public array $statistics,
        public array $transactions,
    ) {}

    public static function fromDirectory(string $relativeDirectory, bool $includeTransactions = false): self
    {
        $root = realpath(storage_path('app/private/imports'));
        $directory = $root === false ? false : realpath($root.DIRECTORY_SEPARATOR.$relativeDirectory);
        if ($directory === false || ! is_dir($directory) || ! self::within($directory, $root)) {
            throw new InvalidArgumentException('Gunakan direktori paket relatif di dalam storage/app/private/imports.');
        }

        $manifestBytes = self::readFile($directory, 'manifest.json', 65536);
        try {
            $manifest = json_decode($manifestBytes, true, 32, JSON_THROW_ON_ERROR);
        } catch (JsonException $e) {
            throw new InvalidArgumentException('Format JSON manifest tidak valid.', previous: $e);
        }

        $version = $manifest['dataset_version'] ?? null;
        $dataKind = $manifest['data_kind'] ?? null;
        $start = $manifest['coverage']['start'] ?? null;
        $end = $manifest['coverage']['end'] ?? null;
        if (($manifest['schema_version'] ?? null) !== 1 ||
            ($manifest['timezone'] ?? null) !== OwnerReport::TIMEZONE ||
            ($manifest['currency'] ?? null) !== 'IDR' ||
            ! is_string($version) || ! preg_match('/\A[A-Za-z0-9._-]{1,80}\z/', $version) ||
            ! in_array($dataKind, ['synthetic', 'actual'], true) ||
            ! self::isDate($start) || ! self::isDate($end) || $start > $end) {
            throw new InvalidArgumentException('Versi, sumber, zona waktu, mata uang, atau cakupan manifest tidak valid.');
        }

        $transactions = [];
        $details = [];
        $counts = [];
        $blankNames = 0;
        foreach (self::rows($directory, $manifest, 'transactions.csv') as $index => $row) {
            [$id, $recordedAt, $barber, $customer] = $row;
            $day = substr($recordedAt, 0, 10);
            if (! preg_match('/\A[A-Za-z0-9._-]{1,80}\z/', $id) || isset($transactions[$id]) ||
                ! self::isDate($day) || ! preg_match('/\A.{10} (?:[01][0-9]|2[0-3]):[0-5][0-9]:[0-5][0-9]\z/', $recordedAt) ||
                $day < $start || $day > $end ||
                trim($barber) === '' || mb_strlen($barber) > 120 || mb_strlen($customer) > 120) {
                throw new InvalidArgumentException("Transaksi tidak valid pada baris $index.");
            }

            $month = substr($day, 0, 7).'-01';
            $transactions[$id] = $month;
            if ($includeTransactions) {
                $details[$id] = [
                    'recorded_at_wib' => $recordedAt,
                    'barber_name' => $barber,
                    'customer_name' => trim($customer) === '' ? null : $customer,
                    'items' => [],
                ];
            }
            $counts[$month] = ($counts[$month] ?? 0) + 1;
            $blankNames += (int) (trim($customer) === '');
        }

        $totals = [];
        $seen = [];
        $itemCount = 0;
        foreach (self::rows($directory, $manifest, 'transaction_items.csv') as $index => $row) {
            [$id, $itemNumber, $type, $label, $quantity, $unitPrice, $amount] = $row;
            if (! isset($transactions[$id]) || isset($seen[$id][$itemNumber]) ||
                ! self::isAmount($itemNumber, 6, positive: true) ||
                ! in_array($type, ['service', 'product'], true) || trim($label) === '' || mb_strlen($label) > 255 ||
                ! self::isAmount($quantity, 6, positive: true) || ($type === 'service' && $quantity !== '1') ||
                ! self::isAmount($unitPrice, 30, positive: true) || ! self::isAmount($amount, 30) ||
                ! BigInteger::of($unitPrice)->multipliedBy($quantity)->isEqualTo($amount)) {
                throw new InvalidArgumentException("Item tidak valid pada baris $index.");
            }

            $seen[$id][$itemNumber] = true;
            $month = $transactions[$id];
            $totals[$month] = ($totals[$month] ?? BigInteger::zero())->plus($amount);
            $itemCount++;
            if ($includeTransactions) {
                $details[$id]['items'][] = [
                    'type' => $type, 'name' => $label, 'quantity' => (int) $quantity, 'unit_price' => $unitPrice,
                ];
            }
        }

        if (count($seen) !== count($transactions)) {
            throw new InvalidArgumentException('Setiap transaksi harus mempunyai sekurangnya satu item.');
        }

        $months = [];
        $firstMonth = (new DateTimeImmutable($start))->modify('first day of this month');
        $lastMonth = substr($end, 0, 7).'-01';
        foreach (self::rows($directory, $manifest, 'monthly_revenues.csv') as $index => $row) {
            [$month, $revenue, $count, $complete] = $row;
            if (! self::isDate($month) || ! str_ends_with($month, '-01') ||
                $month < $firstMonth->format('Y-m-d') || $month > $lastMonth ||
                isset($months[$month]) || ! self::isAmount($revenue, 30) ||
                $revenue !== (string) ($totals[$month] ?? 0) || $count !== (string) ($counts[$month] ?? 0) ||
                ! in_array($complete, ['0', '1'], true)) {
                throw new InvalidArgumentException("Rekap bulanan tidak cocok pada baris $index.");
            }

            if ($complete === '1' && ($month < $start || (new DateTimeImmutable($month))->format('Y-m-t') > $end)) {
                throw new InvalidArgumentException("Bulan $month ditandai lengkap tetapi cakupan kalender terpotong.");
            }

            $months[$month] = [
                'month_start' => $month,
                'revenue' => $revenue,
                'transaction_count' => (int) $count,
                'is_complete' => $complete === '1',
            ];
        }

        for ($month = $firstMonth; ; $month = $month->modify('+1 month')) {
            $date = $month->format('Y-m-d');
            if (! isset($months[$date])) {
                throw new InvalidArgumentException("Rekap bulan $date tidak tersedia.");
            }
            if ($date === $lastMonth) {
                break;
            }
        }

        ksort($months);

        return new self($version, $dataKind, hash('sha256', $manifestBytes), array_values($months), [
            'transactions' => count($transactions),
            'items' => $itemCount,
            'blank_names' => $blankNames,
        ], $details);
    }

    private static function within(string $path, string $root): bool
    {
        if (PHP_OS_FAMILY === 'Windows') {
            $path = strtolower($path);
            $root = strtolower($root);
        }

        return str_starts_with($path, $root.DIRECTORY_SEPARATOR);
    }

    private static function readFile(string $directory, string $file, int $maximumBytes): string
    {
        $path = realpath($directory.DIRECTORY_SEPARATOR.$file);
        if ($path === false || ! self::within($path, $directory) || ! is_file($path) || ! is_readable($path)) {
            throw new InvalidArgumentException("Berkas paket tidak ditemukan atau tidak dapat dibaca ($file).");
        }

        $content = file_get_contents($path, length: $maximumBytes + 1);
        if ($content === false || strlen($content) > $maximumBytes || ! mb_check_encoding($content, 'UTF-8')) {
            throw new InvalidArgumentException("Berkas $file harus UTF-8 dan memenuhi batas ukuran.");
        }

        return $content;
    }

    private static function rows(string $directory, array $manifest, string $file): Generator
    {
        $bytes = self::readFile($directory, $file, 10 * 1024 * 1024);
        if (hash('sha256', $bytes) !== ($manifest['files'][$file] ?? null)) {
            throw new InvalidArgumentException("SHA-256 berkas $file tidak cocok dengan manifest.");
        }

        $stream = fopen('php://memory', 'w+b');
        try {
            fwrite($stream, $bytes);
            rewind($stream);
            $headers = self::HEADERS[$file];
            if (fgetcsv($stream, escape: '') !== $headers) {
                throw new InvalidArgumentException("Header $file tidak sesuai format csv-v1.");
            }

            for ($index = 2; ($row = fgetcsv($stream, escape: '')) !== false; $index++) {
                if (count($row) !== count($headers) || in_array(null, $row, true)) {
                    throw new InvalidArgumentException("Jumlah kolom $file tidak sesuai pada baris $index.");
                }
                yield $index => $row;
            }
        } finally {
            fclose($stream);
        }
    }

    private static function isDate(mixed $value): bool
    {
        return is_string($value) && preg_match('/\A([1-9][0-9]{3})-([0-9]{2})-([0-9]{2})\z/', $value, $parts)
            && checkdate((int) $parts[2], (int) $parts[3], (int) $parts[1]);
    }

    private static function isAmount(string $value, int $digits, bool $positive = false): bool
    {
        return strlen($value) <= $digits && preg_match('/\A(?:0|[1-9][0-9]*)\z/', $value)
            && (! $positive || $value !== '0');
    }
}
