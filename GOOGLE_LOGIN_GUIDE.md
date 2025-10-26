# Google Login Integration Guide

## Overview
Your Laravel API now supports **Google OAuth Login** alongside traditional email/password authentication. Users can sign in with their Google account, and the system will automatically create or update their user record.

---

## Database Changes ✅

The following columns have been added to the `users` table:

| Column | Type | Nullable | Default | Description |
|--------|------|----------|---------|-------------|
| `google_id` | VARCHAR(255) | YES | NULL | Unique Google account ID |
| `photo_url` | VARCHAR(255) | YES | NULL | Google profile photo URL |
| `login_method` | VARCHAR(50) | YES | 'manual' | Login method: 'manual' or 'google' |

---

## API Endpoint

### **POST** `/api/login`

This single endpoint handles both manual and Google login based on the `login_method` parameter.

---

## Usage Examples

### 1. Manual Login (Email/Password)

**Request:**
```json
POST /api/login
Content-Type: application/json

{
    "email": "user@example.com",
    "password": "password123",
    "login_method": "manual"
}
```

**Response:**
```json
{
    "success": true,
    "message": "Login successful",
    "data": {
        "user": {
            "id": 1,
            "name": "John Doe",
            "email": "user@example.com",
            "role": "buyer",
            "login_method": "manual",
            ...
        },
        "token": "1|abc123xyz..."
    }
}
```

---

### 2. Google Login

**Request:**
```json
POST /api/login
Content-Type: application/json

{
    "login_method": "google",
    "email": "user@gmail.com",
    "google_id": "1234567890",
    "name": "John Doe",
    "photo_url": "https://lh3.googleusercontent.com/..."
}
```

**Response:**
```json
{
    "success": true,
    "message": "Google login successful",
    "data": {
        "user": {
            "id": 2,
            "name": "John Doe",
            "email": "user@gmail.com",
            "google_id": "1234567890",
            "photo_url": "https://lh3.googleusercontent.com/...",
            "login_method": "google",
            "role": "buyer",
            "is_verified": true,
            ...
        },
        "token": "2|def456uvw..."
    }
}
```

---

## Frontend Integration (Flutter Example)

### Using `google_sign_in` package

```dart
import 'package:google_sign_in/google_sign_in.dart';
import 'package:http/http.dart' as http;
import 'dart:convert';

class AuthService {
  final GoogleSignIn _googleSignIn = GoogleSignIn(
    scopes: ['email', 'profile'],
  );

  Future<Map<String, dynamic>?> signInWithGoogle() async {
    try {
      // Trigger Google Sign-In flow
      final GoogleSignInAccount? googleUser = await _googleSignIn.signIn();

      if (googleUser == null) {
        return null; // User canceled
      }

      // Get user details
      final String email = googleUser.email;
      final String name = googleUser.displayName ?? '';
      final String googleId = googleUser.id;
      final String? photoUrl = googleUser.photoUrl;

      // Send to your Laravel API
      final response = await http.post(
        Uri.parse('https://your-api.com/api/login'),
        headers: {'Content-Type': 'application/json'},
        body: jsonEncode({
          'login_method': 'google',
          'email': email,
          'google_id': googleId,
          'name': name,
          'photo_url': photoUrl,
        }),
      );

      if (response.statusCode == 200) {
        final data = jsonDecode(response.body);
        return data;
      }

      return null;
    } catch (e) {
      print('Google Sign-In Error: $e');
      return null;
    }
  }

  Future<void> signOut() async {
    await _googleSignIn.signOut();
  }
}
```

---

## How It Works

### First-Time Google Login (New User)
1. User signs in with Google on frontend
2. Frontend sends Google user data to `/api/login` with `login_method: "google"`
3. Backend checks if user exists by email
4. If not found, creates new user with:
   - `google_id` from Google
   - `photo_url` from Google
   - `login_method` = `'google'`
   - `role` = `'buyer'` (default)
   - `is_verified` = `true` (Google users are pre-verified)
   - Random password (not used for Google login)
5. Returns user data + authentication token

### Returning Google Login (Existing User)
1. User signs in with Google on frontend
2. Frontend sends Google user data to `/api/login`
3. Backend finds existing user by email
4. Updates user's `google_id`, `photo_url`, and `login_method`
5. Returns user data + authentication token

---

## Security Features

- ✅ Google ID is validated and stored
- ✅ Email uniqueness is enforced
- ✅ Google users are automatically verified
- ✅ Existing users can link their Google account
- ✅ Tokens use Laravel Sanctum for security
- ✅ Random password generated for Google users (not exposed)

---

## Testing with Postman

### Test Google Login

1. Open Postman
2. Create new POST request to: `https://your-api.com/api/login`
3. Headers:
   ```
   Content-Type: application/json
   ```
4. Body (raw JSON):
   ```json
   {
       "login_method": "google",
       "email": "test@gmail.com",
       "google_id": "123456789",
       "name": "Test User",
       "photo_url": "https://example.com/photo.jpg"
   }
   ```
5. Send request
6. You should receive a success response with user data and token

---

## Error Handling

### Validation Errors

If required fields are missing:

```json
{
    "message": "The email field is required. (and 2 more errors)",
    "errors": {
        "email": ["The email field is required."],
        "google_id": ["The google id field is required."],
        "name": ["The name field is required."]
    }
}
```

---

## Migration Files

The migration has been applied to your remote database at `31.97.46.103`.

Local migration file created at:
```
database/migrations/2025_10_25_084543_add_google_fields_to_users_table.php
```

To run locally (if needed):
```bash
php artisan migrate
```

---

## Next Steps

1. ✅ Configure Google OAuth in your Flutter app
2. ✅ Add Google Sign-In button to your login screen
3. ✅ Test the login flow end-to-end
4. ✅ Handle token storage in your app
5. ✅ Use the token for authenticated API requests

---

## Support

For issues or questions:
- Check Laravel logs: `storage/logs/laravel.log`
- Test API endpoints with Postman
- Verify database columns are present

---

**Status:** ✅ **READY TO USE**

Your API now supports Google Login! 🎉
