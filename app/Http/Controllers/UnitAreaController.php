<?php

namespace App\Http\Controllers;

use App\Models\UnitArea;
use App\Models\Unit;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class UnitAreaController extends Controller
{
    /**
     * Display the unit area for a specific unit
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function show(Request $request): JsonResponse
    {
        try {
            $unitId = $request->get('unit_id');
            $id = $request->get('id');

            if (!$unitId && !$id) {
                return response()->json([
                    'error' => 'unit_id or id parameter is required'
                ], 400);
            }

            if ($unitId) {
                $areas = UnitArea::where('unit_id', $unitId)->first();
            } else {
                $areas = UnitArea::find($id);
            }

            if (!$areas) {
                return response()->json([
                    'error' => 'Unit areas not found'
                ], 404);
            }

            return response()->json($areas, 200);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to fetch unit areas',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store newly created unit areas
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'unit_id' => 'required|integer|exists:units,id'
            ]);

            // Check if unit exists
            $unit = Unit::find($request->unit_id);
            if (!$unit) {
                return response()->json([
                    'error' => 'Unit not found'
                ], 404);
            }

            // Check if areas already exist
            $existing = UnitArea::where('unit_id', $request->unit_id)->first();
            if ($existing) {
                return response()->json([
                    'error' => 'Unit areas already exist. Use PUT to update.'
                ], 409);
            }

            $areas = UnitArea::create([
                'unit_id' => $request->unit_id,
                'built_area' => $request->built_area,
                'land_area' => $request->land_area,
                'total_area' => $request->total_area,
                'basement_area' => $request->basement_area,
                'uncovered_basement' => $request->uncovered_basement,
                'penthouse_area' => $request->penthouse_area,
                'semi_covered_roof' => $request->semi_covered_roof,
                'roof_area' => $request->roof_area,
                'garage_area' => $request->garage_area,
                'pergola_area' => $request->pergola_area,
                'storage_area' => $request->storage_area,
                'garden_area' => $request->garden_area,
                'extra_built_up' => $request->extra_built_up
            ]);

            return response()->json([
                'message' => 'Unit areas created successfully',
                'id' => $areas->id
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to create unit areas',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update the specified unit areas
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function update(Request $request): JsonResponse
    {
        try {
            $unitId = $request->get('unit_id');
            $id = $request->get('id');

            if (!$unitId && !$id) {
                return response()->json([
                    'error' => 'unit_id or id parameter is required'
                ], 400);
            }

            if ($unitId) {
                $areas = UnitArea::where('unit_id', $unitId)->first();
            } else {
                $areas = UnitArea::find($id);
            }

            if (!$areas) {
                return response()->json([
                    'error' => 'Unit areas not found'
                ], 404);
            }

            $updateData = [];
            $fields = [
                'built_area', 'land_area', 'total_area', 'basement_area',
                'uncovered_basement', 'penthouse_area', 'semi_covered_roof',
                'roof_area', 'garage_area', 'pergola_area', 'storage_area',
                'garden_area', 'extra_built_up'
            ];

            foreach ($fields as $field) {
                if ($request->has($field)) {
                    $updateData[$field] = $request->$field;
                }
            }

            if (empty($updateData)) {
                return response()->json([
                    'error' => 'No fields to update'
                ], 400);
            }

            $areas->update($updateData);

            return response()->json([
                'message' => 'Unit areas updated successfully'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to update unit areas',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified unit areas
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function destroy(Request $request): JsonResponse
    {
        try {
            $unitId = $request->get('unit_id');
            $id = $request->get('id');

            if (!$unitId && !$id) {
                return response()->json([
                    'error' => 'unit_id or id parameter is required'
                ], 400);
            }

            if ($unitId) {
                $deleted = UnitArea::where('unit_id', $unitId)->delete();
            } else {
                $deleted = UnitArea::where('id', $id)->delete();
            }

            if (!$deleted) {
                return response()->json([
                    'error' => 'Unit areas not found'
                ], 404);
            }

            return response()->json([
                'message' => 'Unit areas deleted successfully'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to delete unit areas',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
