<?php

namespace App\Http\Controllers;

use App\Services\OwnerRecapReport;
use App\Services\OwnerRecapWorkbook;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class OwnerRecapController extends Controller
{
    public function index(Request $request): View
    {
        $report = $this->report($request);
        $query = $report->query();
        [$items, $detailTotal, $summary] = DB::transaction(fn () => [
            (clone $query)->paginate(25)->appends($report->filters()),
            (string) $query->sum('amount'),
            $report->summary(),
        ]);

        return view('owner.recap.index', compact('report', 'items', 'detailTotal', 'summary'));
    }

    public function export(Request $request, OwnerRecapWorkbook $workbook): StreamedResponse
    {
        $report = $this->report($request);

        return response()->streamDownload(
            fn () => DB::transaction(fn () => $workbook->write($report)),
            'rekap-'.($report->period ?? 'seluruh-periode').'.xlsx',
            ['Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'],
        );
    }

    private function report(Request $request): OwnerRecapReport
    {
        $filters = $request->validate([
            'month' => ['nullable', 'string', 'regex:/\A[1-9][0-9]{3}-(0[1-9]|1[0-2])\z/'],
            'type' => ['nullable', 'string', Rule::in(array_keys(OwnerRecapReport::TYPES))],
            'q' => ['nullable', 'string', 'max:255'],
        ], [
            'month.regex' => 'Pilih bulan dengan format tahun dan bulan yang valid.',
            'type.in' => 'Pilih jenis pemasukan atau pengeluaran.',
        ]);

        return new OwnerRecapReport($filters['month'] ?? null, $filters['type'] ?? null, $filters['q'] ?? '');
    }
}
