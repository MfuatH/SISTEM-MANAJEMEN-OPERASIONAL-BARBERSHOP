<?php

namespace App\Http\Controllers;

use App\Services\OwnerDashboardReport;
use Illuminate\View\View;

class OwnerDashboardController extends Controller
{
    public function __invoke(OwnerDashboardReport $report): View
    {
        return view('owner.home', $report->currentMonth());
    }
}
