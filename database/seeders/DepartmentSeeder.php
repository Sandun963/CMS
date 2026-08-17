<?php

namespace Database\Seeders;

use App\Models\Department;
use Illuminate\Database\Seeder;

class DepartmentSeeder extends Seeder
{
    public function run(): void
    {
        $departments = [
            ['name' => 'Accounts', 'code' => 'ABC', 'floor' => '6th Floor'],
            ['name' => 'HR', 'code' => 'DEF', 'floor' => '3rd Floor'],
            ['name' => 'Admin', 'code' => 'GHI', 'floor' => '2nd Floor'],
            ['name' => 'Finance', 'code' => 'JKL', 'floor' => '5th Floor'],
            ['name' => 'Registry', 'code' => 'MNO', 'floor' => '4th Floor'],
            ['name' => 'Planning', 'code' => 'PQR', 'floor' => '7th Floor'],
            ['name' => 'Legal', 'code' => 'XYZ', 'floor' => '1st Floor'],
        ];

        foreach ($departments as $dept) {
            Department::updateOrCreate(['code' => $dept['code']], $dept);
        }
    }
}
