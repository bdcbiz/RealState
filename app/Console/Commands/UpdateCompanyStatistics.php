<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Company;

class UpdateCompanyStatistics extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'company:update-statistics {company_id?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update company statistics (compounds and units count). Optionally specify a company_id to update only that company.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $companyId = $this->argument('company_id');

        if ($companyId) {
            // Update specific company
            $company = Company::find($companyId);

            if (!$company) {
                $this->error("Company with ID {$companyId} not found!");
                return 1;
            }

            $this->info("Updating statistics for: {$company->name}");
            $company->updateStatistics();

            $this->info("✓ {$company->name}:");
            $this->info("  - Compounds: {$company->number_of_compounds}");
            $this->info("  - Available Units: {$company->number_of_available_units}");

            $this->info("\nStatistics updated successfully!");
            return 0;
        }

        // Update all companies
        $this->info("Updating statistics for all companies...\n");

        $companies = Company::all();
        $progressBar = $this->output->createProgressBar($companies->count());

        foreach ($companies as $company) {
            $company->updateStatistics();
            $progressBar->advance();
        }

        $progressBar->finish();
        $this->newLine(2);

        // Display results
        $this->info("Statistics updated for {$companies->count()} companies:\n");

        $companies = Company::orderBy('number_of_compounds', 'desc')->get();

        $this->table(
            ['ID', 'Company Name', 'Compounds', 'Available Units'],
            $companies->map(function ($company) {
                return [
                    $company->id,
                    $company->name,
                    $company->number_of_compounds,
                    $company->number_of_available_units,
                ];
            })
        );

        $this->info("\nAll company statistics updated successfully!");
        return 0;
    }
}
