<?php

namespace App\Http\Controllers;

use App\Exports\SalesAvailabilityExport;
use App\Exports\UnitsAvailabilityExport;
use App\Exports\MergedAvailabilityExport;
use App\Exports\ComprehensiveDataExport;
use App\Exports\AllDataExport;
use App\Imports\MergedAvailabilityImport;
use App\Imports\AllDataImport;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class ExportController extends Controller
{
    /**
     * Export Sales Availability to Excel
     */
    public function exportSalesAvailability()
    {
        return Excel::download(new SalesAvailabilityExport, 'sales_availability_' . date('Y-m-d_H-i-s') . '.xlsx');
    }

    /**
     * Export Units Availability to Excel
     */
    public function exportUnitsAvailability()
    {
        return Excel::download(new UnitsAvailabilityExport, 'units_availability_' . date('Y-m-d_H-i-s') . '.xlsx');
    }

    /**
     * Export Merged Availability to Excel
     */
    public function exportMergedAvailability()
    {
        // Increase memory limit temporarily for large exports
        ini_set('memory_limit', '512M');

        // Use CSV format which is more memory efficient than XLSX
        return Excel::download(new MergedAvailabilityExport, 'merged_availability_' . date('Y-m-d_H-i-s') . '.csv');
    }

    /**
     * Export Comprehensive Data (Units + Compounds + Companies) to Excel
     */
    public function exportComprehensiveData()
    {
        // Increase memory limit for large dataset (5467 units)
        ini_set('memory_limit', '1024M');
        ini_set('max_execution_time', '300'); // 5 minutes

        $filename = 'comprehensive_data_' . date('Y-m-d_His') . '.xlsx';

        return Excel::download(
            new ComprehensiveDataExport(),
            $filename,
            \Maatwebsite\Excel\Excel::XLSX
        );
    }

    /**
     * Export All Data from flat table (Units + Compounds + Companies combined)
     */
    public function exportAllData()
    {
        // Increase memory limit for large dataset (5467 units)
        ini_set('memory_limit', '1024M');
        ini_set('max_execution_time', '300'); // 5 minutes

        $filename = 'all_data_export_' . date('Y-m-d_His') . '.xlsx';

        return Excel::download(
            new AllDataExport(),
            $filename,
            \Maatwebsite\Excel\Excel::XLSX
        );
    }

    /**
     * Import Merged Availability from Excel
     */
    public function importMergedAvailability(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv|max:10240', // Max 10MB
        ]);

        try {
            $import = new MergedAvailabilityImport;
            Excel::import($import, $request->file('file'));

            $stats = $import->getStats();

            $message = sprintf(
                'Successfully imported %d records! (%d to Sales Availability, %d to Units Availability)',
                $stats['total'],
                $stats['sales'],
                $stats['units']
            );

            return redirect()->back()->with('success', $message);
        } catch (\Maatwebsite\Excel\Validators\ValidationException $e) {
            $failures = $e->failures();

            $errors = [];
            foreach ($failures as $failure) {
                $errors[] = "Row {$failure->row()}: " . implode(', ', $failure->errors());
            }

            return redirect()->back()->with('error', 'Import failed: ' . implode(' | ', $errors));
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Import failed: ' . $e->getMessage());
        }
    }

    /**
     * Show import form page
     */
    public function showImportForm()
    {
        return view('import.merged-availability');
    }

    /**
     * Show import form for all data
     */
    public function showAllDataImportForm()
    {
        return view('import.all-data');
    }

    /**
     * Import All Data from Excel (increased size limit)
     */
    public function importAllData(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv|max:51200', // Max 50MB
        ]);

        try {
            // Increase limits for large imports
            ini_set('memory_limit', '1024M');
            ini_set('max_execution_time', '600'); // 10 minutes

            $import = new AllDataImport;
            Excel::import($import, $request->file('file'));

            $stats = $import->getStats();

            $message = sprintf(
                'Successfully imported %d records to All Data table!',
                $stats['total']
            );

            if ($stats['errors'] > 0) {
                $message .= sprintf(' (%d rows skipped due to errors)', $stats['errors']);
            }

            return redirect()->back()->with('success', $message);
        } catch (\Maatwebsite\Excel\Validators\ValidationException $e) {
            $failures = $e->failures();

            $errors = [];
            foreach ($failures as $failure) {
                $errors[] = "Row {$failure->row()}: " . implode(', ', $failure->errors());
            }

            return redirect()->back()->with('error', 'Import failed: ' . implode(' | ', $errors));
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Import failed: ' . $e->getMessage());
        }
    }
}
