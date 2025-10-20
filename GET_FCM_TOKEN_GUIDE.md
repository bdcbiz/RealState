# How to Get Your FCM Token for Push Notifications

## ⚠️ IMPORTANT: You're Using the Wrong Token!

### ❌ What You Used (WRONG):
```
35|3SUo4Nw60Sx64U7gGYNYjbdg6LRTmilObMYAqoAQ566b880e
```
- This is a **Laravel Sanctum API Token**
- Used for: API authentication
- Length: ~50 characters
- **Cannot receive push notifications**

### ✅ What You Need (CORRECT):
```
dEXAMPLE:APA91bGHK4aW9xnKF7TpvLGAFJQSTUVWXYZ1234567890abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890...
```
- This is a **Firebase Cloud Messaging (FCM) Token**
- Used for: Receiving push notifications
- Length: 150-200+ characters
- **This is what makes notifications work**

---

## 📱 How to Get FCM Token from Your Emulator

### For Flutter Apps

1. **Add Firebase Messaging package** to `pubspec.yaml`:
```yaml
dependencies:
  firebase_core: ^2.24.2
  firebase_messaging: ^14.7.9
```

2. **Run:** `flutter pub get`

3. **Add this code** to your app (e.g., in `main.dart`):

```dart
import 'package:firebase_core/firebase_core.dart';
import 'package:firebase_messaging/firebase_messaging.dart';

Future<void> main() async {
  WidgetsFlutterBinding.ensureInitialized();

  // Initialize Firebase
  await Firebase.initializeApp();

  // Get FCM token
  await getFCMToken();

  runApp(MyApp());
}

Future<void> getFCMToken() async {
  FirebaseMessaging messaging = FirebaseMessaging.instance;

  // Request permission (iOS)
  NotificationSettings settings = await messaging.requestPermission(
    alert: true,
    badge: true,
    sound: true,
  );

  // Get the token
  String? token = await messaging.getToken();

  print("╔═══════════════════════════════════════════════════════╗");
  print("║  FCM TOKEN - COPY THIS ENTIRE LINE:                  ║");
  print("╠═══════════════════════════════════════════════════════╣");
  print("║  $token");
  print("╚═══════════════════════════════════════════════════════╝");

  // Send to your Laravel API
  if (token != null) {
    await sendTokenToServer(token);
  }
}

Future<void> sendTokenToServer(String fcmToken) async {
  // Replace with your API endpoint
  final response = await http.post(
    Uri.parse('http://127.0.0.1:8001/api/fcm-token'),
    headers: {
      'Content-Type': 'application/json',
      'Authorization': 'Bearer YOUR_API_TOKEN_HERE',
    },
    body: jsonEncode({'fcm_token': fcmToken}),
  );

  print('Token sent to server: ${response.statusCode}');
}
```

4. **Run your app** on the emulator
5. **Check the console** - copy the FCM token

---

### For React Native Apps

1. **Install Firebase Messaging:**
```bash
npm install @react-native-firebase/app @react-native-firebase/messaging
```

2. **Add this code** to your app:

```javascript
import messaging from '@react-native-firebase/messaging';
import axios from 'axios';

async function getFCMToken() {
  try {
    // Request permission
    const authStatus = await messaging().requestPermission();
    const enabled =
      authStatus === messaging.AuthorizationStatus.AUTHORIZED ||
      authStatus === messaging.AuthorizationStatus.PROVISIONAL;

    if (enabled) {
      // Get token
      const token = await messaging().getToken();

      console.log("╔═══════════════════════════════════════════════════════╗");
      console.log("║  FCM TOKEN - COPY THIS ENTIRE LINE:                  ║");
      console.log("╠═══════════════════════════════════════════════════════╣");
      console.log("║ ", token);
      console.log("╚═══════════════════════════════════════════════════════╝");

      // Send to your Laravel API
      await sendTokenToServer(token);
    }
  } catch (error) {
    console.error('Error getting FCM token:', error);
  }
}

async function sendTokenToServer(fcmToken) {
  try {
    const response = await axios.post(
      'http://127.0.0.1:8001/api/fcm-token',
      { fcm_token: fcmToken },
      {
        headers: {
          'Authorization': 'Bearer YOUR_API_TOKEN_HERE',
          'Content-Type': 'application/json',
        },
      }
    );
    console.log('Token sent to server:', response.data);
  } catch (error) {
    console.error('Error sending token:', error);
  }
}

// Call this when app starts
useEffect(() => {
  getFCMToken();
}, []);
```

3. **Run your app** on the emulator
4. **Check the console** - copy the FCM token

---

### For Native Android

**In your MainActivity.kt or Application class:**

```kotlin
import com.google.firebase.messaging.FirebaseMessaging
import android.util.Log

// In onCreate() or initialization
FirebaseMessaging.getInstance().token.addOnCompleteListener { task ->
    if (!task.isSuccessful) {
        Log.w("FCM", "Fetching FCM token failed", task.exception)
        return@addOnCompleteListener
    }

    // Get token
    val token = task.result

    Log.d("FCM", "╔═══════════════════════════════════════════════════════╗")
    Log.d("FCM", "║  FCM TOKEN - COPY THIS ENTIRE LINE:                  ║")
    Log.d("FCM", "╠═══════════════════════════════════════════════════════╣")
    Log.d("FCM", "║  $token")
    Log.d("FCM", "╚═══════════════════════════════════════════════════════╝")

    // Send to your Laravel API
    sendTokenToServer(token)
}
```

---

## 🎯 What to Do After Getting the Token

### Method 1: Via Testing Dashboard (Easiest)
1. Open: http://127.0.0.1:8001/test-fcm.html
2. Scroll to "Add Real FCM Token" section
3. Paste the **LONG FCM token** (not the API token)
4. Click "Save Real Token"
5. Click "Send Test Notification"
6. Check your emulator - you should receive the notification!

### Method 2: Via API Call
```bash
curl -X POST "http://127.0.0.1:8001/api/fcm-token" \
  -H "Authorization: Bearer 35|3SUo4Nw60Sx64U7gGYNYjbdg6LRTmilObMYAqoAQ566b880e" \
  -H "Content-Type: application/json" \
  -d "{\"fcm_token\":\"PASTE_YOUR_LONG_FCM_TOKEN_HERE\"}"
```

### Method 3: Via Tinker
```bash
cd c:\xampp\htdocs\larvel2\real-estate-api
php artisan tinker
```
```php
$user = App\Models\User::first();
$user->fcm_token = 'PASTE_YOUR_LONG_FCM_TOKEN_HERE';
$user->save();
exit
```

---

## 🔍 How to Identify the Correct Token

### API Token (WRONG for notifications):
- ✗ Format: `35|3SUo4Nw60Sx64U7gGYNYjbdg6LRTmilObMYAqoAQ566b880e`
- ✗ Length: ~50 characters
- ✗ Starts with: number followed by pipe (|)
- ✗ Purpose: API authentication only

### FCM Token (CORRECT for notifications):
- ✓ Format: `dEXAMPLE:APA91bG...very_long_alphanumeric_string...`
- ✓ Length: 150-200+ characters
- ✓ Contains: colon (:) in the middle
- ✓ Purpose: Receiving push notifications

---

## ⚡ Quick Test

After saving the correct FCM token:

1. **Send a test notification:**
```bash
cd c:\xampp\htdocs\larvel2\real-estate-api
php artisan tinker
```
```php
$fcm = new App\Services\FCMNotificationService();
$fcm->sendToAllUsers('Test from Laravel', 'Check your emulator now!');
exit
```

2. **Check your emulator** - you should see a notification appear!

---

## 🆘 Still Not Working?

### Checklist:
- [ ] Firebase project created in Firebase Console
- [ ] Firebase app added to your project (Android/iOS)
- [ ] Firebase Cloud Messaging enabled
- [ ] Firebase credentials JSON file placed in: `storage/app/firebase/`
- [ ] Real FCM token (150+ chars) saved to database
- [ ] Emulator has Google Play Services (for Android)
- [ ] App has notification permissions

### Test Firebase Setup:
Visit Firebase Console → Cloud Messaging → Send test message
- Enter your FCM token
- Send
- If you receive it, Firebase is working!
- If not, check your mobile app Firebase setup

---

## 📞 Need Help?

1. Show me your mobile app's console output
2. Tell me what platform you're using (Flutter/React Native/Native)
3. Confirm you see the FCM token printed in logs
4. The token should be 150+ characters long

Remember: **API tokens ≠ FCM tokens**. They are completely different!
