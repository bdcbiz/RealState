<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Sale;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class SaleAdminController extends Controller
{
    /**
     * Create a new sale (triggers notification to all buyers)
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'company_id' => 'required|exists:companies,id',
            'sales_person_id' => 'nullable|exists:users,id',
            'sale_type' => 'required|in:unit,compound',
            'unit_id' => 'required_if:sale_type,unit|exists:units,id',
            'compound_id' => 'required_if:sale_type,compound|exists:compounds,id',
            'sale_name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'discount_percentage' => 'required|numeric|min:0|max:100',
            'old_price' => 'required|numeric',
            'new_price' => 'required|numeric',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'is_active' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $sale = Sale::create($request->all());

            // Observer will automatically send FCM notification to all buyers

            return response()->json([
                'success' => true,
                'message' => 'Sale created successfully and notification sent to buyers',
                'data' => $sale
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Failed to create sale',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update an existing sale (triggers notification if activated or discount increased)
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'company_id' => 'sometimes|exists:companies,id',
            'sales_person_id' => 'sometimes|nullable|exists:users,id',
            'sale_type' => 'sometimes|in:unit,compound',
            'unit_id' => 'sometimes|exists:units,id',
            'compound_id' => 'sometimes|exists:compounds,id',
            'sale_name' => 'sometimes|string|max:255',
            'description' => 'sometimes|nullable|string',
            'discount_percentage' => 'sometimes|numeric|min:0|max:100',
            'old_price' => 'sometimes|numeric',
            'new_price' => 'sometimes|numeric',
            'start_date' => 'sometimes|date',
            'end_date' => 'sometimes|date',
            'is_active' => 'sometimes|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $sale = Sale::find($id);

            if (!$sale) {
                return response()->json([
                    'success' => false,
                    'error' => 'Sale not found'
                ], 404);
            }

            $sale->update($request->all());

            // Observer will automatically send FCM notification if activated or discount increased

            return response()->json([
                'success' => true,
                'message' => 'Sale updated successfully',
                'data' => $sale
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Failed to update sale',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete a sale
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        try {
            $sale = Sale::find($id);

            if (!$sale) {
                return response()->json([
                    'success' => false,
                    'error' => 'Sale not found'
                ], 404);
            }

            $sale->delete();

            return response()->json([
                'success' => true,
                'message' => 'Sale deleted successfully'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Failed to delete sale',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
