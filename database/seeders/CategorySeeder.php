<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [

            [
                'name' => 'Hardware & Repairing',
                'code' => 'HARDWARE',
            ],

            [
                'name' => 'Network & Maintenance',
                'code' => 'NETWORK',
            ],

            [
                'name' => 'Systems & Web',
                'code' => 'SYSTEMS',
            ],

            [
                'name' => 'Communications',
                'code' => 'COMM',
            ],

            [
                'name' => 'ICT Equipments',
                'code' => 'ICT_EQ',
            ],

        ];

        foreach ($categories as $category) {

            Category::updateOrCreate(

                [
                    'code' => $category['code']
                ],

                [
                    'name' => $category['name'],
                    'is_active' => true,
                ]
            );
        }
    }
}