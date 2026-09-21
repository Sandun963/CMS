<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Area;
use App\Models\Attachment;
use App\Models\BreakdownRequest;
use App\Models\Category;
use App\Models\Division;
use App\Models\Floor;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class BreakdownRequestController extends Controller
{
    /**
     * All Requests view.
     */
    public function index(Request $request)
    {
        $user = Auth::user();


        /*
        |--------------------------------------------------------------------------
        | Base Query
        |--------------------------------------------------------------------------
        */

        $query = BreakdownRequest::with([
            'floor',
            'division',
            'areaLocation',

            // Legacy relationship - kept temporarily
            'department',

            'requestedBy',
            'category',
            'assignedTo',
        ]);


        /*
        |--------------------------------------------------------------------------
        | Role Based Visibility
        |--------------------------------------------------------------------------
        |
        | Super Admin:
        |   Can view all requests.
        |
        | Administrator:
        |   Can view all requests for monitoring.
        |
        | Ministry User:
        |   Can view only requests submitted by themselves.
        |
        | Technical Officer:
        |   Can view only requests assigned to themselves.
        |
        | Assign Officer:
        |   Can view New/Reopened requests and requests assigned to them.
        |
        */

        if (
            $user->isSuperAdmin()
            ||
            $user->isAdministrator()
        ) {

            /*
             * No query restriction.
             *
             * Super Admin and Administrator
             * can view all breakdown requests.
             */

        } elseif ($user->isMinistryUser()) {

            $query->where(
                'requested_by',
                $user->id
            );

        } elseif ($user->isTechnicalOfficer()) {

            $query->where(
                'assigned_to',
                $user->id
            );

        } elseif ($user->isAssignOfficer()) {

            $query->where(function ($q) use ($user) {

                $q->whereIn(
                    'status',
                    [
                        'New',
                        'Reopened',
                    ]
                )

                ->orWhereHas(
                    'assignments',
                    function ($assignmentQuery) use ($user) {

                        $assignmentQuery->where(
                            'assign_officer_id',
                            $user->id
                        );

                    }
                );

            });

        } else {

            /*
             * Safety protection.
             *
             * Any unknown role must not automatically
             * gain access to all requests.
             */

            abort(
                403,
                'You do not have access to breakdown requests.'
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Status Filter
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
        | Floor Filter
        |--------------------------------------------------------------------------
        */

        if ($request->filled('floor_id')) {

            $query->where(
                'floor_id',
                $request->floor_id
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Division Filter
        |--------------------------------------------------------------------------
        */

        if ($request->filled('division_id')) {

            $query->where(
                'division_id',
                $request->division_id
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Search
        |--------------------------------------------------------------------------
        */

        if ($request->filled('q')) {

            $search = $request->q;

            $query->where(function ($w) use ($search) {

                $w->where(
                    'request_number',
                    'like',
                    "%{$search}%"
                )

                ->orWhere(
                    'title',
                    'like',
                    "%{$search}%"
                );

            });
        }


        /*
        |--------------------------------------------------------------------------
        | Get Requests
        |--------------------------------------------------------------------------
        */

        /*
        |--------------------------------------------------------------------------
        | Check Whether Search / Filters Are Applied
        |--------------------------------------------------------------------------
        */

        $hasFilters =
            $request->filled('q') ||
            $request->filled('status') ||
            $request->filled('floor_id') ||
            $request->filled('division_id');


        /*
        |--------------------------------------------------------------------------
        | Get Requests
        |--------------------------------------------------------------------------
        */

        if ($hasFilters) {

            /*
            * A search/filter is active.
            *
            * Search is performed against all requests in the database
            * that this logged-in user is allowed to see.
            *
            * Return ALL matching results.
            */
            $requests = $query
                ->latest()
                ->get();

        } else {

            /*
            * No search/filter.
            *
            * Do NOT load the entire requests table.
            * Only load the newest 50 requests.
            */
            $requests = $query
                ->latest()
                ->limit(50)
                ->get();
        }


        /*
        |--------------------------------------------------------------------------
        | Floors For Filter
        |--------------------------------------------------------------------------
        */

        $floors = Floor::where(
                'is_active',
                true
            )
            ->orderBy('id')
            ->get();


        return view(
            'requests.index',
            compact(
                'requests',
                'floors'
            )
        );
    }


    /**
     * Show request creation page.
     */
    public function create()
    {
        /*
        |--------------------------------------------------------------------------
        | Active Categories
        |--------------------------------------------------------------------------
        */

        $categories = Category::where(
                'is_active',
                true
            )
            ->orderBy('name')
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


        return view(
            'requests.create',
            compact(
                'categories',
                'floors'
            )
        );
    }


    /**
     * Store new breakdown request.
     */
    public function store(Request $request)
    {
        $user = Auth::user();


        /*
        |--------------------------------------------------------------------------
        | Validation
        |--------------------------------------------------------------------------
        */

        $data = $request->validate([

        'category_id' => [
            'required',
            'integer',

            Rule::exists(
                'categories',
                'id'
            )->where(
                function ($query) {
                    $query->where(
                        'is_active',
                        true
                    );
                }
            ),
        ],


            'description' => [
                'nullable',
                'string',
            ],


            /*
            |--------------------------------------------------------------------------
            | New Location Fields
            |--------------------------------------------------------------------------
            */

            'floor_id' => [
                'required',
                'integer',
                'exists:floors,id',
            ],


            'division_id' => [
                'required',
                'integer',
                'exists:divisions,id',
            ],


            'area_id' => [
                'required',
                'integer',
                'exists:areas,id',
            ],


            /*
            |--------------------------------------------------------------------------
            | Machine Owner
            |--------------------------------------------------------------------------
            */

            'machine_owner_name' => [
                'required',
                'string',
                'max:255',
            ],


            'troubleshooter_name' => [
                'nullable',
                'string',
                'max:255',
            ],

            'troubleshooter_contact' => [
                'nullable',
                'regex:/^[0-9]{1,10}$/',
            ],


            /*
            |--------------------------------------------------------------------------
            | Attachments
            |--------------------------------------------------------------------------
            */

            'attachments' => [
                'nullable',
                'array',
            ],


            'attachments.*' => [
                'nullable',
                'file',
                'max:5120',
                'mimes:jpg,jpeg,png,pdf',
            ],

        ]);


        /*
        |--------------------------------------------------------------------------
        | Get Floor
        |--------------------------------------------------------------------------
        */

        $floor = Floor::where(
                'id',
                $data['floor_id']
            )
            ->where(
                'is_active',
                true
            )
            ->first();


        if (! $floor) {

            return back()
                ->withErrors([
                    'floor_id' =>
                        'The selected floor is invalid.',
                ])
                ->withInput();
        }


        /*
        |--------------------------------------------------------------------------
        | Validate Division belongs to Floor
        |--------------------------------------------------------------------------
        */

        $division = Division::where(
                'id',
                $data['division_id']
            )
            ->where(
                'floor_id',
                $floor->id
            )
            ->where(
                'is_active',
                true
            )
            ->first();


        if (! $division) {

            return back()
                ->withErrors([
                    'division_id' =>
                        'The selected division does not belong to the selected floor.',
                ])
                ->withInput();
        }


        /*
        |--------------------------------------------------------------------------
        | Validate Area belongs to Division
        |--------------------------------------------------------------------------
        */

        $area = Area::where(
                'id',
                $data['area_id']
            )
            ->where(
                'division_id',
                $division->id
            )
            ->where(
                'is_active',
                true
            )
            ->first();


        if (! $area) {

            return back()
                ->withErrors([
                    'area_id' =>
                        'The selected area does not belong to the selected division.',
                ])
                ->withInput();
        }


        /*
        |--------------------------------------------------------------------------
        | Create Breakdown Request
        |--------------------------------------------------------------------------
        */

        $breakdown = BreakdownRequest::create([

            'request_number' =>
                $this->generateRequestNumber(),


            /*
             * Old Department system is no longer used
             * for new location selections.
             */

            'department_id' =>
                null,


            /*
             * New relational location fields.
             */

            'floor_id' =>
                $floor->id,

            'division_id' =>
                $division->id,

            'area_id' =>
                $area->id,


            'requested_by' =>
                $user->id,


            'category_id' =>
                $data['category_id'],


            /*
            |--------------------------------------------------------------------------
            | Legacy title
            |--------------------------------------------------------------------------
            |
            | The system previously stored the Sub Category in title.
            | Keep title populated for backward compatibility.
            |
            */

            'title' =>
                Category::findOrFail(
                    $data['category_id']
                )->name,


            'description' =>
                $data['description'] ?? null,


            'status' =>
                'New',


            'received_at' =>
                now(),


            /*
             * Keep old area text column temporarily.
             *
             * This prevents older pages/reports that display
             * $breakdownRequest->area from breaking.
             */

            'area' =>
                $area->name,


            'machine_owner_name' =>
                strtoupper($data['machine_owner_name']),

            'troubleshooter_name' =>
                !empty($data['troubleshooter_name'])
                    ? strtoupper($data['troubleshooter_name'])
                    : null,

            'troubleshooter_contact' =>
                $data['troubleshooter_contact'] ?? null,

        ]);


        /*
        |--------------------------------------------------------------------------
        | Attachments
        |--------------------------------------------------------------------------
        */

        $this->storeAttachments(
            $request,
            $breakdown,
            $user
        );


        /*
        |--------------------------------------------------------------------------
        | Activity Log
        |--------------------------------------------------------------------------
        */

        ActivityLog::log(
            $breakdown->id,
            $user->id,
            'Submitted request',
            $breakdown->title
        );


        /*
        |--------------------------------------------------------------------------
        | Redirect
        |--------------------------------------------------------------------------
        */

        return redirect()
            ->route(
                'requests.show',
                $breakdown
            )
            ->with(
                'success',
                "Request {$breakdown->request_number} submitted successfully."
            );
    }


    /**
     * Show individual breakdown request.
     */
    public function show(
        BreakdownRequest $breakdownRequest
    ) {
        $user = Auth::user();


        /*
        |--------------------------------------------------------------------------
        | Authorization
        |--------------------------------------------------------------------------
        */

        $this->authorizeView(
            $user,
            $breakdownRequest
        );


        /*
        |--------------------------------------------------------------------------
        | Load Relationships
        |--------------------------------------------------------------------------
        */

        $breakdownRequest->load([

            /*
             * Legacy Department relationship.
             * Kept temporarily for older data.
             */

            'department',


            /*
             * New location relationships.
             */

            'floor',

            'division',

            'areaLocation',


            'requestedBy',

            'category',

            'assignedTo',

            'attachments',

            'assignments.assignedBy',

            'assignments.assignOfficer',

            'assignments.officerAssignments.technicalOfficer',

            'assignments.officerAssignments.workReport.confirmation',

            'assignments.officerAssignments.workReport.attachments',

            'activityLogs.user',

        ]);


        /*
        |--------------------------------------------------------------------------
        | Assign Officers
        |--------------------------------------------------------------------------
        */

        $assignOfficers = User::whereHas(
                'role',
                fn ($r) =>
                    $r->where(
                        'code',
                        'assign_officer'
                    )
            )
            ->where(
                'is_active',
                true
            )
            ->get();


        /*
        |--------------------------------------------------------------------------
        | Technical Officers
        |--------------------------------------------------------------------------
        */

        $technicalOfficers = User::whereHas(
                'role',
                fn ($r) =>
                    $r->where(
                        'code',
                        'technical_officer'
                    )
            )
            ->where(
                'is_active',
                true
            )
            ->get();


        return view(
            'requests.show',
            compact(
                'breakdownRequest',
                'assignOfficers',
                'technicalOfficers'
            )
        );
    }


    /**
     * Authorize request visibility.
     */
    protected function authorizeView(
        User $user,
        BreakdownRequest $breakdownRequest
    ): void {

        /*
        |--------------------------------------------------------------------------
        | Super Admin / Administrator
        |--------------------------------------------------------------------------
        |
        | Both roles can view every breakdown request.
        |
        | This only grants VIEW access.
        | Workflow actions are protected separately by route middleware.
        |
        */

        if (
            $user->isSuperAdmin()
            ||
            $user->isAdministrator()
        ) {

            return;
        }


        /*
        |--------------------------------------------------------------------------
        | Ministry User
        |--------------------------------------------------------------------------
        */

        if (
            $user->isMinistryUser()
            &&
            $breakdownRequest->requested_by ===
                $user->id
        ) {

            return;
        }


        /*
        |--------------------------------------------------------------------------
        | Technical Officer
        |--------------------------------------------------------------------------
        */

        if (
            $user->isTechnicalOfficer()
            &&
            $breakdownRequest->assigned_to ===
                $user->id
        ) {

            return;
        }


        /*
        |--------------------------------------------------------------------------
        | Assign Officer
        |--------------------------------------------------------------------------
        */

        if ($user->isAssignOfficer()) {

            /*
             * Assign Officer can see requests waiting
             * for assignment/reassignment.
             */

            if (
                in_array(
                    $breakdownRequest->status,
                    [
                        'New',
                        'Reopened',
                        'Pending Reassignment',
                    ],
                    true
                )
            ) {
                return;
            }


            /*
             * Assign Officer can also see requests
             * previously assigned to them.
             */

            if (
                $breakdownRequest
                    ->assignments()
                    ->where(
                        'assign_officer_id',
                        $user->id
                    )
                    ->exists()
            ) {

                return;
            }
        }


        /*
        |--------------------------------------------------------------------------
        | Otherwise Deny
        |--------------------------------------------------------------------------
        */

        abort(
            403,
            'You do not have access to this request.'
        );
    }

    /**
     * Generate request/token number.
     *
     * Format:
     * CMS/26/0001
     * CMS/26/0002
     *
     * The sequence automatically restarts
     * from 0001 when a new year begins.
     */
    protected function generateRequestNumber(): string
    {
        $year = now()->format('y');

        $prefix = "CMS/{$year}/";

        /*
        |--------------------------------------------------------------------------
        | Find Highest Number For Current Year
        |--------------------------------------------------------------------------
        */

        $lastRequest = BreakdownRequest::where(
                'request_number',
                'like',
                $prefix . '%'
            )
            ->orderByRaw(
                'CAST(SUBSTRING_INDEX(request_number, "/", -1) AS UNSIGNED) DESC'
            )
            ->first();


        /*
        |--------------------------------------------------------------------------
        | Determine Next Sequence
        |--------------------------------------------------------------------------
        */

        $nextNumber = 1;

        if ($lastRequest) {

            $parts = explode(
                '/',
                $lastRequest->request_number
            );

            $lastNumber = (int) end($parts);

            $nextNumber = $lastNumber + 1;
        }


        /*
        |--------------------------------------------------------------------------
        | Generate Final Token
        |--------------------------------------------------------------------------
        */

        return sprintf(
            'CMS/%s/%04d',
            $year,
            $nextNumber
        );
    }


    /**
     * Store request attachments.
     */
    protected function storeAttachments(
        Request $request,
        BreakdownRequest $breakdown,
        User $user
    ): void {

        /*
        |--------------------------------------------------------------------------
        | No Attachments
        |--------------------------------------------------------------------------
        */

        if (! $request->hasFile('attachments')) {

            return;
        }


        /*
        |--------------------------------------------------------------------------
        | Store Attachments
        |--------------------------------------------------------------------------
        */

        foreach (
            $request->file('attachments')
            as $file
        ) {

            $path = $file->store(
                'attachments/requests/' .
                $breakdown->id,
                'public'
            );


            Attachment::create([

                'attachable_id' =>
                    $breakdown->id,


                'attachable_type' =>
                    BreakdownRequest::class,


                'uploaded_by' =>
                    $user->id,


                'original_name' =>
                    $file->getClientOriginalName(),


                'path' =>
                    $path,


                'mime_type' =>
                    $file->getClientMimeType(),


                'size' =>
                    $file->getSize(),

            ]);
        }
    }
}