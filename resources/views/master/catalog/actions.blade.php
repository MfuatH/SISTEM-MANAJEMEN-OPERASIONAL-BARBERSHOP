<div class="actions">
    <a href="{{ route($resource.'.edit', $item) }}">Ubah</a>
    <form method="post" action="{{ route($resource.($item->archived_at === null ? '.archive' : '.restore'), $item) }}">
        @csrf
        @method('PATCH')
        <button class="secondary" type="submit">{{ $item->archived_at === null ? 'Arsipkan' : 'Aktifkan kembali' }}</button>
    </form>
    @if ($hasHistory)
        <button class="secondary" type="button" disabled title="Sudah tercatat pada transaksi">Hapus</button>
    @else
        <form method="post" action="{{ route($resource.'.destroy', $item) }}">
            @csrf
            @method('DELETE')
            <button class="secondary" type="submit" aria-label="Hapus {{ $item->name }}">Hapus</button>
        </form>
    @endif
</div>
