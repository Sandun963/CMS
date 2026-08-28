<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Attachment;
use App\Models\BreakdownRequest;
use App\Models\Category;
use App\Models\Department;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class BreakdownRequestController extends Controller
{
    /**
     * All Requests view.
     *
     * IT Head:
     *      Can see all requests.
     *
     * Assign Officer:
     *      Can see New / Reopened requests immediately
     *      and requests previously handled by them.
     *
     * Technical Officer:
     *      Can see only jobs assigned to them.
     *
     * Ministry User:
     *      Can see only requests submitted by them.
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        $query = BreakdownRequest::with([
            'department',
            'requestedBy',
            'category',
            'assignedTo'
        ]);

        /*
        |--------------------------------------------------------------------------
        | Role-based request visibility
        |--------------------------------------------------------------------------
        */

        if ($user->isMinistryUser()) {

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

                /*
                 * New and Reopened requests
                 * are immediately visible to Assign Officer.
                 */
                $q->whereIn(
                    'status',
                    ['New', 'Reopened']
                )

                /*
                 * Also show requests previously
                 * handled by this Assign Officer.
                 */
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

        }

        /*
         * IT Head sees everything,
         * so no additional restriction is needed.
         */


        /*
        |--------------------------------------------------------------------------
        | Filters
        |--------------------------------------------------------------------------
        */

        if ($request->filled('status')) {

            $query->where(
                'status',
                $request->status
            );

        }


        if ($request->filled('department_id')) {

            $query->where(
                'department_id',
                $request->department_id
            );

        }


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
        | Get requests
        |--------------------------------------------------------------------------
        */

        $requests = $query
            ->latest()
            ->paginate(15)
            ->withQueryString();


        $departments = Department::orderBy('name')
            ->get();


        return view(
            'requests.index',
            compact(
                'requests',
                'departments'
            )
        );
    }


    /**
     * Show request creation page.
     */
    public function create()
    {
        $categories = Category::where('is_active', true)
            ->orderBy('name')
            ->get();

        /*
        |--------------------------------------------------------------------------
        | Floors
        |--------------------------------------------------------------------------
        */

        $floors = Department::where('is_active', true)
            ->whereNotNull('floor')
            ->where('floor', '!=', '')
            ->select('floor')
            ->distinct()
            ->orderBy('floor')
            ->pluck('floor');


        /*
        |--------------------------------------------------------------------------
        | Divisions
        |--------------------------------------------------------------------------
        |
        | Existing departments table is used.
        | department.name = Division Name
        | department.floor = Floor
        |
        */

        $divisions = Department::where('is_active', true)
            ->whereNotNull('floor')
            ->orderBy('name')
            ->get([
                'id',
                'name',
                'floor'
            ]);


        /*
        |--------------------------------------------------------------------------
        | Sub Categories
        |--------------------------------------------------------------------------
        */

        $subCategories = config(
            'breakdown.sub_categories',
            []
        );


        /*
        |--------------------------------------------------------------------------
        | Areas
        |--------------------------------------------------------------------------
        */

        $areas = config(
            'breakdown.areas',
            []
        );


        return view(
            'requests.create',
            compact(
                'categories',
                'floors',
                'divisions',
                'subCategories',
                'areas'
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
                'nullable',
                'exists:categories,id'
            ],


            /*
             * The UI calls this field "Sub Category",
             * but we continue storing it in the existing
             * title column.
             */
            'title' => [
                'required',
                'string',
                Rule::in(
                    config(
                        'breakdown.sub_categories',
                        []
                    )
                ),
            ],


            'description' => [
                'nullable',
                'string'
            ],


            'floor' => [
                'required',
                'string',
                'max:255'
            ],

            'department_id' => [
                'required',
                'exists:departments,id'
            ],

            'area' => [
                'required',
                'string',
                Rule::in(config('breakdown.areas', [])),
            ],

            'machine_owner_name' => [
                'required',
                'string',
                'max:255'
            ],

            'machine_owner_contact' => [
                'required',
                'string',
                'max:30'
            ],


            'attachments.*' => [
                'nullable',
                'file',
                'max:5120',
                'mimes:jpg,jpeg,png,pdf'
            ],

        ]);


        /*
        |--------------------------------------------------------------------------
        | Extra Ministry / Department Validation
        |--------------------------------------------------------------------------
        |
        | Make sure the selected department actually belongs
        | to the selected ministry.
        |
        */

        $division = Department::where(
                'id',
                $data['department_id']
            )
            ->where(
                'floor',
                $data['floor']
            )
            ->where(
                'is_active',
                true
            )
            ->first();


        if (! $division) {

            return back()
                ->withErrors([
                    'department_id' =>
                        'The selected division does not belong to the selected floor.'
                ])
                ->withInput();
        }


        if (! $department) {

            return back()
                ->withErrors([
                    'department_id' =>
                        'The selected department does not belong to the selected ministry.'
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

            'department_id' =>
                $data['department_id'],

            'requested_by' =>
                $user->id,

            'category_id' =>
                $data['category_id'] ?? null,

            /*
             * Existing database column.
             * UI label = Sub Category.
             */
            'title' =>
                $data['title'],

            'description' =>
                $data['description'],

            'status' =>
                'New',

            'received_at' =>
                now(),

            'area' => $data['area'],

            'machine_owner_name' =>
                $data['machine_owner_name'],

            'machine_owner_contact' =>
                $data['machine_owner_contact'],

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

            'department',

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


        /*
        |--------------------------------------------------------------------------
        | Return View
        |--------------------------------------------------------------------------
        */

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
         * IT Head can view everything.
         */
        if ($user->isItHead()) {
            return;
        }


        /*
         * Ministry User:
         * only their own requests.
         */
        if (
            $user->isMinistryUser()
            &&
            $breakdownRequest->requested_by
                === $user->id
        ) {
            return;
        }


        /*
         * Technical Officer:
         * only their assigned requests.
         */
        if (
            $user->isTechnicalOfficer()
            &&
            $breakdownRequest->assigned_to
                === $user->id
        ) {
            return;
        }


        /*
         * Assign Officer:
         *
         * Can view all New and Reopened requests,
         * plus requests they previously handled.
         */
        if ($user->isAssignOfficer()) {

            if (
                in_array(
                    $breakdownRequest->status,
                    ['New', 'Reopened']
                )
            ) {
                return;
            }


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
         * Otherwise deny.
         */
        abort(
            403,
            'You do not have access to this request.'
        );
    }


    /**
     * Generate request number.
     *
     * Example:
     * BRK-2026-0001
     */
    protected function generateRequestNumber(): string
    {
        $year = now()->year;


        $count = BreakdownRequest::whereYear(
                'created_at',
                $year
            )
            ->count()
            + 1;


        return sprintf(
            'BRK-%d-%04d',
            $year,
            $count
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
         * Nothing uploaded.
         */
        if (! $request->hasFile('attachments')) {
            return;
        }


        foreach (
            $request->file('attachments')
            as $file
        ) {

            /*
             * Store file.
             */
            $path = $file->store(
                'attachments/requests/' .
                $breakdown->id,
                'public'
            );


            /*
             * Save attachment record.
             */
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