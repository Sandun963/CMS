<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Get fixed roles
        $superAdmin = Role::where('code', 'sup_admin')->firstOrFail();
        $itHead = Role::where('code', 'it_head')->firstOrFail();
        $assignOfficer = Role::where('code', 'assign_officer')->firstOrFail();
        $techOfficer = Role::where('code', 'technical_officer')->firstOrFail();
        $ministryUser = Role::where('code', 'ministry_user')->firstOrFail();

        // Existing test departments
        $accounts = Department::where('code', 'ABC')->first();
        $hr = Department::where('code', 'DEF')->first();

        // Demo password
        $password = Hash::make('1234');

        /*
        |--------------------------------------------------------------------------
        | Super Admin
        |--------------------------------------------------------------------------
        */
        User::updateOrCreate(
            ['email' => 'superadmin@example.com'],
            [
                'name' => 'Super Admin',
                'username' => 'superadmin',
                'password' => $password,
                'role_id' => $superAdmin->id,
                'is_active' => true,
            ]
        );

        /*
        |--------------------------------------------------------------------------
        | IT Head
        |--------------------------------------------------------------------------
        */
        User::updateOrCreate(
            ['email' => 'ithead@example.com'],
            [
                'name' => 'IT Head Admin',
                'username' => 'ithead',
                'password' => $password,
                'role_id' => $itHead->id,
                'is_active' => true,
            ]
        );

        /*
        |--------------------------------------------------------------------------
        | Assign Officer
        |--------------------------------------------------------------------------
        */
        User::updateOrCreate(
            ['email' => 'assignofficer@example.com'],
            [
                'name' => 'Assign Officer A',
                'username' => 'assignofficer',
                'password' => $password,
                'role_id' => $assignOfficer->id,
                'is_active' => true,
            ]
        );

        /*
        |--------------------------------------------------------------------------
        | Technical Officer A
        |--------------------------------------------------------------------------
        */
        User::updateOrCreate(
            ['email' => 'techofficer@example.com'],
            [
                'name' => 'Technical Officer A',
                'username' => 'techofficer',
                'password' => $password,
                'role_id' => $techOfficer->id,
                'specialty' => 'Hardware & Network',
                'is_active' => true,
            ]
        );

        /*
        |--------------------------------------------------------------------------
        | Technical Officer B
        |--------------------------------------------------------------------------
        */
        User::updateOrCreate(
            ['email' => 'techofficerb@example.com'],
            [
                'name' => 'Technical Officer B',
                'username' => 'techofficerb',
                'password' => $password,
                'role_id' => $techOfficer->id,
                'specialty' => 'Software & Printers',
                'is_active' => true,
            ]
        );

        /*
        |--------------------------------------------------------------------------
        | Ministry User - ABC
        |--------------------------------------------------------------------------
        */
        User::updateOrCreate(
            ['email' => 'deptuser@example.com'],
            [
                'name' => 'ABC Department User',
                'username' => 'deptuser',
                'password' => $password,
                'role_id' => $ministryUser->id,
                'department_id' => $accounts?->id,
                'is_active' => true,
            ]
        );

        /*
        |--------------------------------------------------------------------------
        | Ministry User - HR
        |--------------------------------------------------------------------------
        */
        User::updateOrCreate(
            ['email' => 'hruser@example.com'],
            [
                'name' => 'HR Department User',
                'username' => 'hruser',
                'password' => $password,
                'role_id' => $ministryUser->id,
                'department_id' => $hr?->id,
                'is_active' => true,
            ]
        );
    }
}