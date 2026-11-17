<?php

namespace App\Services;

use App\Models\IncomeTransaction;
use App\Models\Product;
use App\Models\Service;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class RecordBarberIncome
{
    public function record(array $data, string $barberName, string $sessionScope): void
    {
        $key = $data['request_key'];
        $customer = [
            'barber_name' => $barberName,
            'customer_name' => $data['customer_name'] ?? null,
        ];
        $services = array_map(static fn (array $item): array => [
            'service_id' => (int) $item['service_id'],
            'quoted_price' => (string) $item['quoted_price'],
            'used_product_id' => isset($item['used_product_id']) ? (int) $item['used_product_id'] : null,
        ], $data['services'] ?? []);
        $products = array_map(static fn (array $item): array => [
            'product_id' => (int) $item['product_id'],
            'quantity' => (int) $item['quantity'],
            'quoted_price' => (string) $item['quoted_price'],
        ], $data['products'] ?? []);

        // Keep the original signature of a single-service payload for requests saved before the backfill.
        $payload = count($services) === 1 && $services[0]['used_product_id'] === null && $products === []
            ? $customer + ['service_id' => $services[0]['service_id'], 'quoted_price' => $services[0]['quoted_price']]
            : $customer + ['services' => $services, 'products' => $products];
        $hash = hash('sha256', json_encode($payload, JSON_THROW_ON_ERROR), true);

        try {
            $transaction = DB::transaction(function () use ($services, $products, $customer, $key, $sessionScope, $hash): IncomeTransaction {
                $serviceCatalog = Service::query()->whereKey(array_column($services, 'service_id'))
                    ->orderBy('id')->sharedLock()->get()->keyBy('id');
                $productCatalog = Product::query()->whereKey(array_column($products, 'product_id'))
                    ->orderBy('id')->sharedLock()->get()->keyBy('id');

                $existing = IncomeTransaction::query()->where('request_key', $key)->first();
                if ($existing) {
                    return $existing;
                }

                $entries = [];
                foreach ($services as $index => $item) {
                    $service = $serviceCatalog->get($item['service_id']);
                    if (! $service || $service->archived_at !== null) {
                        throw ValidationException::withMessages([
                            "services.$index.service_id" => 'Layanan ini sudah tidak aktif. Pilih layanan yang tersedia.',
                        ]);
                    }
                    if ($service->current_price !== $item['quoted_price']) {
                        throw ValidationException::withMessages([
                            "services.$index.quoted_price" => 'Harga layanan berubah. Tinjau harga terbaru, lalu simpan kembali.',
                        ]);
                    }

                    if ($item['used_product_id'] !== null) {
                        throw ValidationException::withMessages([
                            "services.$index.used_product_id" => 'Buka form Layanan terbaru untuk mencatat transaksi.',
                        ]);
                    }

                    $entries[] = [
                        'service_id' => $service->id,
                        'service_name' => $service->name,
                        'amount' => $service->current_price,
                    ];
                }

                $sales = [];
                foreach ($products as $index => $item) {
                    $product = $productCatalog->get($item['product_id']);
                    if (! $product || $product->archived_at !== null || $product->sale_price === null) {
                        throw ValidationException::withMessages([
                            "products.$index.product_id" => 'Produk ini tidak tersedia untuk dijual.',
                        ]);
                    }
                    if ($product->sale_price !== $item['quoted_price']) {
                        throw ValidationException::withMessages([
                            "products.$index.quoted_price" => 'Harga jual produk berubah. Tinjau harga terbaru, lalu simpan kembali.',
                        ]);
                    }

                    $sales[] = [
                        'product_id' => $product->id,
                        'product_name' => $product->name,
                        'quantity' => $item['quantity'],
                        'unit_price' => $product->sale_price,
                    ];
                }

                $transaction = IncomeTransaction::query()->create($customer + [
                    'recorded_at' => now('UTC'),
                    'session_scope' => $sessionScope,
                    'request_key' => $key,
                    'request_hash' => $hash,
                ]);
                $transaction->entries()->createMany($entries);
                $transaction->productSales()->createMany($sales);

                return $transaction;
            }, 3);
        } catch (UniqueConstraintViolationException $exception) {
            $transaction = IncomeTransaction::query()->where('request_key', $key)->first() ?? throw $exception;
        }

        if (! hash_equals($sessionScope, $transaction->session_scope) || ! hash_equals($hash, $transaction->request_hash)) {
            throw ValidationException::withMessages([
                'request_key' => 'Form ini sudah dipakai. Buka form baru untuk pencatatan berikutnya.',
            ]);
        }
    }
}
