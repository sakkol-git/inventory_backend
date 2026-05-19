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

            'achievements.view',
            'achievements.create',
            'achievements.edit',
            'achievements.delete',

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
                str_contains($permission, '.edit')
        );

        $labManagerRole->syncPermissions($labManagerPermissions);

        $studentPermissions = array_filter(
            $permissions,
            fn ($permission) => str_contains($permission, '.view')
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

        /*
        |--------------------------------------------------------------------------
        | Optional Demo Users
        |--------------------------------------------------------------------------
        */

        if (app()->environment(['local', 'development'])) {

            for ($i = 1; $i <= 3; $i++) {

                $manager = User::updateOrCreate(
                    ['email' => "labmanager{$i}@example.com"],
                    [
                        'name' => "Lab Manager {$i}",
                        'role' => 'lab_manager',
                        'password' => Hash::make('LabManager@123'),
                    ]
                );

                $manager->syncRoles([$labManagerRole]);
            }

            for ($i = 1; $i <= 10; $i++) {

                $student = User::updateOrCreate(
                    ['email' => "student{$i}@example.com"],
                    [
                        'name' => "Student {$i}",
                        'role' => 'student',
                        'password' => Hash::make('Student@123'),
                    ]
                );

                $student->syncRoles([$studentRole]);
            }
        }

        resolve(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
