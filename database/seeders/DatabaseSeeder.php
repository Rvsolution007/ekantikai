<?php

namespace Database\Seeders;

use App\Models\SuperAdmin;
use App\Models\Catalogue;
use App\Models\Setting;
use App\Models\Admin;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create Super Admin (no tenant)
        SuperAdmin::create([
            'name' => 'Super Admin',
            'email' => 'rvsolution696@gmail.com',
            'password' => Hash::make('9773256235'),
            'role' => 'super_admin',
            'is_active' => true,
        ]);

        // Create Admin for business
        $admin = Admin::create([
            'name' => 'Datsun Hardware',
            'slug' => 'datsun-hardware',
            'email' => 'admin@datsun.com',
            'password' => Hash::make('admin123'),
            'role' => 'admin',
            'phone' => '9876543210',
            'company_name' => 'Datsun Hardware Pvt Ltd',
            'subscription_plan' => 'pro',
            'is_active' => true,
            'is_admin_active' => true,
            // WhatsApp Evolution API Settings
            'whatsapp_api_url' => 'http://localhost:8081',
            'whatsapp_api_key' => 'datsun-bot-api-key-2026',
            'whatsapp_instance' => 'datsun',
        ]);

        // Create Admin (linked to tenant)
        SuperAdmin::create([
            'admin_id' => $admin->id,
            'name' => 'Datsun Admin',
            'email' => 'admin@datsun.com',
            'password' => Hash::make('1234567890'),
            'role' => 'admin',
            'is_active' => true,
        ]);

        // Initialize default settings
        Setting::initializeDefaults();

        // Seed Catalogue Data for the tenant
        $this->seedCatalogue($admin->id);

        // Seed Questionnaire Data
        $this->call(QuestionnaireSeeder::class);
    }

    /**
     * Seed catalogue with product data
     */
    private function seedCatalogue($adminId): void
    {
        $products = [
            // Cabinet handles
            ['product_type' => 'Cabinet handles', 'model_code' => '007', 'sizes' => '96mm, 128mm', 'material' => 'Zinc Alloy', 'finishes' => 'Chrome, Matt Black, Gold'],
            ['product_type' => 'Cabinet handles', 'model_code' => '008', 'sizes' => '96mm, 128mm', 'material' => 'Zinc Alloy', 'finishes' => 'Chrome, Matt Black'],
            ['product_type' => 'Cabinet handles', 'model_code' => '009', 'sizes' => '96mm, 128mm, 160mm', 'material' => 'Aluminium', 'finishes' => 'Silver, Black, Gold'],
            ['product_type' => 'Cabinet handles', 'model_code' => '0010', 'sizes' => '96mm, 128mm', 'material' => 'Aluminium', 'finishes' => 'Silver, Black'],
            ['product_type' => 'Cabinet handles', 'model_code' => '0011', 'sizes' => '96mm', 'material' => 'Aluminium', 'finishes' => 'Matt Black, Gold'],
            ['product_type' => 'Cabinet handles', 'model_code' => '0015', 'sizes' => '6inch, 8inch', 'material' => 'Aluminium', 'finishes' => 'Pista, Gold, Black'],

            // Wardrobe handles
            ['product_type' => 'Wardrobe handles', 'model_code' => '9005', 'sizes' => '224mm, 96mm, 900mm', 'material' => 'Aluminium', 'finishes' => 'Gold, Black, Chrome'],
            ['product_type' => 'Wardrobe handles', 'model_code' => '9007', 'sizes' => '224mm, 288mm', 'material' => 'Aluminium', 'finishes' => 'Matt Black, Gold'],
            ['product_type' => 'Wardrobe handles', 'model_code' => '9010', 'sizes' => '288mm, 320mm', 'material' => 'Aluminium', 'finishes' => 'Gold, Black'],
            ['product_type' => 'Wardrobe handles', 'model_code' => '252', 'sizes' => '6inch, 8inch', 'material' => 'Zinc Alloy', 'finishes' => 'Antique, Chrome'],
            ['product_type' => 'Wardrobe handles', 'model_code' => '253', 'sizes' => '6inch, 8inch', 'material' => 'Zinc Alloy', 'finishes' => 'Antique, Gold'],

            // Knob handles
            ['product_type' => 'Knob handles', 'model_code' => '401', 'sizes' => '25mm, 30mm', 'material' => 'Zinc Alloy', 'finishes' => 'Chrome, Gold, Antique'],
            ['product_type' => 'Knob handles', 'model_code' => '407', 'sizes' => '25mm, 30mm, 35mm', 'material' => 'Zinc Alloy', 'finishes' => 'Chrome, Matt Black, Gold'],
            ['product_type' => 'Knob handles', 'model_code' => '412', 'sizes' => '25mm, 30mm', 'material' => 'Brass', 'finishes' => 'Antique, Gold'],

            // Profile handles
            ['product_type' => 'Profile handles', 'model_code' => '028', 'sizes' => '4ft, 6ft, 8ft', 'material' => 'Aluminium', 'finishes' => 'Silver, Black, Champagne'],
            ['product_type' => 'Profile handles', 'model_code' => '029', 'sizes' => '4ft, 6ft', 'material' => 'Aluminium', 'finishes' => 'Silver, Black'],
            ['product_type' => 'Profile handles', 'model_code' => '034 BS', 'sizes' => '4ft, 6ft, 8ft', 'material' => 'Aluminium', 'finishes' => 'Blasting Gold, Blasting Silver'],

            // Main door handles
            ['product_type' => 'Main door handles', 'model_code' => '90', 'sizes' => '10inch, 12inch', 'material' => 'Stainless Steel', 'finishes' => 'Gold PVD, Black PVD'],
            ['product_type' => 'Main door handles', 'model_code' => '95', 'sizes' => '10inch, 12inch', 'material' => 'Brass', 'finishes' => 'Antique, Gold'],
            ['product_type' => 'Main door handles', 'model_code' => '100', 'sizes' => '12inch, 14inch', 'material' => 'Stainless Steel', 'finishes' => 'Gold, Rose Gold, Black'],

            // Wardrobe profile handle
            ['product_type' => 'Wardrobe profile handle', 'model_code' => '05', 'sizes' => '4ft, 6ft', 'material' => 'Aluminium', 'finishes' => 'Silver, Black'],
            ['product_type' => 'Wardrobe profile handle', 'model_code' => '022', 'sizes' => '4ft, 6ft, 8ft', 'material' => 'Aluminium', 'finishes' => 'Gold, Champagne, Black'],
        ];

        foreach ($products as $product) {
            Catalogue::create(array_merge($product, [
                'admin_id' => $adminId,
                'is_active' => true,
                'pack_per_size' => '10 pcs/box',
            ]));
        }
    }
}
