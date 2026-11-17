<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreIncomeEntryRequest;
use App\Models\IncomeEntry;
use App\Models\Service;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class BarberIncomeController extends Controller
{
    public function index(Request $request): View
    {
        $entries = IncomeEntry::query()
            ->where('session_scope', hash('sha256', $request->session()->getId(), true))
            ->orderByDesc('recorded_at')
            ->orderByDesc('id')
            ->simplePaginate(25);

        return view('pos.dashboard', ['entries' => $entries]);
    }

    public function create(Request $request): View
    {
        $requestKey = (string) Str::uuid();
        $services = Service::query()->active()
            ->orderBy('name')
            ->orderBy('id')
            ->get(['id', 'name', 'current_price']);

        return view('pos.entry-form', [
            'services' => $services,
            'requestKey' => $requestKey,
            'requestSignature' => $this->sign($request, $requestKey),
        ]);
    }

    public function store(StoreIncomeEntryRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $key = $data['request_key'];

        if (! hash_equals($this->sign($request, $key), $data['request_signature'])) {
            throw ValidationException::withMessages([
                'request_key' => 'Form ini tidak berasal dari sesi Kapster yang aktif. Buka form Tambah lagi.',
            ])->redirectTo(route('barber.entry-form'));
        }

        $scope = hash('sha256', $request->session()->getId(), true);
        $payload = [
            'barber_name' => $request->session()->get('barber_name'),
            'customer_name' => $data['customer_name'] ?? null,
            'service_id' => (int) $data['service_id'],
        ];
        $quotedPrice = (string) $data['quoted_price'];
        $hash = hash('sha256', json_encode($payload + ['quoted_price' => $quotedPrice], JSON_THROW_ON_ERROR), true);

        try {
            $entry = DB::transaction(function () use ($payload, $quotedPrice, $key, $scope, $hash): IncomeEntry {
                $service = Service::query()->sharedLock()->find($payload['service_id']);
                $existing = IncomeEntry::query()->where('request_key', $key)->first();
                if ($existing) {
                    return $existing;
                }

                if (! $service || $service->archived_at !== null) {
                    throw ValidationException::withMessages([
                        'service_id' => 'Layanan ini sudah tidak aktif. Pilih layanan yang tersedia.',
                    ])->redirectTo(route('barber.entry-form'));
                }

                if ($service->current_price !== $quotedPrice) {
                    throw ValidationException::withMessages([
                        'quoted_price' => 'Harga layanan berubah. Tinjau harga terbaru, lalu simpan kembali.',
                    ])->redirectTo(route('barber.entry-form'));
                }

                return IncomeEntry::query()->create($payload + [
                    'service_name' => $service->name,
                    'amount' => $service->current_price,
                    'recorded_at' => now('UTC'),
                    'session_scope' => $scope,
                    'request_key' => $key,
                    'request_hash' => $hash,
                ]);
            }, 3);
        } catch (UniqueConstraintViolationException $exception) {
            $entry = IncomeEntry::query()->where('request_key', $key)->first() ?? throw $exception;
        }

        if (! hash_equals($scope, $entry->session_scope) || ! hash_equals($hash, $entry->request_hash)) {
            throw ValidationException::withMessages([
                'request_key' => 'Form ini sudah dipakai. Buka form Tambah untuk pencatatan baru.',
            ])->redirectTo(route('barber.entry-form'));
        }

        return to_route('barber.dashboard')->with('status', 'Pencatatan tersimpan.');
    }

    private function sign(Request $request, string $key): string
    {
        return hash_hmac('sha256', $key, $request->session()->getId());
    }
}
