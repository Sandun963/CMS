<?php

namespace App\Exports;

use App\Models\BreakdownRequest;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class BreakdownRequestsExport implements FromView
{
    /**
     * Data used to generate Excel report.
     */
    public function view(): View
    {
        $requests = BreakdownRequest::with([
            'department',
            'category',
            'requestedBy',
            'assignedTo',
        ])
        ->latest()
        ->get();

        return view(
            'reports.excel',
            compact('requests')
        );
    }
}