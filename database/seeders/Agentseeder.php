<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class Agentseeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
         DB::table('users')->insert([
            'name' =>'Agent',
            'email' =>'agent@gmail.com',
            'password' =>Hash::make('agent@gmail.com'),
            'role'=>'agent',
        ]);
    }
}
