<?php

namespace App\Console\Commands;

use App\Models\Unit;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class RemoveDuplicateUnits extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'units:remove-duplicates {--dry-run : Run without actually deleting} {--force : Force deletion without confirmation}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Remove duplicate units intelligently keeping the record with more data';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔍 Searching for duplicate unit codes...');

        // Find duplicate unit codes
        $duplicates = DB::table('units')
            ->select('unit_code', DB::raw('COUNT(*) as count'), DB::raw('GROUP_CONCAT(id ORDER BY id) as ids'))
            ->whereNotNull('unit_code')
            ->where('unit_code', '!=', '')
            ->groupBy('unit_code')
            ->having('count', '>', 1)
            ->get();

        if ($duplicates->isEmpty()) {
            $this->info('✅ No duplicate unit codes found!');
            return Command::SUCCESS;
        }

        $this->info("📊 Found {$duplicates->count()} unit codes with duplicates");
        $this->newLine();

        $toDelete = [];
        $totalDuplicates = 0;

        foreach ($duplicates as $duplicate) {
            $ids = explode(',', $duplicate->ids);
            $units = Unit::whereIn('id', $ids)->get();

            $this->line("Unit Code: {$duplicate->unit_code} ({$duplicate->count} duplicates)");

            // Score each unit based on data completeness
            $scored = $units->map(function ($unit) {
                $score = 0;
                $fields = $unit->getAttributes();

                foreach ($fields as $key => $value) {
                    // Skip certain fields from scoring
                    if (in_array($key, ['id', 'created_at', 'updated_at'])) {
                        continue;
                    }

                    // Count non-empty values
                    if (!is_null($value) && $value !== '' && $value !== 0 && $value !== '0.00') {
                        $score++;
                    }
                }

                return [
                    'unit' => $unit,
                    'score' => $score,
                    'id' => $unit->id,
                    'created_at' => $unit->created_at,
                ];
            });

            // Sort by score (desc), then by created_at (asc - older first), then by id (asc)
            $sorted = $scored->sortByDesc('score')
                ->sortBy('created_at')
                ->sortBy('id');

            // Keep the first one (best), mark others for deletion
            $keep = $sorted->first();
            $delete = $sorted->skip(1);

            $this->line("  ✅ Keep: ID {$keep['id']} (Score: {$keep['score']}, Created: {$keep['created_at']})");

            foreach ($delete as $item) {
                $this->line("  ❌ Delete: ID {$item['id']} (Score: {$item['score']}, Created: {$item['created_at']})");
                $toDelete[] = $item['id'];
                $totalDuplicates++;
            }

            $this->newLine();
        }

        if (empty($toDelete)) {
            $this->info('✅ No units need to be deleted!');
            return Command::SUCCESS;
        }

        $this->info("═══════════════════════════════════════");
        $this->info("📊 Summary:");
        $this->info("   Duplicate groups: {$duplicates->count()}");
        $this->info("   Units to delete: {$totalDuplicates}");
        $this->info("═══════════════════════════════════════");
        $this->newLine();

        if ($this->option('dry-run')) {
            $this->warn('🔍 DRY RUN: No units were actually deleted.');
            return Command::SUCCESS;
        }

        if (!$this->option('force')) {
            if (!$this->confirm('Do you want to proceed with deletion?')) {
                $this->info('❌ Operation cancelled.');
                return Command::FAILURE;
            }
        }

        // Perform deletion
        $this->info('🗑️  Deleting duplicate units...');
        $deleted = Unit::whereIn('id', $toDelete)->delete();

        $this->info("✅ Successfully deleted {$deleted} duplicate units!");

        return Command::SUCCESS;
    }
}
