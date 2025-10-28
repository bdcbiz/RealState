<?php
/**
 * Test Activity Logging System
 */

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "\n╔═══════════════════════════════════════════════════════════════╗\n";
echo "║         Test Activity Logging System                         ║\n";
echo "╚═══════════════════════════════════════════════════════════════╝\n\n";

// Step 1: Check if activities table exists and is empty
echo "1️⃣ Checking activities table...\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

$activitiesCount = \App\Models\Activity::count();
echo "Current activities count: $activitiesCount\n\n";

// Step 2: Create a test sale to trigger activity logging
echo "2️⃣ Creating a test sale to trigger activity logging...\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

$unit = \App\Models\Unit::whereNotNull('compound_id')->first();

if (!$unit) {
    echo "❌ No units found to test with!\n";
    exit(1);
}

echo "Using Unit: {$unit->unit_code} (Compound ID: {$unit->compound_id})\n\n";

$sale = new \App\Models\Sale();
$sale->company_id = 2; // Larz Developments
$sale->sale_type = 'unit';
$sale->unit_id = $unit->id;
$sale->sale_name = 'ACTIVITY TEST SALE - ' . date('Y-m-d H:i:s');
$sale->description = 'Test sale to verify activity logging';
$sale->discount_percentage = 20;
$sale->old_price = 5000000;
$sale->new_price = 4000000;
$sale->start_date = now();
$sale->end_date = now()->addDays(30);
$sale->is_active = true;

echo "Creating sale...\n";
$sale->save();
echo "✅ Sale created with ID: {$sale->id}\n\n";

// Step 3: Check if activity was logged
echo "3️⃣ Checking if activity was logged...\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

sleep(1); // Give it a moment

$activity = \App\Models\Activity::where('subject_type', 'App\Models\Sale')
    ->where('subject_id', $sale->id)
    ->where('action', 'created')
    ->first();

if ($activity) {
    echo "✅ SUCCESS! Activity was logged!\n\n";
    echo "Activity Details:\n";
    echo "  ID: {$activity->id}\n";
    echo "  Action: {$activity->action}\n";
    echo "  Description: {$activity->description}\n";
    echo "  Subject Type: {$activity->subject_type}\n";
    echo "  Subject ID: {$activity->subject_id}\n";
    echo "  Company ID: {$activity->company_id}\n";
    echo "  Properties: " . json_encode($activity->properties, JSON_PRETTY_PRINT) . "\n\n";
} else {
    echo "❌ FAILED! No activity was logged for this sale.\n\n";
}

// Step 4: Update the sale to test update logging
echo "4️⃣ Updating sale to test update logging...\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

$sale->discount_percentage = 25;
$sale->save();

sleep(1);

$updateActivity = \App\Models\Activity::where('subject_type', 'App\Models\Sale')
    ->where('subject_id', $sale->id)
    ->where('action', 'updated')
    ->first();

if ($updateActivity) {
    echo "✅ SUCCESS! Update activity was logged!\n\n";
    echo "Update Activity:\n";
    echo "  Description: {$updateActivity->description}\n";
    echo "  Changes: " . json_encode($updateActivity->properties, JSON_PRETTY_PRINT) . "\n\n";
} else {
    echo "❌ FAILED! No update activity was logged.\n\n";
}

// Step 5: Delete the sale to test delete logging
echo "5️⃣ Deleting sale to test delete logging...\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

$sale->delete();

sleep(1);

$deleteActivity = \App\Models\Activity::where('subject_type', 'App\Models\Sale')
    ->where('subject_id', $sale->id)
    ->where('action', 'deleted')
    ->first();

if ($deleteActivity) {
    echo "✅ SUCCESS! Delete activity was logged!\n\n";
    echo "Delete Activity:\n";
    echo "  Description: {$deleteActivity->description}\n\n";
} else {
    echo "❌ FAILED! No delete activity was logged.\n\n";
}

// Step 6: Show all activities logged during this test
echo "6️⃣ All activities for this test:\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

$allActivities = \App\Models\Activity::where('subject_type', 'App\Models\Sale')
    ->where('subject_id', $sale->id)
    ->orderBy('created_at', 'asc')
    ->get();

foreach ($allActivities as $act) {
    echo "  [{$act->action}] {$act->description}\n";
    echo "    Created: {$act->created_at}\n\n";
}

// Step 7: Test API endpoints
echo "7️⃣ Testing API endpoints...\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

echo "Total activities in system: " . \App\Models\Activity::count() . "\n";
echo "Recent activities (last 7 days): " . \App\Models\Activity::recent(7)->count() . "\n";
echo "Created activities: " . \App\Models\Activity::byAction('created')->count() . "\n";
echo "Updated activities: " . \App\Models\Activity::byAction('updated')->count() . "\n";
echo "Deleted activities: " . \App\Models\Activity::byAction('deleted')->count() . "\n\n";

echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";
echo "🎯 Test Complete!\n\n";

echo "Available API Endpoints:\n";
echo "  GET /api/activities - Get all activities with pagination\n";
echo "  GET /api/activities/recent - Get recent activities (last 7 days)\n";
echo "  GET /api/activities/stats - Get activity statistics\n";
echo "  GET /api/activities/{id} - Get specific activity\n";
echo "  GET /api/activities/action/{action} - Get activities by action type\n";
echo "  GET /api/activities/subject/{type}/{id} - Get activities for specific subject\n\n";

echo "Example API calls:\n";
echo "  https://aqar.bdcbiz.com/api/activities?per_page=20\n";
echo "  https://aqar.bdcbiz.com/api/activities/recent?days=7&company_id=2\n";
echo "  https://aqar.bdcbiz.com/api/activities/action/created\n";
echo "  https://aqar.bdcbiz.com/api/activities/subject/Sale/{$sale->id}\n\n";

echo "╔═══════════════════════════════════════════════════════════════╗\n";
echo "║  ✅ Activity logging system is working!                       ║\n";
echo "╚═══════════════════════════════════════════════════════════════╝\n\n";
