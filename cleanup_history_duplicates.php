<?php

/**
 * Cleanup Script for Duplicate History Entries
 *
 * This script removes duplicate entries from user_history table,
 * keeping only the most recent entry for each unique combination of:
 * - user_id + action_type + unit_id (for view_unit)
 * - user_id + action_type + compound_id (for view_compound)
 * - user_id + action_type + search_query (for search)
 */

require __DIR__ . '/vendor/autoload.php';

// Bootstrap Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

echo "🧹 Starting History Duplicates Cleanup...\n\n";

try {
    $totalDeleted = 0;

    // Clean up view_unit duplicates
    echo "📋 Cleaning view_unit duplicates...\n";

    $unitDuplicates = DB::select("
        SELECT
            user_id,
            action_type,
            unit_id,
            COUNT(*) as count,
            GROUP_CONCAT(id ORDER BY created_at DESC) as ids
        FROM user_history
        WHERE action_type = 'view_unit'
        AND unit_id IS NOT NULL
        GROUP BY user_id, action_type, unit_id
        HAVING count > 1
    ");

    foreach ($unitDuplicates as $dup) {
        $ids = explode(',', $dup->ids);
        // Keep first (most recent), delete the rest
        $keepId = array_shift($ids);
        $deleteIds = $ids;

        if (!empty($deleteIds)) {
            $deleted = DB::table('user_history')
                ->whereIn('id', $deleteIds)
                ->delete();

            $totalDeleted += $deleted;
            echo "  ✓ Kept entry #{$keepId}, deleted " . count($deleteIds) . " duplicates for unit_id={$dup->unit_id}\n";
        }
    }

    echo "  → Deleted " . count($unitDuplicates) * (count($deleteIds ?? []) ?: 0) . " duplicate unit entries\n\n";

    // Clean up view_compound duplicates
    echo "📋 Cleaning view_compound duplicates...\n";

    $compoundDuplicates = DB::select("
        SELECT
            user_id,
            action_type,
            compound_id,
            COUNT(*) as count,
            GROUP_CONCAT(id ORDER BY created_at DESC) as ids
        FROM user_history
        WHERE action_type = 'view_compound'
        AND compound_id IS NOT NULL
        GROUP BY user_id, action_type, compound_id
        HAVING count > 1
    ");

    foreach ($compoundDuplicates as $dup) {
        $ids = explode(',', $dup->ids);
        // Keep first (most recent), delete the rest
        $keepId = array_shift($ids);
        $deleteIds = $ids;

        if (!empty($deleteIds)) {
            $deleted = DB::table('user_history')
                ->whereIn('id', $deleteIds)
                ->delete();

            $totalDeleted += $deleted;
            echo "  ✓ Kept entry #{$keepId}, deleted " . count($deleteIds) . " duplicates for compound_id={$dup->compound_id}\n";
        }
    }

    echo "  → Deleted duplicates for " . count($compoundDuplicates) . " compounds\n\n";

    // Clean up search duplicates
    echo "📋 Cleaning search duplicates...\n";

    $searchDuplicates = DB::select("
        SELECT
            user_id,
            action_type,
            search_query,
            COUNT(*) as count,
            GROUP_CONCAT(id ORDER BY created_at DESC) as ids
        FROM user_history
        WHERE action_type = 'search'
        AND search_query IS NOT NULL
        GROUP BY user_id, action_type, search_query
        HAVING count > 1
    ");

    foreach ($searchDuplicates as $dup) {
        $ids = explode(',', $dup->ids);
        // Keep first (most recent), delete the rest
        $keepId = array_shift($ids);
        $deleteIds = $ids;

        if (!empty($deleteIds)) {
            $deleted = DB::table('user_history')
                ->whereIn('id', $deleteIds)
                ->delete();

            $totalDeleted += $deleted;
            echo "  ✓ Kept entry #{$keepId}, deleted " . count($deleteIds) . " duplicates for search='{$dup->search_query}'\n";
        }
    }

    echo "  → Deleted duplicates for " . count($searchDuplicates) . " searches\n\n";

    // Summary
    echo "═══════════════════════════════════════════\n";
    echo "✅ CLEANUP COMPLETE!\n";
    echo "═══════════════════════════════════════════\n";
    echo "Total duplicate entries deleted: {$totalDeleted}\n";
    echo "Remaining unique entries: " . DB::table('user_history')->count() . "\n";
    echo "\n";

    // Verify no duplicates remain
    echo "🔍 Verifying no duplicates remain...\n\n";

    $remainingDuplicates = DB::select("
        SELECT
            action_type,
            COUNT(*) as duplicate_groups
        FROM (
            SELECT user_id, action_type, unit_id, compound_id, search_query, COUNT(*) as cnt
            FROM user_history
            GROUP BY user_id, action_type, unit_id, compound_id, search_query
            HAVING cnt > 1
        ) as dups
        GROUP BY action_type
    ");

    if (empty($remainingDuplicates)) {
        echo "✅ NO DUPLICATES FOUND! Database is clean.\n\n";
    } else {
        echo "⚠️  WARNING: Some duplicates still remain:\n";
        foreach ($remainingDuplicates as $dup) {
            echo "  - {$dup->action_type}: {$dup->duplicate_groups} duplicate groups\n";
        }
    }

} catch (\Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
    exit(1);
}

echo "\n✅ Done!\n";
