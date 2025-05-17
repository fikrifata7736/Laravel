<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{

    public function run(): void
    {

        User::create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => Hash::make('password'),
            'role' => User::ROLE_ADMIN,
            'status' => true,
        ]);

        User::create([
            'name' => 'Manager User',
            'email' => 'manager@example.com',
            'password' => Hash::make('password'),
            'role' => User::ROLE_MANAGER,
            'status' => true,
        ]);

        User::create([
            'name' => 'Staff User',
            'email' => 'staff@example.com',
            'password' => Hash::make('password'),
            'role' => User::ROLE_STAFF,
            'status' => true,
        ]);


        User::create([
            'name' => 'Inactive Staff',
            'email' => 'inactive@example.com',
            'password' => Hash::make('password'),
            'role' => User::ROLE_STAFF,
            'status' => false,
        ]);
    }
}
