# 📋 API Testing Guide - Inventory Management System

## Table of Contents
1. [Getting Started](#getting-started)
2. [Authentication Flow](#authentication-flow)
3. [Role-Based Access](#role-based-access)
4. [API Endpoints Overview](#api-endpoints-overview)
5. [Testing Workflows](#testing-workflows)
6. [Security Testing](#security-testing)
7. [Best Practices](#best-practices)

---

## Getting Started

### Prerequisites
- Postman installed (v11.0.0+)
- Local Laravel API running on `http://localhost:8000`
- Database seeded with test data

### Setup Instructions

1. **Import the Postman Collection**
   ```
   File → Import → Select postman_collection.json
   ```

2. **Import the Environment**
   ```
   Settings → Manage Environments → Import → Select postman_environment.json
   ```

3. **Select the Environment**
   - Top-right corner: Select "Inventory API - Local Environment"

4. **Run Pre-Test Setup**
   - Execute: "🔐 Authentication & Setup → Auth - Login (Admin)"
   - This will populate auth tokens for all roles

---

## Authentication Flow

### User Roles & Credentials

#### Admin User
```
Email: admin@example.com
Password: Admin@123
Permissions: All (CRUD on all resources, manage roles)
```

#### Lab Manager Users (3 available)
```
Email: labmanager1@example.com
Password: LabManager@123
Permissions: Create & Update only (create, edit)
```

#### Student Users (10 available)
```
Email: student1@example.com - student10@example.com
Password: Student@123
Permissions: View only
```

### Login & Token Management

1. **Login to get token:**
   ```
   POST /auth/login
   Body: { "email": "admin@example.com", "password": "Admin@123" }
   Response: { "data": { "token": "eyJ0eXAiOiJKV1QiLCJhbGc..." } }
   ```

2. **Token is auto-saved** to environment variable `{{token}}`

3. **Refresh token when expired:**
   ```
   POST /auth/refresh
   (Automatically done by Postman pre-request script)
   ```

4. **Logout:**
   ```
   POST /auth/logout
   (Clears token from environment)
   ```

---

## Role-Based Access Control (RBAC)

### Permission Matrix

| Permission | Admin | Lab Manager | Student |
|-----------|-------|-------------|---------|
| users.view | ✅ | ❌ | ❌ |
| users.create | ✅ | ❌ | ❌ |
| users.edit | ✅ | ❌ | ❌ |
| users.delete | ✅ | ❌ | ❌ |
| plants.view | ✅ | ✅ | ✅ |
| plants.create | ✅ | ✅ | ❌ |
| plants.edit | ✅ | ✅ | ❌ |
| plants.delete | ✅ | ✅ | ❌ |
| chemicals.view | ✅ | ✅ | ✅ |
| chemicals.create | ✅ | ✅ | ❌ |
| chemicals.edit | ✅ | ✅ | ❌ |
| chemicals.delete | ✅ | ✅ | ❌ |
| equipment.view | ✅ | ✅ | ✅ |
| equipment.create | ✅ | ✅ | ❌ |
| equipment.edit | ✅ | ✅ | ❌ |
| equipment.delete | ✅ | ✅ | ❌ |
| achievements.view | ✅ | ✅ | ✅ |
| achievements.create | ✅ | ❌ | ❌ |
| transactions.view | ✅ | ✅ | ✅ |
| reports.view | ✅ | ✅ | ✅ |
| manage-roles | ✅ | ❌ | ❌ |

---

## API Endpoints Overview

### 🔐 Authentication Endpoints

```
POST   /auth/register              Public - Register new user
POST   /auth/login                 Public - Login and get token
GET    /auth/profile               Protected - Get current user
POST   /auth/logout                Protected - Logout and clear token
POST   /auth/refresh               Protected - Refresh access token
```

### 👤 User Management

```
GET    /users                      View all users (paginated)
POST   /users                      Create new user
GET    /users/{id}                 View specific user
PUT    /users/{id}                 Update user details
DELETE /users/{id}                 Soft delete user
```

### 🌿 Plant Management

```
GET    /plant-species              List all plant species
POST   /plant-species              Create new plant species
GET    /plant-species/{id}         View plant species details
PUT    /plant-species/{id}         Update plant species
DELETE /plant-species/{id}         Delete plant species

GET    /plant-varieties            List all plant varieties
POST   /plant-varieties            Create new variety
GET    /plant-varieties/{id}       View variety details
PUT    /plant-varieties/{id}       Update variety
DELETE /plant-varieties/{id}       Delete variety

GET    /plant-samples              List plant samples
POST   /plant-samples              Create sample
GET    /plant-samples/{id}         View sample
PUT    /plant-samples/{id}         Update sample
DELETE /plant-samples/{id}         Delete sample

GET    /plant-stocks               List plant stocks
POST   /plant-stocks               Create stock record
GET    /plant-stocks/{id}          View stock details
PUT    /plant-stocks/{id}          Update stock
DELETE /plant-stocks/{id}          Delete stock
```

### ⚗️ Chemical Management

```
GET    /chemicals                  List all chemicals
POST   /chemicals                  Create new chemical
GET    /chemicals/{id}             View chemical details
PUT    /chemicals/{id}             Update chemical
DELETE /chemicals/{id}             Delete chemical

GET    /chemical-usage-logs        List usage logs
POST   /chemical-usage-logs        Record chemical usage
GET    /chemical-usage-logs/{id}   View usage log
```

### 🔧 Equipment Management

```
GET    /equipment                  List all equipment
POST   /equipment                  Create new equipment
GET    /equipment/{id}             View equipment details
PUT    /equipment/{id}             Update equipment
DELETE /equipment/{id}             Delete equipment
```

### 📋 Borrowing System

```
GET    /borrow-records             List all borrow requests
POST   /borrow-records             Create borrow request
GET    /borrow-records/{id}        View borrow details
GET    /borrow-records/pending     Get pending requests
GET    /borrow-records/overdue     Get overdue items
POST   /borrow-records/{id}/approve    Approve request
POST   /borrow-records/{id}/return     Return borrowed item
POST   /borrow-records/{id}/reject     Reject request
```

### 🎖️ Achievements

```
GET    /achievements               List all achievements
POST   /achievements               Create achievement
GET    /achievements/{id}          View achievement
PUT    /achievements/{id}          Update achievement
DELETE /achievements/{id}          Delete achievement
POST   /achievements/{id}/assign/{user}    Assign to user
DELETE /achievements/{id}/revoke/{user}    Revoke from user
```

### 📊 Dashboard & Reports

```
GET    /dashboard                  Get dashboard overview
GET    /transactions               List transactions
GET    /activity-logs              Get activity logs
```

---

## Testing Workflows

### Workflow 1: Complete User Registration & Login

**Test Sequence:**
1. Register new user
   ```
   POST /auth/register
   {
     "name": "New User",
     "email": "newuser@example.com",
     "password": "Password@123",
     "password_confirmation": "Password@123"
   }
   ```

2. Login with credentials
   ```
   POST /auth/login
   {
     "email": "newuser@example.com",
     "password": "Password@123"
   }
   ```

3. Get user profile
   ```
   GET /auth/profile
   (Token automatically added)
   ```

4. Logout
   ```
   POST /auth/logout
   ```

---

### Workflow 2: Admin Creates User & Assigns Permissions

**Prerequisites:** Admin token in `{{admin_token}}`

**Test Sequence:**
1. Create user
   ```
   POST /users
   {
     "name": "Lab Assistant",
     "email": "assistant@example.com",
     "password": "Password@123",
     "password_confirmation": "Password@123",
     "role": "lab_manager"
   }
   ```

2. Verify user created
   ```
   GET /users/{user_id}
   ```

3. User can login
   ```
   POST /auth/login
   {
     "email": "assistant@example.com",
     "password": "Password@123"
   }
   ```

---

### Workflow 3: Equipment Borrow & Return Process

**Test Sequence:**

1. **List available equipment:**
   ```
   GET /equipment?status=available
   ```

2. **Create borrow request:**
   ```
   POST /borrow-records
   {
     "borrowable_id": 1,
     "quantity": 1,
     "due_at": "2026-05-25",
     "notes": "For lab experiment"
   }
   ```

3. **Check pending requests (as admin):**
   ```
   GET /borrow-records/pending
   ```

4. **Approve request:**
   ```
   POST /borrow-records/{borrow_id}/approve
   ```

5. **Return equipment:**
   ```
   POST /borrow-records/{borrow_id}/return
   ```

6. **Verify equipment status:**
   ```
   GET /equipment/{equipment_id}
   ```

---

### Workflow 4: Plant Management

**Test Sequence:**

1. **Create plant species:**
   ```
   POST /plant-species
   {
     "common_name": "Tomato",
     "scientific_name": "Solanum lycopersicum",
     "growth_type": "annual",
     "family": "Solanaceae"
   }
   ```

2. **Create plant variety:**
   ```
   POST /plant-varieties
   {
     "species_id": 1,
     "variety_name": "Cherry Tomato",
     "description": "Small sweet variety"
   }
   ```

3. **Create plant sample:**
   ```
   POST /plant-samples
   {
     "variety_id": 1,
     "growth_stage": "seedling",
     "health_status": "healthy"
   }
   ```

4. **Create stock record:**
   ```
   POST /plant-stocks
   {
     "plant_sample_id": 1,
     "quantity": 50,
     "status": "healthy"
   }
   ```

---

## Security Testing

### SQL Injection Prevention

**Test:**
```
POST /auth/login
{
  "email": "' OR 1=1 --",
  "password": "anything"
}
```

**Expected:** 401 Unauthorized (invalid credentials)

---

### XSS Prevention

**Test:**
```
POST /auth/register
{
  "name": "<script>alert('xss')</script>",
  "email": "test@example.com",
  "password": "Password@123",
  "password_confirmation": "Password@123"
}
```

**Expected:** 422 Unprocessable Entity (validation error)

---

### Authentication & Authorization

**Test: Unauthenticated Access**
```
GET /users
(No token)
```
**Expected:** 401 Unauthorized

---

**Test: Insufficient Permissions**
```
GET /users
(Using student_token)
```
**Expected:** 403 Forbidden

---

**Test: Invalid Token**
```
GET /auth/profile
Authorization: Bearer invalid.token.here
```
**Expected:** 401 Unauthorized

---

## Edge Cases & Validation Tests

### Email Validation
- Valid: `test@example.com` ✅
- Invalid: `invalid.email` ❌
- Empty: `` ❌
- Duplicate: `admin@example.com` (if exists) ❌

### Password Validation
- Minimum 8 characters
- Must contain uppercase letter
- Must contain lowercase letter
- Must contain number
- Must contain special character

### Field Length Limits
- Name: Max 255 characters
- Email: Max 255 characters
- Phone: Max 20 characters
- Descriptions: Varies by field

### Enum Values
- **Equipment Status:** `available`, `borrowed`, `in_use`, `under_maintenance`
- **Equipment Condition:** `good`, `normal`, `broken`
- **Equipment Category:** `microscope`, `centrifuge`, `incubator`, `spectrophotometer`, `other`
- **Chemical Category:** `acid`, `base`, `solvent`, `oxidizer`, `reducer`, `other`
- **Danger Level:** `low`, `medium`, `high`
- **Plant Growth Type:** `annual`, `perennial`, `biennial`
- **User Role:** `admin`, `lab_manager`, `student`

---

## Performance & Optimization Tests

### Pagination Testing

**Test:** Get users with custom pagination
```
GET /users?page=1&per_page=20
```

**Expected:**
- Response includes `meta` with pagination info
- `meta.total` = total number of items
- `meta.per_page` = items per page
- `meta.current_page` = current page
- `meta.last_page` = total pages

---

### Response Time Benchmarks

| Endpoint Type | Target | Max |
|--------------|--------|-----|
| List endpoints | < 500ms | 2000ms |
| Get single | < 300ms | 1000ms |
| Create/Update | < 500ms | 2000ms |
| Delete | < 300ms | 1000ms |

---

## Common Error Responses

### 400 Bad Request
```json
{
  "success": false,
  "message": "Malformed request",
  "debug": {
    "exception": "InvalidArgumentException",
    "message": "Invalid request format"
  }
}
```

### 401 Unauthorized
```json
{
  "success": false,
  "message": "Unauthenticated"
}
```

### 403 Forbidden
```json
{
  "success": false,
  "message": "Unauthorized access"
}
```

### 404 Not Found
```json
{
  "success": false,
  "message": "Resource not found"
}
```

### 422 Unprocessable Entity (Validation Error)
```json
{
  "success": false,
  "message": "Validation failed",
  "debug": {
    "errors": {
      "email": ["The email field is required"],
      "password": ["The password must be at least 8 characters"]
    }
  }
}
```

### 429 Too Many Requests
```json
{
  "success": false,
  "message": "Too many requests, please throttle your requests"
}
```

### 500 Internal Server Error
```json
{
  "success": false,
  "message": "An unexpected error occurred"
}
```

---

## Tips & Tricks

### 1. Use Collection Variables
- Define reusable values in Collection Variables
- Reference with `{{variable_name}}`

### 2. Pre-request Scripts
- Run before each request
- Set up test data dynamically
- Generate random values

### 3. Test Scripts
- Run after response received
- Validate status codes
- Check response structure
- Set environment variables

### 4. Debug Mode
- Enable Postman Console (Alt + Ctrl + C)
- View all requests/responses
- Check variable values
- See pre/post-request script execution

### 5. Export Results
- Run as Collection
- Generate HTML report
- Share test results with team

---

## Automation & CI/CD Integration

### Run Collection in CI/CD Pipeline
```bash
newman run postman_collection.json \
  -e postman_environment.json \
  --reporters cli,json \
  --reporter-json-export results.json
```

### GitHub Actions Example
```yaml
- name: Run API Tests
  run: |
    npm install -g newman
    newman run postman_collection.json \
      -e postman_environment.json
```

---

## Support & Troubleshooting

### Issue: "Unauthenticated" on all requests

**Solution:**
1. Run "Auth - Login (Admin)" first
2. Check if token is stored in `{{token}}`
3. Verify environment is selected

### Issue: "Token expired"

**Solution:**
1. Run "Auth - Refresh Token"
2. Or re-login

### Issue: "Permission denied"

**Solution:**
1. Verify you're using correct user role
2. Check permission matrix above
3. Ensure role has required permissions

### Issue: "Invalid request format"

**Solution:**
1. Check JSON syntax in request body
2. Verify Content-Type header is application/json
3. Check for required fields

---

## Version History

| Version | Date | Changes |
|---------|------|---------|
| 1.0.0 | 2026-05-17 | Initial release |

---

**Last Updated:** May 17, 2026
**Created for:** Inventory Management System
**Postman Version:** 11.0.0+
