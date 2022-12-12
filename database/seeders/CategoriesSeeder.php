<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

use App\Models\Categories;
use Illuminate\Support\Str;

class CategoriesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $categories = [
            [
                'uuid' => (string) Str::uuid(),
                'name' => 'Beauty',
                'slug' => 'beauty',
            ],
            [
                'uuid' => (string) Str::uuid(),
                'name' => 'Food',
                'slug' => 'food',
            ],
            [
                'uuid' => (string) Str::uuid(),
                'name' => 'Car',
                'slug' => 'car',
            ],
            [
                'uuid' => (string) Str::uuid(),
                'name' => 'Hotel',
                'slug' => 'hotel',
            ],
            [
                'uuid' => (string) Str::uuid(),
                'name' => 'Hospital',
                'slug' => 'hospital',
            ],
            [
                'uuid' => (string) Str::uuid(),
                'name' => 'Gas',
                'slug' => 'gas',
            ],
            [
                'uuid' => (string) Str::uuid(),
                'name' => 'ATM',
                'slug' => 'atm',
            ],
        ];
        foreach($categories as $category)
        {
            Categories::create($category);
        }
    }
}
