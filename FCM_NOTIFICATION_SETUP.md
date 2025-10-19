# Firebase Cloud Messaging (FCM) Push Notifications Setup

## Overview
This real estate application now includes Firebase Cloud Messaging (FCM) push notifications that automatically notify users when:
- New units are added to the database
- New compounds are added
- New sales/promotions are created
- Unit prices drop
- Units are sold
- Sales discounts increase

## What Was Installed

### 1. Firebase Admin SDK Package
```bash
composer require kreait/laravel-firebase
```

### 2. Files Created

#### Configuration
- `config/firebase.php` - Firebase configuration file
- `.env` updated with `FIREBASE_CREDENTIALS` path

#### Service Class
- `app/Services/FCMNotificationService.php` - Main notification service

#### Observers (Automatic Notifications)
- `app/Observers/UnitObserver.php` - Monitors unit changes
- `app/Observers/CompoundObserver.php` - Monitors compound changes
- `app/Observers/SaleObserver.php` - Monitors sale changes

#### API Controller
- `app/Http/Controllers/Api/FCMTokenController.php` - Manages user FCM tokens

#### Database Migration
- Migration file to add `fcm_token` field to users table

### 3. Routes Added
```
POST   /api/fcm-token    - Save user's FCM token
DELETE /api/fcm-token    - Remove user's FCM token
```

## Setup Instructions

### Step 1: Firebase Project Setup
1. Go to [Firebase Console](https://console.firebase.google.com/)
2. Create a new project or select existing project
3. Go to Project Settings > Service Accounts
4. Click "Generate New Private Key"
5. Save the JSON file

### Step 2: Add Firebase Credentials
1. Place your Firebase JSON credentials file in:
   ```
   real-estate-api/storage/app/firebase/realstate-4564d-firebase-adminsdk-fbsvc-8f8d00baed.json
   ```

2. Verify `.env` has the correct path:
   ```
   FIREBASE_CREDENTIALS=app/firebase/realstate-4564d-firebase-adminsdk-fbsvc-8f8d00baed.json
   ```

### Step 3: Run Database Migration
```bash
cd real-estate-api
php artisan migrate
```

This adds the `fcm_token` field to the users table.

### Step 4: Start MySQL Server
Make sure XAMPP MySQL is running for the migration to work.

## How It Works

### Automatic Notifications (Database Observers)
The system automatically sends notifications when:

#### New Unit Added
- **Event**: New unit created in database
- **Recipients**: All buyers
- **Notification**: "New Unit Available! A new unit '{name}' has been added in {compound}"

#### Unit Sold
- **Event**: Unit `is_sold` status changes to true
- **Recipients**: All users
- **Notification**: "Unit Sold! Unit '{name}' in {compound} has been sold"

#### Price Drop
- **Event**: Unit price is reduced
- **Recipients**: All buyers
- **Notification**: "Price Drop Alert! Unit '{name}' price reduced by X%"

#### New Compound Added
- **Event**: New compound created in database
- **Recipients**: All buyers and agents
- **Notification**: "New Compound Available! '{name}' by {company} in {location} is now available"

#### New Sale/Promotion
- **Event**: New sale created in database
- **Recipients**: All buyers
- **Notification**: "New Sale Alert! {sale_name} - Save up to X%"

#### Sale Activated
- **Event**: Sale `is_active` status changes to true
- **Recipients**: All buyers
- **Notification**: "Sale Now Active! {sale_name} is now live!"

#### Discount Increased
- **Event**: Sale discount_percentage is increased
- **Recipients**: All buyers
- **Notification**: "Discount Increased! {sale_name} - Discount increased from X% to Y%"

## API Usage

### Save FCM Token (Mobile App Integration)
When a user logs in to the mobile app, save their FCM token:

```http
POST /api/fcm-token
Authorization: Bearer {token}
Content-Type: application/json

{
  "fcm_token": "user_device_fcm_token_here"
}
```

**Response:**
```json
{
  "success": true,
  "message": "FCM token saved successfully",
  "data": {
    "user_id": 1,
    "fcm_token": "user_device_fcm_token_here"
  }
}
```

### Remove FCM Token (Logout)
When a user logs out:

```http
DELETE /api/fcm-token
Authorization: Bearer {token}
```

**Response:**
```json
{
  "success": true,
  "message": "FCM token removed successfully"
}
```

## Manual Notification Sending (Optional)

You can manually send notifications using the `FCMNotificationService`:

```php
use App\Services\FCMNotificationService;

$fcmService = new FCMNotificationService();

// Send to all users
$fcmService->sendToAllUsers(
    'Notification Title',
    'Notification Body',
    ['key' => 'value'] // Optional data
);

// Send to users by role
$fcmService->sendToUsersByRole(
    'buyer', // or 'seller', 'agent'
    'Notification Title',
    'Notification Body',
    ['key' => 'value']
);

// Send to specific user by token
$fcmService->sendToUser(
    'user_fcm_token',
    'Notification Title',
    'Notification Body',
    ['key' => 'value']
);

// Send to multiple users
$fcmService->sendToMultipleUsers(
    ['token1', 'token2'],
    'Notification Title',
    'Notification Body',
    ['key' => 'value']
);

// Send to topic (requires topic subscription)
$fcmService->sendToTopic(
    'all', // topic name
    'Notification Title',
    'Notification Body',
    ['key' => 'value']
);
```

## Mobile App Integration

### Android (Flutter/React Native)
1. Install Firebase messaging package
2. Get FCM token on app launch
3. Send token to API endpoint `/api/fcm-token`
4. Handle incoming notifications

### iOS (Flutter/React Native)
1. Install Firebase messaging package
2. Request notification permissions
3. Get FCM token
4. Send token to API endpoint `/api/fcm-token`
5. Handle incoming notifications

## Testing

### Test Notification Manually
You can test by directly calling the service in tinker:

```bash
php artisan tinker
```

```php
$fcmService = new App\Services\FCMNotificationService();
$fcmService->sendToUsersByRole('buyer', 'Test Notification', 'This is a test message');
```

### Test with Database Changes
1. Make sure at least one user has an FCM token in the database
2. Add a new unit, compound, or sale directly to the database
3. Check your mobile device for the notification

## Troubleshooting

### Notifications Not Being Sent
1. Check Laravel logs: `storage/logs/laravel.log`
2. Verify Firebase credentials file exists and path is correct
3. Ensure users have valid FCM tokens in database
4. Check that Firebase project has Cloud Messaging enabled

### Firebase Credentials Error
```
Failed to initialize FCM: Firebase credentials file not found
```
**Solution**: Verify the credentials file path in `.env` and ensure file exists

### No Users with FCM Tokens
```
No users with FCM tokens found
```
**Solution**: Users need to log in through the mobile app and save their FCM tokens

## Notification Data Structure

All notifications include additional data that can be used by the mobile app:

```json
{
  "type": "new_unit|unit_sold|price_drop|new_compound|new_sale|sale_activated|discount_increased",
  "unit_id": "123",
  "unit_name": "Apartment 101",
  "compound_id": "45",
  "compound_name": "Green Valley",
  "price": "1500000",
  "sale_id": "67",
  "discount_percentage": "20"
}
```

The mobile app can use this data to:
- Navigate to specific screens
- Show rich notifications
- Update UI in real-time

## Security Notes

1. FCM tokens are stored securely in the database
2. Only authenticated users can save/remove their tokens
3. Firebase credentials are stored outside web root in `storage/` directory
4. All notification sending is logged for audit purposes

## Performance Considerations

- Notifications are sent asynchronously
- If notification sending fails, it won't affect the main application flow
- Errors are logged but don't stop database operations
- Consider using Laravel Queues for high-volume notifications (future enhancement)

## Future Enhancements

1. **Queue Integration**: Move notification sending to background jobs
2. **Notification History**: Store sent notifications in database
3. **User Preferences**: Allow users to customize notification settings
4. **Rich Notifications**: Add images and action buttons
5. **Scheduled Notifications**: Send reminders for expiring sales
6. **Analytics**: Track notification delivery and engagement

## Support

For issues or questions:
1. Check Laravel logs: `storage/logs/laravel.log`
2. Check Firebase Console for delivery statistics
3. Review this documentation
4. Contact the development team
