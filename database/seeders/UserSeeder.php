<?php

namespace Database\Seeders;

use App\Modules\Core\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        resolve(PermissionRegistrar::class)->forgetCachedPermissions();

        $permissions = [
            'users.view',
            'users.create',
            'users.edit',
            'users.delete',

            'plants.view',
            'plants.create',
            'plants.edit',
            'plants.delete',

            'chemicals.view',
            'chemicals.create',
            'chemicals.edit',
            'chemicals.delete',

            'equipment.view',
            'equipment.create',
            'equipment.edit',
            'equipment.delete',

            'borrows.view',
            'borrows.create',
            'borrows.approve',
            'borrows.reject',
            'borrows.return',

            'achievements.view',
            'achievements.create',
            'achievements.edit',
            'achievements.delete',

            'documents.view',
            'documents.create',
            'documents.edit',
            'documents.delete',
            'documents.download',

            'transactions.view',
            'reports.view',

            'manage-roles',
        ];

        /*
        |--------------------------------------------------------------------------
        | Permissions
        |--------------------------------------------------------------------------
        */

        foreach ($permissions as $permission) {
            Permission::firstOrCreate([
                'name' => $permission,
                'guard_name' => 'api',
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | Roles
        |--------------------------------------------------------------------------
        */

        $adminRole = Role::firstOrCreate([
            'name' => 'admin',
            'guard_name' => 'api',
        ]);

        $labManagerRole = Role::firstOrCreate([
            'name' => 'lab_manager',
            'guard_name' => 'api',
        ]);

        $studentRole = Role::firstOrCreate([
            'name' => 'student',
            'guard_name' => 'api',
        ]);

        $adminRole->syncPermissions($permissions);

        $labManagerPermissions = array_filter(
            $permissions,
            fn ($permission) => str_contains($permission, '.create') ||
                str_contains($permission, '.edit') ||
                str_contains($permission, 'borrows.create') ||
                str_contains($permission, 'borrows.approve') ||
                str_contains($permission, 'borrows.reject') ||
                str_contains($permission, 'borrows.return') ||
                str_contains($permission, 'documents.download')
        );

        $labManagerRole->syncPermissions($labManagerPermissions);

        $studentPermissions = array_filter(
            $permissions,
            fn ($permission) => str_contains($permission, '.view') ||
                str_contains($permission, 'borrows.create') ||
                str_contains($permission, 'borrows.return') ||
                str_contains($permission, 'documents.download')
        );

        $studentRole->syncPermissions($studentPermissions);

        /*
        |--------------------------------------------------------------------------
        | Admin User
        |--------------------------------------------------------------------------
        */

        $admin = User::updateOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Admin',
                'role' => 'admin',
                'password' => Hash::make(env('ADMIN_PASSWORD', 'Admin@123')),
            ]
        );

        $admin->syncRoles([$adminRole]);

       

        resolve(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
