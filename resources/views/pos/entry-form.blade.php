@extends('auth.owner-layout')

@section('title', $productSale ? 'Penjualan produk' : 'Pencatatan layanan')
@section('main-class', 'wide')

@section('content')
    <p><a href="{{ route('barber.dashboard') }}">Kembali ke dashboard</a></p>
    <h1>{{ $productSale ? 'Penjualan produk' : 'Pencatatan layanan' }}</h1>
    <p>Kapster {{ session('barber_name') }}</p>

    @if ($errors->any())
        <ul class="error" role="alert">
            @foreach ($errors->all() as $message)
                <li>{{ $message }}</li>
            @endforeach
        </ul>
    @endif

    @php($sellableProducts = $products->whereNotNull('sale_price'))
    @if ($productSale && $sellableProducts->isEmpty())
        <p>Belum ada produk aktif dengan harga jual.</p>
    @elseif (! $productSale && $services->isEmpty())
        <p>Belum ada layanan aktif.</p>
    @else
        <form id="income-form" method="post" action="{{ route($productSale ? 'barber.products.store' : 'barber.entries.store') }}">
            @csrf
            <div class="field">
                <label for="customer-name">Nama pelanggan (opsional)</label>
                <input id="customer-name" name="customer_name" maxlength="120"
                    value="{{ is_string(old('customer_name')) ? old('customer_name') : '' }}">
            </div>

            @unless ($productSale)
                <fieldset>
                    <legend>Layanan</legend>
                    <div id="services-items"></div>
                    <button type="button" class="secondary" id="services-add">Tambah layanan</button>
                </fieldset>
                <template id="services-row">
                    <div class="entry-row">
                        <label>Layanan
                            <select data-field="service_id" data-item required>
                                <option value="">Pilih layanan</option>
                                @foreach ($services as $service)
                                    <option value="{{ $service->id }}" data-price="Rp{{ number_format((int) $service->current_price, 0, ',', '.') }}"
                                        data-amount="{{ $service->current_price }}">{{ $service->name }}</option>
                                @endforeach
                            </select>
                        </label>
                        <label>Produk dipakai (opsional)
                            <select data-field="used_product_id">
                                <option value="">Tanpa produk</option>
                                @foreach ($products as $product)
                                    <option value="{{ $product->id }}">{{ $product->name }}</option>
                                @endforeach
                            </select>
                        </label>
                        <input type="hidden" data-field="quoted_price">
                        <p>Harga <output data-price-output aria-live="polite">—</output></p>
                        <button type="button" class="secondary" data-remove>Hapus layanan</button>
                    </div>
                </template>
            @endunless

            @if ($sellableProducts->isNotEmpty())
                <fieldset>
                    <legend>{{ $productSale ? 'Produk dijual' : 'Produk dijual (opsional)' }}</legend>
                    <div id="products-items"></div>
                    <button type="button" class="secondary" id="products-add">Tambah produk</button>
                </fieldset>
                <template id="products-row">
                    <div class="entry-row">
                        <label>Produk dijual
                            <select data-field="product_id" data-item required>
                                <option value="">Pilih produk</option>
                                @foreach ($sellableProducts as $product)
                                    <option value="{{ $product->id }}" data-amount="{{ $product->sale_price }}">{{ $product->name }}</option>
                                @endforeach
                            </select>
                        </label>
                        <label>Jumlah
                            <input type="number" min="1" max="4294967295" step="1" value="1" data-field="quantity" required>
                        </label>
                        <input type="hidden" data-field="quoted_price">
                        <p>Harga satuan <output data-price-output aria-live="polite">—</output></p>
                        <button type="button" class="secondary" data-remove>Hapus produk</button>
                    </div>
                </template>
            @endif

            <p>Total <output id="income-total" aria-live="polite">—</output></p>
            <noscript><p>Aktifkan JavaScript untuk memilih item dan melihat harga.</p></noscript>
            <input type="hidden" name="request_key" value="{{ $requestKey }}">
            <input type="hidden" name="request_signature" value="{{ $requestSignature }}">
            <button type="submit" id="save-entry" disabled>Simpan</button>
        </form>

        <script>
            const incomeForm = document.getElementById('income-form');
            const saveEntry = document.getElementById('save-entry');
            const totalOutput = document.getElementById('income-total');
            const productSale = {{ $productSale ? 'true' : 'false' }};
            const rupiah = new Intl.NumberFormat('id-ID');
            const groups = [
                { key: 'services', required: !productSale, old: {{ Illuminate\Support\Js::from(old('services', [])) }} },
                { key: 'products', required: productSale, old: {{ Illuminate\Support\Js::from(old('products', [])) }} },
            ].map(group => ({
                ...group,
                items: document.getElementById(group.key + '-items'),
                template: document.getElementById(group.key + '-row'),
                add: document.getElementById(group.key + '-add'),
            })).filter(group => group.items);

            function refresh() {
                let total = 0n;
                let ready = true;
                for (const group of groups) {
                    const rows = [...group.items.children];
                    if (group.required && rows.length === 0) {
                        ready = false;
                    }

                    rows.forEach((row, index) => {
                        for (const field of row.querySelectorAll('[data-field]')) {
                            field.name = group.key + '[' + index + '][' + field.dataset.field + ']';
                        }
                        const selected = row.querySelector('[data-item]').selectedOptions[0];
                        const amount = selected?.dataset.amount ?? '';
                        const quantity = row.querySelector('[data-field="quantity"]')?.value ?? '1';
                        row.querySelector('[data-field="quoted_price"]').value = amount;
                        row.querySelector('[data-price-output]').textContent = amount ? 'Rp' + rupiah.format(BigInt(amount)) : '—';
                        row.querySelector('[data-remove]').disabled = group.required && rows.length === 1;
                        if (!amount || !/^[1-9][0-9]*$/.test(quantity) || BigInt(quantity) > 4294967295n) {
                            ready = false;
                            return;
                        }
                        total += BigInt(amount) * BigInt(quantity);
                    });
                }

                totalOutput.textContent = ready ? 'Rp' + rupiah.format(total) : '—';
                saveEntry.disabled = !ready;
            }

            function addRow(group, initial = {}) {
                const row = group.template.content.firstElementChild.cloneNode(true);
                for (const field of row.querySelectorAll('[data-field]')) {
                    const value = initial?.[field.dataset.field];
                    if (typeof value === 'string' || typeof value === 'number') {
                        field.value = value;
                    }
                }
                row.querySelector('[data-remove]').addEventListener('click', () => {
                    row.remove();
                    refresh();
                    group.add.focus();
                });
                group.items.append(row);
                return row;
            }

            for (const group of groups) {
                for (const initial of Array.isArray(group.old) ? group.old : []) {
                    addRow(group, initial);
                }
                if (group.required && !group.items.children.length) {
                    addRow(group);
                }
                group.add.addEventListener('click', () => {
                    addRow(group).querySelector('[data-item]').focus();
                    refresh();
                });
            }
            incomeForm.addEventListener('input', refresh);
            incomeForm.addEventListener('change', refresh);
            refresh();
        </script>
    @endif
@endsection
