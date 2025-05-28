<?php

namespace Database\Seeders;

use App\Models\Client;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ClientSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get the first user (John Doe)
        $user = User::first();

        // Create first client
        Client::create([
            'user_id' => $user->id,
            'name' => 'Tech Solutions Inc.',
            'email' => 'contact@techsolutions.com',
            'contact_person' => 'Sarah Johnson',
            'phone' => '+1 (555) 123-4567',
            'address' => '123 Business Ave, Tech City, TC 12345',
            'hourly_rate' => 75.00,
            'status' => 'active',
        ]);

        // Create second client
        Client::create([
            'user_id' => $user->id,
            'name' => 'Digital Marketing Pro',
            'email' => 'hello@digitalmarketingpro.com',
            'contact_person' => 'Mike Roberts',
            'phone' => '+1 (555) 987-6543',
            'address' => '456 Marketing Blvd, Creative City, CC 67890',
            'hourly_rate' => 85.00,
            'status' => 'active',
        ]);

        // Create additional clients using factory
        Client::factory(3)->create([
            'user_id' => $user->id,
        ]);
    }
}
