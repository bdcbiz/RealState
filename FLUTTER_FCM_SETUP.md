# Flutter FCM Setup - Complete Guide

## 🎯 Quick Setup (5 Steps)

### Step 1: Add Dependencies

Edit your `pubspec.yaml`:

```yaml
dependencies:
  flutter:
    sdk: flutter
  firebase_core: ^2.24.2
  firebase_messaging: ^14.7.9
  http: ^1.1.0
```

Run:
```bash
flutter pub get
```

### Step 2: Configure Firebase for Your Flutter App

#### For Android:
1. Go to [Firebase Console](https://console.firebase.google.com/)
2. Select your project
3. Click "Add app" → Android
4. Enter your package name (from `android/app/build.gradle`)
5. Download `google-services.json`
6. Place it in: `android/app/google-services.json`

#### For iOS (if needed):
1. In Firebase Console, click "Add app" → iOS
2. Enter bundle ID (from `ios/Runner/Info.plist`)
3. Download `GoogleService-Info.plist`
4. Place it in: `ios/Runner/GoogleService-Info.plist`

### Step 3: Update Android Configuration

Edit `android/app/build.gradle`:

```gradle
// At the top, after 'apply plugin: com.android.application'
apply plugin: 'com.google.gms.google-services'

android {
    // ... existing config
    defaultConfig {
        // ... existing config
        minSdkVersion 21  // Must be 21 or higher
    }
}

dependencies {
    // ... existing dependencies
    implementation platform('com.google.firebase:firebase-bom:32.7.0')
}
```

Edit `android/build.gradle`:

```gradle
buildscript {
    dependencies {
        // ... existing dependencies
        classpath 'com.google.gms:google-services:4.4.0'
    }
}
```

### Step 4: Add FCM Code to Your App

Replace your `lib/main.dart` with this complete example:

```dart
import 'package:flutter/material.dart';
import 'package:firebase_core/firebase_core.dart';
import 'package:firebase_messaging/firebase_messaging.dart';
import 'package:http/http.dart' as http;
import 'dart:convert';

// IMPORTANT: Background message handler - must be top-level function
@pragma('vm:entry-point')
Future<void> _firebaseMessagingBackgroundHandler(RemoteMessage message) async {
  await Firebase.initializeApp();
  print('📱 Background notification received!');
  print('Title: ${message.notification?.title}');
  print('Body: ${message.notification?.body}');
}

void main() async {
  WidgetsFlutterBinding.ensureInitialized();

  // Initialize Firebase
  await Firebase.initializeApp();

  // Set up background message handler
  FirebaseMessaging.onBackgroundMessage(_firebaseMessagingBackgroundHandler);

  // Get FCM token and setup messaging
  await setupFirebaseMessaging();

  runApp(MyApp());
}

Future<void> setupFirebaseMessaging() async {
  FirebaseMessaging messaging = FirebaseMessaging.instance;

  // Request permission (required for iOS, good practice for Android)
  NotificationSettings settings = await messaging.requestPermission(
    alert: true,
    badge: true,
    sound: true,
    provisional: false,
  );

  print('\n═══════════════════════════════════════════════════════');
  print('Firebase Messaging Setup');
  print('═══════════════════════════════════════════════════════\n');

  if (settings.authorizationStatus == AuthorizationStatus.authorized) {
    print('✅ User granted notification permission');
  } else if (settings.authorizationStatus == AuthorizationStatus.provisional) {
    print('⚠️ User granted provisional permission');
  } else {
    print('❌ User declined notification permission');
  }

  // Get the FCM token
  String? token = await messaging.getToken();

  print('\n╔═══════════════════════════════════════════════════════╗');
  print('║                   FCM TOKEN                           ║');
  print('║           COPY THIS ENTIRE STRING:                   ║');
  print('╠═══════════════════════════════════════════════════════╣');
  print('║ $token');
  print('╚═══════════════════════════════════════════════════════╝\n');

  // Auto-send token to Laravel backend
  if (token != null) {
    await sendTokenToBackend(token);
  }

  // Listen for token refresh (when token changes)
  messaging.onTokenRefresh.listen((newToken) {
    print('🔄 FCM Token refreshed: $newToken');
    sendTokenToBackend(newToken);
  });

  // Handle foreground messages (when app is open)
  FirebaseMessaging.onMessage.listen((RemoteMessage message) {
    print('\n📬 Foreground notification received!');
    print('Title: ${message.notification?.title}');
    print('Body: ${message.notification?.body}');
    print('Data: ${message.data}\n');

    // Show notification in app (you can customize this)
    if (message.notification != null) {
      showDialog(
        context: navigatorKey.currentContext!,
        builder: (context) => AlertDialog(
          title: Text(message.notification!.title ?? 'Notification'),
          content: Text(message.notification!.body ?? ''),
          actions: [
            TextButton(
              onPressed: () => Navigator.pop(context),
              child: Text('OK'),
            ),
          ],
        ),
      );
    }
  });

  // Handle notification when app is opened from notification
  FirebaseMessaging.onMessageOpenedApp.listen((RemoteMessage message) {
    print('\n📲 App opened from notification!');
    print('Data: ${message.data}');

    // Navigate based on notification data
    handleNotificationNavigation(message.data);
  });

  // Check if app was opened from terminated state by notification
  RemoteMessage? initialMessage = await messaging.getInitialMessage();
  if (initialMessage != null) {
    print('\n🚀 App launched from notification!');
    print('Data: ${initialMessage.data}');
    handleNotificationNavigation(initialMessage.data);
  }
}

Future<void> sendTokenToBackend(String fcmToken) async {
  const String apiUrl = 'http://127.0.0.1:8001/api/fcm-token';
  const String apiToken = '35|3SUo4Nw60Sx64U7gGYNYjbdg6LRTmilObMYAqoAQ566b880e';

  try {
    print('📤 Sending FCM token to backend...');

    final response = await http.post(
      Uri.parse(apiUrl),
      headers: {
        'Content-Type': 'application/json',
        'Authorization': 'Bearer $apiToken',
        'Accept': 'application/json',
      },
      body: jsonEncode({'fcm_token': fcmToken}),
    );

    if (response.statusCode == 200) {
      print('✅ FCM token successfully sent to backend!');
      print('Response: ${response.body}');
    } else {
      print('❌ Failed to send token. Status: ${response.statusCode}');
      print('Response: ${response.body}');
    }
  } catch (e) {
    print('❌ Error sending token to backend: $e');
    print('Make sure Laravel server is running at http://127.0.0.1:8001');
  }
}

void handleNotificationNavigation(Map<String, dynamic> data) {
  // Handle navigation based on notification type
  print('Handling notification navigation: $data');

  String? type = data['type'];

  switch (type) {
    case 'new_unit':
      // Navigate to unit details
      String? unitId = data['unit_id'];
      print('Navigate to unit: $unitId');
      // Navigator.pushNamed(context, '/unit-details', arguments: unitId);
      break;
    case 'new_sale':
      // Navigate to sale details
      String? saleId = data['sale_id'];
      print('Navigate to sale: $saleId');
      // Navigator.pushNamed(context, '/sale-details', arguments: saleId);
      break;
    case 'new_compound':
      // Navigate to compound details
      String? compoundId = data['compound_id'];
      print('Navigate to compound: $compoundId');
      break;
    default:
      print('Unknown notification type: $type');
  }
}

// Global navigator key for showing dialogs from anywhere
final GlobalKey<NavigatorState> navigatorKey = GlobalKey<NavigatorState>();

class MyApp extends StatelessWidget {
  @override
  Widget build(BuildContext context) {
    return MaterialApp(
      title: 'Real Estate App',
      navigatorKey: navigatorKey,
      home: HomeScreen(),
      debugShowCheckedModeBanner: false,
    );
  }
}

class HomeScreen extends StatelessWidget {
  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: Text('Real Estate App'),
        backgroundColor: Colors.blue,
      ),
      body: Center(
        child: Padding(
          padding: EdgeInsets.all(20),
          child: Column(
            mainAxisAlignment: MainAxisAlignment.center,
            children: [
              Icon(
                Icons.notifications_active,
                size: 100,
                color: Colors.blue,
              ),
              SizedBox(height: 20),
              Text(
                'FCM Notifications Enabled!',
                style: TextStyle(
                  fontSize: 24,
                  fontWeight: FontWeight.bold,
                ),
              ),
              SizedBox(height: 10),
              Text(
                'Check your console for FCM token',
                style: TextStyle(
                  fontSize: 16,
                  color: Colors.grey[600],
                ),
              ),
              SizedBox(height: 40),
              ElevatedButton.icon(
                onPressed: () async {
                  String? token = await FirebaseMessaging.instance.getToken();
                  print('\n═══ Current FCM Token ═══');
                  print(token);
                  print('═══════════════════════════\n');

                  ScaffoldMessenger.of(context).showSnackBar(
                    SnackBar(content: Text('Token printed to console!')),
                  );
                },
                icon: Icon(Icons.print),
                label: Text('Print FCM Token'),
                style: ElevatedButton.styleFrom(
                  padding: EdgeInsets.symmetric(horizontal: 30, vertical: 15),
                ),
              ),
              SizedBox(height: 10),
              ElevatedButton.icon(
                onPressed: () async {
                  String? token = await FirebaseMessaging.instance.getToken();
                  if (token != null) {
                    await sendTokenToBackend(token);
                    ScaffoldMessenger.of(context).showSnackBar(
                      SnackBar(content: Text('Token sent to backend!')),
                    );
                  }
                },
                icon: Icon(Icons.send),
                label: Text('Send Token to Backend'),
                style: ElevatedButton.styleFrom(
                  padding: EdgeInsets.symmetric(horizontal: 30, vertical: 15),
                  backgroundColor: Colors.green,
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }
}
```

### Step 5: Run Your App

```bash
flutter run
```

## ✅ Verification Checklist

After running your app, check for these in the console:

```
✅ Firebase Messaging Setup
✅ User granted notification permission
✅ FCM TOKEN displayed
✅ FCM token successfully sent to backend!
```

## 🧪 Testing Notifications

### Method 1: From Laravel Backend

Open a new terminal:

```bash
cd c:\xampp\htdocs\larvel2\real-estate-api
php send-test-notification.php
```

### Method 2: Via Testing Dashboard

1. Open: http://127.0.0.1:8001/test-fcm.html
2. Click "Send Test Notification"
3. Check your emulator!

### Method 3: Via Tinker

```bash
php artisan tinker
```
```php
$fcm = new App\Services\FCMNotificationService();
$fcm->sendToAllUsers('Test', 'Hello Flutter!');
exit
```

## 📱 Expected Behavior

### When App is Open (Foreground):
- ✅ Notification appears as a dialog in the app
- ✅ Console prints notification details

### When App is in Background:
- ✅ Notification appears in system tray
- ✅ Clicking it opens the app

### When App is Closed:
- ✅ Notification appears in system tray
- ✅ Clicking it launches the app

## 🐛 Troubleshooting

### "MissingPluginException"
```bash
flutter clean
flutter pub get
flutter run
```

### "Default FirebaseApp is not initialized"
Make sure `Firebase.initializeApp()` is called before anything else in `main()`.

### Token is null
- Check `google-services.json` is in `android/app/`
- Ensure `minSdkVersion` is 21 or higher
- Rebuild the app: `flutter clean && flutter run`

### Notifications not appearing
- Check notification permissions are granted
- Make sure Google Play Services is installed on emulator
- Verify FCM token was sent to Laravel backend successfully

### "Connection refused" when sending token
- Make sure Laravel server is running: `php artisan serve --host=127.0.0.1 --port=8001`
- Update the API URL in code if using different host/port

## 📊 Notification Data Structure

Your Laravel backend sends this data with notifications:

```dart
{
  "type": "new_unit" | "new_sale" | "new_compound" | "price_drop",
  "unit_id": "123",
  "compound_id": "45",
  "sale_id": "67",
  "unit_name": "Luxury Apartment",
  // ... other fields
}
```

Use `message.data` to access this and navigate to specific screens!

## 🎉 Success!

If you see:
- ✅ FCM token in console
- ✅ "Token sent to backend successfully"
- ✅ Notification appears on emulator

**Congratulations! FCM is working!** 🚀

Now whenever you add a new unit, compound, or sale to your Laravel database, all users with the app will receive a push notification automatically!
