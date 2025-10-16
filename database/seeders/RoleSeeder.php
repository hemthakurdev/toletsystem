<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create permissions
        $permissions = [
            // Organization permissions
            'view_organization',
            'edit_organization',
            'delete_organization',
            
            // User permissions
            'view_users',
            'create_users',
            'edit_users',
            'delete_users',
            
            // Property permissions
            'view_properties',
            'create_properties',
            'edit_properties',
            'delete_properties',
            'publish_properties',
            
            // Tenant permissions
            'view_tenants',
            'create_tenants',
            'edit_tenants',
            'delete_tenants',
            
            // Invoice permissions
            'view_invoices',
            'create_invoices',
            'edit_invoices',
            'delete_invoices',
            'send_invoices',
            
            // Payment permissions
            'view_payments',
            'create_payments',
            'edit_payments',
            'delete_payments',
            
            // Lead permissions
            'view_leads',
            'edit_leads',
            'delete_leads',
            'contact_leads',
            
            // Expense permissions
            'view_expenses',
            'create_expenses',
            'edit_expenses',
            'delete_expenses',
            'approve_expenses',
            
            // Document permissions
            'view_documents',
            'upload_documents',
            'delete_documents',
            
            // Analytics permissions
            'view_analytics',
            'export_reports',
            
            // Subscription permissions
            'view_subscription',
            'manage_subscription',
            
            // Admin permissions
            'view_admin',
            'manage_organizations',
            'manage_plans',
            'view_all_analytics',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Create roles
        $superAdminRole = Role::firstOrCreate(['name' => 'super_admin']);
        $superAdminRole->givePermissionTo(Permission::all());

        $adminRole = Role::firstOrCreate(['name' => 'admin']);
        $adminRole->givePermissionTo([
            'view_organization',
            'edit_organization',
            'view_users',
            'create_users',
            'edit_users',
            'delete_users',
            'view_properties',
            'create_properties',
            'edit_properties',
            'delete_properties',
            'publish_properties',
            'view_tenants',
            'create_tenants',
            'edit_tenants',
            'delete_tenants',
            'view_invoices',
            'create_invoices',
            'edit_invoices',
            'delete_invoices',
            'send_invoices',
            'view_payments',
            'create_payments',
            'edit_payments',
            'delete_payments',
            'view_leads',
            'edit_leads',
            'delete_leads',
            'contact_leads',
            'view_expenses',
            'create_expenses',
            'edit_expenses',
            'delete_expenses',
            'approve_expenses',
            'view_documents',
            'upload_documents',
            'delete_documents',
            'view_analytics',
            'export_reports',
            'view_subscription',
            'manage_subscription',
        ]);

        $staffRole = Role::firstOrCreate(['name' => 'staff']);
        $staffRole->givePermissionTo([
            'view_properties',
            'create_properties',
            'edit_properties',
            'view_tenants',
            'create_tenants',
            'edit_tenants',
            'view_invoices',
            'create_invoices',
            'edit_invoices',
            'view_payments',
            'view_leads',
            'edit_leads',
            'contact_leads',
            'view_expenses',
            'create_expenses',
            'view_documents',
            'upload_documents',
            'view_analytics',
        ]);

        $supportRole = Role::firstOrCreate(['name' => 'support']);
        $supportRole->givePermissionTo([
            'view_organization',
            'view_users',
            'view_properties',
            'view_tenants',
            'view_invoices',
            'view_payments',
            'view_leads',
            'edit_leads',
            'contact_leads',
            'view_expenses',
            'view_documents',
            'view_analytics',
        ]);
    }
}