<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Str;

class CreateUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $users = [
                [
                    'uuid' => (string) Str::uuid(),
                    'name' => 'User',
                    'email' => 'user@test.com',
                    'phone' => 123456789,
                    'password' => bcrypt('password'),
                    'role' => 0,
                    'status' => 1
                ],
                [
                    'uuid' => (string) Str::uuid(),
                    'name' => 'Business',
                    'email' => 'business@test.com',
                    'phone' => 9901234567,
                    'password' => bcrypt('password'),
                    'role' => 1,
                    'status' => 1
                ],
                [
                    'uuid' => (string) Str::uuid(),
                    'name' => 'Admin',
                    'email' => 'admin@test.com',
                    'phone' => 5981234567,
                    'password' => bcrypt('password'),
                    'role' => 2,
                    'status' => 1
                ]
        ];
        foreach($users as $user)
        {
            User::create($user);
        }
    }
}
