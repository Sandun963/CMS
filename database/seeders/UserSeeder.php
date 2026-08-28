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
        $itHead = Role::where('code', 'it_head')->first();
        $assignOfficer = Role::where('code', 'assign_officer')->first();
        $techOfficer = Role::where('code', 'technical_officer')->first();
        $ministryUser = Role::where('code', 'ministry_user')->first();

        $accounts = Department::where('code', 'ABC')->first();
        $hr = Department::where('code', 'DEF')->first();

        // All demo accounts share the same password for easy testing
        $password = Hash::make('1234');

        User::updateOrCreate(['username' => 'ithead'], [
            'name' => 'IT Head Admin',
            'email' => 'ithead@example.com',
            'username' => 'ithead',
            'password' => $password,
            'role_id' => $itHead->id,
            'is_active' => true,
        ]);

        User::updateOrCreate(['username' => 'assignofficer'], [
            'name' => 'Assign Officer A',
            'email' => 'assignofficer@example.com',
            'username' => 'assignofficer',
            'password' => $password,
            'role_id' => $assignOfficer->id,
            'is_active' => true,
        ]);

        User::updateOrCreate(['username' => 'techofficer'], [
            'name' => 'Technical Officer A',
            'email' => 'techofficer@example.com',
            'username' => 'techofficer',
            'password' => $password,
            'role_id' => $techOfficer->id,
            'specialty' => 'Hardware & Network',
            'is_active' => true,
        ]);

        User::updateOrCreate(['username' => 'techofficerb'], [
            'name' => 'Technical Officer B',
            'email' => 'techofficerb@example.com',
            'username' => 'techofficerb',
            'password' => $password,
            'role_id' => $techOfficer->id,
            'specialty' => 'Software & Printers',
            'is_active' => true,
        ]);

        User::updateOrCreate(['username' => 'deptuser'], [
            'name' => 'ABC Department User',
            'email' => 'deptuser@example.com',
            'username' => 'deptuser',
            'password' => $password,
            'role_id' => $ministryUser->id,
            'department_id' => $accounts->id,
            'is_active' => true,
        ]);

        User::updateOrCreate(['username' => 'hruser'], [
            'name' => 'HR Department User',
            'email' => 'hruser@example.com',
            'username' => 'hruser',
            'password' => $password,
            'role_id' => $ministryUser->id,
            'department_id' => $hr->id,
            'is_active' => true,
        ]);
    }
}
