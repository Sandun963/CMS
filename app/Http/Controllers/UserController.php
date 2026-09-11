<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\Division;
use App\Models\Floor;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $query = User::with([ 'role' , 'floor', 'division',]);

        if ($request->filled('role')) {
            $query->whereHas('role', fn ($r) => $r->where('code', $request->role));
        }

        $users = $query->orderBy('name')->paginate(15)->withQueryString();
        $roles = Role::orderBy('name')->get();

        return view('users.index', compact('users', 'roles'));
    }

    public function create()
    {
        $roles = Role::orderBy('name')->get();

        $floors = Floor::where('is_active', true)
            ->orderBy('id')
            ->get();

        return view('users.create', compact(
            'roles',
            'floors'
        ));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:users,email'],
            'username' => ['required', 'string', 'unique:users,username'],
            'password' => ['required', 'string', 'min:6'],
            'role_id' => ['required', 'exists:roles,id'],
            'admin_scope' => ['nullable','string','max:100'],
            'floor_id' => ['nullable', 'exists:floors,id'],
            'division_id' => ['nullable', 'exists:divisions,id'],
            'phone' => ['nullable', 'string'],
            'specialty' => ['nullable', 'string'],
        ]);


        $administratorRole = Role::where(
            'code',
            Role::ADMINISTRATOR
        )->first();

        if (
            ! $administratorRole
            ||
            (int) $data['role_id'] !== (int) $administratorRole->id
        ) {
            $data['admin_scope'] = null;
        }

        
        if (! empty($data['division_id'])) {

            $validDivision = Division::where('id', $data['division_id'])
                ->where('floor_id', $data['floor_id'])
                ->exists();

            if (! $validDivision) {
                return back()
                    ->withErrors([
                        'division_id' => 'The selected division does not belong to the selected floor.'
                    ])
                    ->withInput();
            }
        }

        $data['password'] = Hash::make($data['password']);
        $data['is_active'] = true;

        User::create($data);

        return redirect()->route('users.index')->with('success', 'User created.');
    }


    public function edit(User $user)
    {
        $roles = Role::orderBy('name')->get();

        $floors = Floor::where('is_active', true)
            ->orderBy('id')
            ->get();

        $divisions = collect();

        if ($user->floor_id) {
            $divisions = Division::where('floor_id', $user->floor_id)
                ->where('is_active', true)
                ->orderBy('name')
                ->get();
        }

        return view('users.edit', compact(
            'user',
            'roles',
            'floors',
            'divisions'
        ));
    }


    public function update(Request $request, User $user)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:users,email,' . $user->id],
            'username' => ['required', 'string', 'unique:users,username,' . $user->id],
            'role_id' => ['required', 'exists:roles,id'],
            'admin_scope' => ['nullable','string','max:100'],
            'floor_id' => ['nullable', 'exists:floors,id'],
            'division_id' => ['nullable', 'exists:divisions,id'],
            'phone' => ['nullable', 'string'],
            'specialty' => ['nullable', 'string'],
            'is_active' => ['nullable', 'boolean'],
            'password' => ['nullable', 'string', 'min:6'],
        ]);

        $administratorRole = Role::where(
            'code',
            Role::ADMINISTRATOR
        )->first();

        if (
            ! $administratorRole
            ||
            (int) $data['role_id'] !== (int) $administratorRole->id
        ) {
            $data['admin_scope'] = null;
        }


        if (! empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }

        $data['is_active'] = $request->boolean('is_active');

        $user->update($data);

        return redirect()->route('users.index')->with('success', 'User updated.');

        if (! empty($data['division_id'])) {

        $validDivision = Division::where('id', $data['division_id'])
            ->where('floor_id', $data['floor_id'])
            ->exists();

        if (! $validDivision) {
            return back()
                ->withErrors([
                    'division_id' => 'The selected division does not belong to the selected floor.'
                ])
                ->withInput();
        }
    }
    }

    public function destroy(User $user)
    {
        $user->update(['is_active' => false]);
        return back()->with('success', 'User deactivated.');
    }
}
