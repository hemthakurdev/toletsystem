<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class FrontendUserRoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create frontend_user role
        $frontendUserRole = Role::firstOrCreate(['name' => 'frontend_user']);

        // Create permissions for frontend users
        $permissions = [
            'browse_properties',
            'view_property_details',
            'contact_property_owner',
            'save_favorites',
            'create_inquiries',
            'manage_profile',
            'view_dashboard',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Assign permissions to frontend_user role
        $frontendUserRole->syncPermissions($permissions);
    }
}