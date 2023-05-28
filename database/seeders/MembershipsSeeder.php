<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Membership;
use Illuminate\Support\Str;

class MembershipsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $memberships = [
            [
                'uuid' => (string) Str::uuid(),
                'name' => 'Platinum',
                'parent_id' => NULL,
                'amount' => NULL,
                'duration' => NULL
            ],
            [
                'uuid' => (string) Str::uuid(),
                'name' => 'Annually',
                'parent_id' => 1,
                'amount' => 200,
                'duration' => 365
            ],
            [
                'uuid' => (string) Str::uuid(),
                'name' => 'Monthly',
                'parent_id' => 1,
                'amount' => 50,
                'duration' => 30
            ],
            [
                'uuid' => (string) Str::uuid(),
                'name' => 'Silver',
                'amount' => NULL,
                'duration' => NULL
            ],
            [
                'uuid' => (string) Str::uuid(),
                'name' => 'Annually',
                'parent_id' => 4,
                'amount' => 150,
                'duration' => 365
            ],
            [
                'uuid' => (string) Str::uuid(),
                'name' => 'Monthly',
                'parent_id' => 4,
                'amount' => 25,
                'duration' => 30
            ],
            [
                'uuid' => (string) Str::uuid(),
                'name' => 'Gold',
                'amount' => NULL,
                'duration' => NULL
            ],
            [
                'uuid' => (string) Str::uuid(),
                'name' => 'Annually',
                'parent_id' => 7,
                'amount' => 100,
                'duration' => 365
            ],
            [
                'uuid' => (string) Str::uuid(),
                'name' => 'Monthly',
                'parent_id' => 7,
                'amount' => 10,
                'duration' => 30
            ],
        ];
        foreach($memberships as $membership)
        {
            Membership::create($membership);
        }
    }
}
