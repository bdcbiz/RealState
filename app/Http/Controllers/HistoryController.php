<?php

namespace App\Http\Controllers;

use App\Models\UserHistory;
use App\Models\Unit;
use App\Models\Compound;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class HistoryController extends Controller
{
    /**
     * Display a listing of user's history.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        try {
            // Get user_id from request parameter or authenticated user
            $userId = $request->get('user_id', $request->user()->id);

            $query = UserHistory::where('user_id', $userId)
                ->with(['unit.compound', 'compound']);

            // Filter by action type
            if ($request->has('action_type')) {
                $query->where('action_type', $request->action_type);
            }

            // Limit results
            $limit = $request->get('limit', 50);

            $history = $query->orderBy('created_at', 'desc')
                ->limit($limit)
                ->get();

            return response()->json([
                'success' => true,
                'message' => 'History retrieved successfully',
                'data' => [
                    'history' => $history,
                    'count' => $history->count()
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch history',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a new history entry.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        try {
            // Get user_id from request parameter or authenticated user
            $userId = $request->get('user_id', $request->user()->id);

            // Validate request
            $validated = $request->validate([
                'action_type' => 'required|in:view_unit,search,view_compound,filter',
                'unit_id' => 'required_if:action_type,view_unit|nullable|integer|exists:units,id',
                'compound_id' => 'required_if:action_type,view_compound|nullable|integer|exists:compounds,id',
                'search_query' => 'required_if:action_type,search|nullable|string',
                'metadata' => 'nullable|array'
            ]);

            // Create history entry
            $history = UserHistory::create([
                'user_id' => $userId,
                'action_type' => $validated['action_type'],
                'unit_id' => $validated['unit_id'] ?? null,
                'compound_id' => $validated['compound_id'] ?? null,
                'search_query' => $validated['search_query'] ?? null,
                'metadata' => $validated['metadata'] ?? null
            ]);

            // Load relationships
            $history->load(['unit.compound', 'compound']);

            return response()->json([
                'success' => true,
                'message' => 'History entry added',
                'data' => [
                    'history' => $history
                ]
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to add history entry',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete a specific history entry.
     *
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function destroy(Request $request, $id): JsonResponse
    {
        try {
            // Get user_id from request parameter or authenticated user
            $userId = $request->get('user_id', $request->user()->id);

            $history = UserHistory::where('id', $id)
                ->where('user_id', $userId)
                ->first();

            if (!$history) {
                return response()->json([
                    'success' => false,
                    'message' => 'History entry not found'
                ], 404);
            }

            $history->delete();

            return response()->json([
                'success' => true,
                'message' => 'History entry deleted'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete history entry',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Clear all history or specific type.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function clear(Request $request): JsonResponse
    {
        try {
            // Get user_id from request parameter or authenticated user
            $userId = $request->get('user_id', $request->user()->id);

            $query = UserHistory::where('user_id', $userId);

            // Filter by action type if provided
            if ($request->has('action_type')) {
                $query->where('action_type', $request->action_type);
            }

            $count = $query->count();
            $query->delete();

            return response()->json([
                'success' => true,
                'message' => "Cleared {$count} history entries"
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to clear history',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get recently viewed units.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function recentlyViewed(Request $request): JsonResponse
    {
        try {
            // Get user_id from request parameter or authenticated user
            $userId = $request->get('user_id', $request->user()->id);
            $limit = $request->get('limit', 10);

            $history = UserHistory::where('user_id', $userId)
                ->where('action_type', 'view_unit')
                ->whereNotNull('unit_id')
                ->with(['unit.compound'])
                ->orderBy('created_at', 'desc')
                ->limit($limit)
                ->get()
                ->unique('unit_id')
                ->values();

            return response()->json([
                'success' => true,
                'message' => 'Recently viewed units retrieved',
                'data' => [
                    'units' => $history,
                    'count' => $history->count()
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch recently viewed units',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get search history.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function searches(Request $request): JsonResponse
    {
        try {
            // Get user_id from request parameter or authenticated user
            $userId = $request->get('user_id', $request->user()->id);
            $limit = $request->get('limit', 20);

            $history = UserHistory::where('user_id', $userId)
                ->where('action_type', 'search')
                ->whereNotNull('search_query')
                ->orderBy('created_at', 'desc')
                ->limit($limit)
                ->get()
                ->unique('search_query')
                ->values();

            return response()->json([
                'success' => true,
                'message' => 'Search history retrieved',
                'data' => [
                    'searches' => $history,
                    'count' => $history->count()
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch search history',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
