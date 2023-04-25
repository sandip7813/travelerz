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
                'name' => 'Anually',
                'amount' => 100,
                'duration' => 365
            ],
            [
                'uuid' => (string) Str::uuid(),
                'name' => 'Monthly',
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
