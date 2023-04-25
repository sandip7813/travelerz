<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

use Database\Seeders\CreateUserSeeder;
use Database\Seeders\InterestSeeder;
use Database\Seeders\CategoriesSeeder;
use Database\Seeders\CountriesSeeder;
use Database\Seeders\StatesTableSeeder;
use Database\Seeders\Membership;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        // \App\Models\User::factory(10)->create();

        // \App\Models\User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);

        $this->call([
            CreateUserSeeder::class,
            InterestSeeder::class,
            CategoriesSeeder::class,
            CountriesSeeder::class,
            StatesTableSeeder::class,
            Membership::class,
        ]);
    }
}
