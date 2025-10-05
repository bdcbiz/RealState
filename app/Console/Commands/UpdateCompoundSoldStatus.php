<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Compound;

class UpdateCompoundSoldStatus extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'compounds:update-sold-status';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update compound sold status based on their units';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $compounds = Compound::with('units')->get();
        $updated = 0;

        foreach ($compounds as $compound) {
            $totalUnits = $compound->units()->count();
            $soldUnits = $compound->units()->where('is_sold', true)->count();

            $shouldBeSold = $totalUnits > 0 && $soldUnits === $totalUnits;

            if ($compound->is_sold !== $shouldBeSold) {
                $compound->update(['is_sold' => $shouldBeSold]);
                $updated++;
            }
        }

        $this->info("Updated {$updated} compounds.");
        return 0;
    }
}
