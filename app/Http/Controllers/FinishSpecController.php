<?php

namespace App\Http\Controllers;

use App\Models\FinishSpec;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class FinishSpecController extends Controller
{
    /**
     * Display a listing of finish specs.
     *
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        try {
            $finishSpecs = FinishSpec::orderBy('name')->get();

            return response()->json([
                'data' => $finishSpecs
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to fetch finish specs',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a newly created finish spec.
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

            $finishSpec = FinishSpec::create([
                'name' => $request->name,
                'description' => $request->description,
                'base_price' => $request->base_price
            ]);

            return response()->json([
                'message' => 'Finish spec created successfully',
                'id' => $finishSpec->id
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to create finish spec',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified finish spec.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function show(int $id): JsonResponse
    {
        try {
            $finishSpec = FinishSpec::find($id);

            if (!$finishSpec) {
                return response()->json([
                    'error' => 'Finish spec not found'
                ], 404);
            }

            return response()->json($finishSpec, 200);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to fetch finish spec',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update the specified finish spec.
     *
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function update(Request $request, int $id): JsonResponse
    {
        try {
            $finishSpec = FinishSpec::find($id);

            if (!$finishSpec) {
                return response()->json([
                    'error' => 'Finish spec not found'
                ], 404);
            }

            $finishSpec->update([
                'name' => $request->name ?? $finishSpec->name,
                'description' => $request->description ?? $finishSpec->description,
                'base_price' => $request->base_price ?? $finishSpec->base_price
            ]);

            return response()->json([
                'message' => 'Finish spec updated successfully'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to update finish spec',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified finish spec.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            $finishSpec = FinishSpec::find($id);

            if (!$finishSpec) {
                return response()->json([
                    'error' => 'Finish spec not found'
                ], 404);
            }

            $finishSpec->delete();

            return response()->json([
                'message' => 'Finish spec deleted successfully'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to delete finish spec',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
