<?php

namespace App\Http\Controllers;

use App\Models\Area;
use App\Models\BreakdownRequest;
use App\Models\Division;
use App\Models\Floor;
use App\Models\Role;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReportController extends Controller
{
    /**
     * Display report summary page.
     */
    public function index(Request $request)
    {
        abort_unless(Auth::user()->isAdministrator(), 403);

        /*
        |--------------------------------------------------------------------------
        | Requests by Division
        |--------------------------------------------------------------------------
        */

        $byDivision = BreakdownRequest::selectRaw(
                'division_id, count(*) as total'
            )
            ->whereNotNull('division_id')
            ->with('division')
            ->groupBy('division_id')
            ->get();

        /*
        |--------------------------------------------------------------------------
        | Requests by Status
        |--------------------------------------------------------------------------
        */

        $byStatus = BreakdownRequest::selectRaw(
                'status, count(*) as total'
            )
            ->groupBy('status')
            ->get();

        /*
        |--------------------------------------------------------------------------
        | Requests by Category
        |--------------------------------------------------------------------------
        */

        $byCategory = BreakdownRequest::selectRaw(
                'category_id, count(*) as total'
            )
            ->with('category')
            ->groupBy('category_id')
            ->get();

        /*
        |--------------------------------------------------------------------------
        | Requests by Technical Officer
        |--------------------------------------------------------------------------
        */

        $byTechnician = BreakdownRequest::selectRaw(
                'assigned_to, count(*) as total'
            )
            ->whereNotNull('assigned_to')
            ->with('assignedTo')
            ->groupBy('assigned_to')
            ->get();

        /*
        |--------------------------------------------------------------------------
        | Floors - New Location Table
        |--------------------------------------------------------------------------
        */

        $floors = Floor::where('is_active', true)
            ->orderBy('id')
            ->get();

        /*
        |--------------------------------------------------------------------------
        | Technical Officers
        |--------------------------------------------------------------------------
        */

        $technicians = User::whereHas(
                'role',
                function ($query) {
                    $query->where(
                        'code',
                        Role::TECHNICAL_OFFICER
                    );
                }
            )
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return view(
            'reports.index',
            compact(
                'byDivision',
                'byStatus',
                'byCategory',
                'byTechnician',
                'floors',
                'technicians'
            )
        );
    }


    /**
     * Export detailed breakdown request report as PDF.
     */
    public function exportPdf(Request $request)
    {
        abort_unless(Auth::user()->isAdministrator(), 403);

        $query = BreakdownRequest::with([
            'department',
            'floor',
            'division',
            'areaLocation',
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
            $query->where(
                'status',
                $request->status
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Floor
        |--------------------------------------------------------------------------
        |
        | New requests use floor_id.
        | Legacy requests can still match using department.floor.
        |
        */

        if ($request->filled('floor_id')) {

            $selectedFloorForFilter = Floor::find(
                $request->floor_id
            );

            $query->where(
                function ($q) use (
                    $request,
                    $selectedFloorForFilter
                ) {
                    $q->where(
                        'floor_id',
                        $request->floor_id
                    );

                    if ($selectedFloorForFilter) {
                        $q->orWhereHas(
                            'department',
                            function ($departmentQuery) use (
                                $selectedFloorForFilter
                            ) {
                                $departmentQuery->where(
                                    'floor',
                                    $selectedFloorForFilter->name
                                );
                            }
                        );
                    }
                }
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Division
        |--------------------------------------------------------------------------
        |
        | New requests use division_id.
        | Legacy requests can still match the old department name.
        |
        */

        if ($request->filled('division_id')) {

            $selectedDivisionForFilter = Division::with('floor')
                ->find($request->division_id);

            $query->where(
                function ($q) use (
                    $request,
                    $selectedDivisionForFilter
                ) {
                    $q->where(
                        'division_id',
                        $request->division_id
                    );

                    if ($selectedDivisionForFilter) {
                        $q->orWhereHas(
                            'department',
                            function ($departmentQuery) use (
                                $selectedDivisionForFilter
                            ) {
                                $departmentQuery->where(
                                    'name',
                                    $selectedDivisionForFilter->name
                                );

                                if ($selectedDivisionForFilter->floor) {
                                    $departmentQuery->where(
                                        'floor',
                                        $selectedDivisionForFilter
                                            ->floor
                                            ->name
                                    );
                                }
                            }
                        );
                    }
                }
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Area
        |--------------------------------------------------------------------------
        |
        | New requests use area_id.
        | Legacy requests can still match the old area text.
        |
        */

        if ($request->filled('area_id')) {

            $selectedAreaForFilter = Area::find(
                $request->area_id
            );

            $query->where(
                function ($q) use (
                    $request,
                    $selectedAreaForFilter
                ) {
                    $q->where(
                        'area_id',
                        $request->area_id
                    );

                    if ($selectedAreaForFilter) {
                        $q->orWhere(
                            'area',
                            $selectedAreaForFilter->name
                        );
                    }
                }
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

        /*
        |--------------------------------------------------------------------------
        | Get Requests
        |--------------------------------------------------------------------------
        */

        $requests = $query
            ->latest()
            ->get();

        /*
        |--------------------------------------------------------------------------
        | Filters
        |--------------------------------------------------------------------------
        */

        $filters = [
            'request_number' =>
                $request->request_number,

            'status' =>
                $request->status,

            'floor_id' =>
                $request->floor_id,

            'division_id' =>
                $request->division_id,

            'area_id' =>
                $request->area_id,

            'technician_id' =>
                $request->technician_id,

            'date_from' =>
                $request->date_from,

            'date_to' =>
                $request->date_to,
        ];

        /*
        |--------------------------------------------------------------------------
        | Selected Filter Names for PDF
        |--------------------------------------------------------------------------
        */

        $selectedFloor = null;
        $selectedDivision = null;
        $selectedArea = null;
        $selectedTechnician = null;

        if ($request->filled('floor_id')) {
            $selectedFloor = Floor::find(
                $request->floor_id
            );
        }

        if ($request->filled('division_id')) {
            $selectedDivision = Division::find(
                $request->division_id
            );
        }

        if ($request->filled('area_id')) {
            $selectedArea = Area::find(
                $request->area_id
            );
        }

        if ($request->filled('technician_id')) {
            $selectedTechnician = User::find(
                $request->technician_id
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Generate PDF
        |--------------------------------------------------------------------------
        */

        $pdf = Pdf::loadView(
                'reports.pdf',
                compact(
                    'requests',
                    'filters',
                    'selectedFloor',
                    'selectedDivision',
                    'selectedArea',
                    'selectedTechnician'
                )
            )
            ->setPaper(
                'a4',
                'landscape'
            );

        return $pdf->download(
            'breakdown_requests_' .
            now()->format('Y-m-d_H-i-s') .
            '.pdf'
        );
    }
}
