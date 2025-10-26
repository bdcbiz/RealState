<?php

namespace App\Http\Controllers;

use App\Models\Favorite;
use App\Models\Unit;
use App\Models\Compound;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class FavoriteController extends Controller
{
    /**
     * Display a listing of user's favorites.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        try {
            // Get user_id from request parameter or authenticated user
            $userId = $request->get('user_id', $request->user()->id);

            $favorites = Favorite::where('user_id', $userId)
                ->with(['unit.compound', 'compound'])
                ->orderBy('created_at', 'desc')
                ->get();

            return response()->json([
                'success' => true,
                'message' => 'Favorites retrieved successfully',
                'data' => [
                    'favorites' => $favorites,
                    'count' => $favorites->count()
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch favorites',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a newly created favorite.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        try {
            // Get user_id from request parameter or authenticated user
            $userId = $request->get('user_id', $request->user()->id);

            // Validate - can have unit_id, compound_id, or both
            $request->validate([
                'unit_id' => 'nullable|integer|exists:units,id',
                'compound_id' => 'nullable|integer|exists:compounds,id'
            ]);

            // Ensure at least one is provided
            if (!$request->has('unit_id') && !$request->has('compound_id')) {
                return response()->json([
                    'success' => false,
                    'message' => 'At least unit_id or compound_id is required'
                ], 400);
            }

            // Check if unit or compound exists
            if ($request->has('unit_id')) {
                $unit = Unit::find($request->unit_id);
                if (!$unit) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Unit not found'
                    ], 404);
                }
            }

            if ($request->has('compound_id')) {
                $compound = Compound::find($request->compound_id);
                if (!$compound) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Compound not found'
                    ], 404);
                }
            }

            // Check if already favorited (exact combination of unit_id and compound_id)
            $queryCheck = Favorite::where('user_id', $userId);

            if ($request->has('unit_id')) {
                $queryCheck->where('unit_id', $request->unit_id);
            } else {
                $queryCheck->whereNull('unit_id');
            }

            if ($request->has('compound_id')) {
                $queryCheck->where('compound_id', $request->compound_id);
            } else {
                $queryCheck->whereNull('compound_id');
            }

            $existing = $queryCheck->first();

            if ($existing) {
                return response()->json([
                    'success' => false,
                    'message' => 'Already in favorites'
                ], 409);
            }

            // Create favorite
            $favorite = Favorite::create([
                'user_id' => $userId,
                'unit_id' => $request->unit_id ?? null,
                'compound_id' => $request->compound_id ?? null
            ]);

            // Load relationships
            $favorite->load(['unit.compound', 'compound']);

            // Determine message based on what was saved
            $itemType = 'Item';
            if ($request->has('unit_id') && $request->has('compound_id')) {
                $itemType = 'Unit and Compound';
            } elseif ($request->has('unit_id')) {
                $itemType = 'Unit';
            } elseif ($request->has('compound_id')) {
                $itemType = 'Compound';
            }

            return response()->json([
                'success' => true,
                'message' => $itemType . ' added to favorites',
                'data' => [
                    'favorite' => $favorite
                ]
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to add favorite',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified favorite.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function destroy(Request $request): JsonResponse
    {
        try {
            // Get user_id from request parameter or authenticated user
            $userId = $request->get('user_id', $request->user()->id);

            // Must have at least unit_id or compound_id
            if (!$request->has('unit_id') && !$request->has('compound_id')) {
                return response()->json([
                    'success' => false,
                    'message' => 'At least unit_id or compound_id is required'
                ], 400);
            }

            // Build query matching exact combination
            $queryDelete = Favorite::where('user_id', $userId);

            if ($request->has('unit_id')) {
                $queryDelete->where('unit_id', $request->unit_id);
            } else {
                $queryDelete->whereNull('unit_id');
            }

            if ($request->has('compound_id')) {
                $queryDelete->where('compound_id', $request->compound_id);
            } else {
                $queryDelete->whereNull('compound_id');
            }

            $deleted = $queryDelete->delete();

            if (!$deleted) {
                return response()->json([
                    'success' => false,
                    'message' => 'Favorite not found'
                ], 404);
            }

            // Determine message based on what was deleted
            $itemType = 'Item';
            if ($request->has('unit_id') && $request->has('compound_id')) {
                $itemType = 'Unit and Compound';
            } elseif ($request->has('unit_id')) {
                $itemType = 'Unit';
            } elseif ($request->has('compound_id')) {
                $itemType = 'Compound';
            }

            return response()->json([
                'success' => true,
                'message' => $itemType . ' removed from favorites'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to remove favorite',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
