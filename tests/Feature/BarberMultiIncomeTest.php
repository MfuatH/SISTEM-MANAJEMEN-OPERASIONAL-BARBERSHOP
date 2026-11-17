<?php

namespace Tests\Feature;

use App\Models\IncomeTransaction;
use App\Models\Product;
use App\Models\Service;
use App\Models\User;
use App\Services\IncomeItems;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class BarberMultiIncomeTest extends TestCase
{
    use DatabaseTransactions;

    public function test_services_usage_and_sales_are_saved_together_and_reconciled_once(): void
    {
        $first = Service::factory()->create(['name' => 'Layanan A', 'current_price' => '35000']);
        $second = Service::factory()->create(['name' => 'Layanan B', 'current_price' => '15000']);
        $product = Product::create(['name' => 'Hair Tonic', 'sale_price' => '15000']);
        $this->startBarber();

        $payload = $this->formKey('barber.entry-form') + [
            'services' => [
                ['service_id' => $first->id, 'quoted_price' => '35000', 'used_product_id' => $product->id],
                ['service_id' => $second->id, 'quoted_price' => '15000'],
            ],
            'products' => [['product_id' => $product->id, 'quantity' => 2, 'quoted_price' => '15000']],
        ];

        $this->post(route('barber.entries.store'), $payload)->assertRedirect(route('barber.dashboard'));
        $product->update(['name' => 'Hair Tonic Baru', 'sale_price' => '20000']);
        $this->post(route('barber.entries.store'), $payload)->assertRedirect(route('barber.dashboard'));

        $transaction = IncomeTransaction::where('request_key', $payload['request_key'])->sole();
        $items = IncomeItems::query()->where('tx.id', $transaction->id)->get();
        $this->assertCount(3, $items);
        $this->assertSame(0, 35_000 + 15_000 + 2 * 15_000 - (int) IncomeItems::query()->where('tx.id', $transaction->id)->sum('items.amount'));
        $this->assertSame('Hair Tonic', $transaction->entries()->where('service_id', $first->id)->sole()->used_product_name);
        $this->assertNull($transaction->entries()->where('service_id', $second->id)->sole()->used_product_name);
        $sale = $transaction->productSales()->sole();
        $this->assertSame('Hair Tonic', $sale->product_name);
        $this->assertSame('15000', $sale->unit_price);
        $this->assertSame(2, $sale->quantity);
        $this->get(route('barber.dashboard'))->assertOk()->assertSee('Produk dipakai Hair Tonic')->assertSee('Rp30.000');
    }

    public function test_product_form_can_sell_products_without_services(): void
    {
        $product = Product::create(['name' => 'Pomade', 'sale_price' => '15000']);
        Product::create(['name' => 'Bahan Khusus Pemakaian']);
        $this->startBarber();

        $form = $this->get(route('barber.product-form'))->assertOk()
            ->assertSee('Pomade')->assertDontSee('Bahan Khusus Pemakaian')->assertDontSee('Pilih layanan');
        $payload = [
            'request_key' => $form->viewData('requestKey'),
            'request_signature' => $form->viewData('requestSignature'),
            'products' => [['product_id' => $product->id, 'quantity' => 2, 'quoted_price' => '15000']],
        ];
        $this->post(route('barber.products.store'), $payload)->assertRedirect(route('barber.dashboard'));
        $this->post(route('barber.products.store'), $payload)->assertRedirect(route('barber.dashboard'));

        $transaction = IncomeTransaction::where('request_key', $payload['request_key'])->sole();
        $this->assertFalse($transaction->entries()->exists());
        $item = IncomeItems::query()->where('tx.id', $transaction->id)->sole();
        $this->assertSame('product', $item->item_type);
        $this->assertSame('30000', (string) $item->amount);
    }

    public function test_each_form_validates_its_required_items_and_rejects_invalid_products(): void
    {
        $service = Service::factory()->create(['current_price' => '35000']);
        $product = Product::create(['name' => 'Bahan', 'sale_price' => null]);
        $this->startBarber();
        $form = $this->formKey('barber.entry-form');
        $serviceItems = [['service_id' => $service->id, 'quoted_price' => '35000']];
        $saleItems = [['product_id' => $product->id, 'quantity' => 2, 'quoted_price' => '15000']];

        $this->post(route('barber.entries.store'), $form + ['products' => $saleItems])
            ->assertSessionHasErrors('services');
        $this->post(route('barber.entries.store'), $form + ['services' => $serviceItems, 'products' => $saleItems])
            ->assertSessionHasErrors('products.0.product_id');
        $this->assertDatabaseMissing('income_transactions', ['request_key' => $form['request_key']]);

        $productForm = $this->formKey('barber.product-form');
        $this->post(route('barber.products.store'), $productForm + ['services' => $serviceItems])
            ->assertRedirect(route('barber.product-form'))->assertSessionHasErrors(['products', 'services']);
        $this->assertDatabaseMissing('income_transactions', ['request_key' => $productForm['request_key']]);

        $product->update(['sale_price' => '20000']);
        $this->post(route('barber.products.store'), $productForm + ['products' => $saleItems])
            ->assertSessionHasErrors('products.0.quoted_price');
        $this->assertDatabaseMissing('income_transactions', ['request_key' => $productForm['request_key']]);
    }

    public function test_owner_can_manage_products_and_guest_access_is_redirected(): void
    {
        $this->get(route('owner.products.index'))->assertRedirect(route('login'));
        $this->post(route('owner.products.store'), ['name' => 'Ditolak'])->assertRedirect(route('login'));
        $this->actingAs(User::factory()->create());
        $this->post(route('owner.products.store'), ['name' => 'Produk Uji', 'sale_price' => '0'])
            ->assertSessionHasErrors('sale_price');
        $this->post(route('owner.products.store'), ['name' => 'Produk Uji'])
            ->assertRedirect(route('owner.products.index'));
        $product = Product::where('name', 'Produk Uji')->sole();
        $this->assertNull($product->sale_price);
        $this->put(route('owner.products.update', $product), ['name' => 'Produk Uji', 'sale_price' => '15000'])
            ->assertRedirect(route('owner.products.index'));
        $this->get(route('owner.products.index'))->assertOk()->assertSee('Rp15.000');
        $this->patch(route('owner.products.archive', $product))->assertRedirect(route('owner.products.index'));
        $this->assertNotNull($product->fresh()->archived_at);
    }

    private function startBarber(): void
    {
        $this->post(route('barber.session.store'), ['name' => 'Bayu'])->assertRedirect(route('barber.dashboard'));
        $this->withCookie(config('session.cookie'), session()->getId());
    }

    private function formKey(string $route): array
    {
        $form = $this->get(route($route))->assertOk();

        return [
            'request_key' => $form->viewData('requestKey'),
            'request_signature' => $form->viewData('requestSignature'),
        ];
    }
}
