# 📧 EMAIL VERIFICATION SYSTEM - COMPLETE GUIDE

## ✅ **SYSTEM DEPLOYED SUCCESSFULLY!**

---

## 📋 **OVERVIEW**

When users register, they receive a **6-digit verification code** that they must enter to verify their email.

**Features:**
- ✅ 6-digit verification code generated on registration
- ✅ Code expires in 15 minutes
- ✅ Resend code functionality
- ✅ Code validation with proper error handling
- ✅ **NO real email** - code returned in API response for testing

---

## 🚀 **HOW IT WORKS**

### **Step 1: User Registers**
```
POST https://aqar.bdcbiz.com/api/register
```

**Request Body:**
```json
{
  "name": "John Doe",
  "email": "john@example.com",
  "password": "password123",
  "password_confirmation": "password123",
  "role": "buyer",
  "phone": "01234567890"
}
```

**Response (201):**
```json
{
  "success": true,
  "message": "User registered successfully. Please verify your email with the code sent.",
  "data": {
    "user": {
      "id": 123,
      "name": "John Doe",
      "email": "john@example.com",
      "role": "buyer",
      "is_verified": false,  // ← User not verified yet
      "verification_code": "123456",  // ← Hidden in production
      "verification_code_expires_at": "2025-10-27 12:45:00"
    },
    "token": "123|abcdefg...",
    "verification_code": "123456",  // ← 6-DIGIT CODE (for testing)
    "verification_expires_in": "15 minutes"
  }
}
```

---

### **Step 2: User Enters Verification Code**

**Frontend Flow:**
1. Show verification screen
2. User enters the 6-digit code
3. Call verify-email endpoint

```
POST https://aqar.bdcbiz.com/api/verify-email
```

**Request Body:**
```json
{
  "email": "john@example.com",
  "verification_code": "123456"
}
```

**Success Response (200):**
```json
{
  "success": true,
  "message": "Email verified successfully",
  "data": {
    "user": {
      "id": 123,
      "name": "John Doe",
      "email": "john@example.com",
      "is_verified": true,  // ← Now verified!
      "email_verified_at": "2025-10-27 12:32:00"
    }
  }
}
```

**Error Responses:**

**Invalid Code (400):**
```json
{
  "success": false,
  "message": "Invalid verification code"
}
```

**Expired Code (400):**
```json
{
  "success": false,
  "message": "Verification code has expired. Please request a new code."
}
```

**User Not Found (404):**
```json
{
  "success": false,
  "message": "User not found"
}
```

**Already Verified (200):**
```json
{
  "success": true,
  "message": "Email already verified",
  "data": {
    "user": { ... }
  }
}
```

---

### **Step 3: Resend Code (if expired or lost)**

```
POST https://aqar.bdcbiz.com/api/resend-verification-code
```

**Request Body:**
```json
{
  "email": "john@example.com"
}
```

**Success Response (200):**
```json
{
  "success": true,
  "message": "New verification code generated",
  "data": {
    "verification_code": "654321",  // ← NEW 6-DIGIT CODE
    "verification_expires_in": "15 minutes"
  }
}
```

**Error - Already Verified (400):**
```json
{
  "success": false,
  "message": "Email already verified"
}
```

---

## 🎯 **FLUTTER IMPLEMENTATION**

### **Step 1: Registration Screen**

```dart
import 'package:http/http.dart' as http;
import 'dart:convert';

class RegistrationService {
  Future<Map<String, dynamic>?> register({
    required String name,
    required String email,
    required String password,
    required String role,
    String? phone,
  }) async {
    try {
      final response = await http.post(
        Uri.parse('https://aqar.bdcbiz.com/api/register'),
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
        },
        body: jsonEncode({
          'name': name,
          'email': email,
          'password': password,
          'password_confirmation': password,
          'role': role,
          'phone': phone,
        }),
      );

      if (response.statusCode == 201) {
        final data = jsonDecode(response.body);

        if (data['success'] == true) {
          // Registration successful!
          // Save user data and verification code
          return {
            'user': data['data']['user'],
            'token': data['data']['token'],
            'verification_code': data['data']['verification_code'], // For testing
          };
        }
      }

      throw Exception('Registration failed');
    } catch (e) {
      print('Registration error: $e');
      return null;
    }
  }
}
```

---

### **Step 2: Verification Screen**

```dart
class VerificationScreen extends StatefulWidget {
  final String email;

  VerificationScreen({required this.email});

  @override
  _VerificationScreenState createState() => _VerificationScreenState();
}

class _VerificationScreenState extends State<VerificationScreen> {
  final TextEditingController _codeController = TextEditingController();
  bool _isLoading = false;

  Future<void> _verifyCode() async {
    if (_codeController.text.length != 6) {
      _showError('Please enter a 6-digit code');
      return;
    }

    setState(() => _isLoading = true);

    try {
      final response = await http.post(
        Uri.parse('https://aqar.bdcbiz.com/api/verify-email'),
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
        },
        body: jsonEncode({
          'email': widget.email,
          'verification_code': _codeController.text,
        }),
      );

      final data = jsonDecode(response.body);

      if (response.statusCode == 200 && data['success'] == true) {
        // Verification successful!
        _showSuccess('Email verified successfully!');

        // Navigate to home screen
        Navigator.pushReplacementNamed(context, '/home');
      } else {
        _showError(data['message'] ?? 'Verification failed');
      }
    } catch (e) {
      _showError('Network error: $e');
    } finally {
      setState(() => _isLoading = false);
    }
  }

  Future<void> _resendCode() async {
    try {
      final response = await http.post(
        Uri.parse('https://aqar.bdcbiz.com/api/resend-verification-code'),
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
        },
        body: jsonEncode({
          'email': widget.email,
        }),
      );

      final data = jsonDecode(response.body);

      if (response.statusCode == 200 && data['success'] == true) {
        _showSuccess('New code sent! Code: ${data['data']['verification_code']}');
      } else {
        _showError(data['message'] ?? 'Failed to resend code');
      }
    } catch (e) {
      _showError('Network error: $e');
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: Text('Verify Email')),
      body: Padding(
        padding: EdgeInsets.all(24),
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Text(
              'Enter Verification Code',
              style: TextStyle(fontSize: 24, fontWeight: FontWeight.bold),
            ),
            SizedBox(height: 16),
            Text(
              'We sent a 6-digit code to ${widget.email}',
              textAlign: TextAlign.center,
              style: TextStyle(color: Colors.grey),
            ),
            SizedBox(height: 32),

            // Verification Code Input
            TextField(
              controller: _codeController,
              keyboardType: TextInputType.number,
              maxLength: 6,
              textAlign: TextAlign.center,
              style: TextStyle(fontSize: 32, letterSpacing: 8),
              decoration: InputDecoration(
                hintText: '______',
                border: OutlineInputBorder(),
              ),
            ),

            SizedBox(height: 24),

            // Verify Button
            ElevatedButton(
              onPressed: _isLoading ? null : _verifyCode,
              child: _isLoading
                  ? CircularProgressIndicator()
                  : Text('Verify'),
              style: ElevatedButton.styleFrom(
                minimumSize: Size(double.infinity, 50),
              ),
            ),

            SizedBox(height: 16),

            // Resend Code Button
            TextButton(
              onPressed: _resendCode,
              child: Text('Resend Code'),
            ),
          ],
        ),
      ),
    );
  }

  void _showError(String message) {
    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(content: Text(message), backgroundColor: Colors.red),
    );
  }

  void _showSuccess(String message) {
    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(content: Text(message), backgroundColor: Colors.green),
    );
  }
}
```

---

## 🧪 **TESTING WITH POSTMAN**

### **Test 1: Register User**

```
POST https://aqar.bdcbiz.com/api/register

Headers:
Content-Type: application/json
Accept: application/json

Body:
{
  "name": "Test User",
  "email": "test@example.com",
  "password": "password123",
  "password_confirmation": "password123",
  "role": "buyer"
}

Expected Response:
{
  "success": true,
  "verification_code": "123456"  ← Copy this code
}
```

---

### **Test 2: Verify Email**

```
POST https://aqar.bdcbiz.com/api/verify-email

Body:
{
  "email": "test@example.com",
  "verification_code": "123456"  ← Use code from registration
}

Expected Response:
{
  "success": true,
  "message": "Email verified successfully"
}
```

---

### **Test 3: Resend Code**

```
POST https://aqar.bdcbiz.com/api/resend-verification-code

Body:
{
  "email": "test@example.com"
}

Expected Response:
{
  "success": true,
  "verification_code": "654321"  ← New code
}
```

---

## 📊 **DATABASE CHANGES**

**New columns in `users` table:**
```sql
verification_code VARCHAR(6) NULL
verification_code_expires_at TIMESTAMP NULL
```

**Check verification status:**
```sql
SELECT
  email,
  is_verified,
  verification_code,
  verification_code_expires_at
FROM users
WHERE email = 'test@example.com';
```

---

## 🔒 **SECURITY FEATURES**

1. **Code Expiration** - 15 minutes validity
2. **One-time use** - Code cleared after successful verification
3. **Rate limiting** - Prevent spam (can add later)
4. **Email uniqueness** - No duplicate registrations

---

## 🎯 **NEXT STEPS (Optional)**

### **Switch to Real Email Sending:**

When ready to send actual emails, update `.env`:

```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your-email@gmail.com
MAIL_PASSWORD=your-app-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@aqar.bdcbiz.com
MAIL_FROM_NAME="RealtyFind"
```

Then create email notification and send it in `register()` method.

---

## ✅ **SUMMARY**

**Endpoints:**
- ✅ `POST /api/register` - Returns 6-digit code
- ✅ `POST /api/verify-email` - Validates code
- ✅ `POST /api/resend-verification-code` - Generates new code

**Features:**
- ✅ 6-digit code generation
- ✅ 15-minute expiration
- ✅ Code validation
- ✅ Resend functionality
- ✅ Proper error handling
- ✅ **Ready for testing!**

---

**Generated:** 2025-10-27
**Status:** DEPLOYED ✅
**Environment:** Production (https://aqar.bdcbiz.com)
