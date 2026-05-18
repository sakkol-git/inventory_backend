<?php

namespace Database\Seeders;

use App\Modules\Core\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class UserSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        resolve(PermissionRegistrar::class)->forgetCachedPermissions();

        // Define all permissions
        $permissions = [
            // Users
            'users.view',
            'users.create',
            'users.edit',
            'users.delete',
            // Plants
            'plants.view',
            'plants.create',
            'plants.edit',
            'plants.delete',
            // Chemicals
            'chemicals.view',
            'chemicals.create',
            'chemicals.edit',
            'chemicals.delete',
            // Equipment
            'equipment.view',
            'equipment.create',
            'equipment.edit',
            'equipment.delete',
            // Achievements
            'achievements.view',
            'achievements.create',
            'achievements.edit',
            'achievements.delete',
            // Transactions
            'transactions.view',
            // Reports
            'reports.view',
            // Roles
            'manage-roles',
        ];

        // Create all permissions
        foreach ($permissions as $permission) {
            Permission::firstOrCreate([
                'name' => $permission,
                'guard_name' => 'api',
            ]);
        }

        // Create Admin Role with all permissions
        $adminRole = Role::firstOrCreate([
            'name' => 'admin',
            'guard_name' => 'api',
        ]);
        $adminRole->syncPermissions($permissions);

        // Create Lab-Manager Role with only create and update permissions
        $labManagerRole = Role::firstOrCreate([
            'name' => 'lab_manager',
            'guard_name' => 'api',
        ]);
        $labManagerPermissions = array_filter($permissions, fn ($perm) => 
            str_contains($perm, '.create') || str_contains($perm, '.edit')
        );
        $labManagerRole->syncPermissions($labManagerPermissions);

        // Create Student Role with only view permissions
        $studentRole = Role::firstOrCreate([
            'name' => 'student',
            'guard_name' => 'api',
        ]);
        $studentPermissions = array_filter($permissions, fn ($perm) => 
            str_contains($perm, '.view') && !str_contains($perm, 'manage-roles')
        );
        $studentRole->syncPermissions($studentPermissions);

        // Create Admin User
        $adminUser = User::where('email', 'admin@example.com')->first();
        if (!$adminUser) {
            $adminUser = User::factory()->admin()->create([
                'name' => 'Admin',
                'email' => 'admin@example.com',
                'password' => bcrypt('Admin@123'),
            ]);
        } else {
            $adminUser->update([
                'name' => 'Admin',
                'role' => 'admin',
                'password' => bcrypt('Admin@123'),
            ]);
        }
        $adminUser->assignRole($adminRole);

        // Create 3 Lab-Manager Users
        for ($i = 1; $i <= 3; $i++) {
            $labManager = User::where('email', "labmanager{$i}@example.com")->first();
            if (!$labManager) {
                $labManager = User::factory()->labManager()->create([
                    'name' => "Lab Manager {$i}",
                    'email' => "labmanager{$i}@example.com",
                    'password' => bcrypt('LabManager@123'),
                ]);
            } else {
                $labManager->update([
                    'name' => "Lab Manager {$i}",
                    'role' => 'lab_manager',
                    'password' => bcrypt('LabManager@123'),
                ]);
            }
            $labManager->assignRole($labManagerRole);
        }

        // Create 10 Student Users
        for ($i = 1; $i <= 10; $i++) {
            $student = User::where('email', "student{$i}@example.com")->first();
            if (!$student) {
                $student = User::factory()->student()->create([
                    'name' => "Student {$i}",
                    'email' => "student{$i}@example.com",
                    'password' => bcrypt('Student@123'),
                ]);
            } else {
                $student->update([
                    'name' => "Student {$i}",
                    'role' => 'student',
                    'password' => bcrypt('Student@123'),
                ]);
            }
            $student->assignRole($studentRole);
        }

        resolve(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
