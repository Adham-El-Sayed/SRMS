<?php

namespace App\Http\Controllers;

use App\Exports\SalesReportExport;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class ReportController extends Controller
{
    public function index()
    {
        return view('reports.sales');
    }

    public function export(Request $request)
    {
        $validated = $request->validate([
            'from' => ['required', 'date'],
            'to' => ['required', 'date', 'after_or_equal:from'],
        ]);

        $from = Carbon::parse($validated['from'])->startOfDay();
        $to = Carbon::parse($validated['to'])->endOfDay();

        return Excel::download(
            new SalesReportExport($from, $to),
            "sales-report-{$validated['from']}-to-{$validated['to']}.xlsx"
        );
    }
}