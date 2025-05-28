<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            UserSeeder::class,
            ClientSeeder::class,
            ProjectSeeder::class,
            TimeLogSeeder::class,
        ]);

        $this->command->info('Database seeded successfully!');
        $this->command->info('Demo users created:');
        $this->command->info('- Admin: admin@freelancer-tracker.com (password: password)');
        $this->command->info('- Freelancer: john@freelancer-tracker.com (password: password)');
    }
}
