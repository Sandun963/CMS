<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['name' => 'Hardware', 'code' => 'hardware'],
            ['name' => 'Network', 'code' => 'network'],
            ['name' => 'Software', 'code' => 'software'],
            ['name' => 'Printer / Scanner', 'code' => 'printer'],
            ['name' => 'Email / Internet', 'code' => 'email'],
            ['name' => 'Other', 'code' => 'other'],
        ];

        foreach ($categories as $cat) {
            Category::updateOrCreate(['code' => $cat['code']], $cat);
        }
    }
}
