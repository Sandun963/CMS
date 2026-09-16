<?php

namespace App\Http\Controllers;

use App\Models\Division;
use App\Models\Floor;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    /**
     * Display all users.
     */
    public function index(Request $request)
    {
        $query = User::with([
            'role',
            'floor',
            'division',
        ]);

        if ($request->filled('role')) {
            $query->whereHas(
                'role',
                fn ($roleQuery) =>
                    $roleQuery->where(
                        'code',
                        $request->role
                    )
            );
        }

        $users = $query
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        $roles = Role::orderBy('name')->get();

        return view(
            'users.index',
            compact(
                'users',
                'roles'
            )
        );
    }


    /**
     * Show create user form.
     */
    public function create()
    {
        $roles = Role::orderBy('name')->get();

        $floors = Floor::where(
            'is_active',
            true
        )
            ->orderBy('id')
            ->get();

        return view(
            'users.create',
            compact(
                'roles',
                'floors'
            )
        );
    }


    /**
     * Create a new user.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
            ],

            'email' => [
                'required',
                'email',
                'unique:users,email',
            ],

            'username' => [
                'required',
                'string',
                'unique:users,username',
            ],

            'password' => [
                'required',
                'string',
                'min:6',
            ],

            'role_id' => [
                'required',
                'exists:roles,id',
            ],

            'admin_scope' => [
                'nullable',
                'string',
                'max:100',
            ],

            'floor_id' => [
                'nullable',
                'exists:floors,id',
            ],

            'division_id' => [
                'nullable',
                'exists:divisions,id',
            ],

            'phone' => [
                'nullable',
                'string',
            ],

            'specialty' => [
                'nullable',
                'string',
            ],
        ]);


        /*
        |--------------------------------------------------------------------------
        | Administrator Scope
        |--------------------------------------------------------------------------
        |
        | admin_scope should only exist when the selected role is Administrator.
        |
        */

        $administratorRole = Role::where(
            'code',
            Role::ADMINISTRATOR
        )->first();

        if (
            ! $administratorRole
            ||
            (int) $data['role_id']
                !== (int) $administratorRole->id
        ) {
            $data['admin_scope'] = null;
        }


        /*
        |--------------------------------------------------------------------------
        | Validate Division belongs to selected Floor
        |--------------------------------------------------------------------------
        */

        if (! empty($data['division_id'])) {

            if (empty($data['floor_id'])) {
                return back()
                    ->withErrors([
                        'floor_id' =>
                            'Please select a floor before selecting a division.',
                    ])
                    ->withInput();
            }

            $validDivision = Division::where(
                'id',
                $data['division_id']
            )
                ->where(
                    'floor_id',
                    $data['floor_id']
                )
                ->exists();

            if (! $validDivision) {
                return back()
                    ->withErrors([
                        'division_id' =>
                            'The selected division does not belong to the selected floor.',
                    ])
                    ->withInput();
            }
        }


        /*
        |--------------------------------------------------------------------------
        | Password
        |--------------------------------------------------------------------------
        */

        $data['password'] = Hash::make(
            $data['password']
        );


        /*
        |--------------------------------------------------------------------------
        | New users are active by default
        |--------------------------------------------------------------------------
        */

        $data['is_active'] = true;


        /*
        |--------------------------------------------------------------------------
        | Create User
        |--------------------------------------------------------------------------
        */

        User::create($data);


        return redirect()
            ->route('users.index')
            ->with(
                'success',
                'User created.'
            );
    }


    /**
     * Show edit user form.
     */
    public function edit(User $user)
    {
        $roles = Role::orderBy('name')->get();

        $floors = Floor::where(
            'is_active',
            true
        )
            ->orderBy('id')
            ->get();

        /*
        |--------------------------------------------------------------------------
        | Load only divisions belonging to user's selected floor
        |--------------------------------------------------------------------------
        */

        $divisions = collect();

        if ($user->floor_id) {

            $divisions = Division::where(
                'floor_id',
                $user->floor_id
            )
                ->where(
                    'is_active',
                    true
                )
                ->orderBy('name')
                ->get();
        }


        return view(
            'users.edit',
            compact(
                'user',
                'roles',
                'floors',
                'divisions'
            )
        );
    }


    /**
     * Update existing user.
     */
    public function update(
        Request $request,
        User $user
    ) {
        $data = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
            ],

            'email' => [
                'required',
                'email',
                'unique:users,email,' . $user->id,
            ],

            'username' => [
                'required',
                'string',
                'unique:users,username,' . $user->id,
            ],

            'role_id' => [
                'required',
                'exists:roles,id',
            ],

            'admin_scope' => [
                'nullable',
                'string',
                'max:100',
            ],

            'floor_id' => [
                'nullable',
                'exists:floors,id',
            ],

            'division_id' => [
                'nullable',
                'exists:divisions,id',
            ],

            'phone' => [
                'nullable',
                'string',
            ],

            'specialty' => [
                'nullable',
                'string',
            ],

            'is_active' => [
                'nullable',
                'boolean',
            ],

            'password' => [
                'nullable',
                'string',
                'min:6',
            ],
        ]);


        /*
        |--------------------------------------------------------------------------
        | Administrator Scope
        |--------------------------------------------------------------------------
        */

        $administratorRole = Role::where(
            'code',
            Role::ADMINISTRATOR
        )->first();

        if (
            ! $administratorRole
            ||
            (int) $data['role_id']
                !== (int) $administratorRole->id
        ) {
            $data['admin_scope'] = null;
        }


        /*
        |--------------------------------------------------------------------------
        | Validate Division belongs to selected Floor
        |--------------------------------------------------------------------------
        |
        | This MUST happen before $user->update().
        |
        */

        if (! empty($data['division_id'])) {

            if (empty($data['floor_id'])) {
                return back()
                    ->withErrors([
                        'floor_id' =>
                            'Please select a floor before selecting a division.',
                    ])
                    ->withInput();
            }

            $validDivision = Division::where(
                'id',
                $data['division_id']
            )
                ->where(
                    'floor_id',
                    $data['floor_id']
                )
                ->exists();

            if (! $validDivision) {
                return back()
                    ->withErrors([
                        'division_id' =>
                            'The selected division does not belong to the selected floor.',
                    ])
                    ->withInput();
            }
        }


        /*
        |--------------------------------------------------------------------------
        | Password
        |--------------------------------------------------------------------------
        |
        | If password is blank, keep the existing password.
        |
        */

        if (! empty($data['password'])) {

            $data['password'] = Hash::make(
                $data['password']
            );

        } else {

            unset($data['password']);
        }


        /*
        |--------------------------------------------------------------------------
        | Active Status
        |--------------------------------------------------------------------------
        */

        $data['is_active'] =
            $request->boolean('is_active');


        /*
        |--------------------------------------------------------------------------
        | Update User
        |--------------------------------------------------------------------------
        */

        $user->update($data);


        return redirect()
            ->route('users.index')
            ->with(
                'success',
                'User updated.'
            );
    }


    /**
     * Deactivate user.
     */
    public function destroy(User $user)
    {
        $user->update([
            'is_active' => false,
        ]);

        return back()->with(
            'success',
            'User deactivated.'
        );
    }
}