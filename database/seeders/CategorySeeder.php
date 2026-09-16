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
                'name' => 'Printer Not Working',
                'code' => 'PRINTER',
            ],

            [
                'name' => 'Computer Not Working',
                'code' => 'COMPUTER',
            ],

            [
                'name' => 'Monitor Not Working',
                'code' => 'MONITOR',
            ],

            [
                'name' => 'Keyboard / Mouse Issue',
                'code' => 'KEYBOARD_MOUSE',
            ],

            [
                'name' => 'Network Connection Issue',
                'code' => 'NETWORK',
            ],

            [
                'name' => 'Internet Not Working',
                'code' => 'INTERNET',
            ],

            [
                'name' => 'Email Issue',
                'code' => 'EMAIL',
            ],

            [
                'name' => 'Scanner Issue',
                'code' => 'SCANNER',
            ],

            [
                'name' => 'Software Issue',
                'code' => 'SOFTWARE',
            ],

            [
                'name' => 'Operating System Issue',
                'code' => 'OS',
            ],

            [
                'name' => 'Other IT Issue',
                'code' => 'OTHER_IT',
            ],

        ];


        foreach ($categories as $category) {

            Category::updateOrCreate(
                [
                    'code' => $category['code'],
                ],
                [
                    'name' => $category['name'],
                    'description' => null,
                    'is_active' => true,
                ]
            );
        }
    }
}