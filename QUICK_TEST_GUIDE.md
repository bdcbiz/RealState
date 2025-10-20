# Quick FCM Testing Guide - 5 Minutes Setup

## Prerequisites Checklist
- [ ] XAMPP MySQL is running
- [ ] Firebase credentials JSON file downloaded from Firebase Console
- [ ] Laravel project is accessible

## Step-by-Step Testing (5 Minutes)

### Step 1: Place Firebase Credentials (1 min)
1. Download your Firebase Admin SDK JSON from Firebase Console
2. Create folder: `real-estate-api/storage/app/firebase/`
3. Copy your JSON file there with the exact name in `.env` file

### Step 2: Run Migration (1 min)
```bash
cd c:\xampp\htdocs\larvel2\real-estate-api
php artisan migrate
```

This adds the `fcm_token` column to users table.

### Step 3: Open Testing Dashboard (30 seconds)
Open in browser:
```
http://127.0.0.1:8001/test-fcm.html
```

### Step 4: Test the System (2 minutes)

Click the buttons in order:

1. **Refresh Status** - Check system configuration
2. **Add Test Token** - Creates a test user with FCM token
3. **Send Test Notification** - Sends a notification
4. **Test Unit Notification** - Creates a unit and triggers auto-notification
5. **Test Sale Notification** - Creates a sale and triggers auto-notification

### Step 5: Check Results (30 seconds)

**View Logs:**
```bash
type storage\logs\laravel.log
```

Look for:
- ✅ "Notification sent to X users"
- ✅ "Notification sent for new unit"
- ✅ "Notification sent for new sale"

---

## Alternative: Command Line Testing

### Quick Test with Tinker
```bash
cd c:\xampp\htdocs\larvel2\real-estate-api
php artisan tinker
```

```php
// Add test token to first user
$user = App\Models\User::first();
$user->fcm_token = 'test_token_123';
$user->save();

// Send test notification
$fcm = new App\Services\FCMNotificationService();
$fcm->sendToAllUsers('Test', 'Hello from Laravel!');

exit
```

---

## Testing with Real Mobile Device

### Get FCM Token from Your Mobile App

**Flutter:**
```dart
FirebaseMessaging.instance.getToken().then((token) {
  print("FCM Token: $token");
});
```

**React Native:**
```javascript
messaging().getToken().then(token => {
  console.log("FCM Token:", token);
});
```

### Save Token to Database

**Option 1: Via Testing Dashboard**
1. Open http://127.0.0.1:8001/test-fcm.html
2. Paste token in "Add Real FCM Token" section
3. Click "Save Real Token"

**Option 2: Via API**
```bash
curl -X POST "http://127.0.0.1:8001/api/login" \
  -H "Content-Type: application/json" \
  -d "{\"email\":\"user@example.com\",\"password\":\"password\"}"

curl -X POST "http://127.0.0.1:8001/api/fcm-token" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d "{\"fcm_token\":\"YOUR_DEVICE_FCM_TOKEN\"}"
```

**Option 3: Via Tinker**
```bash
php artisan tinker
```
```php
$user = App\Models\User::where('email', 'your@email.com')->first();
$user->fcm_token = 'paste_your_real_fcm_token_here';
$user->save();
exit
```

### Send Notification to Real Device
```bash
php artisan tinker
```
```php
$fcm = new App\Services\FCMNotificationService();
$fcm->sendToAllUsers('Real Test', 'Check your device now!');
exit
```

---

## Available Test URLs

| URL | Purpose |
|-----|---------|
| http://127.0.0.1:8001/test-fcm.html | Interactive testing dashboard |
| http://127.0.0.1:8001/test-fcm/status | Check system status (JSON) |
| http://127.0.0.1:8001/test-fcm/send | Send test notification |
| http://127.0.0.1:8001/test-fcm/add-token | Add test FCM token |
| http://127.0.0.1:8001/test-fcm/test-unit | Create test unit + notification |
| http://127.0.0.1:8001/test-fcm/test-sale | Create test sale + notification |

---

## Troubleshooting

### ❌ "Firebase credentials file not found"
**Fix:**
```bash
# Check if file exists
dir storage\app\firebase\*.json

# If missing, download from Firebase Console and place there
```

### ❌ "No users with FCM tokens found"
**Fix:** Use testing dashboard or run:
```bash
php artisan tinker
```
```php
$user = App\Models\User::first();
$user->fcm_token = 'test_token';
$user->save();
exit
```

### ❌ "Database connection error"
**Fix:**
1. Start MySQL in XAMPP
2. Run: `php artisan migrate`

### ❌ "Notification sent but not received on device"
**Check:**
1. ✅ FCM token is valid and current
2. ✅ Token is saved in database
3. ✅ Mobile app has notification permissions
4. ✅ Check Firebase Console delivery status
5. ✅ Check Laravel logs: `storage/logs/laravel.log`

---

## What Triggers Automatic Notifications?

| Action | Notification | Recipients |
|--------|-------------|-----------|
| New unit added to DB | "New Unit Available!" | All buyers |
| Unit marked as sold | "Unit Sold!" | All users |
| Unit price reduced | "Price Drop Alert!" | All buyers |
| New compound added | "New Compound Available!" | Buyers & Agents |
| New sale created | "New Sale Alert!" | All buyers |
| Sale activated | "Sale Now Active!" | All buyers |
| Sale discount increased | "Discount Increased!" | All buyers |

---

## Firebase Console Testing

1. Go to https://console.firebase.google.com/
2. Select your project
3. Click "Cloud Messaging" in left menu
4. Click "Send test message"
5. Add your device token
6. Send

This verifies your Firebase setup is correct.

---

## Production Checklist

Before going live:

- [ ] Replace test FCM tokens with real device tokens
- [ ] Test on both Android and iOS devices
- [ ] Monitor Firebase Console for delivery rates
- [ ] Set up error monitoring/alerting
- [ ] Consider using Laravel Queues for high volume
- [ ] Add notification preferences for users
- [ ] Test with multiple users
- [ ] Document notification types for mobile team
- [ ] Set up Firebase Analytics
- [ ] Test with poor network conditions

---

## Need Help?

1. **Check logs first:** `storage/logs/laravel.log`
2. **Check Firebase Console:** Delivery statistics
3. **Review full docs:** `FCM_NOTIFICATION_SETUP.md`
4. **Testing guide:** `FCM_TESTING_GUIDE.md`

## Quick Commands Reference

```bash
# Start testing
php artisan tinker

# Check status
$fcm = new App\Services\FCMNotificationService();

# Add token
$user = App\Models\User::first();
$user->fcm_token = 'your_token';
$user->save();

# Send notification
$fcm->sendToAllUsers('Title', 'Body');

# Check logs
exit
type storage\logs\laravel.log

# View in browser
start http://127.0.0.1:8001/test-fcm.html
```

---

**You're all set! Start with the Testing Dashboard for the easiest experience.** 🚀
