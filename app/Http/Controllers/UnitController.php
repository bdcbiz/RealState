<?php

namespace App\Http\Controllers;

use App\Models\Unit;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class UnitController extends Controller
{
    /**
     * Display a listing of units with filters
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $query = Unit::with(['compound.company', 'compound.currentSale', 'stage']);

            // Apply filters
            $this->applyFilters($query, $request);

            // Pagination
            $page = $request->get('page', 1);
            $limit = $request->get('limit', 20);
            $total = $query->count();

            $units = $query
                ->orderBy('created_at', 'desc')
                ->skip(($page - 1) * $limit)
                ->take($limit)
                ->get();

            // Process units
            $processedUnits = $units->map(function($unit) {
                return $this->processUnit($unit);
            });

            return response()->json([
                'success' => true,
                'count' => $units->count(),
                'total' => $total,
                'page' => (int)$page,
                'limit' => (int)$limit,
                'total_pages' => ceil($total / $limit),
                'data' => $processedUnits
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Database error',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified unit
     *
     * @param int $id
     * @return JsonResponse
     */
    public function show(int $id): JsonResponse
    {
        try {
            $unit = Unit::with(['compound.company', 'compound.currentSale', 'stage'])->find($id);

            if (!$unit) {
                return response()->json([
                    'success' => false,
                    'error' => 'Unit not found'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $this->processUnit($unit)
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Database error',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Filter units with advanced criteria
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function filter(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            $filters = $request->all();
            $page = $request->get('page', 1);
            $limit = $request->get('limit', 20);

            $query = Unit::with(['compound.company', 'compound.currentSale']);

            // Text filters
            if (!empty($filters['usage_type'])) {
                $query->where('usage_type', $filters['usage_type']);
            }

            if (!empty($filters['unit_type'])) {
                $query->where('unit_type', $filters['unit_type']);
            }

            if (!empty($filters['status'])) {
                $query->where('status', $filters['status']);
            }

            if (!empty($filters['unit_name'])) {
                $query->where('unit_name', 'LIKE', "%{$filters['unit_name']}%");
            }

            if (!empty($filters['building_name'])) {
                $query->where('building_name', 'LIKE', "%{$filters['building_name']}%");
            }

            if (!empty($filters['stage_number'])) {
                $query->where('stage_number', $filters['stage_number']);
            }

            // Numeric filters
            if (isset($filters['number_of_beds'])) {
                $query->where('number_of_beds', (int)$filters['number_of_beds']);
            }

            if (isset($filters['floor_number'])) {
                $query->where('floor_number', (int)$filters['floor_number']);
            }

            if (isset($filters['compound_id'])) {
                $query->where('compound_id', (int)$filters['compound_id']);
            }

            // Boolean filters
            if (isset($filters['available'])) {
                $available = in_array($filters['available'], ['true', '1', 1]) ? 1 : 0;
                $query->where('available', $available);
            }

            if (isset($filters['is_sold'])) {
                $isSold = in_array($filters['is_sold'], ['true', '1', 1]) ? 1 : 0;
                $query->where('is_sold', $isSold);
            }

            // Price range
            if (isset($filters['min_price'])) {
                $query->where('normal_price', '>=', (float)$filters['min_price']);
            }

            if (isset($filters['max_price'])) {
                $query->where('normal_price', '<=', (float)$filters['max_price']);
            }

            if (isset($filters['min_total_pricing'])) {
                $query->where('total_pricing', '>=', (float)$filters['min_total_pricing']);
            }

            if (isset($filters['max_total_pricing'])) {
                $query->where('total_pricing', '<=', (float)$filters['max_total_pricing']);
            }

            // Area filters (if columns exist)
            foreach (['garden', 'roof', 'basement', 'garage'] as $areaType) {
                if (isset($filters["min_{$areaType}_area"])) {
                    $query->where("{$areaType}_area", '>=', (float)$filters["min_{$areaType}_area"]);
                }
                if (isset($filters["max_{$areaType}_area"])) {
                    $query->where("{$areaType}_area", '<=', (float)$filters["max_{$areaType}_area"]);
                }
            }

            $totalUnits = $query->count();

            $units = $query
                ->orderBy('created_at', 'desc')
                ->skip(($page - 1) * $limit)
                ->take($limit)
                ->get();

            $processedUnits = $units->map(function($unit) {
                return $this->processUnit($unit);
            });

            $appliedFilters = array_keys(array_filter($filters, function($key) {
                return !in_array($key, ['page', 'limit']);
            }, ARRAY_FILTER_USE_KEY));

            // Increment search count for the user
            if ($user && $user->role !== 'admin') {
                $user->incrementSearchCount();
            }

            // Get subscription info
            $subscriptionInfo = null;
            if ($user) {
                $subscription = $user->getCurrentSubscription();
                if ($subscription) {
                    $subscriptionInfo = [
                        'plan_name' => $subscription->subscriptionPlan->name,
                        'searches_used' => $subscription->searches_used,
                        'search_limit' => $subscription->subscriptionPlan->search_limit,
                        'remaining_searches' => $subscription->getRemainingSearches(),
                        'expires_at' => $subscription->expires_at?->format('Y-m-d H:i:s'),
                    ];
                }
            }

            return response()->json([
                'success' => true,
                'total_units' => $totalUnits,
                'page' => (int)$page,
                'limit' => (int)$limit,
                'total_pages' => ceil($totalUnits / $limit),
                'filters_applied' => $appliedFilters,
                'units' => $processedUnits,
                'subscription' => $subscriptionInfo
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Database error',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Apply filters to query
     *
     * @param $query
     * @param Request $request
     * @return void
     */
    private function applyFilters($query, Request $request): void
    {
        // Filter by compound
        if ($request->has('compound_id')) {
            $query->where('compound_id', $request->compound_id);
        }

        // Filter by company (through compound relationship)
        if ($request->has('company_id')) {
            $query->whereHas('compound', function($q) use ($request) {
                $q->where('company_id', $request->company_id);
            });
        }

        // Filter by unit type
        if ($request->has('unit_type')) {
            $query->where('unit_type', $request->unit_type);
        }

        // Filter by availability
        if ($request->has('available')) {
            $query->where('available', $request->available);
        }

        // Filter by sold status
        if ($request->has('is_sold')) {
            $query->where('is_sold', $request->is_sold);
        }

        // Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Filter by number of beds
        if ($request->has('number_of_beds')) {
            $query->where('number_of_beds', $request->number_of_beds);
        }

        // Filter by price range
        if ($request->has('min_price')) {
            $query->where('normal_price', '>=', $request->min_price);
        }
        if ($request->has('max_price')) {
            $query->where('normal_price', '<=', $request->max_price);
        }

        // Search by unit code or name
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('unit_code', 'LIKE', "%{$search}%")
                  ->orWhere('unit_name', 'LIKE', "%{$search}%")
                  ->orWhere('code', 'LIKE', "%{$search}%");
            });
        }
    }

    /**
     * Process unit data with images and relationships
     *
     * @param Unit $unit
     * @return array
     */
    private function processUnit(Unit $unit): array
    {
        // Calculate total area if area fields exist
        $totalArea = 0;
        $areaFields = ['garden_area', 'roof_area', 'basement_area', 'garage_area',
                       'uncovered_basement', 'penthouse', 'semi_covered_roof_area',
                       'pergola_area', 'storage_area', 'extra_built_up_area'];

        foreach ($areaFields as $field) {
            if (isset($unit->$field)) {
                $totalArea += (float)$unit->$field;
            }
        }

        // Build compound object
        $compoundData = null;
        if (isset($unit->compound) && $unit->compound) {
            $compoundData = [
                'id' => $unit->compound->id,
                'name' => $unit->compound->name ?? $unit->compound->project ?? null,
                'project' => $unit->compound->project ?? null,
                'location' => $unit->compound->location ?? null,
                'status' => $unit->compound->status ?? null,
                'completion_progress' => $unit->compound->completion_progress ?? null,
                'images' => $unit->compound->images_urls ?? [],
            ];
        }

        // Build company object
        $companyData = null;
        if (isset($unit->compound->company) && $unit->compound->company) {
            $companyData = [
                'id' => $unit->compound->company->id,
                'name' => $unit->compound->company->name,
                'logo' => $unit->compound->company->logo_url ?? null,
                'email' => $unit->compound->company->email ?? null,
            ];
        }

        // Calculate discounted price if compound has active sale
        $originalPrice = $unit->normal_price;
        $discountedPrice = null;
        $discountPercentage = null;
        $saleData = null;

        if (isset($unit->compound->currentSale) && $unit->compound->currentSale) {
            $sale = $unit->compound->currentSale;

            // Check if sale is active and within date range
            $now = now();
            $isActive = $sale->is_active &&
                        $now->greaterThanOrEqualTo($sale->start_date) &&
                        $now->lessThanOrEqualTo($sale->end_date);

            if ($isActive && $originalPrice) {
                $discountPercentage = $sale->discount_percentage;
                $discountedPrice = $originalPrice - ($originalPrice * $discountPercentage / 100);

                $saleData = [
                    'id' => $sale->id,
                    'sale_name' => $sale->sale_name,
                    'description' => $sale->description,
                    'discount_percentage' => $sale->discount_percentage,
                    'start_date' => $sale->start_date->format('Y-m-d'),
                    'end_date' => $sale->end_date->format('Y-m-d'),
                    'is_active' => true,
                ];
            }
        }

        return [
            'id' => $unit->id,
            'compound_id' => $unit->compound_id,
            'compound_name' => $unit->compound->name ?? $unit->compound->project ?? null,
            'compound_location' => $unit->compound->location ?? null,
            'company_id' => $unit->compound->company->id ?? null,
            'company_name' => $unit->compound->company->name ?? null,
            'company_logo' => $unit->compound->company->logo_url ?? null,
            'unit_name' => $unit->unit_name,
            'unit_code' => $unit->unit_code ?? $unit->code ?? null,
            'code' => $unit->code ?? $unit->unit_code ?? null,
            'unit_type' => $unit->unit_type ?? $unit->usage_type,
            'usage_type' => $unit->usage_type,
            'status' => $unit->status,
            'number_of_beds' => $unit->number_of_beds,
            'floor_number' => $unit->floor_number,
            'original_price' => $originalPrice,
            'normal_price' => $discountedPrice ?? $originalPrice, // Show discounted price if sale exists
            'discounted_price' => $discountedPrice,
            'discount_percentage' => $discountPercentage,
            'has_active_sale' => !is_null($saleData),
            'total_pricing' => $unit->total_pricing,
            'total_area' => $totalArea,
            'available' => (bool)!$unit->is_sold,
            'is_sold' => (bool)$unit->is_sold,
            'images' => $unit->images_urls ?? [],
            'created_at' => $unit->created_at,
            'updated_at' => $unit->updated_at,
            // Localized fields
            'unit_name_localized' => $unit->unit_name_localized ?? $unit->unit_name,
            'unit_type_localized' => $unit->unit_type_localized ?? $unit->unit_type ?? $unit->usage_type,
            'usage_type_localized' => $unit->usage_type_localized ?? $unit->usage_type,
            'status_localized' => $unit->status_localized ?? $unit->status,
            // Structured objects
            'compound' => $compoundData,
            'company' => $companyData,
            'sale' => $saleData,
        ];
    }
}
