<?php

namespace App\Http\Controllers;

use App\Models\Favorite;
use App\Models\Unit;
use App\Models\Compound;
use App\Models\Stage;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

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
            // In production, get user_id from authenticated user
            $userId = $request->get('user_id');

            if (!$userId) {
                return response()->json([
                    'error' => 'User ID is required'
                ], 401);
            }

            $query = Favorite::where('user_id', $userId);

            // Filter by type
            if ($request->has('type')) {
                $query->where('favoritable_type', $request->type);
            }

            $favorites = $query->orderBy('created_at', 'desc')->get();

            // Load item details for each favorite
            $favorites->each(function($favorite) {
                $itemDetails = null;

                switch($favorite->favoritable_type) {
                    case 'unit':
                        $unit = Unit::with(['stage.compound'])
                            ->find($favorite->favoritable_id);
                        if ($unit) {
                            $itemDetails = [
                                'id' => $unit->id,
                                'unit_code' => $unit->unit_code,
                                'status' => $unit->status,
                                'base_price' => $unit->base_price,
                                'total_price' => $unit->total_price,
                                'compound_name' => $unit->stage->compound->name ?? null,
                                'stage_name' => $unit->stage->stage_name ?? null
                            ];
                        }
                        break;

                    case 'compound':
                        $compound = Compound::find($favorite->favoritable_id);
                        if ($compound) {
                            $itemDetails = [
                                'id' => $compound->id,
                                'name' => $compound->name,
                                'location' => $compound->location,
                                'logo' => $compound->logo
                            ];
                        }
                        break;

                    case 'stage':
                        $stage = Stage::with('compound')
                            ->find($favorite->favoritable_id);
                        if ($stage) {
                            $itemDetails = [
                                'id' => $stage->id,
                                'stage_name' => $stage->stage_name,
                                'compound_name' => $stage->compound->name ?? null,
                                'completion_progress' => $stage->completion_progress
                            ];
                        }
                        break;
                }

                $favorite->item_details = $itemDetails;
            });

            return response()->json([
                'data' => $favorites
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to fetch favorites',
                'message' => $e->getMessage()
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
            // In production, get user_id from authenticated user
            $userId = $request->get('user_id');

            if (!$userId) {
                return response()->json([
                    'error' => 'User ID is required'
                ], 401);
            }

            $request->validate([
                'type' => 'required|in:unit,compound,stage',
                'id' => 'required|integer'
            ]);

            // Validate type and check if item exists
            $table = $request->type === 'unit' ? Unit::class :
                    ($request->type === 'compound' ? Compound::class : Stage::class);

            $item = $table::find($request->id);
            if (!$item) {
                return response()->json([
                    'error' => ucfirst($request->type) . ' not found'
                ], 404);
            }

            // Check if already favorited
            $existing = Favorite::where([
                'user_id' => $userId,
                'favoritable_type' => $request->type,
                'favoritable_id' => $request->id
            ])->first();

            if ($existing) {
                return response()->json([
                    'error' => 'Already in favorites'
                ], 409);
            }

            // Create favorite
            $favorite = Favorite::create([
                'user_id' => $userId,
                'favoritable_type' => $request->type,
                'favoritable_id' => $request->id
            ]);

            return response()->json([
                'message' => 'Added to favorites',
                'favorite_id' => $favorite->id
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to add favorite',
                'message' => $e->getMessage()
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
            // In production, get user_id from authenticated user
            $userId = $request->get('user_id');

            if (!$userId) {
                return response()->json([
                    'error' => 'User ID is required'
                ], 401);
            }

            $deleted = false;

            // Delete by favorite_id
            if ($request->has('favorite_id')) {
                $deleted = Favorite::where('id', $request->favorite_id)
                    ->where('user_id', $userId)
                    ->delete();
            }
            // Delete by type + id
            elseif ($request->has('type') && $request->has('id')) {
                $deleted = Favorite::where([
                    'user_id' => $userId,
                    'favoritable_type' => $request->type,
                    'favoritable_id' => $request->id
                ])->delete();
            } else {
                return response()->json([
                    'error' => 'Either favorite_id or type+id is required'
                ], 400);
            }

            if (!$deleted) {
                return response()->json([
                    'error' => 'Favorite not found'
                ], 404);
            }

            return response()->json([
                'message' => 'Removed from favorites'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to remove favorite',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
