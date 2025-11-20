<?php

namespace Modules\User\Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class SuperAdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create Super Admin user
        $superAdmin = User::firstOrCreate(
            ['email' => 'admin@pos.com'],
            [
                'name' => 'Super Admin',
                'password' => Hash::make('password'),
                'is_active' => true,
                'phone' => '+1234567890',
            ]
        );

        // Assign super-admin role
        $superAdmin->assignRole('super-admin');

        $this->command->info('Super Admin user created successfully!');
        $this->command->info('Email: admin@pos.com');
        $this->command->info('Password: password');
    }
}
