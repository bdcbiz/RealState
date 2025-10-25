# Real Estate API Documentation

**Base URL:** `https://aqar.bdcbiz.com/api`

**Authentication:** Most endpoints require Bearer token authentication.

---

## Table of Contents

1. [Authentication](#1-authentication)
2. [Companies](#2-companies)
3. [Compounds](#3-compounds)
4. [Sales & Discounts](#4-sales--discounts)
5. [Units](#5-units)
6. [Search](#6-search)
7. [User Profile](#7-user-profile)
8. [Favorites](#8-favorites)
9. [Saved Searches](#9-saved-searches)
10. [Statistics](#10-statistics)
11. [Notifications (FCM)](#11-notifications-fcm)
12. [Admin - Units](#12-admin---units)
13. [Admin - Sales](#13-admin---sales)
14. [Other Resources](#14-other-resources)

---

## 1. Authentication

### Register
```
POST /api/register
```
**Public Access** - No authentication required

**Request Body:**
```json
{
  "name": "John Doe",
  "email": "john@example.com",
  "password": "password123",
  "password_confirmation": "password123",
  "phone": "01234567890",
  "role": "buyer"
}
```

**Roles:** `buyer`, `agent`, `seller`, `company`, `admin`

**Response:**
```json
{
  "user": { ... },
  "token": "1|abcdef..."
}
```

---

### Login
```
POST /api/login
```
**Public Access** - No authentication required

**Request Body:**
```json
{
  "email": "john@example.com",
  "password": "password123"
}
```

**Response:**
```json
{
  "user": { ... },
  "token": "1|abcdef..."
}
```

---

### Logout
```
POST /api/logout
```
**Requires Authentication**

**Headers:**
```
Authorization: Bearer {token}
```

---

### Get Current User
```
GET /api/user
```
**Requires Authentication**

**Headers:**
```
Authorization: Bearer {token}
```

---

## 2. Companies

### Get All Companies
```
GET /api/companies
```
**Public Access** - No authentication required

**Response:**
```json
{
  "data": [
    {
      "id": 154,
      "name": "Palm Hills Developments",
      "logo_url": "...",
      "number_of_compounds": 56,
      "number_of_available_units": 851
    }
  ]
}
```

---

### Get Company by ID
```
GET /api/companies/{id}
```
**Public Access** - No authentication required

**Example:** `/api/companies/154`

---

### Get Companies with Sales
```
GET /api/companies-with-sales
```
**Requires Authentication**

Returns only companies that have active sales/discounts.

---

## 3. Compounds

### Get All Compounds
```
GET /api/compounds
```
**Public Access** - No authentication required

---

### Get Compound by ID
```
GET /api/compounds/{id}
```
**Public Access** - No authentication required

---

## 4. Sales & Discounts

### Get All Sales
```
GET /api/sales
```
**Public Access** - No authentication required

Returns all active sales and discounts.

---

### Get Sale by ID
```
GET /api/sales/{id}
```
**Public Access** - No authentication required

---

## 5. Units

### Get All Units
```
GET /api/units
```
**Requires Authentication**

---

### Get Unit by ID
```
GET /api/units/{id}
```
**Requires Authentication**

---

### Filter Units (POST)
```
POST /api/filter-units
```
**Requires Authentication**

**Request Body:**
```json
{
  "compound_id": 1,
  "min_price": 1000000,
  "max_price": 5000000,
  "unit_type": "apartment",
  "min_area": 100,
  "max_area": 200,
  "number_of_beds": 3
}
```

---

### Filter Units (GET)
```
GET /api/filter-units?compound_id=1&min_price=1000000&max_price=5000000
```
**Requires Authentication**

---

## 6. Search

### Search
```
GET /api/search?q=palm+hills
```
**Requires Authentication**

Search across companies, compounds, and units.

---

## 7. User Profile

### Get Profile
```
GET /api/profile
```
**Requires Authentication**

---

### Update Profile
```
PUT /api/profile
```
**Requires Authentication**

**Request Body:**
```json
{
  "name": "John Doe Updated",
  "phone": "01234567890"
}
```

---

### Change Password
```
POST /api/change-password
```
**Requires Authentication**

**Request Body:**
```json
{
  "current_password": "password123",
  "new_password": "newpassword123",
  "new_password_confirmation": "newpassword123"
}
```

---

### Get User by Email
```
GET /api/user-by-email?email=john@example.com
```
**Requires Authentication**

---

### Get Salespeople by Compound
```
GET /api/salespeople-by-compound?compound_id=1
```
**Requires Authentication**

---

## 8. Favorites

### Get My Favorites
```
GET /api/favorites
```
**Requires Authentication**

Returns user's favorite units.

---

### Add to Favorites
```
POST /api/favorites
```
**Requires Authentication**

**Request Body:**
```json
{
  "unit_id": 1
}
```

---

### Remove from Favorites
```
DELETE /api/favorites
```
**Requires Authentication**

**Request Body:**
```json
{
  "unit_id": 1
}
```

---

## 9. Saved Searches

### Get All Saved Searches
```
GET /api/saved-searches
```
**Requires Authentication**

---

### Get Saved Search by ID
```
GET /api/saved-searches/{id}
```
**Requires Authentication**

---

### Create Saved Search
```
POST /api/saved-searches
```
**Requires Authentication**

**Request Body:**
```json
{
  "name": "Apartments in Palm Hills",
  "criteria": {
    "compound_id": 1,
    "min_price": 1000000,
    "max_price": 3000000,
    "unit_type": "apartment"
  }
}
```

---

### Update Saved Search
```
PUT /api/saved-searches/{id}
```
**Requires Authentication**

---

### Delete Saved Search
```
DELETE /api/saved-searches/{id}
```
**Requires Authentication**

---

## 10. Statistics

### Get Statistics
```
GET /api/statistics
```
**Requires Authentication**

Returns system-wide statistics.

---

## 11. Notifications (FCM)

### Register FCM Token
```
POST /api/fcm-token
```
**Requires Authentication**

**Request Body:**
```json
{
  "fcm_token": "your-firebase-device-token-here"
}
```

Register device for push notifications.

---

### Delete FCM Token
```
DELETE /api/fcm-token
```
**Requires Authentication**

**Request Body:**
```json
{
  "fcm_token": "your-firebase-device-token-here"
}
```

---

### Send Notification to All (Admin)
```
POST /api/notifications/send-all
```
**Requires Authentication** (Admin only)

**Request Body:**
```json
{
  "title": "Test Notification",
  "body": "This is a test notification",
  "data": {
    "type": "test",
    "message": "Testing"
  }
}
```

---

### Send Notification by Role (Admin)
```
POST /api/notifications/send-role
```
**Requires Authentication**

**Request Body:**
```json
{
  "role": "buyer",
  "title": "Special Offer!",
  "body": "Check out our new units",
  "data": {
    "type": "offer"
  }
}
```

**Roles:** `buyer`, `agent`, `seller`

---

### Send Notification to Topic
```
POST /api/notifications/send-topic
```
**Requires Authentication**

**Request Body:**
```json
{
  "topic": "palm_hills",
  "title": "Palm Hills Update",
  "body": "New units available",
  "data": {
    "company_id": "154"
  }
}
```

---

## 12. Admin - Units

### Create Unit
```
POST /api/admin/units
```
**Requires Authentication** (Admin only)

**Request Body:**
```json
{
  "compound_id": 1,
  "unit_name": "A-101",
  "unit_code": "PH-A-101",
  "unit_type": "apartment",
  "number_of_beds": 3,
  "built_up_area": 150,
  "normal_price": 2500000,
  "status": "available"
}
```

**Auto-sends FCM notification to all buyers**

---

### Update Unit
```
PUT /api/admin/units/{id}
```
**Requires Authentication** (Admin only)

**Request Body:**
```json
{
  "normal_price": 2300000,
  "status": "reserved"
}
```

**Auto-sends FCM notification on price drop**

---

### Delete Unit
```
DELETE /api/admin/units/{id}
```
**Requires Authentication** (Admin only)

---

## 13. Admin - Sales

### Create Sale
```
POST /api/admin/sales
```
**Requires Authentication** (Admin only)

**Request Body:**
```json
{
  "company_id": 154,
  "sale_type": "unit",
  "unit_id": 1,
  "sale_name": "Spring Sale 2025",
  "discount_percentage": 20,
  "old_price": 2500000,
  "new_price": 2000000,
  "start_date": "2025-03-01",
  "end_date": "2025-03-31",
  "is_active": true
}
```

**Sale Types:** `unit`, `compound`

**Auto-sends FCM notification to all buyers**

---

### Update Sale
```
PUT /api/admin/sales/{id}
```
**Requires Authentication** (Admin only)

---

### Delete Sale
```
DELETE /api/admin/sales/{id}
```
**Requires Authentication** (Admin only)

---

## 14. Other Resources

### Stages

```
GET    /api/stages           - Get all stages
GET    /api/stages/{id}      - Get stage by ID
POST   /api/stages           - Create stage
PUT    /api/stages/{id}      - Update stage
DELETE /api/stages/{id}      - Delete stage
```

All require authentication.

---

### Unit Types

```
GET    /api/unit-types       - Get all unit types
GET    /api/unit-types/{id}  - Get unit type by ID
POST   /api/unit-types       - Create unit type
PUT    /api/unit-types/{id}  - Update unit type
DELETE /api/unit-types/{id}  - Delete unit type
```

All require authentication.

---

### Unit Areas

```
GET    /api/unit-areas       - Get unit areas
POST   /api/unit-areas       - Create unit area
PUT    /api/unit-areas       - Update unit area
DELETE /api/unit-areas       - Delete unit area
```

All require authentication.

---

### Finish Specifications

```
GET    /api/finish-specs       - Get all finish specs
GET    /api/finish-specs/{id}  - Get finish spec by ID
POST   /api/finish-specs       - Create finish spec
PUT    /api/finish-specs/{id}  - Update finish spec
DELETE /api/finish-specs/{id}  - Delete finish spec
```

All require authentication.

---

### Share Link

```
GET /api/share-link?unit_id=1
```
**Requires Authentication**

Get shareable link data for a unit.

---

## Authentication Flow

1. **Register** or **Login** to get a token
2. Save the token from the response
3. Use the token in all subsequent requests:
   ```
   Authorization: Bearer {your-token-here}
   ```

---

## Error Responses

All endpoints may return error responses in this format:

```json
{
  "message": "Error description",
  "errors": {
    "field": ["Validation error message"]
  }
}
```

**Common HTTP Status Codes:**
- `200` - Success
- `201` - Created
- `401` - Unauthorized (invalid/missing token)
- `403` - Forbidden (insufficient permissions)
- `404` - Not Found
- `422` - Validation Error
- `500` - Server Error

---

## Automatic Notifications

The system automatically sends FCM push notifications when:

1. **New Unit Created** → Notifies all buyers
2. **Unit Price Drops** → Notifies all buyers
3. **Unit Sold** → Notifies all users
4. **New Sale/Discount Created** → Notifies all buyers
5. **New Compound Added** → Notifies buyers and agents

---

## Database Statistics

**Current Data (as of 2025-10-22):**
- **557 Companies** in the system
- **5,467 Units** available
- **Top Companies:**
  - G Developments: 72 compounds
  - Palm Hills Developments: 56 compounds, 851 units
  - Orascom Development Egypt: 44 compounds
  - SODIC: 38 compounds

---

## Support

For API support, contact the development team or refer to the Filament admin panel at:
- Admin Panel: https://aqar.bdcbiz.com/admin
- Company Panel: https://aqar.bdcbiz.com/company
