<?php

namespace Modules\User\Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Create permissions
        $permissions = [
            // User permissions
            'users.view',
            'users.create',
            'users.edit',
            'users.delete',
            
            // Product permissions
            'products.view',
            'products.create',
            'products.edit',
            'products.delete',
            
            // Inventory permissions
            'inventory.view',
            'inventory.manage',
            'inventory.adjust',
            
            // Sales permissions
            'sales.view',
            'sales.create',
            'sales.refund',
            'sales.void',
            
            // Customer permissions
            'customers.view',
            'customers.create',
            'customers.edit',
            'customers.delete',
            
            // Reports permissions
            'reports.view',
            'reports.export',
            
            // Settings permissions
            'settings.view',
            'settings.manage',
            
            // POS permissions
            'pos.access',
            'pos.open-register',
            'pos.close-register',
            
            // Discount permissions
            'discounts.view',
            'discounts.create',
            'discounts.edit',
            'discounts.delete',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Create roles and assign permissions
        
        // Super Admin - All permissions
        $superAdmin = Role::firstOrCreate(['name' => 'super-admin']);
        $superAdmin->givePermissionTo(Permission::all());

        // Admin - All except system settings
        $admin = Role::firstOrCreate(['name' => 'admin']);
        $admin->givePermissionTo([
            'users.view', 'users.create', 'users.edit',
            'products.view', 'products.create', 'products.edit', 'products.delete',
            'inventory.view', 'inventory.manage', 'inventory.adjust',
            'sales.view', 'sales.create', 'sales.refund', 'sales.void',
            'customers.view', 'customers.create', 'customers.edit', 'customers.delete',
            'reports.view', 'reports.export',
            'pos.access', 'pos.open-register', 'pos.close-register',
            'discounts.view', 'discounts.create', 'discounts.edit', 'discounts.delete',
        ]);

        // Manager - Sales, inventory, reports, customers
        $manager = Role::firstOrCreate(['name' => 'manager']);
        $manager->givePermissionTo([
            'products.view',
            'inventory.view', 'inventory.manage',
            'sales.view', 'sales.create', 'sales.refund',
            'customers.view', 'customers.create', 'customers.edit',
            'reports.view', 'reports.export',
            'pos.access', 'pos.open-register', 'pos.close-register',
            'discounts.view',
        ]);

        // Cashier - POS access and basic operations
        $cashier = Role::firstOrCreate(['name' => 'cashier']);
        $cashier->givePermissionTo([
            'products.view',
            'sales.view', 'sales.create',
            'customers.view',
            'pos.access',
            'discounts.view',
        ]);

        $this->command->info('Roles and permissions created successfully!');
    }
}
