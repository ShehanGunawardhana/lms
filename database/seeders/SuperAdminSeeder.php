<?php

namespace Database\Seeders;

use App\Enums\InstitutionType;
use App\Enums\RoleName;
use App\Models\Institution;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class SuperAdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Ensure Default Institution exists
        $institution = Institution::firstOrCreate(
            ['slug' => 'premier-academy'],
            [
                'name' => 'Premier Learning Institute & Nursery',
                'type' => InstitutionType::Mixed,
                'timezone' => config('app.timezone', 'UTC'),
                'locale' => 'en',
                'status' => 'active',
            ]
        );

        // 2. Ensure Super Admin user exists
        $superAdmin = User::firstOrCreate(
            ['email' => 'superadmin@portal.local'],
            [
                'institution_id' => $institution->id,
                'name' => 'System Super Admin',
                'phone' => '+10000000000',
                'password' => Hash::make((string) config('portal.passwords.super_admin', 'password')),
                'status' => 'active',
                'locale' => 'en',
                'email_verified_at' => now(),
            ]
        );

        // 3. Assign Super Admin role
        $superAdmin->syncRoles([RoleName::SuperAdmin->value]);
    }
}
