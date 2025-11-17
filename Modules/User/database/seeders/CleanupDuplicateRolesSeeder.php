<?php

namespace Modules\User\Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class CleanupDuplicateRolesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Cleaning up duplicate roles...');

        // Remove Shield's super_admin if it exists
        $shieldSuperAdmin = Role::where('name', 'super_admin')->first();
        
        if ($shieldSuperAdmin) {
            $this->command->warn('Found Shield super_admin role (with underscore)');
            
            // Check if any users are assigned
            if ($shieldSuperAdmin->users->count() > 0) {
                $correctRole = Role::where('name', 'super-admin')->first();
                
                if ($correctRole) {
                    $this->command->info('Reassigning users to super-admin (with hyphen)...');
                    
                    foreach ($shieldSuperAdmin->users as $user) {
                        $user->removeRole('super_admin');
                        $user->assignRole('super-admin');
                        $this->command->info("  ✓ Reassigned: {$user->email}");
                    }
                }
            }
            
            $shieldSuperAdmin->delete();
            $this->command->info('✓ Deleted super_admin role');
        } else {
            $this->command->info('✓ No duplicate super_admin role found');
        }

        // Display current roles
        $this->command->info("\nCurrent roles:");
        $roles = Role::withCount('permissions')->get();
        
        foreach ($roles as $role) {
            $this->command->info("  • {$role->name}: {$role->permissions_count} permissions");
        }
    }
}
