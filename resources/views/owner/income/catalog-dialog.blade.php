<div class="actions income-catalog-actions">
    <button class="secondary" type="button" aria-haspopup="dialog" aria-controls="owner-catalog-dialog"
        data-catalog-url="{{ route('owner.services.index', absolute: false) }}">Katalog layanan</button>
    <button class="secondary" type="button" aria-haspopup="dialog" aria-controls="owner-catalog-dialog"
        data-catalog-url="{{ route('owner.products.index', absolute: false) }}">Katalog produk</button>
</div>

<dialog id="owner-catalog-dialog" class="catalog-dialog" aria-labelledby="owner-catalog-title">
    <header class="catalog-dialog-header">
        <h2 id="owner-catalog-title">Katalog</h2>
        <form method="dialog">
            <button class="secondary" type="submit" autofocus>Tutup</button>
        </form>
    </header>
    <iframe src="about:blank" title="Katalog"></iframe>
</dialog>

@push('scripts')
    <script src="{{ asset('js/owner-catalog-dialog.js') }}" defer></script>
@endpush
