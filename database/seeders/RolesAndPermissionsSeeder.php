<?php

namespace Database\Seeders;

use App\Enums\RoleName;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $permissions = [
            // User Management
            'users.view',
            'users.create',
            'users.update',
            'users.delete',
            'users.suspend',
            'users.invite',

            // Approvals
            'parents.approve',
            'teachers.manage',
            'portfolio.publish',

            // Students & Enrollment
            'students.register',
            'students.view',
            'students.manage',
            'enrollment.manage',

            // Academics
            'classes.manage',
            'materials.manage',
            'activities.manage',
            'assignments.manage',
            'quizzes.manage',

            // Operations
            'attendance.scan',
            'attendance.edit',
            'payments.review',
            'payments.view',
            'printing.operate',
            'printing.approve',

            // Communication & Audit
            'chat.use',
            'chat.audit',
            'reports.view',
            'settings.manage',
            'audit.view',
        ];

        foreach ($permissions as $permissionName) {
            Permission::firstOrCreate(['name' => $permissionName, 'guard_name' => 'web']);
        }

        // 1. Super Admin Role
        $superAdmin = Role::firstOrCreate(['name' => RoleName::SuperAdmin->value, 'guard_name' => 'web']);
        $superAdmin->syncPermissions(Permission::all());

        // 2. Admin / Office Staff Role
        $admin = Role::firstOrCreate(['name' => RoleName::Admin->value, 'guard_name' => 'web']);
        $admin->syncPermissions([
            'users.view', 'users.create', 'users.update', 'users.suspend', 'users.invite',
            'parents.approve', 'teachers.manage', 'portfolio.publish',
            'students.view', 'enrollment.manage', 'classes.manage',
            'attendance.scan', 'attendance.edit',
            'payments.review', 'payments.view',
            'printing.operate', 'printing.approve',
            'chat.use', 'reports.view', 'settings.manage', 'audit.view',
        ]);

        // 3. Teacher / Lecturer Role
        $teacher = Role::firstOrCreate(['name' => RoleName::Teacher->value, 'guard_name' => 'web']);
        $teacher->syncPermissions([
            'classes.manage', 'materials.manage', 'activities.manage',
            'assignments.manage', 'quizzes.manage', 'students.view',
            'attendance.scan', 'chat.use',
        ]);

        // 4. Parent / Guardian Role
        $parent = Role::firstOrCreate(['name' => RoleName::Parent->value, 'guard_name' => 'web']);
        $parent->syncPermissions([
            'chat.use',
        ]);

        // 5. Student Role
        $student = Role::firstOrCreate(['name' => RoleName::Student->value, 'guard_name' => 'web']);
        $student->syncPermissions([
            'chat.use',
        ]);

        // 6. Printing Staff Role
        $printStaff = Role::firstOrCreate(['name' => RoleName::PrintingStaff->value, 'guard_name' => 'web']);
        $printStaff->syncPermissions([
            'printing.operate',
        ]);

        // 7. Cashier Role
        $cashier = Role::firstOrCreate(['name' => RoleName::Cashier->value, 'guard_name' => 'web']);
        $cashier->syncPermissions([
            'payments.review', 'payments.view', 'reports.view',
        ]);

        // 8. Attendance Marshal Role
        $marshal = Role::firstOrCreate(['name' => RoleName::AttendanceMarshal->value, 'guard_name' => 'web']);
        $marshal->syncPermissions([
            'attendance.scan',
        ]);
    }
}
