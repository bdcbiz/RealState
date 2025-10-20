<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Company;
use App\Models\Compound;
use App\Models\Stage;
use App\Models\Unit;
use App\Models\Favorite;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class StatisticsController extends Controller
{
    /**
     * Get comprehensive statistics
     *
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        try {
            $stats = [];

            // 1. User Statistics
            $stats['users'] = [
                'total_users' => User::count(),
                'verified_users' => User::where('is_verified', 1)->count(),
                'buyers' => User::where('role', 'buyer')->count(),
                'owners' => User::where('role', 'owner')->count(),
                'companies' => User::where('role', 'company')->count()
            ];

            // 2. Compound Statistics
            $stats['compounds'] = [
                'total_compounds' => Compound::count(),
                'unique_companies' => Company::count()
            ];

            // 3. Stage Statistics
            $stats['stages'] = [
                'total_stages' => Stage::count(),
                'avg_completion' => Stage::whereNotNull('completion_progress')
                    ->avg(DB::raw('CAST(REPLACE(completion_progress, "%", "") AS DECIMAL(5,2))'))
            ];

            // 4. Unit Statistics
            $unitStats = Unit::selectRaw('
                COUNT(*) as total_units,
                SUM(CASE WHEN status = "available" THEN 1 ELSE 0 END) as available_units,
                SUM(CASE WHEN status = "sold" THEN 1 ELSE 0 END) as sold_units,
                SUM(CASE WHEN status = "reserved" THEN 1 ELSE 0 END) as reserved_units,
                SUM(CASE WHEN status = "blocked" THEN 1 ELSE 0 END) as blocked_units,
                AVG(base_price) as avg_base_price,
                MIN(base_price) as min_price,
                MAX(base_price) as max_price,
                SUM(base_price) as total_inventory_value
            ')->first();

            $stats['units'] = $unitStats->toArray();

            // 5. Favorites Statistics
            $stats['favorites'] = [
                'total_favorites' => Favorite::count(),
                'unit_favorites' => Favorite::where('favoritable_type', 'unit')->count(),
                'compound_favorites' => Favorite::where('favoritable_type', 'compound')->count(),
                'stage_favorites' => Favorite::where('favoritable_type', 'stage')->count()
            ];

            // 6. Top Compounds by Units
            $stats['top_compounds'] = Compound::with('units')
                ->withCount([
                    'units as total_units',
                    'units as available_units' => function($q) {
                        $q->where('status', 'available');
                    },
                    'units as sold_units' => function($q) {
                        $q->where('status', 'sold');
                    }
                ])
                ->orderBy('total_units', 'desc')
                ->limit(10)
                ->get()
                ->map(function($compound) {
                    return [
                        'compound_name' => $compound->name ?? $compound->project,
                        'location' => $compound->location,
                        'total_units' => $compound->total_units,
                        'available_units' => $compound->available_units,
                        'sold_units' => $compound->sold_units
                    ];
                });

            // 7. Most Favorited Units
            $stats['most_favorited_units'] = Unit::with(['stage.compound'])
                ->select('units.*')
                ->leftJoin('favorites', function($join) {
                    $join->on('favorites.favoritable_id', '=', 'units.id')
                         ->where('favorites.favoritable_type', '=', 'unit');
                })
                ->groupBy('units.id')
                ->havingRaw('COUNT(favorites.id) > 0')
                ->orderByRaw('COUNT(favorites.id) DESC')
                ->limit(10)
                ->get()
                ->map(function($unit) {
                    return [
                        'unit_code' => $unit->unit_code,
                        'status' => $unit->status,
                        'base_price' => $unit->base_price,
                        'compound_name' => $unit->stage->compound->name ?? null,
                        'stage_name' => $unit->stage->stage_name ?? null,
                        'favorites_count' => $unit->favorites()->count()
                    ];
                });

            // 8. Price Range Distribution
            $stats['price_distribution'] = Unit::selectRaw("
                CASE
                    WHEN base_price < 1000000 THEN 'Under 1M'
                    WHEN base_price BETWEEN 1000000 AND 3000000 THEN '1M - 3M'
                    WHEN base_price BETWEEN 3000001 AND 5000000 THEN '3M - 5M'
                    WHEN base_price BETWEEN 5000001 AND 10000000 THEN '5M - 10M'
                    WHEN base_price > 10000000 THEN 'Over 10M'
                    ELSE 'Not Priced'
                END as price_range,
                COUNT(*) as count,
                SUM(CASE WHEN status = 'available' THEN 1 ELSE 0 END) as available
            ")
            ->whereNotNull('base_price')
            ->groupBy('price_range')
            ->orderByRaw('MIN(base_price)')
            ->get();

            // 9. Recent Activity
            $recentUsers = User::select(DB::raw("'user' as type"), 'name as description', 'created_at')
                ->orderBy('created_at', 'desc')
                ->limit(5)
                ->get();

            $recentFavorites = Favorite::select(
                DB::raw("'favorite' as type"),
                DB::raw("CONCAT('User favorited ', favoritable_type, ' #', favoritable_id) as description"),
                'created_at'
            )
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

            $recentActivity = $recentUsers->merge($recentFavorites)
                ->sortByDesc('created_at')
                ->take(10)
                ->values();

            $stats['recent_activity'] = $recentActivity;

            return response()->json([
                'statistics' => $stats,
                'generated_at' => now()->format('Y-m-d H:i:s')
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to generate statistics',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
