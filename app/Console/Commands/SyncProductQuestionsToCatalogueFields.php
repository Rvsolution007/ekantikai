<?php

namespace App\Console\Commands;

use App\Models\CatalogueField;
use App\Models\ProductQuestion;
use Illuminate\Console\Command;

class SyncProductQuestionsToCatalogueFields extends Command
{
    protected $signature = 'sync:product-questions-to-catalogue {--admin= : Specific admin ID to sync}';
    protected $description = 'Sync all ProductQuestions to CatalogueFields (creates missing, updates existing, removes orphaned)';

    public function handle()
    {
        $adminId = $this->option('admin');

        $query = ProductQuestion::query();
        if ($adminId) {
            $query->where('admin_id', $adminId);
        }

        $questions = $query->orderBy('admin_id')->orderBy('sort_order')->get();

        $this->info("Found {$questions->count()} ProductQuestions to sync...");

        $synced = 0;
        $created = 0;
        $deleted = 0;

        // Group by admin
        $groupedByAdmin = $questions->groupBy('admin_id');

        foreach ($groupedByAdmin as $currentAdminId => $adminQuestions) {
            $this->line("\n--- Admin ID: {$currentAdminId} ---");

            $productQuestionIds = [];

            foreach ($adminQuestions as $question) {
                $productQuestionIds[] = $question->id;

                // Check if CatalogueField exists
                $existing = CatalogueField::where('admin_id', $currentAdminId)
                    ->where('product_question_id', $question->id)
                    ->first();

                if ($existing) {
                    $this->line("  Updating: {$question->field_name} (sort: {$question->sort_order})");
                    $synced++;
                } else {
                    $this->line("  Creating: {$question->field_name} (sort: {$question->sort_order})");
                    $created++;
                }

                // Sync
                $question->syncToCatalogueField();
            }

            // Remove orphaned CatalogueFields (linked to deleted ProductQuestions)
            $orphaned = CatalogueField::where('admin_id', $currentAdminId)
                ->whereNotNull('product_question_id')
                ->whereNotIn('product_question_id', $productQuestionIds)
                ->get();

            foreach ($orphaned as $orphan) {
                $this->warn("  Deleting orphan: {$orphan->field_name} (product_question_id: {$orphan->product_question_id})");
                $orphan->delete();
                $deleted++;
            }

            // IMPORTANT: Final reorder - set exact sort_order from ProductQuestion
            $this->line("\n  Reordering CatalogueFields to match ProductQuestion order...");
            foreach ($adminQuestions as $question) {
                CatalogueField::where('admin_id', $currentAdminId)
                    ->where('product_question_id', $question->id)
                    ->update(['sort_order' => $question->sort_order]);
            }
            $this->info("  ✓ Order synced for Admin {$currentAdminId}");
        }

        $this->newLine();
        $this->info("✅ Sync Complete!");
        $this->table(
            ['Action', 'Count'],
            [
                ['Synced (Updated)', $synced],
                ['Created', $created],
                ['Deleted Orphans', $deleted],
            ]
        );

        return Command::SUCCESS;
    }
}
