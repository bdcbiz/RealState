<?php

namespace App\Http\Controllers;

use App\Models\Compound;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class CompoundController extends Controller
{
    /**
     * Display a listing of compounds with filters and pagination.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $query = Compound::with('company:id,name,logo')
                ->withCount([
                    'units as total_units',
                    'units as sold_units' => function($q) {
                        $q->where('is_sold', 1);
                    },
                    'units as available_units' => function($q) {
                        $q->where('is_sold', 0);
                    }
                ]);

            // Filter by company
            if ($request->has('company_id')) {
                $query->where('company_id', $request->company_id);
            }

            // Filter by location
            if ($request->has('location')) {
                $query->where('location', 'LIKE', '%' . $request->location . '%');
            }

            // Filter by sold status
            if ($request->has('is_sold')) {
                $query->where('is_sold', $request->is_sold);
            }

            // Pagination
            $page = $request->get('page', 1);
            $limit = $request->get('limit', 20);

            $total = $query->count();

            $compounds = $query
                ->orderBy('project', 'asc')
                ->skip(($page - 1) * $limit)
                ->take($limit)
                ->get();

            // Process images and company logo for each compound
            $compounds->each(function($compound) {
                $compound->images = $compound->images_urls;

                if ($compound->company) {
                    $compound->company_name = $compound->company->name;
                    $compound->company_logo = $compound->company->logo;
                    $compound->company_logo_url = $compound->company->logo_url;
                }

                unset($compound->company);
            });

            return response()->json([
                'success' => true,
                'count' => $compounds->count(),
                'total' => $total,
                'page' => (int)$page,
                'limit' => (int)$limit,
                'total_pages' => ceil($total / $limit),
                'data' => $compounds
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
     * Display the specified compound with details.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function show(int $id): JsonResponse
    {
        try {
            $compound = Compound::with('company:id,name,logo')
                ->withCount([
                    'units as total_units',
                    'units as sold_units' => function($q) {
                        $q->where('is_sold', 1);
                    },
                    'units as available_units' => function($q) {
                        $q->where('is_sold', 0);
                    }
                ])
                ->find($id);

            if (!$compound) {
                return response()->json([
                    'success' => false,
                    'error' => 'Compound not found'
                ], 404);
            }

            // Process images
            $compound->images = $compound->images_urls;

            // Add company details
            if ($compound->company) {
                $compound->company_name = $compound->company->name;
                $compound->company_logo = $compound->company->logo;
                $compound->company_logo_url = $compound->company->logo_url;

                unset($compound->company);
            }

            return response()->json([
                'success' => true,
                'data' => $compound
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Database error',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
