<?php

namespace App\Http\Controllers;

use App\Models\SavedSearch;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class SavedSearchController extends Controller
{
    /**
     * Display a listing of saved searches
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
                ], 400);
            }

            $searches = SavedSearch::where('user_id', $userId)
                ->orderBy('created_at', 'desc')
                ->get();

            return response()->json([
                'data' => $searches
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to fetch saved searches',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified saved search
     *
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function show(Request $request, int $id): JsonResponse
    {
        try {
            $userId = $request->get('user_id');

            if (!$userId) {
                return response()->json([
                    'error' => 'User ID is required'
                ], 400);
            }

            $search = SavedSearch::where('id', $id)
                ->where('user_id', $userId)
                ->first();

            if (!$search) {
                return response()->json([
                    'error' => 'Saved search not found'
                ], 404);
            }

            return response()->json($search, 200);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to fetch saved search',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a newly created saved search
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $userId = $request->get('user_id');

            if (!$userId) {
                return response()->json([
                    'error' => 'User ID is required'
                ], 401);
            }

            $request->validate([
                'name' => 'required|string|max:255',
                'search_parameters' => 'required|array'
            ]);

            $search = SavedSearch::create([
                'user_id' => $userId,
                'name' => $request->name,
                'search_parameters' => $request->search_parameters,
                'email_notifications_enabled' => $request->get('email_notifications_enabled', true)
            ]);

            return response()->json([
                'message' => 'Search saved successfully',
                'id' => $search->id
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to save search',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update the specified saved search
     *
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function update(Request $request, int $id): JsonResponse
    {
        try {
            $userId = $request->get('user_id');

            if (!$userId) {
                return response()->json([
                    'error' => 'User ID is required'
                ], 401);
            }

            $search = SavedSearch::where('id', $id)
                ->where('user_id', $userId)
                ->first();

            if (!$search) {
                return response()->json([
                    'error' => 'Saved search not found'
                ], 404);
            }

            $updateData = [];

            if ($request->has('name')) {
                $updateData['name'] = $request->name;
            }

            if ($request->has('search_parameters')) {
                $updateData['search_parameters'] = $request->search_parameters;
            }

            if ($request->has('email_notifications_enabled')) {
                $updateData['email_notifications_enabled'] = $request->email_notifications_enabled;
            }

            if (empty($updateData)) {
                return response()->json([
                    'error' => 'No fields to update'
                ], 400);
            }

            $search->update($updateData);

            return response()->json([
                'message' => 'Saved search updated successfully'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to update saved search',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified saved search
     *
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function destroy(Request $request, int $id): JsonResponse
    {
        try {
            $userId = $request->get('user_id');

            if (!$userId) {
                return response()->json([
                    'error' => 'User ID is required'
                ], 401);
            }

            $deleted = SavedSearch::where('id', $id)
                ->where('user_id', $userId)
                ->delete();

            if (!$deleted) {
                return response()->json([
                    'error' => 'Saved search not found'
                ], 404);
            }

            return response()->json([
                'message' => 'Saved search deleted successfully'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to delete saved search',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
