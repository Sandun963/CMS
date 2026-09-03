<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            ['name' => 'Super Admin', 'code' => 'sup_admin'],
            ['name' => 'Administrator', 'code' => 'administrator'],
            ['name' => 'Assign Officer', 'code' => 'assign_officer'],
            ['name' => 'Technical Officer', 'code' => 'technical_officer'],
            ['name' => 'Ministry User', 'code' => 'ministry_user'],
        ];

        foreach ($roles as $role) {
            Role::updateOrCreate(['code' => $role['code']], $role);
        }
    }
}
