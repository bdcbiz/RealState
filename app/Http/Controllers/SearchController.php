<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\Compound;
use App\Models\Unit;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class SearchController extends Controller
{
    /**
     * Universal search across companies, compounds, and units
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function search(Request $request): JsonResponse
    {
        try {
            $search = $request->get('search', '');
            $type = $request->get('type', ''); // 'company', 'compound', 'unit'
            $perPage = $request->get('per_page', 100);
            $results = [];
            $baseUrl = "http://192.168.1.33/larvel2/storage/app/public";

            // Search companies
            if (!$type || $type === 'company') {
                $companies = Company::with('compounds')
                    ->where(function($query) use ($search) {
                        if ($search) {
                            $query->where('name', 'LIKE', "%{$search}%")
                                  ->orWhere('email', 'LIKE', "%{$search}%");
                        }
                    })
                    ->limit($perPage)
                    ->get();

                foreach ($companies as $company) {
                    $results[] = [
                        'type' => 'company',
                        'id' => $company->id,
                        'name' => $company->name,
                        'email' => $company->email,
                        'logo' => $company->logo_url,
                        'number_of_compounds' => $company->number_of_compounds,
                        'number_of_available_units' => $company->number_of_available_units,
                        'compounds_count' => $company->compounds->count(),
                        'created_at' => $company->created_at
                    ];
                }
            }

            // Search compounds
            if (!$type || $type === 'compound') {
                $compounds = Compound::with(['company', 'units'])
                    ->where(function($query) use ($search) {
                        if ($search) {
                            $query->where('project', 'LIKE', "%{$search}%")
                                  ->orWhere('location', 'LIKE', "%{$search}%");
                        }
                    })
                    ->limit($perPage)
                    ->get();

                foreach ($compounds as $compound) {
                    $results[] = [
                        'type' => 'compound',
                        'id' => $compound->id,
                        'name' => $compound->project,
                        'location' => $compound->location,
                        'status' => $compound->status ?? 'active',
                        'completion_progress' => $compound->completion_progress ?? null,
                        'units_count' => $compound->units->count(),
                        'company' => [
                            'id' => $compound->company->id ?? null,
                            'name' => $compound->company->name ?? null,
                            'logo' => $compound->company->logo_url ?? null
                        ],
                        'images' => $compound->images_urls,
                        'created_at' => $compound->created_at
                    ];
                }
            }

            // Search units
            if (!$type || $type === 'unit') {
                $units = Unit::with(['compound.company'])
                    ->where(function($query) use ($search) {
                        if ($search) {
                            $query->where('unit_name', 'LIKE', "%{$search}%")
                                  ->orWhere('unit_code', 'LIKE', "%{$search}%")
                                  ->orWhere('code', 'LIKE', "%{$search}%")
                                  ->orWhere('unit_type', 'LIKE', "%{$search}%")
                                  ->orWhere('usage_type', 'LIKE', "%{$search}%");
                        }
                    })
                    ->limit($perPage)
                    ->get();

                foreach ($units as $unit) {
                    $results[] = [
                        'type' => 'unit',
                        'id' => $unit->id,
                        'name' => $unit->unit_name,
                        'code' => $unit->unit_code,
                        'unit_type' => $unit->unit_type,
                        'usage_type' => $unit->usage_type,
                        'price' => $unit->base_price,
                        'total_price' => $unit->total_price,
                        'available' => (bool)$unit->is_sold == false,
                        'is_sold' => (bool)$unit->is_sold,
                        'status' => $unit->status,
                        'number_of_beds' => $unit->number_of_beds,
                        'compound' => [
                            'id' => $unit->compound->id ?? null,
                            'name' => $unit->compound->project ?? null,
                            'location' => $unit->compound->location ?? null,
                            'company' => [
                                'id' => $unit->compound->company->id ?? null,
                                'name' => $unit->compound->company->name ?? null,
                                'logo' => $unit->compound->company->logo_url ?? null
                            ]
                        ]
                    ];
                }
            }

            return response()->json([
                'status' => true,
                'search_query' => $search,
                'total_results' => count($results),
                'results' => $results
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'error' => 'Database error',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
