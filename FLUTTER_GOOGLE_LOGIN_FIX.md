# 🔧 FIX: Google Login "Wrong number of segments" Error

## 🚨 **PROBLEM**
Error: "Google authentication failed: Wrong number of segments"

**Cause:** Flutter is sending an invalid or malformed ID token to the backend.

---

## ✅ **SOLUTION: CORRECT FLUTTER CODE**

### **Step 1: Update pubspec.yaml**

```yaml
dependencies:
  flutter:
    sdk: flutter
  google_sign_in: ^6.1.5
  http: ^1.1.0

  # For web support
  google_sign_in_web: ^0.12.0
```

Run: `flutter pub get`

---

### **Step 2: Create GoogleAuthService (CORRECT VERSION)**

**File:** `lib/services/google_auth_service.dart`

```dart
import 'package:google_sign_in/google_sign_in.dart';
import 'package:http/http.dart' as http;
import 'dart:convert';
import 'package:flutter/foundation.dart' show kIsWeb;

class GoogleAuthService {
  // IMPORTANT: Use WEB CLIENT ID for serverClientId on Android
  static const String _webClientId =
      '832433207149-vlahshba4mbt380tbjg43muqo7l6s1o9.apps.googleusercontent.com';

  late final GoogleSignIn _googleSignIn;

  GoogleAuthService() {
    if (kIsWeb) {
      // For Web: Use clientId
      _googleSignIn = GoogleSignIn(
        clientId: _webClientId,
        scopes: ['email', 'profile'],
      );
    } else {
      // For Android/iOS: Use serverClientId
      _googleSignIn = GoogleSignIn(
        scopes: ['email', 'profile'],
        serverClientId: _webClientId, // This makes it return ID token
      );
    }
  }

  Future<Map<String, dynamic>?> signInWithGoogle() async {
    try {
      print('🔵 Starting Google Sign-In...');

      // Sign out first to ensure fresh login
      await _googleSignIn.signOut();

      // Trigger Google Sign-In
      final GoogleSignInAccount? googleUser = await _googleSignIn.signIn();

      if (googleUser == null) {
        print('🔴 User cancelled sign-in');
        return null;
      }

      print('✅ Google user signed in: ${googleUser.email}');

      // Get authentication details
      final GoogleSignInAuthentication googleAuth = await googleUser.authentication;

      // CRITICAL: Get ID token (NOT access token!)
      final String? idToken = googleAuth.idToken;

      if (idToken == null) {
        print('🔴 ERROR: ID token is null!');
        throw Exception('Failed to get ID token from Google');
      }

      // Debug: Print token info
      print('✅ ID Token received');
      print('📊 Token length: ${idToken.length}');
      print('📊 Token segments: ${idToken.split('.').length}');
      print('📊 First 50 chars: ${idToken.substring(0, 50)}...');

      // Validate token format before sending
      if (!_isValidJWT(idToken)) {
        print('🔴 ERROR: Invalid JWT format!');
        throw Exception('ID token is not a valid JWT');
      }

      // Send ID token to backend
      print('🔵 Sending ID token to backend...');

      final response = await http.post(
        Uri.parse('https://aqar.bdcbiz.com/api/login'),
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
        },
        body: jsonEncode({
          'login_method': 'google',
          'id_token': idToken.trim(), // Trim any whitespace
        }),
      );

      print('📊 Backend response status: ${response.statusCode}');
      print('📊 Backend response body: ${response.body}');

      if (response.statusCode == 200) {
        final data = jsonDecode(response.body);

        if (data['success'] == true) {
          print('✅ Login successful!');
          return {
            'user': data['data']['user'],
            'token': data['data']['token'],
          };
        } else {
          print('🔴 Backend returned success=false: ${data['message']}');
          throw Exception(data['message'] ?? 'Login failed');
        }
      } else {
        final errorData = jsonDecode(response.body);
        print('🔴 Backend error: ${errorData['message']}');
        throw Exception('Backend error: ${errorData['message']}');
      }

    } catch (e) {
      print('🔴 Google Sign-In Error: $e');
      rethrow;
    }
  }

  // Validate JWT format (should have 3 parts: header.payload.signature)
  bool _isValidJWT(String token) {
    final parts = token.split('.');
    return parts.length == 3 &&
           parts.every((part) => part.isNotEmpty);
  }

  Future<void> signOut() async {
    await _googleSignIn.signOut();
    print('✅ Signed out from Google');
  }
}
```

---

### **Step 3: For WEB - Update web/index.html**

**File:** `web/index.html`

Add this **BEFORE** the closing `</head>` tag:

```html
<head>
  <!-- ... existing head content ... -->

  <!-- Google Sign-In for Web -->
  <script src="https://accounts.google.com/gsi/client" async defer></script>
  <meta name="google-signin-client_id"
        content="832433207149-vlahshba4mbt380tbjg43muqo7l6s1o9.apps.googleusercontent.com">
</head>
```

---

### **Step 4: For ANDROID - Verify Configuration**

**1. Ensure google-services.json is in place:**
```
android/app/google-services.json
```

**2. Update android/build.gradle:**

```gradle
buildscript {
    dependencies {
        // Add this if not present
        classpath 'com.google.gms:google-services:4.4.0'
    }
}
```

**3. Update android/app/build.gradle:**

```gradle
dependencies {
    implementation 'com.google.android.gms:play-services-auth:20.7.0'
}

// At the VERY BOTTOM of the file
apply plugin: 'com.google.gms.google-services'
```

---

### **Step 5: Usage in Your Login Page**

```dart
import 'package:flutter/material.dart';
import 'services/google_auth_service.dart';

class LoginPage extends StatefulWidget {
  @override
  _LoginPageState createState() => _LoginPageState();
}

class _LoginPageState extends State<LoginPage> {
  final GoogleAuthService _authService = GoogleAuthService();
  bool _isLoading = false;
  String? _errorMessage;

  Future<void> _handleGoogleSignIn() async {
    setState(() {
      _isLoading = true;
      _errorMessage = null;
    });

    try {
      final result = await _authService.signInWithGoogle();

      if (result != null) {
        // Success!
        final user = result['user'];
        final token = result['token'];

        print('✅ Login successful!');
        print('User: ${user['name']}');
        print('Email: ${user['email']}');
        print('Token: $token');

        // TODO: Save token to secure storage
        // TODO: Navigate to home page

        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text('Welcome ${user['name']}!')),
        );
      } else {
        setState(() {
          _errorMessage = 'Login cancelled';
        });
      }
    } catch (e) {
      setState(() {
        _errorMessage = e.toString();
      });

      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text('Login failed: $e'),
          backgroundColor: Colors.red,
        ),
      );
    } finally {
      setState(() {
        _isLoading = false;
      });
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      body: Center(
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Text(
              'Welcome Back',
              style: TextStyle(fontSize: 24, fontWeight: FontWeight.bold),
            ),
            SizedBox(height: 40),

            // Google Sign-In Button
            ElevatedButton.icon(
              onPressed: _isLoading ? null : _handleGoogleSignIn,
              icon: Image.asset('assets/google_logo.png', height: 24), // Add Google logo
              label: Text(_isLoading ? 'Signing in...' : 'Continue with Google'),
              style: ElevatedButton.styleFrom(
                padding: EdgeInsets.symmetric(horizontal: 24, vertical: 12),
              ),
            ),

            // Error message
            if (_errorMessage != null)
              Padding(
                padding: EdgeInsets.all(16),
                child: Text(
                  _errorMessage!,
                  style: TextStyle(color: Colors.red),
                  textAlign: TextAlign.center,
                ),
              ),
          ],
        ),
      ),
    );
  }
}
```

---

## 🔍 **DEBUGGING TIPS**

### **Check Console Output:**

When you run the app, you should see:
```
🔵 Starting Google Sign-In...
✅ Google user signed in: user@email.com
✅ ID Token received
📊 Token length: 900+ characters
📊 Token segments: 3
📊 First 50 chars: eyJhbGciOiJSUzI1NiIsImtpZCI6IjVlOGQzOGU3ZDhmN...
🔵 Sending ID token to backend...
📊 Backend response status: 200
✅ Login successful!
```

### **If You See Token segments: 1 or 2:**
- Problem: Not getting ID token properly
- Fix: Make sure you're using `serverClientId` on Android
- Fix: Make sure you have the correct client ID

### **If Backend Returns 400 with "Wrong number of segments":**
- Check the debug output from backend
- The backend will now tell you exactly how many segments it received
- Check Laravel logs: `tail -f /var/www/realestate/storage/logs/laravel.log`

---

## 🎯 **KEY DIFFERENCES FROM BEFORE**

1. ✅ Added `serverClientId` for Android (required to get ID token)
2. ✅ Added platform detection (web vs mobile)
3. ✅ Added JWT validation before sending to backend
4. ✅ Added detailed logging for debugging
5. ✅ Added token trimming to remove whitespace
6. ✅ Backend now validates token format before processing

---

## 🚀 **TEST STEPS**

1. Run `flutter clean`
2. Run `flutter pub get`
3. For Android: Check google-services.json is in android/app/
4. For Web: Check web/index.html has Google script
5. Run the app: `flutter run`
6. Click "Continue with Google"
7. Watch console for debug output
8. Should see "✅ Login successful!"

---

## 📞 **STILL HAVING ISSUES?**

**Check these:**

1. **For Android:**
   - SHA-1 certificate registered in Firebase Console
   - google-services.json downloaded from Firebase
   - Google Services plugin applied in build.gradle

2. **For Web:**
   - Google Sign-In script in index.html
   - Authorized JavaScript origin: https://aqar.bdcbiz.com
   - Test user added to OAuth consent screen

3. **Backend:**
   - Check Laravel logs for detailed error
   - Updated AuthController deployed on server

**Run this to check backend logs:**
```bash
ssh root@31.97.46.103 "tail -f /var/www/realestate/storage/logs/laravel.log"
```

---

## 📋 **CHECKLIST**

- [ ] Updated pubspec.yaml with correct dependencies
- [ ] Created GoogleAuthService with platform detection
- [ ] Added Google script to web/index.html (for web)
- [ ] Verified google-services.json in android/app/ (for Android)
- [ ] Updated build.gradle files (for Android)
- [ ] Ran flutter pub get
- [ ] Ran flutter clean
- [ ] Tested sign-in
- [ ] Checked console for debug output
- [ ] Checked backend logs if error occurs

---

**This should fix your "Wrong number of segments" error!** 🚀
