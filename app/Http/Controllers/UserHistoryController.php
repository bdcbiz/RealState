<?php

namespace App\Http\Controllers;

use App\Models\UserHistory;
use App\Models\Unit;
use App\Models\Compound;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class UserHistoryController extends Controller
{
    /**
     * Get all history for authenticated user
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        try {
            $user = $request->user();

            // Optional filters
            $actionType = $request->query('action_type'); // Filter by action type
            $limit = $request->query('limit', 50); // Default 50 items

            $query = UserHistory::where('user_id', $user->id)
                ->with(['unit.compound', 'compound'])
                ->orderBy('created_at', 'desc');

            if ($actionType) {
                $query->where('action_type', $actionType);
            }

            $history = $query->limit($limit)->get();

            return response()->json([
                'success' => true,
                'message' => 'History retrieved successfully',
                'data' => [
                    'history' => $history,
                    'count' => $history->count(),
                ],
            ], 200);

        } catch (\Exception $e) {
            Log::error('Error fetching history', [
                'user_id' => $request->user()->id ?? null,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch history',
            ], 500);
        }
    }

    /**
     * Add an item to history
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $request->validate([
            'action_type' => 'required|string|in:view_unit,search,view_compound,filter',
            'unit_id' => 'nullable|integer|exists:units,id',
            'compound_id' => 'nullable|integer|exists:compounds,id',
            'search_query' => 'nullable|string|max:500',
            'metadata' => 'nullable|array',
        ]);

        try {
            $user = $request->user();

            // Validate based on action type
            if ($request->action_type === 'view_unit' && !$request->unit_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'unit_id is required for view_unit action',
                ], 422);
            }

            if ($request->action_type === 'view_compound' && !$request->compound_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'compound_id is required for view_compound action',
                ], 422);
            }

            if ($request->action_type === 'search' && !$request->search_query) {
                return response()->json([
                    'success' => false,
                    'message' => 'search_query is required for search action',
                ], 422);
            }

            // Create history entry
            $history = UserHistory::create([
                'user_id' => $user->id,
                'action_type' => $request->action_type,
                'unit_id' => $request->unit_id,
                'compound_id' => $request->compound_id,
                'search_query' => $request->search_query,
                'metadata' => $request->metadata,
            ]);

            Log::info('History entry created', [
                'user_id' => $user->id,
                'action_type' => $request->action_type,
                'history_id' => $history->id,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'History entry added',
                'data' => [
                    'history' => $history->load(['unit', 'compound']),
                ],
            ], 201);

        } catch (\Exception $e) {
            Log::error('Error adding history', [
                'user_id' => $request->user()->id ?? null,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to add history entry',
            ], 500);
        }
    }

    /**
     * Delete a specific history entry
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Request $request, $id)
    {
        try {
            $user = $request->user();

            $history = UserHistory::where('user_id', $user->id)
                ->where('id', $id)
                ->first();

            if (!$history) {
                return response()->json([
                    'success' => false,
                    'message' => 'History entry not found',
                ], 404);
            }

            $history->delete();

            Log::info('History entry deleted', [
                'user_id' => $user->id,
                'history_id' => $id,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'History entry deleted',
            ], 200);

        } catch (\Exception $e) {
            Log::error('Error deleting history', [
                'user_id' => $request->user()->id ?? null,
                'history_id' => $id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to delete history entry',
            ], 500);
        }
    }

    /**
     * Clear all history for authenticated user
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function clear(Request $request)
    {
        try {
            $user = $request->user();

            // Optional: Clear only specific action type
            $actionType = $request->query('action_type');

            $query = UserHistory::where('user_id', $user->id);

            if ($actionType) {
                $query->where('action_type', $actionType);
            }

            $count = $query->count();
            $query->delete();

            Log::info('History cleared', [
                'user_id' => $user->id,
                'action_type' => $actionType,
                'count' => $count,
            ]);

            return response()->json([
                'success' => true,
                'message' => "Cleared {$count} history entries",
            ], 200);

        } catch (\Exception $e) {
            Log::error('Error clearing history', [
                'user_id' => $request->user()->id ?? null,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to clear history',
            ], 500);
        }
    }

    /**
     * Get recently viewed units (unique units from history)
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function recentlyViewed(Request $request)
    {
        try {
            $user = $request->user();
            $limit = $request->query('limit', 10);

            $recentUnits = UserHistory::where('user_id', $user->id)
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
                    'units' => $recentUnits,
                    'count' => $recentUnits->count(),
                ],
            ], 200);

        } catch (\Exception $e) {
            Log::error('Error fetching recently viewed', [
                'user_id' => $request->user()->id ?? null,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch recently viewed units',
            ], 500);
        }
    }

    /**
     * Get search history (unique searches)
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function searchHistory(Request $request)
    {
        try {
            $user = $request->user();
            $limit = $request->query('limit', 20);

            $searches = UserHistory::where('user_id', $user->id)
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
                    'searches' => $searches,
                    'count' => $searches->count(),
                ],
            ], 200);

        } catch (\Exception $e) {
            Log::error('Error fetching search history', [
                'user_id' => $request->user()->id ?? null,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch search history',
            ], 500);
        }
    }
}
