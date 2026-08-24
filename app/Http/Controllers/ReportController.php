<?php

namespace App\Http\Controllers;

use App\Exports\BreakdownRequestsExport;
use App\Models\BreakdownRequest;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use App\Models\Department;
use App\Models\Role;
use App\Models\User;

class ReportController extends Controller
{
    /**
     * Display report summary page.
     */
    public function index(Request $request)
    {
        abort_unless(Auth::user()->isItHead(), 403);

        $byDepartment = BreakdownRequest::selectRaw('department_id, count(*) as total')
            ->with('department')
            ->groupBy('department_id')
            ->get();

        $byStatus = BreakdownRequest::selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->get();

        $byCategory = BreakdownRequest::selectRaw('category_id, count(*) as total')
            ->with('category')
            ->groupBy('category_id')
            ->get();

        $byTechnician = BreakdownRequest::selectRaw('assigned_to, count(*) as total')
            ->whereNotNull('assigned_to')
            ->with('assignedTo')
            ->groupBy('assigned_to')
            ->get();

        $ministries = Department::whereNotNull('ministry_name')
            ->where('ministry_name', '!=', '')
            ->select('ministry_name')
            ->distinct()
            ->orderBy('ministry_name')
            ->pluck('ministry_name');

        $departments = Department::where('is_active', true)
            ->orderBy('name')
            ->get();

        $technicians = User::whereHas('role', function ($query) {
                $query->where('code', Role::TECHNICAL_OFFICER);
            })
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('reports.index', compact(
            'byDepartment',
            'byStatus',
            'byCategory',
            'byTechnician',
            'ministries',
            'departments',
            'technicians'
        ));
    }


    /**
     * Export detailed breakdown request report as PDF.
     */
    public function exportPdf(Request $request)
    {
        abort_unless(Auth::user()->isItHead(), 403);

        $query = BreakdownRequest::with([
            'department',
            'category',
            'requestedBy',
            'assignedTo',
        ]);

        /*
        |--------------------------------------------------------------------------
        | Request Number
        |--------------------------------------------------------------------------
        */
        if ($request->filled('request_number')) {
            $query->where(
                'request_number',
                'like',
                '%' . $request->request_number . '%'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Status
        |--------------------------------------------------------------------------
        */
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        /*
        |--------------------------------------------------------------------------
        | Ministry
        |--------------------------------------------------------------------------
        */
        if ($request->filled('ministry_name')) {

            $query->whereHas('department', function ($q) use ($request) {

                $q->where(
                    'ministry_name',
                    $request->ministry_name
                );

            });
        }

        /*
        |--------------------------------------------------------------------------
        | Department
        |--------------------------------------------------------------------------
        */
        if ($request->filled('department_id')) {
            $query->where(
                'department_id',
                $request->department_id
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Technical Officer
        |--------------------------------------------------------------------------
        */
        if ($request->filled('technician_id')) {
            $query->where(
                'assigned_to',
                $request->technician_id
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Date From
        |--------------------------------------------------------------------------
        */
        if ($request->filled('date_from')) {
            $query->whereDate(
                'created_at',
                '>=',
                $request->date_from
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Date To
        |--------------------------------------------------------------------------
        */
        if ($request->filled('date_to')) {
            $query->whereDate(
                'created_at',
                '<=',
                $request->date_to
            );
        }

        $requests = $query
            ->latest()
            ->get();

        $filters = [
            'request_number' => $request->request_number,
            'status' => $request->status,
            'ministry_name' => $request->ministry_name,
            'department_id' => $request->department_id,
            'technician_id' => $request->technician_id,
            'date_from' => $request->date_from,
            'date_to' => $request->date_to,
        ];

        $selectedDepartment = null;
        $selectedTechnician = null;

        if ($request->filled('department_id')) {
            $selectedDepartment = Department::find(
                $request->department_id
            );
        }

        if ($request->filled('technician_id')) {
            $selectedTechnician = User::find(
                $request->technician_id
            );
        }

        $pdf = Pdf::loadView(
            'reports.pdf',
            compact(
                'requests',
                'filters',
                'selectedDepartment',
                'selectedTechnician'
            )
        )
        ->setPaper('a4', 'landscape');

        return $pdf->download(
            'breakdown_requests_' .
            now()->format('Y-m-d_H-i-s') .
            '.pdf'
        );
    }


    /**
     * Export detailed breakdown request report as Excel.
     */
    public function exportExcel()
    {
        abort_unless(Auth::user()->isItHead(), 403);

        return Excel::download(
            new BreakdownRequestsExport(),
            'breakdown_requests_' .
            now()->format('Y-m-d_H-i-s') .
            '.xlsx'
        );
    }
}