<?php

namespace App\Http\Controllers;

use App\Services\OwnerIncomeReport;
use App\Services\OwnerIncomeWorkbook;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class OwnerIncomeController extends Controller
{
    public function index(Request $request): View
    {
        $report = $this->report($request);
        $query = $report->query();
        [$items, $total] = DB::transaction(fn () => [
            (clone $query)->paginate(25)->appends(['q' => $report->search]),
            (string) $query->sum('items.amount'),
        ]);

        return view('owner.income.index', compact('report', 'items', 'total'));
    }

    public function export(Request $request, OwnerIncomeWorkbook $workbook): StreamedResponse
    {
        $report = $this->report($request);

        return response()->streamDownload(
            fn () => $workbook->write($report),
            'pemasukan-'.$report->month->format('Y-m').'.xlsx',
            ['Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'],
        );
    }

    private function report(Request $request): OwnerIncomeReport
    {
        $filters = $request->validate(['q' => ['nullable', 'string', 'max:120']]);

        return new OwnerIncomeReport($filters['q'] ?? '');
    }
}
