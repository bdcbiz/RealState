<?php

namespace App\Http\Controllers;

use App\Models\Stage;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class StageController extends Controller
{
    /**
     * Display a listing of stages
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $query = Stage::with(['compound', 'units']);

            // Filter by compound
            if ($request->has('compound_id')) {
                $query->where('compound_id', $request->compound_id);
            }

            // Pagination
            $page = $request->get('page', 1);
            $limit = $request->get('limit', 10);
            $total = $query->count();

            $stages = $query
                ->withCount([
                    'units as total_units',
                    'units as available_units' => function($q) {
                        $q->where('status', 'available');
                    }
                ])
                ->skip(($page - 1) * $limit)
                ->take($limit)
                ->get();

            $processedStages = $stages->map(function($stage) {
                return [
                    'id' => $stage->id,
                    'compound_id' => $stage->compound_id,
                    'compound_name' => $stage->compound->name ?? $stage->compound->project ?? null,
                    'stage_name' => $stage->stage_name,
                    'completion_progress' => $stage->completion_progress,
                    'planned_delivery_date' => $stage->planned_delivery_date,
                    'actual_delivery_date' => $stage->actual_delivery_date,
                    'land_area' => $stage->land_area,
                    'built_area' => $stage->built_area,
                    'floors' => $stage->floors,
                    'total_units' => $stage->total_units ?? 0,
                    'available_units' => $stage->available_units ?? 0,
                    'created_at' => $stage->created_at,
                    'updated_at' => $stage->updated_at
                ];
            });

            return response()->json([
                'data' => $processedStages,
                'pagination' => [
                    'total' => $total,
                    'page' => (int)$page,
                    'limit' => (int)$limit,
                    'total_pages' => ceil($total / $limit)
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Database error',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified stage with units
     *
     * @param int $id
     * @return JsonResponse
     */
    public function show(int $id): JsonResponse
    {
        try {
            $stage = Stage::with(['compound', 'units'])->find($id);

            if (!$stage) {
                return response()->json([
                    'error' => 'Stage not found'
                ], 404);
            }

            return response()->json([
                'id' => $stage->id,
                'compound_id' => $stage->compound_id,
                'compound_name' => $stage->compound->name ?? $stage->compound->project ?? null,
                'stage_name' => $stage->stage_name,
                'completion_progress' => $stage->completion_progress,
                'planned_delivery_date' => $stage->planned_delivery_date,
                'actual_delivery_date' => $stage->actual_delivery_date,
                'land_area' => $stage->land_area,
                'built_area' => $stage->built_area,
                'floors' => $stage->floors,
                'units' => $stage->units,
                'created_at' => $stage->created_at,
                'updated_at' => $stage->updated_at
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Database error',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a newly created stage
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'compound_id' => 'required|integer|exists:compounds,id',
                'stage_name' => 'required|string|max:255'
            ]);

            $stage = Stage::create($request->all());

            return response()->json([
                'message' => 'Stage created successfully',
                'id' => $stage->id
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to create stage',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update the specified stage
     *
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function update(Request $request, int $id): JsonResponse
    {
        try {
            $stage = Stage::find($id);

            if (!$stage) {
                return response()->json([
                    'error' => 'Stage not found'
                ], 404);
            }

            $stage->update($request->all());

            return response()->json([
                'message' => 'Stage updated successfully'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to update stage',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified stage
     *
     * @param int $id
     * @return JsonResponse
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            $stage = Stage::find($id);

            if (!$stage) {
                return response()->json([
                    'error' => 'Stage not found'
                ], 404);
            }

            $stage->delete();

            return response()->json([
                'message' => 'Stage deleted successfully'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to delete stage',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
