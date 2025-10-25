<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use Illuminate\Http\Request;

class ActivityController extends Controller
{
    /**
     * Get all activities with pagination
     */
    public function index(Request $request)
    {
        $query = Activity::with(['company', 'user'])
            ->orderBy('created_at', 'desc');

        // Filter by company
        if ($request->has('company_id')) {
            $query->forCompany($request->company_id);
        }

        // Filter by action
        if ($request->has('action')) {
            $query->byAction($request->action);
        }

        // Filter by subject type (e.g., Sale, Compound, Unit)
        if ($request->has('subject_type')) {
            $query->where('subject_type', 'like', '%' . $request->subject_type . '%');
        }

        // Filter by recent days
        if ($request->has('recent_days')) {
            $query->recent($request->recent_days);
        }

        // Filter by date range
        if ($request->has('from_date')) {
            $query->where('created_at', '>=', $request->from_date);
        }
        if ($request->has('to_date')) {
            $query->where('created_at', '<=', $request->to_date);
        }

        $perPage = $request->get('per_page', 20);
        $activities = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $activities,
        ]);
    }

    /**
     * Get a specific activity
     */
    public function show($id)
    {
        $activity = Activity::with(['company', 'user', 'subject'])->find($id);

        if (!$activity) {
            return response()->json([
                'success' => false,
                'message' => 'Activity not found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $activity,
        ]);
    }

    /**
     * Get recent activities (last 7 days by default)
     */
    public function recent(Request $request)
    {
        $days = $request->get('days', 7);
        $companyId = $request->get('company_id');

        $query = Activity::with(['company', 'user'])
            ->recent($days)
            ->orderBy('created_at', 'desc');

        if ($companyId) {
            $query->forCompany($companyId);
        }

        $activities = $query->limit(50)->get();

        return response()->json([
            'success' => true,
            'data' => $activities,
        ]);
    }

    /**
     * Get activities by action type
     */
    public function byAction(Request $request, $action)
    {
        $companyId = $request->get('company_id');

        $query = Activity::with(['company', 'user'])
            ->byAction($action)
            ->orderBy('created_at', 'desc');

        if ($companyId) {
            $query->forCompany($companyId);
        }

        $perPage = $request->get('per_page', 20);
        $activities = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $activities,
        ]);
    }

    /**
     * Get activities for a specific subject (e.g., all activities for a sale)
     */
    public function bySubject(Request $request, $subjectType, $subjectId)
    {
        $activities = Activity::with(['company', 'user'])
            ->where('subject_type', 'like', '%' . $subjectType . '%')
            ->where('subject_id', $subjectId)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $activities,
        ]);
    }

    /**
     * Get activity statistics
     */
    public function stats(Request $request)
    {
        $companyId = $request->get('company_id');
        $days = $request->get('days', 7);

        $query = Activity::recent($days);

        if ($companyId) {
            $query->forCompany($companyId);
        }

        $totalActivities = $query->count();
        $byAction = Activity::recent($days)
            ->when($companyId, fn($q) => $q->forCompany($companyId))
            ->selectRaw('action, count(*) as count')
            ->groupBy('action')
            ->get();

        $bySubjectType = Activity::recent($days)
            ->when($companyId, fn($q) => $q->forCompany($companyId))
            ->selectRaw('subject_type, count(*) as count')
            ->groupBy('subject_type')
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'total_activities' => $totalActivities,
                'by_action' => $byAction,
                'by_subject_type' => $bySubjectType,
            ],
        ]);
    }
}
