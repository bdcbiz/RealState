<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class SalesController extends Controller
{
    /**
     * Display a listing of sales/promotions
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $baseUrl = url('/storage');

            $query = Sale::with(['company', 'salesPerson', 'unit.compound', 'compound']);

            // Filter by sale type
            if ($request->has('sale_type')) {
                $query->where('sale_type', $request->sale_type);
            }

            // Filter by company
            if ($request->has('company_id')) {
                $query->where('company_id', $request->company_id);
            }

            // Filter active sales only
            $activeOnly = $request->get('active_only', 'true');
            if ($activeOnly === 'true' || $activeOnly === '1') {
                $query->where('is_active', 1)
                      ->whereDate('start_date', '<=', now())
                      ->whereDate('end_date', '>=', now());
            }

            // Pagination
            $page = $request->get('page', 1);
            $limit = $request->get('limit', 20);
            $totalSales = $query->count();

            $sales = $query
                ->orderBy('created_at', 'desc')
                ->skip(($page - 1) * $limit)
                ->take($limit)
                ->get();

            // Process sales
            $processedSales = $sales->map(function($sale) use ($baseUrl) {
                // Get item details based on type
                $itemName = null;
                $compoundName = null;
                $images = [];

                // First, try to get images from sale itself
                if ($sale->images && is_array($sale->images)) {
                    $images = $this->processImages($sale->images, $baseUrl);
                }

                if ($sale->sale_type === 'unit' && $sale->unit) {
                    $itemName = $sale->unit->unit_name;
                    $compoundName = $sale->unit->compound->project ?? null;
                    // Only get unit images if sale doesn't have images
                    if (empty($images) && $sale->unit->images) {
                        $images = $this->processImages($sale->unit->images, $baseUrl);
                    }
                } elseif ($sale->sale_type === 'compound' && $sale->compound) {
                    $itemName = $sale->compound->project;
                    // Only get compound images if sale doesn't have images
                    if (empty($images) && $sale->compound->images) {
                        $images = $this->processImages($sale->compound->images, $baseUrl);
                    }
                }

                // Process sales person image
                $salesPersonImage = null;
                if ($sale->salesPerson && $sale->salesPerson->image) {
                    $img = $sale->salesPerson->image;
                    $salesPersonImage = (strpos($img, 'http://') === 0 || strpos($img, 'https://') === 0)
                        ? $img
                        : $baseUrl . '/' . $img;
                }

                // Calculate savings
                $savings = $sale->old_price - $sale->new_price;

                // Check if currently active
                $now = now();
                $isCurrentlyActive = $sale->is_active &&
                                    $sale->start_date <= $now &&
                                    $sale->end_date >= $now;

                $daysRemaining = $isCurrentlyActive
                    ? $sale->end_date->diffInDays($now)
                    : 0;

                return [
                    'id' => $sale->id,
                    'company_id' => $sale->company_id,
                    'company_name' => $sale->company->name ?? null,
                    'company_logo' => $sale->company->logo_url ?? null,
                    'sales_person' => $sale->salesPerson ? [
                        'id' => $sale->salesPerson->id,
                        'name' => $sale->salesPerson->name,
                        'email' => $sale->salesPerson->email,
                        'phone' => $sale->salesPerson->phone,
                        'image' => $salesPersonImage
                    ] : null,
                    'sale_type' => $sale->sale_type,
                    'unit_id' => $sale->unit_id,
                    'compound_id' => $sale->compound_id,
                    'item_name' => $itemName,
                    'compound_name' => $compoundName,
                    'sale_name' => $sale->sale_name,
                    'description' => $sale->description,
                    'discount_percentage' => (float)$sale->discount_percentage,
                    'old_price' => (float)$sale->old_price,
                    'new_price' => (float)$sale->new_price,
                    'savings' => (float)$savings,
                    'start_date' => $sale->start_date->format('Y-m-d'),
                    'end_date' => $sale->end_date->format('Y-m-d'),
                    'is_active' => (bool)$sale->is_active,
                    'is_currently_active' => $isCurrentlyActive,
                    'days_remaining' => (int)$daysRemaining,
                    'images' => $images,
                    'created_at' => $sale->created_at,
                    'updated_at' => $sale->updated_at
                ];
            });

            return response()->json([
                'success' => true,
                'total_sales' => $totalSales,
                'page' => (int)$page,
                'limit' => (int)$limit,
                'total_pages' => ceil($totalSales / $limit),
                'sales' => $processedSales
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
     * Display a specific sale by ID
     *
     * @param int $id
     * @return JsonResponse
     */
    public function show(int $id): JsonResponse
    {
        try {
            $baseUrl = url('/storage');

            $sale = Sale::with(['company', 'salesPerson', 'unit.compound', 'compound'])
                ->find($id);

            if (!$sale) {
                return response()->json([
                    'success' => false,
                    'error' => 'Sale not found'
                ], 404);
            }

            // Get item details based on type
            $itemName = null;
            $compoundName = null;
            $images = [];

            // First, try to get images from sale itself
            if ($sale->images && is_array($sale->images)) {
                $images = $this->processImages($sale->images, $baseUrl);
            }

            if ($sale->sale_type === 'unit' && $sale->unit) {
                $itemName = $sale->unit->unit_name;
                $compoundName = $sale->unit->compound->project ?? null;
                // Only get unit images if sale doesn't have images
                if (empty($images) && $sale->unit->images) {
                    $images = $this->processImages($sale->unit->images, $baseUrl);
                }
            } elseif ($sale->sale_type === 'compound' && $sale->compound) {
                $itemName = $sale->compound->project;
                // Only get compound images if sale doesn't have images
                if (empty($images) && $sale->compound->images) {
                    $images = $this->processImages($sale->compound->images, $baseUrl);
                }
            }

            // Process sales person image
            $salesPersonImage = null;
            if ($sale->salesPerson && $sale->salesPerson->image) {
                $img = $sale->salesPerson->image;
                $salesPersonImage = (strpos($img, 'http://') === 0 || strpos($img, 'https://') === 0)
                    ? $img
                    : $baseUrl . '/' . $img;
            }

            // Calculate savings
            $savings = $sale->old_price - $sale->new_price;

            // Check if currently active
            $now = now();
            $isCurrentlyActive = $sale->is_active &&
                                $sale->start_date <= $now &&
                                $sale->end_date >= $now;

            $daysRemaining = $isCurrentlyActive
                ? $sale->end_date->diffInDays($now)
                : 0;

            $saleData = [
                'id' => $sale->id,
                'company_id' => $sale->company_id,
                'company_name' => $sale->company->name ?? null,
                'company_logo' => $sale->company->logo_url ?? null,
                'sales_person' => $sale->salesPerson ? [
                    'id' => $sale->salesPerson->id,
                    'name' => $sale->salesPerson->name,
                    'email' => $sale->salesPerson->email,
                    'phone' => $sale->salesPerson->phone,
                    'image' => $salesPersonImage
                ] : null,
                'sale_type' => $sale->sale_type,
                'unit_id' => $sale->unit_id,
                'compound_id' => $sale->compound_id,
                'item_name' => $itemName,
                'compound_name' => $compoundName,
                'sale_name' => $sale->sale_name,
                'description' => $sale->description,
                'discount_percentage' => (float)$sale->discount_percentage,
                'old_price' => (float)$sale->old_price,
                'new_price' => (float)$sale->new_price,
                'savings' => (float)$savings,
                'start_date' => $sale->start_date->format('Y-m-d'),
                'end_date' => $sale->end_date->format('Y-m-d'),
                'is_active' => (bool)$sale->is_active,
                'is_currently_active' => $isCurrentlyActive,
                'days_remaining' => (int)$daysRemaining,
                'images' => $images,
                'created_at' => $sale->created_at,
                'updated_at' => $sale->updated_at
            ];

            return response()->json([
                'success' => true,
                'data' => $saleData
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
     * Get companies with their active sales
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function getCompaniesWithSales(Request $request): JsonResponse
    {
        try {
            $baseUrl = url('/storage');

            $query = \App\Models\Company::with(['sales' => function($query) use ($request) {
                // Filter by active sales only if specified
                $activeOnly = $request->get('active_only', 'true');
                if ($activeOnly === 'true' || $activeOnly === '1') {
                    $query->where('is_active', 1)
                          ->whereDate('start_date', '<=', now())
                          ->whereDate('end_date', '>=', now());
                }
                $query->with(['unit.compound', 'compound', 'salesPerson'])
                      ->orderBy('created_at', 'desc');
            }]);

            // Filter companies that have sales
            if ($request->get('has_sales', 'false') === 'true') {
                $query->has('sales');
            }

            $page = $request->get('page', 1);
            $limit = $request->get('limit', 20);
            $totalCompanies = $query->count();

            $companies = $query
                ->orderBy('name')
                ->skip(($page - 1) * $limit)
                ->take($limit)
                ->get();

            $processedCompanies = $companies->map(function($company) use ($baseUrl) {
                $sales = $company->sales->map(function($sale) use ($baseUrl) {
                    // Get item details based on type
                    $itemName = null;
                    $compoundName = null;
                    $images = [];

                    // First, try to get images from sale itself
                    if ($sale->images && is_array($sale->images)) {
                        $images = $this->processImages($sale->images, $baseUrl);
                    }

                    if ($sale->sale_type === 'unit' && $sale->unit) {
                        $itemName = $sale->unit->unit_name;
                        $compoundName = $sale->unit->compound->project ?? null;
                        // Only get unit images if sale doesn't have images
                        if (empty($images) && $sale->unit->images) {
                            $images = $this->processImages($sale->unit->images, $baseUrl);
                        }
                    } elseif ($sale->sale_type === 'compound' && $sale->compound) {
                        $itemName = $sale->compound->project;
                        // Only get compound images if sale doesn't have images
                        if (empty($images) && $sale->compound->images) {
                            $images = $this->processImages($sale->compound->images, $baseUrl);
                        }
                    }

                    // Process sales person image
                    $salesPersonImage = null;
                    if ($sale->salesPerson && $sale->salesPerson->image) {
                        $img = $sale->salesPerson->image;
                        $salesPersonImage = (strpos($img, 'http://') === 0 || strpos($img, 'https://') === 0)
                            ? $img
                            : $baseUrl . '/' . $img;
                    }

                    $savings = $sale->old_price - $sale->new_price;
                    $now = now();
                    $isCurrentlyActive = $sale->is_active &&
                                        $sale->start_date <= $now &&
                                        $sale->end_date >= $now;
                    $daysRemaining = $isCurrentlyActive
                        ? $sale->end_date->diffInDays($now)
                        : 0;

                    return [
                        'id' => $sale->id,
                        'sales_person' => $sale->salesPerson ? [
                            'id' => $sale->salesPerson->id,
                            'name' => $sale->salesPerson->name,
                            'email' => $sale->salesPerson->email,
                            'phone' => $sale->salesPerson->phone,
                            'image' => $salesPersonImage
                        ] : null,
                        'sale_type' => $sale->sale_type,
                        'unit_id' => $sale->unit_id,
                        'compound_id' => $sale->compound_id,
                        'item_name' => $itemName,
                        'compound_name' => $compoundName,
                        'sale_name' => $sale->sale_name,
                        'description' => $sale->description,
                        'discount_percentage' => (float)$sale->discount_percentage,
                        'old_price' => (float)$sale->old_price,
                        'new_price' => (float)$sale->new_price,
                        'savings' => (float)$savings,
                        'start_date' => $sale->start_date->format('Y-m-d'),
                        'end_date' => $sale->end_date->format('Y-m-d'),
                        'is_active' => (bool)$sale->is_active,
                        'is_currently_active' => $isCurrentlyActive,
                        'days_remaining' => (int)$daysRemaining,
                        'images' => $images
                    ];
                });

                return [
                    'id' => $company->id,
                    'name' => $company->name,
                    'logo' => $company->logo_url,
                    'email' => $company->email,
                    'phone' => $company->phone,
                    'total_sales' => $sales->count(),
                    'sales' => $sales
                ];
            });

            return response()->json([
                'success' => true,
                'total_companies' => $totalCompanies,
                'page' => (int)$page,
                'limit' => (int)$limit,
                'total_pages' => ceil($totalCompanies / $limit),
                'data' => $processedCompanies
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
     * Process image URLs
     *
     * @param string|array $imagesData
     * @param string $baseUrl
     * @return array
     */
    private function processImages($imagesData, string $baseUrl): array
    {
        $images = [];
        
        // Handle both string (JSON) and array inputs
        if (is_string($imagesData)) {
            $imageArray = json_decode($imagesData, true);
        } else {
            $imageArray = $imagesData;
        }

        if (is_array($imageArray)) {
            foreach ($imageArray as $img) {
                // Skip empty images
                if (empty($img)) {
                    continue;
                }

                // If already a full URL, use as-is
                if (strpos($img, 'http://') === 0 || strpos($img, 'https://') === 0) {
                    $images[] = $img;
                } else {
                    // Strip out 'storage/app/public/' if present
                    $cleanPath = str_replace('storage/app/public/', '', $img);
                    $cleanPath = str_replace('storage/app/', '', $cleanPath);
                    $cleanPath = ltrim($cleanPath, '/');

                    $images[] = $baseUrl . '/' . $cleanPath;
                }
            }
        }

        return $images;
    }
}
