# 🔓 Authentication Flow & Token Management Guide

## Table of Contents
1. [Authentication System Overview](#authentication-system-overview)
2. [JWT/Sanctum Implementation](#jwtsanctum-implementation)
3. [Token Lifecycle](#token-lifecycle)
4. [Postman Configuration](#postman-configuration)
5. [Multi-User Token Management](#multi-user-token-management)
6. [Troubleshooting](#troubleshooting)

---

## Authentication System Overview

### Technology Stack
- **Framework:** Laravel 11
- **Authentication:** Laravel Sanctum (API Authentication)
- **Token Type:** Personal Access Tokens / JWT
- **Token Storage:** HTTP Bearer Header
- **Password Hashing:** bcrypt

### Authentication Endpoints

```
POST   /auth/register              Register new user
POST   /auth/login                 Login and receive token
GET    /auth/profile               Get current authenticated user
POST   /auth/logout                Logout and invalidate session
POST   /auth/refresh               Refresh access token
```

---

## JWT/Sanctum Implementation

### Registration Flow

**Endpoint:** `POST /auth/register`

**Request:**
```json
{
  "name": "John Doe",
  "email": "john@example.com",
  "password": "SecurePass@123",
  "password_confirmation": "SecurePass@123",
  "phone": "+1-555-0123"
}
```

**Validation Rules:**
- `name`: Required, string, max 255 characters
- `email`: Required, email, unique in database, max 255 characters
- `password`: Required, confirmed, min 8 chars, uppercase, lowercase, number, special char
- `phone`: Optional, string, max 20 characters

**Success Response (201 Created):**
```json
{
  "success": true,
  "message": "User registered successfully",
  "data": {
    "id": 1,
    "name": "John Doe",
    "email": "john@example.com",
    "role": "student",
    "token": "1|abc123def456ghi789jkl012mno345pqr678",
    "user": {
      "id": 1,
      "name": "John Doe",
      "email": "john@example.com",
      "role": "student",
      "created_at": "2026-05-17T10:30:00Z"
    }
  }
}
```

**Error Response (422 Validation Error):**
```json
{
  "success": false,
  "message": "Validation failed",
  "debug": {
    "exception": "ValidationException",
    "errors": {
      "email": ["The email has already been taken"],
      "password": ["The password confirmation does not match"]
    }
  }
}
```

---

### Login Flow

**Endpoint:** `POST /auth/login`

**Request:**
```json
{
  "email": "john@example.com",
  "password": "SecurePass@123"
}
```

**Validation Rules:**
- `email`: Required, string, email format, max 255 characters
- `password`: Required, string, min 6 characters

**Success Response (200 OK):**
```json
{
  "success": true,
  "message": "Logged in successfully",
  "data": {
    "token": "1|abc123def456ghi789jkl012mno345pqr678",
    "user": {
      "id": 1,
      "name": "John Doe",
      "email": "john@example.com",
      "role": "student",
      "phone": "+1-555-0123",
      "created_at": "2026-05-17T10:30:00Z",
      "updated_at": "2026-05-17T10:30:00Z"
    }
  }
}
```

**Error Response (401 Unauthorized):**
```json
{
  "success": false,
  "message": "Invalid credentials",
  "debug": {
    "exception": "AuthenticationException"
  }
}
```

---

### Get Current Profile

**Endpoint:** `GET /auth/profile`

**Headers Required:**
```
Authorization: Bearer {token}
```

**Success Response (200 OK):**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "name": "John Doe",
    "email": "john@example.com",
    "role": "student",
    "phone": "+1-555-0123",
    "created_at": "2026-05-17T10:30:00Z",
    "updated_at": "2026-05-17T10:30:00Z"
  }
}
```

**Error Response (401 Unauthorized):**
```json
{
  "success": false,
  "message": "Unauthenticated"
}
```

---

### Logout

**Endpoint:** `POST /auth/logout`

**Headers Required:**
```
Authorization: Bearer {token}
```

**Success Response (200 OK):**
```json
{
  "success": true,
  "message": "Logged out successfully"
}
```

**After Logout:**
- Token is invalidated server-side
- Client should remove token
- Subsequent requests with this token → 401 Unauthorized

---

### Refresh Token

**Endpoint:** `POST /auth/refresh`

**Headers Required:**
```
Authorization: Bearer {current_token}
```

**Success Response (200 OK):**
```json
{
  "success": true,
  "message": "Token refreshed",
  "data": {
    "token": "2|xyz789abc123def456ghi789jkl012mno345",
    "user": {
      "id": 1,
      "name": "John Doe",
      "role": "student"
    }
  }
}
```

**Use Case:**
- Current token about to expire
- Get new token without re-login
- Update client with new token
- Continue using API

---

## Token Lifecycle

### Token Attributes

```
Token Format: {id}|{plaintext_token_hash}
Example: 1|abc123def456ghi789jkl012mno345pqr678

Structure:
- id: User ID who owns token
- hash: Securely hashed token for database storage
```

### Token Expiration

```
Laravel Sanctum Default:
- No automatic expiration (depends on configuration)
- Can be revoked manually
- Can be made to expire via middleware

Personal Access Token Levels:
- *: Full access
- read: Read-only
- create: Can create resources
- update: Can update resources
- delete: Can delete resources
```

### Token Security

- ✅ Tokens are hashed before storage
- ✅ Sent via Bearer header (Authorization: Bearer {token})
- ✅ Can be revoked immediately
- ✅ Cannot be used after logout
- ✅ Cannot be reused after expiration
- ✅ Per-user tokens (multiple concurrent sessions)

---

## Postman Configuration

### 1. Environment Variables Setup

Create/update in Postman Environment:

```json
{
  "base_url": "http://localhost:8000/api/v1",
  "email": "admin@example.com",
  "password": "Admin@123",
  "token": "",
  "admin_token": "",
  "lab_manager_token": "",
  "student_token": "",
  "admin_id": "",
  "lab_manager_id": "",
  "student_id": ""
}
```

---

### 2. Pre-Request Script for Auto-Token-Assignment

**Collection-Level Pre-request Script:**

```javascript
// This runs before each request in collection

// If we have a token, it will be used
if (pm.environment.get("token")) {
    // Token is set in environment
    console.log("Using stored token");
}

// Optional: Generate timestamp for unique values
pm.environment.set("timestamp", Date.now());

// Optional: Generate random email
const randomEmail = "test" + Date.now() + "@example.com";
pm.environment.set("random_email", randomEmail);
```

---

### 3. Tests Script for Auto-Token-Capture

**Login Request Test Script:**

```javascript
// Capture token from login response
if (pm.response.code === 200) {
    const jsonData = pm.response.json();
    
    if (jsonData.data && jsonData.data.token) {
        // Store token globally
        pm.environment.set("token", jsonData.data.token);
        
        // Also store in role-specific variable
        const role = jsonData.data.user?.role;
        if (role === "admin") {
            pm.environment.set("admin_token", jsonData.data.token);
            pm.environment.set("admin_id", jsonData.data.user?.id);
        } else if (role === "lab_manager") {
            pm.environment.set("lab_manager_token", jsonData.data.token);
            pm.environment.set("lab_manager_id", jsonData.data.user?.id);
        } else if (role === "student") {
            pm.environment.set("student_token", jsonData.data.token);
            pm.environment.set("student_id", jsonData.data.user?.id);
        }
        
        console.log("Token stored: " + jsonData.data.token.substring(0, 10) + "...");
    }
}

// Validate response
pm.test("Login successful", function () {
    pm.expect(pm.response.code).to.be.oneOf([200, 201]);
});

pm.test("Response contains token", function () {
    const jsonData = pm.response.json();
    pm.expect(jsonData.data).to.have.property("token");
});

pm.test("Response contains user data", function () {
    const jsonData = pm.response.json();
    pm.expect(jsonData.data.user).to.have.property("id");
    pm.expect(jsonData.data.user).to.have.property("email");
    pm.expect(jsonData.data.user).to.have.property("role");
});
```

---

### 4. Bearer Token Configuration

**Collection-Level Authorization:**

```
Type: Bearer Token
Token: {{token}}
```

OR per-request override with role-specific token:

```
Type: Bearer Token
Token: {{admin_token}}  // Use admin token for this request
```

---

### 5. Alternative: Header Configuration

Instead of Collection Authorization, set in Headers tab:

```
Key: Authorization
Value: Bearer {{token}}
```

---

## Multi-User Token Management

### Scenario: Testing with Multiple Roles

**Step 1: Login as Admin**
```
POST {{base_url}}/auth/login
{
  "email": "admin@example.com",
  "password": "Admin@123"
}

→ {{admin_token}} stored
→ {{admin_id}} stored
```

**Step 2: Login as Lab Manager**
```
POST {{base_url}}/auth/login
{
  "email": "labmanager1@example.com",
  "password": "LabManager@123"
}

→ {{lab_manager_token}} stored
→ {{lab_manager_id}} stored
```

**Step 3: Login as Student**
```
POST {{base_url}}/auth/login
{
  "email": "student1@example.com",
  "password": "Student@123"
}

→ {{student_token}} stored
→ {{student_id}} stored
```

**Step 4: Use Specific Token for Testing**

```
// Admin request
GET {{base_url}}/users
Authorization: Bearer {{admin_token}}

// Lab Manager request
POST {{base_url}}/plant-species
Authorization: Bearer {{lab_manager_token}}

// Student request
GET {{base_url}}/plant-species
Authorization: Bearer {{student_token}}
```

---

### Token Switching in Postman

**In Request Header:**
```
Key: Authorization
Value: Bearer {{admin_token}}    // For admin
        Bearer {{lab_manager_token}}  // For lab manager
        Bearer {{student_token}}    // For student
```

**Or use Collection Runner with different environment values**

---

## Token Best Practices

### 1. Never Commit Tokens to Git

```bash
# .gitignore
.env.local
*.token
postman_env.local.json
```

---

### 2. Token Rotation

**When to rotate:**
- Monthly security rotation
- After security incident
- User password changed
- User role changed

**How to rotate:**
```
1. POST /auth/logout (invalidates current)
2. POST /auth/login (get new token)
3. Update environment variable
```

---

### 3. Token Sharing

**Never:**
- Share token via email
- Commit to repository
- Log in debug output
- Display in logs

**Instead:**
- Share login credentials
- Let user generate own token
- Use environment-specific tokens

---

### 4. Token Expiration Handling

**Client-side (Postman):**
```javascript
// Check if token about to expire
if (pm.environment.get("token_expires") < Date.now()) {
    // Refresh token
    // OR re-login
}
```

---

## Troubleshooting

### Issue: "Unauthenticated" on Every Request

**Symptoms:**
- 401 Unauthorized on all requests
- Even with token in header

**Cause:** Token not properly stored or format incorrect

**Solution:**
1. Check token format: `{id}|{hash}`
2. Verify header: `Authorization: Bearer {token}`
3. Test login endpoint first
4. Confirm token in environment variable
5. Clear and re-login

---

### Issue: "Token Mismatch" on POST Requests

**Symptoms:**
- GET requests work
- POST/PUT/DELETE fail with 419

**Cause:** CSRF token missing (if enabled)

**Solution:**
```
Check if CSRF is enabled in Laravel
If yes, include X-CSRF-TOKEN header or disable for API

In Postman, add header:
X-CSRF-TOKEN: {token}
```

---

### Issue: Token Works, Then Stops Working

**Symptoms:**
- First 5 requests work
- 6th request returns 401

**Cause:** Token expired or revoked

**Solution:**
```
POST {{base_url}}/auth/refresh
Authorization: Bearer {{old_token}}

Get new token and update {{token}} variable
```

---

### Issue: Different Tokens for Different Users Not Working

**Symptoms:**
- Admin token works
- Lab Manager token returns 401

**Cause:** Environmental variable issue or token not capturing

**Solution:**
1. Verify each role logs in separately
2. Confirm different tokens captured to different variables
3. Use correct variable in each request
4. Check POST test script capturing logic

---

### Issue: "Invalid Credentials" on Login

**Symptoms:**
- Login fails with 401
- Valid user credentials

**Cause:** 
- User doesn't exist
- Password incorrect
- User soft-deleted
- Case-sensitive email

**Solution:**
1. Verify user exists in database
2. Try with known credentials (admin@example.com)
3. Check password hasn't been changed
4. Verify user is not soft-deleted

---

## HTTP Status Codes Reference

| Code | Status | Meaning |
|------|--------|---------|
| 200 | OK | Request successful |
| 201 | Created | Resource created |
| 400 | Bad Request | Invalid request format |
| 401 | Unauthorized | Missing/invalid token |
| 403 | Forbidden | Insufficient permissions |
| 404 | Not Found | Resource not found |
| 422 | Unprocessable Entity | Validation error |
| 429 | Too Many Requests | Rate limit exceeded |
| 500 | Server Error | Internal error |

---

## Quick Reference: Authentication Endpoints

```bash
# Register
curl -X POST http://localhost:8000/api/v1/auth/register \
  -H "Content-Type: application/json" \
  -d '{
    "name": "User",
    "email": "user@example.com",
    "password": "Pass@123",
    "password_confirmation": "Pass@123"
  }'

# Login
curl -X POST http://localhost:8000/api/v1/auth/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "user@example.com",
    "password": "Pass@123"
  }'

# Get Profile
curl -X GET http://localhost:8000/api/v1/auth/profile \
  -H "Authorization: Bearer {token}"

# Refresh Token
curl -X POST http://localhost:8000/api/v1/auth/refresh \
  -H "Authorization: Bearer {token}"

# Logout
curl -X POST http://localhost:8000/api/v1/auth/logout \
  -H "Authorization: Bearer {token}"
```

---

## Default Test Credentials

| Email | Password | Role |
|-------|----------|------|
| admin@example.com | Admin@123 | admin |
| labmanager1@example.com | LabManager@123 | lab_manager |
| labmanager2@example.com | LabManager@123 | lab_manager |
| labmanager3@example.com | LabManager@123 | lab_manager |
| student1@example.com | Student@123 | student |
| student2-10@example.com | Student@123 | student |

---

**Last Updated:** May 17, 2026
**Version:** 1.0.0
**Framework:** Laravel 11 with Sanctum
