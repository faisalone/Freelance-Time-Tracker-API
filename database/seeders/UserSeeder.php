<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create demo freelancer user
        User::create([
            'name' => 'John Doe',
            'email' => 'john@freelancer-tracker.com',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ]);

        // Create admin user
        User::create([
            'name' => 'Admin User',
            'email' => 'admin@freelancer-tracker.com',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ]);
    }
}
