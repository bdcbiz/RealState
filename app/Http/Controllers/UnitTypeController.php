<?php

namespace App\Http\Controllers;

use App\Models\UnitType;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class UnitTypeController extends Controller
{
    /**
     * Display a listing of unit types
     *
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        try {
            $unitTypes = UnitType::orderBy('name')->get();

            return response()->json([
                'data' => $unitTypes
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to fetch unit types',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified unit type
     *
     * @param int $id
     * @return JsonResponse
     */
    public function show(int $id): JsonResponse
    {
        try {
            $unitType = UnitType::find($id);

            if (!$unitType) {
                return response()->json([
                    'error' => 'Unit type not found'
                ], 404);
            }

            return response()->json($unitType, 200);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to fetch unit type',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a newly created unit type
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'name' => 'required|string|max:255'
            ]);

            $unitType = UnitType::create([
                'name' => $request->name,
                'category' => $request->category,
                'description' => $request->description
            ]);

            return response()->json([
                'message' => 'Unit type created successfully',
                'id' => $unitType->id
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to create unit type',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update the specified unit type
     *
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function update(Request $request, int $id): JsonResponse
    {
        try {
            $unitType = UnitType::find($id);

            if (!$unitType) {
                return response()->json([
                    'error' => 'Unit type not found'
                ], 404);
            }

            $unitType->update([
                'name' => $request->name ?? $unitType->name,
                'category' => $request->category ?? $unitType->category,
                'description' => $request->description ?? $unitType->description
            ]);

            return response()->json([
                'message' => 'Unit type updated successfully'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to update unit type',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified unit type
     *
     * @param int $id
     * @return JsonResponse
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            $unitType = UnitType::find($id);

            if (!$unitType) {
                return response()->json([
                    'error' => 'Unit type not found'
                ], 404);
            }

            $unitType->delete();

            return response()->json([
                'message' => 'Unit type deleted successfully'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to delete unit type',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
