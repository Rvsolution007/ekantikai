<?php

/**
 * Full Catalogue Seeder - Based on User's Screenshots
 * This will add ALL products visible in the UI
 */

namespace Database\Seeders;

use App\Models\Catalogue;
use App\Models\Admin;
use Illuminate\Database\Seeder;

class FullCatalogueSeeder extends Seeder
{
    public function run(): void
    {
        // Get admin@datsun.com admin
        $admin = Admin::where('email', 'admin@datsun.com')->first();

        if (!$admin) {
            echo "❌ Admin not found!\n";
            return;
        }

        echo "✅ Found Admin: {$admin->name} (ID: {$admin->id})\n";
        echo "📦 Adding full catalogue data...\n\n";

        // Profile handles (from screenshot - models 29-42)
        $profileHandles = [
            ['model_code' => '29', 'sizes' => '6inch,8inch,10inch,12inch,14inch,16inch'],
            ['model_code' => '31', 'sizes' => '6inch,8inch,10inch,12inch,14inch,16inch'],
            ['model_code' => '32', 'sizes' => '6inch,8inch,10inch,12inch,14inch,16inch'],
            ['model_code' => '33', 'sizes' => '6inch,8inch,10inch,12inch,14inch,16inch'],
            ['model_code' => '34', 'sizes' => '6inch,8inch,10inch,12inch,14inch,16inch'],
            ['model_code' => '034 BS', 'sizes' => '6inch,8inch,10inch,12inch,14inch,16inch'],
            ['model_code' => '035 BS', 'sizes' => '6inch,8inch,10inch,12inch,14inch,16inch'],
            ['model_code' => '39', 'sizes' => '6inch,8inch,10inch,12inch,14inch,16inch'],
            ['model_code' => '40', 'sizes' => '6inch,8inch,10inch,12inch,14inch,16inch'],
            ['model_code' => '41', 'sizes' => '6inch,8inch,10inch,12inch,14inch,16inch'],
            ['model_code' => '42', 'sizes' => '6inch,8inch,10inch,12inch,14inch,16inch'],
            ['model_code' => '09', 'sizes' => '6inch,8inch,10inch,12inch,14inch,16inch'],
            ['model_code' => '016', 'sizes' => '6inch,8inch,10inch,12inch,14inch,16inch'],
        ];

        foreach ($profileHandles as $product) {
            Catalogue::updateOrCreate(
                [
                    'admin_id' => $admin->id,
                    'product_type' => 'Profile handles',
                    'model_code' => $product['model_code'],
                ],
                [
                    'sizes' => $product['sizes'],
                    'material' => 'Aluminium',
                    'finishes' => 'Silver,Black,Gold,Champagne',
                    'pack_per_size' => '10 pcs/box',
                    'is_active' => true,
                ]
            );
        }
        echo "✅ Added " . count($profileHandles) . " Profile handles\n";

        // Wardrobe handles (from screenshot - models 9040-9053)
        $wardrobeHandles = [
            ['model_code' => '9040', 'sizes' => '96mm,160mm,224mm'],
            ['model_code' => '9041', 'sizes' => '96mm,160mm,224mm'],
            ['model_code' => '9042', 'sizes' => '64mm,96mm,128mm'],
            ['model_code' => '9043', 'sizes' => '96mm,160mm,224mm'],
            ['model_code' => '9044', 'sizes' => '96mm,160mm,224mm'],
            ['model_code' => '9045', 'sizes' => '160mm,224mm,288mm'],
            ['model_code' => '9046', 'sizes' => '96mm,160mm,224mm'],
            ['model_code' => '9047', 'sizes' => '96mm,160mm,224mm'],
            ['model_code' => '9048', 'sizes' => '96mm,160mm,224mm'],
            ['model_code' => '9049', 'sizes' => '96mm,160mm,224mm'],
            ['model_code' => '9050', 'sizes' => '200mm,300mm'],
            ['model_code' => '9051', 'sizes' => '96mm,160mm,224mm'],
            ['model_code' => '9052', 'sizes' => '96mm,160mm,224mm'],
            ['model_code' => '9053', 'sizes' => '96mm,160mm,224mm'],
            // Add more from Catalogue.php ranges
            ['model_code' => '9001', 'sizes' => '96mm,128mm,160mm'],
            ['model_code' => '9002', 'sizes' => '96mm,128mm,160mm'],
            ['model_code' => '9004', 'sizes' => '96mm,128mm,160mm'],
            ['model_code' => '9008', 'sizes' => '224mm,288mm'],
            ['model_code' => '9009', 'sizes' => '224mm,288mm'],
            ['model_code' => '9011', 'sizes' => '288mm,320mm'],
            ['model_code' => '9018', 'sizes' => '224mm,288mm'],
            ['model_code' => '9019', 'sizes' => '224mm,288mm'],
            ['model_code' => '9020', 'sizes' => '224mm,288mm'],
            ['model_code' => '9021', 'sizes' => '224mm,288mm'],
            ['model_code' => '9023', 'sizes' => '224mm,288mm'],
            ['model_code' => '9024', 'sizes' => '224mm,288mm'],
            ['model_code' => '9025', 'sizes' => '224mm,288mm'],
            ['model_code' => '9026', 'sizes' => '224mm,288mm'],
            ['model_code' => '9027', 'sizes' => '224mm,288mm'],
            ['model_code' => '9028', 'sizes' => '224mm,288mm'],
            ['model_code' => '9029', 'sizes' => '224mm,288mm'],
            ['model_code' => '9030', 'sizes' => '224mm,288mm'],
            ['model_code' => '9031', 'sizes' => '224mm,288mm'],
            ['model_code' => '9032', 'sizes' => '224mm,288mm'],
            ['model_code' => '9033', 'sizes' => '224mm,288mm'],
            ['model_code' => '9034', 'sizes' => '224mm,288mm'],
            ['model_code' => '9035', 'sizes' => '224mm,288mm'],
            ['model_code' => '9036', 'sizes' => '224mm,288mm'],
            ['model_code' => '9037', 'sizes' => '224mm,288mm'],
            ['model_code' => '9038', 'sizes' => '224mm,288mm'],
            ['model_code' => '9039', 'sizes' => '224mm,288mm'],
        ];

        foreach ($wardrobeHandles as $product) {
            Catalogue::updateOrCreate(
                [
                    'admin_id' => $admin->id,
                    'product_type' => 'Wardrobe handles',
                    'model_code' => $product['model_code'],
                ],
                [
                    'sizes' => $product['sizes'],
                    'material' => 'Aluminium',
                    'finishes' => 'Gold,Black,Chrome,Matt Black',
                    'pack_per_size' => '10 pcs/box',
                    'is_active' => true,
                ]
            );
        }
        echo "✅ Added " . count($wardrobeHandles) . " Wardrobe handles\n";

        // Cabinet handles
        $cabinetHandles = [
            ['model_code' => '0012', 'sizes' => '96mm,128mm'],
            ['model_code' => '0013', 'sizes' => '96mm,128mm'],
            ['model_code' => '0014', 'sizes' => '96mm,128mm,160mm'],
        ];

        foreach ($cabinetHandles as $product) {
            Catalogue::updateOrCreate(
                [
                    'admin_id' => $admin->id,
                    'product_type' => 'Cabinet handles',
                    'model_code' => $product['model_code'],
                ],
                [
                    'sizes' => $product['sizes'],
                    'material' => 'Zinc Alloy',
                    'finishes' => 'Chrome,Matt Black,Gold',
                    'pack_per_size' => '10 pcs/box',
                    'is_active' => true,
                ]
            );
        }
        echo "✅ Added " . count($cabinetHandles) . " Cabinet handles\n";

        // Knob handles
        $knobHandles = [
            ['model_code' => '402', 'sizes' => '25mm,30mm'],
            ['model_code' => '404', 'sizes' => '25mm,30mm'],
            ['model_code' => '406', 'sizes' => '25mm,30mm,35mm'],
            ['model_code' => '408', 'sizes' => '25mm,30mm'],
            ['model_code' => '409', 'sizes' => '25mm,30mm'],
            ['model_code' => '410', 'sizes' => '25mm,30mm'],
            ['model_code' => '413', 'sizes' => '25mm,30mm'],
            ['model_code' => '414', 'sizes' => '25mm,30mm'],
            ['model_code' => '415', 'sizes' => '25mm,30mm'],
            ['model_code' => '416', 'sizes' => '25mm,30mm'],
            ['model_code' => '417', 'sizes' => '25mm,30mm'],
            ['model_code' => '418', 'sizes' => '25mm,30mm'],
            ['model_code' => '419', 'sizes' => '25mm,30mm'],
            ['model_code' => '422', 'sizes' => '25mm,30mm'],
            ['model_code' => '9014', 'sizes' => '30mm,35mm'],
            ['model_code' => '9017', 'sizes' => '30mm,35mm'],
        ];

        foreach ($knobHandles as $product) {
            Catalogue::updateOrCreate(
                [
                    'admin_id' => $admin->id,
                    'product_type' => 'Knob handles',
                    'model_code' => $product['model_code'],
                ],
                [
                    'sizes' => $product['sizes'],
                    'material' => 'Zinc Alloy',
                    'finishes' => 'Chrome,Gold,Antique,Matt Black',
                    'pack_per_size' => '10 pcs/box',
                    'is_active' => true,
                ]
            );
        }
        echo "✅ Added " . count($knobHandles) . " Knob handles\n";

        // Main door handles
        $mainDoorHandles = [
            ['model_code' => '91', 'sizes' => '10inch,12inch'],
            ['model_code' => '92', 'sizes' => '10inch,12inch'],
            ['model_code' => '93', 'sizes' => '10inch,12inch'],
            ['model_code' => '94', 'sizes' => '10inch,12inch'],
            ['model_code' => '96', 'sizes' => '10inch,12inch'],
            ['model_code' => '97', 'sizes' => '10inch,12inch,14inch'],
            ['model_code' => '98', 'sizes' => '10inch,12inch'],
            ['model_code' => '99', 'sizes' => '12inch,14inch'],
        ];

        foreach ($mainDoorHandles as $product) {
            Catalogue::updateOrCreate(
                [
                    'admin_id' => $admin->id,
                    'product_type' => 'Main door handles',
                    'model_code' => $product['model_code'],
                ],
                [
                    'sizes' => $product['sizes'],
                    'material' => 'Stainless Steel',
                    'finishes' => 'Gold PVD,Black PVD,Rose Gold',
                    'pack_per_size' => '5 pcs/box',
                    'is_active' => true,
                ]
            );
        }
        echo "✅ Added " . count($mainDoorHandles) . " Main door handles\n";

        // Final count
        $totalCount = Catalogue::where('admin_id', $admin->id)->count();
        echo "\n🎉 COMPLETE! Total products in database: {$totalCount}\n";

        // Show breakdown
        echo "\n📊 Breakdown by category:\n";
        $breakdown = Catalogue::where('admin_id', $admin->id)
            ->select('product_type', \DB::raw('count(*) as count'))
            ->groupBy('product_type')
            ->get();

        foreach ($breakdown as $item) {
            echo "   {$item->product_type}: {$item->count}\n";
        }
    }
}
