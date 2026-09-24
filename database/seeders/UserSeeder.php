<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('users')->updateOrInsert(
            ['email' => 'user@gmail.com'],
            [
                'name' => 'testuser',
                'password' => Hash::make('user@gmail.com'),
                'role' => 'user',
                'updated_at' => now(),
            ]
        );
    }
}
