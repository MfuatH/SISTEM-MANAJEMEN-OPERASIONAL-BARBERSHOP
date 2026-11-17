<?php

namespace App\Http\Controllers;

use App\Http\Requests\SaveProductRequest;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function index(): View
    {
        $products = Product::query()->orderBy('archived_at')->orderBy('name')->orderBy('id')->simplePaginate(20);

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
}
