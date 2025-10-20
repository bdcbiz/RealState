<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Unit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class UnitAdminController extends Controller
{
    /**
     * Create a new unit (triggers notification to all buyers)
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'compound_id' => 'required|exists:compounds,id',
            'unit_name' => 'required|string|max:255',
            'unit_code' => 'nullable|string|max:100',
            'unit_type' => 'nullable|string|max:100',
            'usage_type' => 'nullable|string|max:100',
            'status' => 'nullable|string|max:100',
            'base_price' => 'nullable|numeric',
            'total_price' => 'nullable|numeric',
            'number_of_beds' => 'nullable|integer',
            'is_sold' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $unit = Unit::create($request->all());

            // Observer will automatically send FCM notification to all buyers

            return response()->json([
                'success' => true,
                'message' => 'Unit created successfully and notification sent to buyers',
                'data' => $unit
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Failed to create unit',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update an existing unit (triggers notification if price/status changes)
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'compound_id' => 'sometimes|exists:compounds,id',
            'unit_name' => 'sometimes|string|max:255',
            'unit_code' => 'sometimes|string|max:100',
            'unit_type' => 'sometimes|string|max:100',
            'usage_type' => 'sometimes|string|max:100',
            'status' => 'sometimes|string|max:100',
            'base_price' => 'sometimes|numeric',
            'total_price' => 'sometimes|numeric',
            'normal_price' => 'sometimes|numeric',
            'number_of_beds' => 'sometimes|integer',
            'is_sold' => 'sometimes|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $unit = Unit::find($id);

            if (!$unit) {
                return response()->json([
                    'success' => false,
                    'error' => 'Unit not found'
                ], 404);
            }

            $unit->update($request->all());

            // Observer will automatically send FCM notification if significant fields changed

            return response()->json([
                'success' => true,
                'message' => 'Unit updated successfully',
                'data' => $unit
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Failed to update unit',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete a unit
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        try {
            $unit = Unit::find($id);

            if (!$unit) {
                return response()->json([
                    'success' => false,
                    'error' => 'Unit not found'
                ], 404);
            }

            $unit->delete();

            return response()->json([
                'success' => true,
                'message' => 'Unit deleted successfully'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Failed to delete unit',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
