# Real Estate API - Postman Collection Guide

## 📥 How to Import into Postman

### Method 1: Import from File
1. Open Postman application
2. Click "Import" button (top left)
3. Click "Upload Files"
4. Select `Real_Estate_API_Postman_Collection.json`
5. Click "Import"

### Method 2: Import from URL
1. Open Postman
2. Click "Import" button
3. Select "Link" tab
4. Paste: `https://aqar.bdcbiz.com/Real_Estate_API_Postman_Collection.json`
5. Click "Continue" then "Import"

---

## 🔧 Setup Instructions

### Step 1: Set Environment Variables

After importing, you need to configure the collection variables:

1. Click on the collection name "Real Estate API - Complete Collection"
2. Go to "Variables" tab
3. Set the values:

| Variable | Current Value | Description |
|----------|---------------|-------------|
| `base_url` | `https://aqar.bdcbiz.com/api` | API base URL (already set) |
| `token` | *empty* | Your auth token (get from login) |

### Step 2: Get Authentication Token

1. **Register** (first time users):
   - Open: `Authentication > Register`
   - Fill in the body with your details
   - Click "Send"
   - Copy the `token` from response

2. **Login** (existing users):
   - Open: `Authentication > Login`
   - Fill in email and password
   - Click "Send"
   - Copy the `token` from response

3. **Set Token Variable**:
   - Click collection name
   - Go to "Variables" tab
   - Paste token in `token` variable "Current Value"
   - Click "Save"

---

## 📁 Collection Structure

### 1. **Authentication** (4 requests)
   - Register
   - Login
   - Logout
   - Get Current User

### 2. **Companies** (3 requests)
   - Get All Companies
   - Get Company by ID
   - Get Companies with Sales

### 3. **Compounds** (2 requests)
   - Get All Compounds
   - Get Compound by ID

### 4. **Units** (3 requests)
   - Get All Units
   - Get Unit by ID
   - Filter Units

### 5. **Sales** (2 requests)
   - Get All Sales
   - Get Sale by ID

### 6. **Activities** (6 requests) ⭐ NEW
   - Get All Activities (with filters)
   - Get Recent Activities
   - Get Activity Statistics
   - Get Activity by ID
   - Get Activities by Action
   - Get Activities by Subject

### 7. **Search** (1 request)
   - Search All

### 8. **Stages** (5 requests)
   - CRUD operations for stages

### 9. **User Profile** (4 requests)
   - Get User by Email
   - Get Profile
   - Update Profile
   - Change Password

### 10. **Salespeople** (1 request)
   - Get Salespeople by Compound

### 11. **Favorites** (3 requests)
   - Get Favorites
   - Add to Favorites
   - Remove from Favorites

### 12. **Statistics** (1 request)
   - Get Statistics

### 13. **Saved Searches** (5 requests)
   - CRUD operations for saved searches

### 14. **Unit Types** (1 request)
   - Get All Unit Types

### 15. **Unit Areas** (1 request)
   - Get Unit Areas

### 16. **Finish Specs** (1 request)
   - Get All Finish Specs

### 17. **Share Links** (1 request)
   - Get Share Link Data

### 18. **FCM Notifications** (2 requests)
   - Store FCM Token
   - Delete FCM Token

### 19. **Admin - Units** (3 requests)
   - Create Unit
   - Update Unit
   - Delete Unit

### 20. **Admin - Sales** (3 requests)
   - Create Sale
   - Update Sale
   - Delete Sale

---

## 🔐 Authentication Types

### Public Endpoints (No Auth Required)
- Register
- Login
- Get All Companies
- Get Company by ID
- Get All Compounds
- Get Compound by ID
- Get All Sales
- Get Sale by ID
- **Get All Activities**
- **Get Recent Activities**
- **Get Activity Statistics**

### Protected Endpoints (Auth Required)
All other endpoints require Bearer token authentication.

The token is automatically added to requests using the `{{token}}` variable.

---

## 📝 Example Usage Scenarios

### Scenario 1: Get Recent Updates
```
1. Open: Activities > Get Recent Activities
2. Parameters are already set: days=7, company_id=2
3. Click "Send"
4. View recent changes to Sales, Compounds, and Units
```

### Scenario 2: Filter Sales Activities
```
1. Open: Activities > Get All Activities
2. Modify query parameters:
   - subject_type: Sale
   - action: created
   - recent_days: 30
3. Click "Send"
4. See all new sales in last 30 days
```

### Scenario 3: Get Activity Statistics
```
1. Open: Activities > Get Activity Statistics
2. Parameters: days=30, company_id=2
3. Click "Send"
4. View stats: total activities, by action type, by subject type
```

### Scenario 4: Create a Sale (Admin)
```
1. Login first to get token
2. Open: Admin - Sales > Create Sale
3. Modify body with your data
4. Click "Send"
5. Check Activities to see it was logged!
```

---

## 🔍 Query Parameters Guide

### Activities Endpoints

**GET /activities** - All activities with pagination
| Parameter | Type | Description | Example |
|-----------|------|-------------|---------|
| `per_page` | integer | Items per page (default: 20) | `20` |
| `company_id` | integer | Filter by company | `2` |
| `action` | string | Filter by action type | `created` |
| `subject_type` | string | Filter by model type | `Sale` |
| `recent_days` | integer | Last N days | `7` |
| `from_date` | date | Start date | `2025-01-01` |
| `to_date` | date | End date | `2025-12-31` |

**GET /activities/recent** - Recent activities
| Parameter | Type | Description | Example |
|-----------|------|-------------|---------|
| `days` | integer | Number of days (default: 7) | `30` |
| `company_id` | integer | Filter by company | `2` |

**GET /activities/stats** - Activity statistics
| Parameter | Type | Description | Example |
|-----------|------|-------------|---------|
| `days` | integer | Number of days (default: 7) | `30` |
| `company_id` | integer | Filter by company | `2` |

---

## 💡 Tips & Best Practices

1. **Save Your Token**: After login, always save the token to collection variables
2. **Use Folders**: Organize requests by feature for easy access
3. **Environment Variables**: Use `{{variable}}` syntax for dynamic values
4. **Test Public First**: Try public endpoints before protected ones
5. **Check Activities**: After creating/updating/deleting, check Activities to see it was logged

---

## 🚀 Quick Start Checklist

- [ ] Import collection into Postman
- [ ] Register a new account (or login with existing)
- [ ] Copy token to collection variables
- [ ] Test public endpoints (Companies, Compounds, Sales)
- [ ] Test Activities endpoints
- [ ] Test protected endpoints with token
- [ ] Try creating a sale and check Activities

---

## 📊 Activity Logging

All changes to **Sales**, **Compounds**, and **Units** are automatically logged:

**Actions tracked:**
- `created` - When a record is created
- `updated` - When a record is modified
- `deleted` - When a record is removed

**Data stored:**
- What happened (action)
- When it happened (timestamp)
- What was affected (subject type + ID)
- Who did it (user ID)
- Detailed changes (JSON properties)

**Use cases:**
- Display "What's New" feed in app
- Show activity timeline
- Track changes history
- Generate statistics

---

## 🆘 Troubleshooting

### Issue: "Unauthorized" error
**Solution**: Make sure you've set the `token` variable in collection settings

### Issue: "404 Not Found"
**Solution**: Check that `base_url` is set to `https://aqar.bdcbiz.com/api`

### Issue: Empty response
**Solution**: Some endpoints require authentication. Check if endpoint is protected.

### Issue: Token expired
**Solution**: Login again and update the token variable

---

## 📚 Additional Resources

- **API Documentation**: Available on server at `/var/www/realestate/API-Documentation.md`
- **Activity System Report**: `/var/www/realestate/ACTIVITY_LOGGING_SYSTEM_REPORT.txt`
- **Complete System Report**: `/var/www/realestate/COMPLETE_SALE_SYSTEM_REPORT.txt`

---

## 📅 Last Updated

**Date**: 2025-10-22

**Features**:
- ✅ Complete API collection with 60+ requests
- ✅ Activity logging endpoints (NEW)
- ✅ Organized in 20 folders
- ✅ Pre-configured with environment variables
- ✅ Example requests with sample data
- ✅ Public and protected endpoints

---

## 📧 Support

For issues or questions about the API, check the documentation files on the server or contact your development team.

**Happy Testing! 🎉**
