<?php

namespace Database\Seeders;

use App\Models\Admin;
use App\Models\SuperAdmin;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DemoTenantSeeder extends Seeder
{
    public function run(): void
    {
        // Create Demo Admin
        $admin = Admin::create([
            'name' => 'Demo Company',
            'slug' => 'demo-company',
            'email' => 'demo@company.com',
            'phone' => '9876543210',
            'company_name' => 'Demo Company Pvt Ltd',
            'subscription_plan' => 'basic',
            'trial_ends_at' => now()->addDays(14),
            'is_active' => true,
        ]);

        // Create Tenant Admin User
        SuperAdmin::create([
            'admin_id' => $admin->id,
            'name' => 'Demo User',
            'email' => 'user@demo.com',
            'password' => Hash::make('user123'),
            'role' => 'admin',
            'is_super_admin' => false,
        ]);

        $this->command->info('Demo tenant and user created!');
    }
}
