<?php

namespace App\Console\Commands;

use App\Models\Catalogue;
use App\Models\CatalogueField;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class FixCatalogueFieldKeys extends Command
{
    protected $signature = 'catalogue:fix-field-keys {--admin= : Specific admin ID to fix} {--dry-run : Show what would be changed without making changes}';

    protected $description = 'Fix catalogue field keys to be AI-compatible (snake_case, no spaces)';

    public function handle()
    {
        $dryRun = $this->option('dry-run');
        $adminId = $this->option('admin');

        $this->info('=== Catalogue Field Key Fixer ===');
        $this->info($dryRun ? '(DRY RUN - no changes will be made)' : '');

        // Get catalogues to fix
        $query = Catalogue::query();
        if ($adminId) {
            $query->where('admin_id', $adminId);
        }

        $catalogues = $query->get();
        $this->info("Found {$catalogues->count()} catalogue items to check.");

        $fixedCount = 0;
        $fieldFixedCount = 0;

        foreach ($catalogues as $catalogue) {
            $data = $catalogue->data ?? [];
            $newData = [];
            $needsFix = false;

            foreach ($data as $key => $value) {
                // Convert key to snake_case (no spaces, lowercase)
                $newKey = Str::snake(Str::lower(trim($key)));
                $newKey = preg_replace('/[^a-z0-9_]/', '_', $newKey); // Remove any remaining special chars
                $newKey = preg_replace('/_+/', '_', $newKey); // Remove double underscores
                $newKey = trim($newKey, '_'); // Trim leading/trailing underscores

                if ($newKey !== $key) {
                    $this->line("  [{$catalogue->id}] '{$key}' → '{$newKey}'");
                    $needsFix = true;
                    $fieldFixedCount++;
                }

                $newData[$newKey] = $value;
            }

            if ($needsFix) {
                if (!$dryRun) {
                    $catalogue->data = $newData;
                    $catalogue->save();
                }
                $fixedCount++;
            }
        }

        $this->info("");
        $this->info("=== Summary ===");
        $this->info("Catalogues checked: {$catalogues->count()}");
        $this->info("Catalogues " . ($dryRun ? 'needing fix' : 'fixed') . ": {$fixedCount}");
        $this->info("Field keys " . ($dryRun ? 'needing fix' : 'fixed') . ": {$fieldFixedCount}");

        if ($dryRun && $fixedCount > 0) {
            $this->warn("\nRun without --dry-run to apply changes.");
        }

        // Also fix CatalogueField table
        $this->info("\n=== Checking CatalogueField Table ===");

        $fieldsQuery = CatalogueField::query();
        if ($adminId) {
            $fieldsQuery->where('admin_id', $adminId);
        }
        $fields = $fieldsQuery->get();

        $fieldTableFixCount = 0;

        foreach ($fields as $field) {
            $oldKey = $field->field_key;
            $newKey = Str::snake(Str::lower(trim($field->field_name)));
            $newKey = preg_replace('/[^a-z0-9_]/', '_', $newKey);
            $newKey = preg_replace('/_+/', '_', $newKey);
            $newKey = trim($newKey, '_');

            if ($oldKey !== $newKey) {
                $this->line("  Field [{$field->id}] '{$oldKey}' → '{$newKey}'");

                if (!$dryRun) {
                    $field->field_key = $newKey;
                    $field->save();
                }
                $fieldTableFixCount++;
            }
        }

        $this->info("CatalogueField rows " . ($dryRun ? 'needing fix' : 'fixed') . ": {$fieldTableFixCount}");

        $this->info("\n✓ Done!");

        return Command::SUCCESS;
    }
}
