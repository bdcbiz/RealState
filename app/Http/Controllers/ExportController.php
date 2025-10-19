<?php

namespace App\Http\Controllers;

use App\Exports\SalesAvailabilityExport;
use App\Exports\UnitsAvailabilityExport;
use App\Exports\MergedAvailabilityExport;
use App\Imports\MergedAvailabilityImport;
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
}
