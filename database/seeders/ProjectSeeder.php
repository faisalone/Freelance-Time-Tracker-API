<?php

namespace Database\Seeders;

use App\Models\Client;
use App\Models\Project;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ProjectSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $clients = Client::all();

        if ($clients->count() >= 2) {
            // Project for first client
            Project::create([
                'client_id' => $clients->first()->id,
                'title' => 'E-commerce Website Development',
                'description' => 'Build a modern e-commerce platform with React frontend and Laravel API backend. Includes payment integration, inventory management, and admin dashboard.',
                'status' => 'active',
                'deadline' => now()->addMonths(3),
            ]);

            // Project for second client
            Project::create([
                'client_id' => $clients->skip(1)->first()->id,
                'title' => 'SEO Optimization Campaign',
                'description' => 'Comprehensive SEO audit and optimization for improved search engine rankings. Includes keyword research, content optimization, and technical SEO improvements.',
                'status' => 'active',
                'deadline' => now()->addMonths(2),
            ]);

            // Additional completed project
            Project::create([
                'client_id' => $clients->first()->id,
                'title' => 'Mobile App Prototype',
                'description' => 'Design and develop a mobile app prototype for iOS and Android platforms.',
                'status' => 'completed',
                'deadline' => now()->subWeeks(2),
            ]);
        }

        // Create additional projects using factory
        Project::factory(5)->create();
    }
}
