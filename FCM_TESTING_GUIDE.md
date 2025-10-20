# FCM Notification Testing Guide

## Prerequisites

Before testing, ensure:
1. ✅ Firebase credentials file is in place
2. ✅ MySQL server is running in XAMPP
3. ✅ Database migration has been run
4. ✅ At least one user exists with an FCM token

## Testing Methods

### Method 1: Using Laravel Tinker (Recommended for Initial Testing)

This is the easiest way to test without needing a mobile app.

#### Step 1: Start Laravel Tinker
```bash
cd c:\xampp\htdocs\larvel2\real-estate-api
php artisan tinker
```

#### Step 2: Create a Test User with FCM Token
```php
// Create or update a user with a test FCM token
$user = App\Models\User::first();
$user->fcm_token = 'test_token_12345'; // Use a real FCM token from a mobile device
$user->save();
```

#### Step 3: Test Sending Notifications
```php
// Initialize the FCM service
$fcm = new App\Services\FCMNotificationService();

// Test 1: Send to all users
$fcm->sendToAllUsers('Test Notification', 'This is a test message from Laravel');

// Test 2: Send to buyers only
$fcm->sendToUsersByRole('buyer', 'Buyer Alert', 'Special message for buyers');

// Test 3: Send to a specific token
$fcm->sendToUser('your_device_fcm_token_here', 'Personal Message', 'This is for you');

// Test 4: Send with custom data
$fcm->sendToAllUsers(
    'New Unit Available',
    'Check out this amazing unit!',
    [
        'type' => 'new_unit',
        'unit_id' => '123',
        'unit_name' => 'Luxury Apartment'
    ]
);
```

#### Step 4: Exit Tinker
```php
exit
```

---

### Method 2: Test Automatic Notifications (Database Observers)

This tests the automatic notification system when data changes.

#### Step 1: Ensure User Has FCM Token
```bash
php artisan tinker
```

```php
$user = App\Models\User::where('role', 'buyer')->first();
$user->fcm_token = 'your_real_fcm_token_here';
$user->save();
exit
```

#### Step 2: Add Test Data to Trigger Notifications

**Option A: Using Tinker**
```bash
php artisan tinker
```

```php
// Test Unit Creation Notification
$unit = new App\Models\Unit();
$unit->compound_id = 1; // Use existing compound ID
$unit->unit_code = 'TEST-001';
$unit->unit_name = 'Test Apartment';
$unit->normal_price = 1500000;
$unit->is_sold = 0;
$unit->save();
// Notification should be sent to all buyers!

// Test Sale Creation Notification
$sale = new App\Models\Sale();
$sale->company_id = 1; // Use existing company ID
$sale->sale_type = 'compound';
$sale->compound_id = 1; // Use existing compound ID
$sale->sale_name = 'Summer Sale 2025';
$sale->description = 'Amazing discounts!';
$sale->discount_percentage = 25;
$sale->old_price = 2000000;
$sale->new_price = 1500000;
$sale->start_date = now();
$sale->end_date = now()->addDays(30);
$sale->is_active = 1;
$sale->save();
// Notification should be sent to all buyers!

exit
```

**Option B: Using MySQL Database Directly**
```sql
-- Insert a new unit (triggers UnitObserver)
INSERT INTO units (compound_id, unit_code, unit_name, normal_price, is_sold, created_at, updated_at)
VALUES (1, 'TEST-002', 'Test Villa', 3000000, 0, NOW(), NOW());

-- Insert a new sale (triggers SaleObserver)
INSERT INTO sales (company_id, sale_type, compound_id, sale_name, discount_percentage, old_price, new_price, start_date, end_date, is_active, created_at, updated_at)
VALUES (1, 'compound', 1, 'Flash Sale', 30, 2000000, 1400000, NOW(), DATE_ADD(NOW(), INTERVAL 7 DAY), 1, NOW(), NOW());
```

---

### Method 3: Test via API Endpoints

Test the manual notification endpoints using Postman or curl.

#### Step 1: Get Authentication Token
```bash
curl -X POST "http://127.0.0.1:8001/api/login" \
  -H "Content-Type: application/json" \
  -d "{\"email\":\"user@example.com\",\"password\":\"password\"}"
```

Save the `token` from the response.

#### Step 2: Save FCM Token
```bash
curl -X POST "http://127.0.0.1:8001/api/fcm-token" \
  -H "Authorization: Bearer YOUR_TOKEN_HERE" \
  -H "Content-Type: application/json" \
  -d "{\"fcm_token\":\"your_device_fcm_token_here\"}"
```

#### Step 3: Send Test Notifications

**Send to All Users:**
```bash
curl -X POST "http://127.0.0.1:8001/api/notifications/send-all" \
  -H "Authorization: Bearer YOUR_TOKEN_HERE" \
  -H "Content-Type: application/json" \
  -d "{\"title\":\"Test Notification\",\"body\":\"This is a test message\",\"data\":{\"test\":\"true\"}}"
```

**Send to Specific Role:**
```bash
curl -X POST "http://127.0.0.1:8001/api/notifications/send-role" \
  -H "Authorization: Bearer YOUR_TOKEN_HERE" \
  -H "Content-Type: application/json" \
  -d "{\"role\":\"buyer\",\"title\":\"Buyer Alert\",\"body\":\"Special offer for buyers\"}"
```

**Send to Topic:**
```bash
curl -X POST "http://127.0.0.1:8001/api/notifications/send-topic" \
  -H "Authorization: Bearer YOUR_TOKEN_HERE" \
  -H "Content-Type: application/json" \
  -d "{\"topic\":\"all\",\"title\":\"Topic Test\",\"body\":\"Message to topic subscribers\"}"
```

---

### Method 4: Create a Test Controller

Create a dedicated test controller for easy testing.

#### Create Test Controller
```bash
cd c:\xampp\htdocs\larvel2\real-estate-api
php artisan make:controller TestNotificationController
```

#### Add Test Methods

Edit `app/Http/Controllers/TestNotificationController.php`:

```php
<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\FCMNotificationService;
use App\Models\User;

class TestNotificationController extends Controller
{
    public function test()
    {
        try {
            $fcm = new FCMNotificationService();

            // Test sending notification
            $fcm->sendToAllUsers(
                'Test Notification',
                'If you receive this, FCM is working!',
                ['test' => 'true', 'timestamp' => now()->toIso8601String()]
            );

            return response()->json([
                'success' => true,
                'message' => 'Test notification sent',
                'users_with_tokens' => User::whereNotNull('fcm_token')->count()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function status()
    {
        return response()->json([
            'fcm_enabled' => true,
            'credentials_file' => config('firebase.credentials.file'),
            'credentials_exists' => file_exists(storage_path(config('firebase.credentials.file'))),
            'total_users' => User::count(),
            'users_with_fcm_tokens' => User::whereNotNull('fcm_token')->count(),
            'buyers_with_tokens' => User::where('role', 'buyer')->whereNotNull('fcm_token')->count(),
            'sellers_with_tokens' => User::where('role', 'seller')->whereNotNull('fcm_token')->count(),
            'agents_with_tokens' => User::where('role', 'agent')->whereNotNull('fcm_token')->count(),
        ]);
    }
}
```

#### Add Test Routes

Edit `routes/web.php` (or api.php):

```php
use App\Http\Controllers\TestNotificationController;

Route::get('/test-fcm', [TestNotificationController::class, 'test']);
Route::get('/fcm-status', [TestNotificationController::class, 'status']);
```

#### Test via Browser
Visit: `http://127.0.0.1:8001/fcm-status`
Visit: `http://127.0.0.1:8001/test-fcm`

---

## Getting a Real FCM Token

To properly test, you need a real FCM token from a device:

### Option 1: Using Firebase Console (Test Message)
1. Go to [Firebase Console](https://console.firebase.google.com/)
2. Select your project
3. Go to Cloud Messaging
4. Click "Send test message"
5. Add your device token
6. Send

### Option 2: Using a Mobile App

**Flutter Example:**
```dart
import 'package:firebase_messaging/firebase_messaging.dart';

FirebaseMessaging.instance.getToken().then((token) {
  print("FCM Token: $token");
  // Send this token to your API
});
```

**React Native Example:**
```javascript
import messaging from '@react-native-firebase/messaging';

messaging().getToken().then(token => {
  console.log("FCM Token:", token);
  // Send this token to your API
});
```

### Option 3: Using Postman Echo (For Testing Without Real Device)

You can test the notification sending logic without a real device:

```bash
php artisan tinker
```

```php
// Override the FCM service to log instead of send
$user = App\Models\User::first();
$user->fcm_token = 'test_dummy_token_for_logging';
$user->save();

// Now add a unit - check the logs
$unit = new App\Models\Unit([
    'compound_id' => 1,
    'unit_code' => 'LOG-TEST',
    'unit_name' => 'Log Test Unit',
    'normal_price' => 1000000,
    'is_sold' => 0
]);
$unit->save();

// Check storage/logs/laravel.log for the notification attempt
exit
```

---

## Checking Logs

All notification attempts are logged. Check the logs:

### View Laravel Logs
```bash
# Windows
type storage\logs\laravel.log

# Or open in text editor
notepad storage\logs\laravel.log
```

### Look for These Log Entries:
- ✅ `Notification sent to X users: [Title]`
- ✅ `Notification sent for new unit: [Name]`
- ✅ `Notification sent for new sale: [Name]`
- ❌ `Failed to send notification: [Error]`
- ❌ `No users with FCM tokens found`

---

## Troubleshooting

### Issue: "Firebase credentials file not found"
**Solution:**
```bash
# Check if file exists
cd c:\xampp\htdocs\larvel2\real-estate-api
dir storage\app\firebase\*.json
```

If not found, place your Firebase JSON credentials there.

### Issue: "No users with FCM tokens found"
**Solution:**
```bash
php artisan tinker
```
```php
$user = App\Models\User::first();
$user->fcm_token = 'test_token';
$user->save();
exit
```

### Issue: Notifications not received on device
**Checklist:**
1. ✅ Firebase credentials are correct
2. ✅ FCM token is valid and current
3. ✅ Token is saved in database
4. ✅ User role matches notification target
5. ✅ Mobile app has notification permissions
6. ✅ Check Firebase Console for delivery status

### Issue: Database connection error
**Solution:**
1. Start MySQL in XAMPP Control Panel
2. Verify database credentials in `.env`
3. Run migration: `php artisan migrate`

---

## Quick Test Checklist

- [ ] 1. XAMPP MySQL is running
- [ ] 2. Firebase credentials file exists
- [ ] 3. Migration ran successfully (`fcm_token` column exists)
- [ ] 4. At least one user has an FCM token in database
- [ ] 5. Test via Tinker: `$fcm->sendToAllUsers('Test', 'Message')`
- [ ] 6. Check logs: `storage/logs/laravel.log`
- [ ] 7. Test automatic notification by creating a unit
- [ ] 8. Check Firebase Console for delivery stats

---

## Sample Test Script

Save this as `test-fcm.php` in your project root:

```php
<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "=== FCM Notification Test ===\n\n";

// Check credentials
$credPath = storage_path(config('firebase.credentials.file'));
echo "1. Credentials file: " . ($credPath) . "\n";
echo "   Exists: " . (file_exists($credPath) ? "YES" : "NO") . "\n\n";

// Check users with tokens
$totalUsers = App\Models\User::count();
$usersWithTokens = App\Models\User::whereNotNull('fcm_token')->count();
echo "2. Total users: {$totalUsers}\n";
echo "   Users with FCM tokens: {$usersWithTokens}\n\n";

// Try to send a test notification
if ($usersWithTokens > 0) {
    try {
        $fcm = new App\Services\FCMNotificationService();
        $fcm->sendToAllUsers('Test Notification', 'FCM is working!');
        echo "3. Test notification: SENT\n";
        echo "   Check your device or Firebase Console\n";
    } catch (Exception $e) {
        echo "3. Test notification: FAILED\n";
        echo "   Error: " . $e->getMessage() . "\n";
    }
} else {
    echo "3. Test notification: SKIPPED (no users with tokens)\n";
}

echo "\n=== Test Complete ===\n";
```

Run it:
```bash
php test-fcm.php
```

---

## Next Steps After Testing

Once testing is successful:
1. ✅ Integrate FCM token saving in your mobile app login flow
2. ✅ Test with real devices
3. ✅ Monitor Firebase Console for delivery metrics
4. ✅ Set up error monitoring
5. ✅ Consider implementing notification preferences
6. ✅ Add rate limiting for manual notifications

Happy Testing! 🚀
