<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreExpenseRequest;
use App\Models\Expense;
use App\Services\OwnerExpenseReport;
use App\Services\OwnerExpenseWorkbook;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class OwnerExpenseController extends Controller
{
    public function index(Request $request): View
    {
        $report = $this->report($request);
        $query = $report->query();
        [$items, $total] = DB::transaction(fn () => [
            (clone $query)->paginate(25)->appends(['q' => $report->search]),
            (string) $query->sum('amount'),
        ]);

        return view('owner.expenses.index', compact('report', 'items', 'total'));
    }

    public function create(): View
    {
        return view('owner.expenses.create', [
            'today' => now(OwnerExpenseReport::TIMEZONE)->toDateString(),
            'requestKey' => (string) Str::uuid(),
        ]);
    }

    public function store(StoreExpenseRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $payload = [
            'incurred_on' => $data['incurred_on'],
            'description' => $data['description'],
            'amount' => (string) (int) $data['amount'],
        ];
        $hash = hash('sha256', json_encode($payload, JSON_THROW_ON_ERROR), true);
        $expense = Expense::query()->createOrFirst([
            'recorded_by_user_id' => $request->user()->getKey(),
            'request_key' => strtolower($data['request_key']),
        ], $payload + [
            'request_hash' => $hash,
            'created_at' => now('UTC'),
        ]);

        if (! hash_equals($hash, $expense->request_hash)) {
            throw ValidationException::withMessages([
                'request_key' => 'Form ini sudah dipakai. Gunakan form baru untuk mencatat pengeluaran berikutnya.',
            ])->redirectTo(route('owner.expenses.create'));
        }

        return to_route('owner.expenses.index')->with('status', 'Pengeluaran '.Expense::reference($expense->id).' tersimpan.');
    }

    public function export(Request $request, OwnerExpenseWorkbook $workbook): StreamedResponse
    {
        $report = $this->report($request);

        return response()->streamDownload(
            fn () => $workbook->write($report),
            'pengeluaran-'.$report->month->format('Y-m').'.xlsx',
            ['Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'],
        );
    }

    private function report(Request $request): OwnerExpenseReport
    {
        $filters = $request->validate(['q' => ['nullable', 'string', 'max:255']]);

        return new OwnerExpenseReport($filters['q'] ?? '');
    }
}
