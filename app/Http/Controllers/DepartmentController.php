<?php

namespace App\Http\Controllers;

use App\Models\Department;
use Illuminate\Http\Request;

class DepartmentController extends Controller
{
    public function index()
    {
        $departments = Department::orderBy('name')->paginate(15);
        return view('departments.index', compact('departments'));
    }

    public function create()
    {
        return view('departments.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'ministry_name' => ['nullable', 'string', 'max:255'],
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'unique:departments,code'],
            'floor' => ['nullable', 'string'],
            'location' => ['nullable', 'string'],
            'phone' => ['nullable', 'string'],
        ]);
        $data['is_active'] = true;

        Department::create($data);

        return redirect()->route('departments.index')->with('success', 'Department created.');
    }

    public function edit(Department $department)
    {
        return view('departments.edit', compact('department'));
    }

    public function update(Request $request, Department $department)
    {
        $data = $request->validate([
            'ministry_name' => ['nullable', 'string', 'max:255'],
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'unique:departments,code,' . $department->id],
            'floor' => ['nullable', 'string'],
            'location' => ['nullable', 'string'],
            'phone' => ['nullable', 'string'],
            'is_active' => ['nullable', 'boolean'],
        ]);
        $data['is_active'] = $request->boolean('is_active');

        $department->update($data);

        return redirect()->route('departments.index')->with('success', 'Department updated.');
    }

    public function destroy(Department $department)
    {
        $department->update(['is_active' => false]);
        return back()->with('success', 'Department deactivated.');
    }
}
