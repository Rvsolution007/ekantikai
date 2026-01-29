<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== Debug Service Test ===\n\n";

$debugService = new \App\Services\DebugService();
$admin = \App\Models\Admin::first();

if (!$admin) {
    echo "No admin found!\n";
    exit(1);
}

echo "Admin: {$admin->name} (ID: {$admin->id})\n";

// Count products directly
$productCount = \App\Models\Product::whereHas('lead', function ($q) use ($admin) {
    $q->where('admin_id', $admin->id);
})->count();
echo "Products (via lead): {$productCount}\n\n";

// Show sample products
$products = \App\Models\Product::whereHas('lead', function ($q) use ($admin) {
    $q->where('admin_id', $admin->id);
})->take(5)->get();
foreach ($products as $p) {
    echo "  - [{$p->id}] {$p->product} / {$p->model}\n";
}
echo "\n";

$result = $debugService->runFullScan($admin);

echo "Badge: {$result['badge']}\n";
echo "Passed: {$result['summary']['passed']}\n";
echo "Failed: {$result['summary']['failed']}\n\n";

if (count($result['checks_failed']) > 0) {
    echo "=== ERRORS ===\n";
    foreach ($result['checks_failed'] as $e) {
        echo "X [{$e['id']}] {$e['name']} ({$e['severity']})\n";
        echo "   Details: {$e['details']}\n";
        if (isset($e['fix']))
            echo "   Fix: {$e['fix']}\n";
        echo "\n";
    }
}
