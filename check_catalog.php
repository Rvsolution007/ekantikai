<?php

// Quick Database Diagnostic Script
// Run: php check_catalog.php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== CATALOG DATABASE CHECK ===\n\n";

// Get all catalogues grouped by product_type
$catalogues = DB::table('catalogues')
    ->select('product_type', 'model_code')
    ->orderBy('product_type')
    ->orderBy('model_code')
    ->get();

$grouped = [];
foreach ($catalogues as $cat) {
    if (!isset($grouped[$cat->product_type])) {
        $grouped[$cat->product_type] = [];
    }
    $grouped[$cat->product_type][] = $cat->model_code;
}

foreach ($grouped as $type => $models) {
    echo "📦 $type\n";
    echo "   Models: " . implode(', ', $models) . "\n";
    echo "   Count: " . count($models) . "\n\n";
}

echo "\n=== CHECKING FOR CONFLICTS ===\n\n";

// Check if 9007-9037 are in Profile handles
$profileHandles = DB::table('catalogues')
    ->where('product_type', 'LIKE', '%Profile handle%')
    ->whereIn('model_code', ['9007', '9008', '9009', '9010', '9011', '9018', '9019', '9020', '9021', '9023', '9024', '9025', '9026', '9027', '9028', '9029', '9030', '9031', '9032', '9033', '9034', '9035', '9036', '9037'])
    ->pluck('model_code')
    ->toArray();

if (!empty($profileHandles)) {
    echo "❌ PROBLEM FOUND!\n";
    echo "These Wardrobe models are incorrectly in Profile handles:\n";
    echo implode(', ', $profileHandles) . "\n\n";
} else {
    echo "✅ No conflicts found in database!\n\n";
}

echo "=== CATALOGUE.PHP MODEL RANGES ===\n\n";
$ranges = \App\Models\Catalogue::getModelRanges();
foreach ($ranges as $type => $models) {
    echo "📋 $type\n";
    echo "   Expected: " . implode(', ', $models) . "\n";
    echo "   Count: " . count($models) . "\n\n";
}
