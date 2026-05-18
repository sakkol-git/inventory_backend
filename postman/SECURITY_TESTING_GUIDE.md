# 🛡️ Security Testing Guide

## Overview

This guide provides comprehensive security testing procedures for the Inventory Management API. All tests are included in the Postman collection under the "🚨 Security & Negative Tests" folder.

---

## Table of Contents

1. [Authentication Security](#authentication-security)
2. [Authorization & Access Control](#authorization--access-control)
3. [Input Validation Security](#input-validation-security)
4. [Data Protection](#data-protection)
5. [API Security](#api-security)
6. [Workflow Security](#workflow-security)

---

## Authentication Security

### 1. SQL Injection Prevention

**Test Case:**
```
POST /auth/login
Content-Type: application/json

{
  "email": "' OR '1'='1",
  "password": "' OR '1'='1"
}
```

**Expected Result:**
- Status Code: `401 Unauthorized`
- Response: Invalid credentials error
- Database: No data breach should occur

**Why:** Validates that input is properly parameterized and not directly concatenated into SQL queries.

---

### 2. XSS (Cross-Site Scripting) Prevention

**Test Case:**
```
POST /auth/register
Content-Type: application/json

{
  "name": "<script>alert('xss')</script>",
  "email": "test@example.com",
  "password": "Password@123",
  "password_confirmation": "Password@123"
}
```

**Expected Result:**
- Status Code: `422 Unprocessable Entity`
- Response: Validation error (special characters not allowed or sanitized)
- Database: Data stored safely without script tags

**Why:** Prevents execution of malicious JavaScript in the application.

---

### 3. Invalid Token Handling

**Test Case:**
```
GET /auth/profile
Authorization: Bearer invalid.token.structure.here
```

**Expected Result:**
- Status Code: `401 Unauthorized`
- Response: Invalid token error
- User: Not authenticated

**Why:** Ensures tokens are properly validated before granting access.

---

### 4. Expired Token Handling

**Test Case:**
```
1. Generate token
2. Wait or manipulate token expiry
3. GET /auth/profile
Authorization: Bearer expired_token
```

**Expected Result:**
- Status Code: `401 Unauthorized`
- Response: Token expired error
- User: Should use refresh endpoint or re-login

**Why:** Prevents use of expired sessions.

---

### 5. Token Tampering

**Test Case:**
```
GET /auth/profile
Authorization: Bearer validtoken123tampered456
```

**Expected Result:**
- Status Code: `401 Unauthorized`
- Response: Invalid signature or token error

**Why:** JWT signatures prevent tampering with token claims.

---

## Authorization & Access Control

### 1. Unauthorized Role Access

**Test Case: Student accessing User Management**
```
Authorization: Bearer {{student_token}}
GET /users
```

**Expected Result:**
- Status Code: `403 Forbidden`
- Response: "Unauthorized access" or similar
- Data: No user list returned

**Permission Matrix:**
| Resource | Admin | Lab Manager | Student |
|----------|-------|-------------|---------|
| Users CRUD | ✅ | ❌ | ❌ |
| Plants CRUD | ✅ | ✅ | ❌ |
| Chemicals CRUD | ✅ | ✅ | ❌ |
| Equipment CRUD | ✅ | ✅ | ❌ |
| View Resources | ✅ | ✅ | ✅ |

---

### 2. Insufficient Permissions for Create

**Test Case: Student trying to create plant**
```
Authorization: Bearer {{student_token}}
POST /plant-species
Content-Type: application/json

{
  "common_name": "Test Plant",
  "scientific_name": "Test spp.",
  "growth_type": "annual",
  "family": "Solanaceae"
}
```

**Expected Result:**
- Status Code: `403 Forbidden`
- Response: "Insufficient permissions"
- Data: Plant not created

---

### 3. Cross-User Data Access (IDOR)

**Test Case: User accessing another user's data**
```
User 1 Token: {{student_token}}
GET /users/{{admin_id}}
```

**Expected Result:**
- Status Code: `403 Forbidden` or `404 Not Found`
- Response: Cannot access other user's private data
- Security: Policy-based access control active

---

### 4. Admin-Only Endpoints

**Test Case: Non-admin accessing admin endpoints**
```
Authorization: Bearer {{lab_manager_token}}
GET /roles
```

**Expected Result:**
- Status Code: `403 Forbidden`
- Response: "Only admins can access this resource"

---

## Input Validation Security

### 1. Missing Required Fields

**Test Cases:**

```
// Missing email
POST /auth/login
{
  "password": "Password@123"
}

// Missing password
POST /auth/login
{
  "email": "test@example.com"
}

// Missing password_confirmation
POST /auth/register
{
  "name": "Test",
  "email": "test@example.com",
  "password": "Password@123"
}
```

**Expected Result:**
- Status Code: `422 Unprocessable Entity`
- Response: Clear error messages for each missing field
- Example:
```json
{
  "success": false,
  "message": "Validation failed",
  "debug": {
    "errors": {
      "password": ["The password field is required"]
    }
  }
}
```

---

### 2. Email Validation

**Invalid Emails:**
```
POST /auth/register

Test Cases:
- missing@
- test@domain
- test@.com
- test @example.com
- test@domain.
- @example.com
```

**Expected Result:**
- Status Code: `422 Unprocessable Entity`
- Response: "Invalid email format"

---

### 3. Password Validation Rules

**Requirements:**
- Minimum 8 characters
- At least 1 uppercase letter
- At least 1 lowercase letter
- At least 1 number
- At least 1 special character

**Test Invalid Passwords:**
```
"weak" → Too short, no uppercase, no number, no special char
"PASSWORD123!" → No lowercase
"password123!" → No uppercase
"Password!" → No number
"Password123" → No special character
"Pass@1" → Less than 8 chars
```

**Expected Result:**
- Status Code: `422 Unprocessable Entity`
- Response: Specific password requirement error

---

### 4. Duplicate Entry Prevention

**Test Case: Register with existing email**
```
POST /auth/register

{
  "name": "Duplicate User",
  "email": "admin@example.com",
  "password": "Password@123",
  "password_confirmation": "Password@123"
}
```

**Expected Result:**
- Status Code: `422 Unprocessable Entity`
- Response: "Email already exists" or "Email is not unique"
- Database: No duplicate entry created

---

### 5. Enum Value Validation

**Valid Enum Values:**
- **Equipment Status:** available, borrowed, in_use, under_maintenance
- **Equipment Condition:** good, normal, broken
- **Chemical Category:** acid, base, solvent, oxidizer, reducer, other
- **Danger Level:** low, medium, high
- **Growth Type:** annual, perennial, biennial

**Test Invalid Enum:**
```
POST /plant-species

{
  "common_name": "Test",
  "scientific_name": "Test spp.",
  "growth_type": "invalid_type",
  "family": "Solanaceae"
}
```

**Expected Result:**
- Status Code: `422 Unprocessable Entity`
- Response: "Invalid growth type" with list of valid values

---

### 6. Field Length Limits

**Test Cases:**

```
// Name: Max 255 characters
POST /auth/register
{
  "name": "a" * 256,  // One over limit
  ...
}

// Email: Max 255 characters
{
  "email": "verylongemailaddress@verylongdomainname.com" * 5,
  ...
}

// Phone: Max 20 characters
{
  "phone": "1234567890123456789012"  // 22 chars
}
```

**Expected Result:**
- Status Code: `422 Unprocessable Entity`
- Response: "Name may not be greater than 255 characters"

---

## Data Protection

### 1. Password Hashing

**Test: Retrieve user and verify password is hashed**
```
POST /auth/login
{
  "email": "admin@example.com",
  "password": "Admin@123"
}

// Login succeeds
GET /users/1

// Response
{
  "data": {
    "id": 1,
    "password": "$2y$12$..." // Hashed, not plain text
  }
}
```

**Expected Result:**
- Password in database is hashed (bcrypt format starting with $2y$)
- Password never appears in API responses
- Password never logged

---

### 2. Sensitive Data Exposure

**Test: Check what data is returned**
```
GET /users  // List all users
```

**Expected Result:**
- Response should NOT include:
  - Plain text passwords
  - Password hashes
  - API secrets
  - Database credentials
  - Sensitive tokens

**Expected Response:**
```json
{
  "data": [
    {
      "id": 1,
      "name": "Admin",
      "email": "admin@example.com",
      "role": "admin",
      "created_at": "2026-05-17T...",
      "updated_at": "2026-05-17T..."
    }
  ]
}
```

---

### 3. Soft Delete Security

**Test: Verify deleted users are not accessible**
```
// Create user
POST /users

// Delete user
DELETE /users/{{last_created_user_id}}

// Try to access deleted user
GET /users/{{last_created_user_id}}
```

**Expected Result:**
- Status Code: `404 Not Found`
- Response: User not found
- Database: Record soft-deleted (deleted_at set)

---

## API Security

### 1. Rate Limiting

**Test: Send multiple requests rapidly**
```
// Send 100+ requests in 10 seconds
for i in {1..100}; do
  curl http://localhost:8000/api/v1/auth/profile \
    -H "Authorization: Bearer {{token}}"
done
```

**Expected Result:**
- Initial requests: `200 OK`
- After limit reached: `429 Too Many Requests`
- Response Header: Should include `Retry-After`

---

### 2. CORS (Cross-Origin Resource Sharing)

**Test: Request from different origin**
```
Origin: http://attacker-site.com
GET /api/v1/users
```

**Expected Result:**
- Status Code: Should respect CORS policy
- If CORS not configured for that origin: `403 Forbidden`
- Response headers should include proper CORS headers

---

### 3. CSRF Protection

**Test: Check for CSRF token requirement**
```
POST /users
(No CSRF token, from different origin)
```

**Expected Result:**
- Status Code: `419 Token Mismatch` (if CSRF is enabled)
- API should validate request origin

---

### 4. HTTPS Enforcement

**Test: Access via HTTP**
```
GET http://localhost:8000/api/v1/users
```

**Expected Result (Production):**
- Status Code: Redirect to HTTPS
- Header: `Strict-Transport-Security`
- Data: Never transmitted over HTTP

---

## Workflow Security

### 1. Authenticated Session Workflow

**Sequence:**
1. Login - Get token
2. Make requests with token
3. Logout - Clear token
4. Try to use old token

**Expected Results:**
1. Token issued
2. Requests authorized
3. Token cleared from server
4. Old token rejected with 401

---

### 2. Role-Based Workflow

**Sequence:**
1. Admin creates resource
2. Lab Manager updates resource
3. Student views resource
4. Student tries to delete (should fail)

**Expected Results:**
1. ✅ Created
2. ✅ Updated
3. ✅ Viewed
4. ❌ 403 Forbidden

---

### 3. Permission Inheritance

**Test: Lab Manager inheriting permissions**
```
1. Admin creates resource (plants.create granted)
2. Lab Manager tries to create plant
3. Should succeed because has permission
```

**Expected Result:**
- ✅ Lab Manager can create plant

---

## Security Checklist

- [ ] All endpoints require authentication (except register, login)
- [ ] Authorization checks RBAC before returning data
- [ ] All inputs are validated and sanitized
- [ ] SQL queries use parameterized statements
- [ ] Passwords hashed with bcrypt
- [ ] Sensitive data not logged
- [ ] CORS properly configured
- [ ] Rate limiting active
- [ ] Soft deletes prevent data access
- [ ] Tokens have expiration
- [ ] Error messages don't expose system details
- [ ] HTTPS enforced in production
- [ ] CSRF protection enabled
- [ ] X-Headers properly set (X-Content-Type-Options, etc.)

---

## Reporting Security Issues

If you discover a security vulnerability:

1. **Do NOT** post it publicly
2. **Do NOT** commit to public repositories
3. Email: security@inventory-system.local
4. Include:
   - Description of vulnerability
   - Steps to reproduce
   - Potential impact
   - Suggested fix (if any)

---

## Security References

- [OWASP Top 10](https://owasp.org/www-project-top-ten/)
- [Laravel Security Best Practices](https://laravel.com/docs/security)
- [API Security Checklist](https://github.com/shieldfy/API-Security-Checklist)

---

**Last Updated:** May 17, 2026
**Version:** 1.0.0
