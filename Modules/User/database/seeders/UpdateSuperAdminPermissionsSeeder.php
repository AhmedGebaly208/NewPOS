<?php

namespace Modules\User\Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class UpdateSuperAdminPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Get super-admin role
        $superAdmin = Role::where('name', 'super-admin')->first();

        if ($superAdmin) {
            // Give super-admin ALL permissions (including Shield-generated ones)
            $allPermissions = Permission::all();
            $superAdmin->syncPermissions($allPermissions);

            $this->command->info('Super Admin permissions updated!');
            $this->command->info('Total permissions: ' . $allPermissions->count());
        } else {
            $this->command->error('Super Admin role not found!');
        }
    }
}
