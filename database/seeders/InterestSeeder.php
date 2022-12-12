<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Interest;
use Illuminate\Support\Str;

class InterestSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $interests = [
            [
                'uuid' => (string) Str::uuid(),
                'name' => 'Music',
                'slug' => 'music',
            ],
            [
                'uuid' => (string) Str::uuid(),
                'name' => 'Chess Contests',
                'slug' => 'chess-contests',
            ],
            [
                'uuid' => (string) Str::uuid(),
                'name' => 'NBA',
                'slug' => 'nba',
            ],
            [
                'uuid' => (string) Str::uuid(),
                'name' => 'Science Fiction',
                'slug' => 'science-fiction',
            ],
            [
                'uuid' => (string) Str::uuid(),
                'name' => 'Cooking',
                'slug' => 'cooking',
            ],
            [
                'uuid' => (string) Str::uuid(),
                'name' => 'Tennis',
                'slug' => 'tennis',
            ],
            [
                'uuid' => (string) Str::uuid(),
                'name' => 'Football',
                'slug' => 'football',
            ],
            [
                'uuid' => (string) Str::uuid(),
                'name' => 'Museums',
                'slug' => 'museums',
            ],
        ];
        foreach($interests as $interest)
        {
            Interest::create($interest);
        }
    }
}
