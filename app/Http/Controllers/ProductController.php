<?php

namespace App\Http\Controllers;

use App\Http\Requests\SaveProductRequest;
use App\Models\Product;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function index(): View
    {
        $products = Product::query()->withExists(['sales', 'historicalUsage'])
            ->orderBy('archived_at')
            ->orderBy('name')
            ->orderBy('id')
            ->paginate(20);

        return view('master.products.index', ['products' => $products]);
    }

    public function create(): View
    {
        return view('master.products.form', ['product' => new Product]);
    }

    public function store(SaveProductRequest $request): RedirectResponse
    {
        Product::create($request->validated());

        return to_route('owner.products.index')->with('status', 'Produk berhasil ditambahkan.');
    }

    public function edit(Product $product): View
    {
        return view('master.products.form', ['product' => $product]);
    }

    public function update(SaveProductRequest $request, Product $product): RedirectResponse
    {
        $product->update($request->validated());

        return to_route('owner.products.index')->with('status', 'Produk berhasil diperbarui.');
    }

    public function archive(Product $product): RedirectResponse
    {
        Product::query()->active()->whereKey($product->id)->update(['archived_at' => now()]);

        return to_route('owner.products.index')->with('status', 'Produk diarsipkan.');
    }

    public function restore(Product $product): RedirectResponse
    {
        Product::query()->whereKey($product->id)->whereNotNull('archived_at')->update(['archived_at' => null]);

        return to_route('owner.products.index')->with('status', 'Produk diaktifkan kembali.');
    }

    public function destroy(Product $product): RedirectResponse
    {
        try {
            $product->delete();
        } catch (QueryException $exception) {
            if ((int) ($exception->errorInfo[1] ?? 0) !== 1451) {
                throw $exception;
            }

            return to_route('owner.products.index')
                ->with('error', 'Produk sudah tercatat pada transaksi. Arsipkan produk untuk mengeluarkannya dari pilihan Kapster.');
        }

        return to_route('owner.products.index')->with('status', 'Produk berhasil dihapus.');
    }
}
