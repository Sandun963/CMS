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
     * Display report summary page + filtered preview.
     */
    public function index(Request $request)
    {
        abort_unless(
            Auth::user()->isAdministrator(),
            403
        );

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
        | Floors
        |--------------------------------------------------------------------------
        */

        $floors = Floor::where(
                'is_active',
                true
            )
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

        /*
        |--------------------------------------------------------------------------
        | Department / Ministry Users
        |--------------------------------------------------------------------------
        */

        $departmentUsers = User::whereHas(
                'role',
                function ($query) {
                    $query->where(
                        'code',
                        Role::MINISTRY_USER
                    );
                }
            )
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        /*
        |--------------------------------------------------------------------------
        | Check Whether Filters Are Applied
        |--------------------------------------------------------------------------
        */

        $hasFilters =
            $request->filled('request_number')
            || $request->filled('status')
            || $request->filled('floor_id')
            || $request->filled('division_id')
            || $request->filled('area_id')
            || $request->filled('department_user_id')
            || $request->filled('technician_id')
            || $request->filled('date_from')
            || $request->filled('date_to');

        /*
        |--------------------------------------------------------------------------
        | Filtered Request Preview
        |--------------------------------------------------------------------------
        */

        $filteredRequests = collect();

        if ($hasFilters) {

            $filteredQuery = BreakdownRequest::with([
                'department',
                'floor',
                'division',
                'areaLocation',
                'category',
                'requestedBy',
                'assignedTo',
            ]);

            $this->applyFilters(
                $filteredQuery,
                $request
            );

            $filteredRequests = $filteredQuery
                ->latest()
                ->paginate(10)
                ->withQueryString();
        }

        /*
        |--------------------------------------------------------------------------
        | Return View
        |--------------------------------------------------------------------------
        */

        return view(
            'reports.index',
            compact(
                'byDivision',
                'byStatus',
                'byCategory',
                'byTechnician',
                'floors',
                'technicians',
                'departmentUsers',
                'filteredRequests',
                'hasFilters'
            )
        );
    }

    /**
     * Export detailed breakdown request report as PDF.
     */
    public function exportPdf(Request $request)
    {
        abort_unless(
            Auth::user()->isAdministrator(),
            403
        );

        /*
        |--------------------------------------------------------------------------
        | Build Query
        |--------------------------------------------------------------------------
        */

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
        | Apply Same Filters Used By Preview
        |--------------------------------------------------------------------------
        */

        $this->applyFilters(
            $query,
            $request
        );

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
        | Filter Values
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

            'department_user_id' =>
                $request->department_user_id,

            'technician_id' =>
                $request->technician_id,

            'date_from' =>
                $request->date_from,

            'date_to' =>
                $request->date_to,
        ];

        /*
        |--------------------------------------------------------------------------
        | Selected Filter Names For PDF
        |--------------------------------------------------------------------------
        */

        $selectedFloor = null;
        $selectedDivision = null;
        $selectedArea = null;
        $selectedDepartmentUser = null;
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

        if ($request->filled('department_user_id')) {

            $selectedDepartmentUser = User::find(
                $request->department_user_id
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
                    'selectedDepartmentUser',
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

    /**
     * Apply report filters to BreakdownRequest query.
     */
    private function applyFilters(
        $query,
        Request $request
    ) {
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
        | Legacy requests may still use department.floor.
        |
        */

        if ($request->filled('floor_id')) {

            $selectedFloor = Floor::find(
                $request->floor_id
            );

            $query->where(
                function ($q) use (
                    $request,
                    $selectedFloor
                ) {

                    $q->where(
                        'floor_id',
                        $request->floor_id
                    );

                    if ($selectedFloor) {

                        $q->orWhereHas(
                            'department',
                            function ($departmentQuery) use (
                                $selectedFloor
                            ) {

                                $departmentQuery->where(
                                    'floor',
                                    $selectedFloor->name
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
        | Legacy requests may still match department name.
        |
        */

        if ($request->filled('division_id')) {

            $selectedDivision =
                Division::with('floor')
                    ->find(
                        $request->division_id
                    );

            $query->where(
                function ($q) use (
                    $request,
                    $selectedDivision
                ) {

                    $q->where(
                        'division_id',
                        $request->division_id
                    );

                    if ($selectedDivision) {

                        $q->orWhereHas(
                            'department',
                            function ($departmentQuery) use (
                                $selectedDivision
                            ) {

                                $departmentQuery->where(
                                    'name',
                                    $selectedDivision->name
                                );

                                if (
                                    $selectedDivision->floor
                                ) {

                                    $departmentQuery->where(
                                        'floor',
                                        $selectedDivision
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
        | Legacy requests may still use area text.
        |
        */

        if ($request->filled('area_id')) {

            $selectedArea = Area::find(
                $request->area_id
            );

            $query->where(
                function ($q) use (
                    $request,
                    $selectedArea
                ) {

                    $q->where(
                        'area_id',
                        $request->area_id
                    );

                    if ($selectedArea) {

                        $q->orWhere(
                            'area',
                            $selectedArea->name
                        );
                    }
                }
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Department / Ministry User
        |--------------------------------------------------------------------------
        */

        if ($request->filled('department_user_id')) {

            $query->where(
                'requested_by',
                $request->department_user_id
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
        | From Date
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
        | To Date
        |--------------------------------------------------------------------------
        */

        if ($request->filled('date_to')) {

            $query->whereDate(
                'created_at',
                '<=',
                $request->date_to
            );
        }

        return $query;
    }
}