<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create permissions
        $permissions = [
            'view-folders',
            'create-folders',
            'edit-folders',
            'delete-folders',
            'view-documents',
            'download-documents',
            'upload-documents',
            'edit-documents',
            'delete-documents',
            'manage-permissions',
            'view-logs'
        ];

        foreach ($permissions as $permission) {
            Permission::create(['name' => $permission]);
        }

        // Create roles
        $superAdmin = Role::create(['name' => 'super admin']);
        // $admin = Role::create(['name' => 'Admin']);
        $direktur = Role::create(['name' => 'direktur']);
        $kabag = Role::create(['name' => 'kabag']);
        $kabid = Role::create(['name' => 'kabid']);
        $staff = Role::create(['name' => 'staff']);

        // Assign permissions to roles
        $superAdmin->givePermissionTo(Permission::all());
        // $admin->givePermissionTo([
        //     'view-folders',
        //     'create-folders',
        //     'edit-folders',
        //     'view-documents',
        //     'upload-documents',
        //     'edit-documents',
        //     'manage-permissions'
        // ]);
        // $direktur->givePermissionTo([
        //     'view-folders',
        //     'view-documents',
        //     'upload-documents',
        //     'download-documents',
        // ]);
        // $kabag->givePermissionTo([
        //     'view-folders',
        //     'view-documents',
        //     'upload-documents'
        // ]);
        // $kabid->givePermissionTo([
        //     'view-folders',
        //     'view-documents'
        // ]);

        // Create default admin user
        $adminUser = User::create([
            'name' => 'System Administrator',
            'username' => 'admin',
            'email' => 'admin@hospital.com',
            'password' => bcrypt('password'),
            'employee_id' => 'ADM001',
            'is_active' => true,
        ]);
        $adminUser->assignRole('Super Admin');
    }
}
