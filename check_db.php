<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$products = \DB::select('SELECT * FROM products LIMIT 5');
echo "Products in DB:\n";
foreach ($products as $p) {
    echo "  ID:{$p->id} Lead:{$p->lead_id} Product:{$p->product} Model:{$p->model}\n";
}

$leads = \DB::select('SELECT id, admin_id, name FROM leads LIMIT 5');
echo "\nLeads in DB:\n";
foreach ($leads as $l) {
    echo "  ID:{$l->id} Admin:{$l->admin_id} Name:{$l->name}\n";
}
