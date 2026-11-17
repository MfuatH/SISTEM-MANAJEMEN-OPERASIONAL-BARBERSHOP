<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreIncomeEntryRequest;
use App\Models\Product;
use App\Models\Service;
use App\Services\IncomeItems;
use App\Services\RecordBarberIncome;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class BarberIncomeController extends Controller
{
    public function index(Request $request): View
    {
        $entries = IncomeItems::query()
            ->where('tx.session_scope', hash('sha256', $request->session()->getId(), true))
            ->orderByDesc('tx.recorded_at')
            ->orderByDesc('tx.id')
            ->orderBy('items.item_type')
            ->orderBy('items.item_id')
            ->simplePaginate(25);

        return view('pos.dashboard', ['entries' => $entries]);
    }

    public function create(Request $request): View
    {
        return $this->form($request, false);
    }

    public function createProductSale(Request $request): View
    {
        return $this->form($request, true);
    }

    public function store(StoreIncomeEntryRequest $request, RecordBarberIncome $recorder): RedirectResponse
    {
        $data = $request->validated();
        $sessionId = $request->session()->getId();
        $form = $request->isProductSale() ? 'barber.product-form' : 'barber.entry-form';

        if (! hash_equals($this->sign($sessionId, $data['request_key']), $data['request_signature'])) {
            throw ValidationException::withMessages([
                'request_key' => 'Form ini tidak berasal dari sesi Kapster yang aktif. Buka form pencatatan lagi.',
            ])->redirectTo(route($form));
        }

        try {
            $recorder->record($data, $request->session()->get('barber_name'), hash('sha256', $sessionId, true));
        } catch (ValidationException $exception) {
            throw $exception->redirectTo(route($form));
        }

        return to_route('barber.dashboard')->with('status', 'Pencatatan tersimpan.');
    }

    private function form(Request $request, bool $productSale): View
    {
        $requestKey = (string) Str::uuid();
        $services = $productSale ? collect() : Service::query()->active()
            ->orderBy('name')->orderBy('id')->get(['id', 'name', 'current_price']);
        $products = Product::query()->active()->orderBy('name')->orderBy('id')->get(['id', 'name', 'sale_price']);

        return view('pos.entry-form', [
            'services' => $services,
            'products' => $products,
            'productSale' => $productSale,
            'requestKey' => $requestKey,
            'requestSignature' => $this->sign($request->session()->getId(), $requestKey),
        ]);
    }

    private function sign(string $sessionId, string $key): string
    {
        return hash_hmac('sha256', $key, $sessionId);
    }
}
