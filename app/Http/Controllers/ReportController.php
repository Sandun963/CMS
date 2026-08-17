<?php

namespace App\Http\Controllers;

use App\Models\BreakdownRequest;
use App\Models\Department;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReportController extends Controller
{
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

        return view('reports.index', compact('byDepartment', 'byStatus', 'byCategory', 'byTechnician'));
    }
}
