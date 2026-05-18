# 🔄 Workflow Testing & Business Logic Guide

## Overview

This guide details comprehensive workflow testing scenarios that simulate real-world business processes in the Inventory Management System.

---

## Table of Contents

1. [Authentication Workflows](#authentication-workflows)
2. [User Management Workflows](#user-management-workflows)
3. [Plant Management Workflows](#plant-management-workflows)
4. [Chemical Management Workflows](#chemical-management-workflows)
5. [Equipment Borrowing Workflows](#equipment-borrowing-workflows)
6. [Achievement System Workflows](#achievement-system-workflows)
7. [Multi-User Workflows](#multi-user-workflows)
8. [Error Handling Workflows](#error-handling-workflows)

---

## Authentication Workflows

### Workflow 1: New User Registration & First Login

**Scenario:** New student joins the lab and registers for the system.

**Steps:**
```
1. User accesses registration page
   POST /auth/register
   {
     "name": "John Doe",
     "email": "john@example.com",
     "password": "SecurePass@123",
     "password_confirmation": "SecurePass@123",
     "phone": "555-1234"
   }

2. System validates and creates user
   Expected: 201 Created
   Response: { "data": { "id": 1, "token": "..." } }

3. User logs in with new account
   POST /auth/login
   {
     "email": "john@example.com",
     "password": "SecurePass@123"
   }
   Expected: 200 OK
   Response: { "data": { "token": "...", "user": {...} } }

4. User accesses profile
   GET /auth/profile
   Authorization: Bearer {{token}}
   Expected: 200 OK
   Response: { "data": { "id": 1, "name": "John Doe", ... } }
```

**Validations:**
- ✅ User created in database
- ✅ Token issued and stored
- ✅ Token validates subsequent requests
- ✅ User profile accessible
- ✅ Email is unique

---

### Workflow 2: Token Refresh & Session Management

**Scenario:** Long-running session with token expiry.

**Steps:**
```
1. Admin logs in (gets initial token)
   POST /auth/login → Token issued

2. Token stored: {{admin_token}} = "eyJ0eXA..."

3. After token near expiry, refresh
   POST /auth/refresh
   Authorization: Bearer {{admin_token}}
   Expected: 200 OK
   Response: { "data": { "token": "newToken..." } }

4. Update environment with new token
   {{admin_token}} = "newToken..."

5. Continue using renewed token
   GET /users
   Authorization: Bearer {{admin_token}}
   Expected: 200 OK (with new token)
```

**Validations:**
- ✅ Old token still works before expiry
- ✅ New token issued on refresh
- ✅ New token validates correctly
- ✅ User session continues uninterrupted

---

### Workflow 3: Logout & Session Termination

**Scenario:** User logs out and token becomes invalid.

**Steps:**
```
1. User has valid token
   {{token}} = "validToken123"

2. User clicks logout
   POST /auth/logout
   Authorization: Bearer {{token}}
   Expected: 200 OK
   Response: { "success": true, "message": "Logged out" }

3. Token cleared from client
   {{token}} = ""

4. Attempt to use old token
   GET /auth/profile
   Authorization: Bearer validToken123
   Expected: 401 Unauthorized
   Response: { "success": false, "message": "Unauthenticated" }
```

**Validations:**
- ✅ Logout successful
- ✅ Old token rejected after logout
- ✅ User forced to re-login
- ✅ Session terminated server-side

---

## User Management Workflows

### Workflow 4: Admin Creates User with Role

**Scenario:** Admin creates a new lab manager user.

**Steps:**
```
1. Admin authenticates
   POST /auth/login → Token obtained

2. Admin creates user
   POST /users
   Authorization: Bearer {{admin_token}}
   {
     "name": "Alice Manager",
     "email": "alice@lab.com",
     "password": "LabPass@123",
     "password_confirmation": "LabPass@123",
     "phone": "555-5678",
     "role": "lab_manager"
   }
   Expected: 201 Created
   Response: { "data": { "id": 2, "role": "lab_manager", ... } }

3. Verify user created
   GET /users/2
   Expected: 200 OK
   Response: { "data": { "name": "Alice Manager", "role": "lab_manager" } }

4. New user logs in
   POST /auth/login
   {
     "email": "alice@lab.com",
     "password": "LabPass@123"
   }
   Expected: 200 OK
   Response: { "data": { "token": "...", "role": "lab_manager" } }

5. Lab Manager accesses resources
   GET /plant-species
   Authorization: Bearer {{lab_manager_token}}
   Expected: 200 OK (can view)

6. Lab Manager tries to create
   POST /users
   Authorization: Bearer {{lab_manager_token}}
   Expected: 403 Forbidden (insufficient permissions)
```

**Validations:**
- ✅ User created with correct role
- ✅ User can login immediately
- ✅ Permissions match assigned role
- ✅ Lab Manager can create (has permission)
- ✅ Lab Manager cannot manage users (no permission)

---

### Workflow 5: Admin Updates User Details

**Scenario:** Admin updates an existing user's information.

**Steps:**
```
1. Get current user data
   GET /users/2
   Authorization: Bearer {{admin_token}}
   Response: { "data": { "name": "Alice Manager", "phone": "555-5678" } }

2. Update user
   PUT /users/2
   Authorization: Bearer {{admin_token}}
   {
     "name": "Alice Manager Updated",
     "phone": "555-9999"
   }
   Expected: 200 OK
   Response: { "data": { "name": "Alice Manager Updated", "phone": "555-9999" } }

3. Verify changes
   GET /users/2
   Authorization: Bearer {{admin_token}}
   Response shows updated values

4. Activity log should record change
   GET /activity-logs
   Response includes update by admin
```

**Validations:**
- ✅ User data updated
- ✅ Changes reflected immediately
- ✅ Activity logged
- ✅ Email not changed (unless allowed)

---

### Workflow 6: Admin Deletes User (Soft Delete)

**Scenario:** Admin deactivates a user account.

**Steps:**
```
1. Delete user
   DELETE /users/2
   Authorization: Bearer {{admin_token}}
   Expected: 204 No Content or 200 OK

2. Try to retrieve deleted user
   GET /users/2
   Authorization: Bearer {{admin_token}}
   Expected: 404 Not Found

3. User cannot login
   POST /auth/login
   {
     "email": "alice@lab.com",
     "password": "LabPass@123"
   }
   Expected: 401 Unauthorized

4. List users doesn't include deleted
   GET /users
   Authorization: Bearer {{admin_token}}
   Response: array without deleted user

5. Database record still exists (soft delete)
   Via database query: deleted_at is set
```

**Validations:**
- ✅ User soft-deleted
- ✅ User not accessible via API
- ✅ User cannot login
- ✅ Record retained in database
- ✅ Activity logged

---

## Plant Management Workflows

### Workflow 7: Complete Plant Lifecycle

**Scenario:** Lab Manager creates a complete plant record with all related data.

**Steps:**

#### Part 1: Create Plant Species
```
POST /plant-species
Authorization: Bearer {{lab_manager_token}}
{
  "common_name": "Tomato",
  "khmer_name": "ដងទម៉ាតូ",
  "scientific_name": "Solanum lycopersicum",
  "family": "Solanaceae",
  "growth_type": "annual",
  "native_region": "South America",
  "propagation_method": "Seed",
  "description": "Popular tomato plant for research"
}
Expected: 201 Created
Response: { "data": { "id": 5, "scientific_name": "Solanum lycopersicum" } }
Save: {{test_plant_id}} = 5
```

#### Part 2: Create Variety
```
POST /plant-varieties
Authorization: Bearer {{lab_manager_token}}
{
  "family_id": 5,
  "variety_name": "Cherry Tomato",
  "description": "Small, sweet variety"
}
Expected: 201 Created
Response: { "data": { "id": 10, "family_id": 5 } }
```

#### Part 3: Create Sample
```
POST /plant-samples
Authorization: Bearer {{lab_manager_token}}
{
  "variety_id": 10,
  "growth_stage": "seedling",
  "health_status": "healthy"
}
Expected: 201 Created
Response: { "data": { "id": 15 } }
```

#### Part 4: Create Stock Record
```
POST /plant-stocks
Authorization: Bearer {{lab_manager_token}}
{
  "plant_sample_id": 15,
  "quantity": 100,
  "reserved_quantity": 0,
  "status": "healthy"
}
Expected: 201 Created
Response: { "data": { "id": 20, "quantity": 100 } }
```

#### Part 5: View Complete Chain
```
GET /plant-species/5
GET /plant-varieties/10
GET /plant-samples/15
GET /plant-stocks/20

All should be linked and viewable by Student
```

**Validations:**
- ✅ All records created successfully
- ✅ Relationships properly established
- ✅ Student can view all (read permission)
- ✅ Lab Manager can create all (create permission)
- ✅ Data integrity maintained

---

## Chemical Management Workflows

### Workflow 8: Chemical Inventory Management

**Scenario:** Lab Manager manages chemical inventory with expiry tracking.

**Steps:**

#### Part 1: Create Chemical
```
POST /chemicals
Authorization: Bearer {{lab_manager_token}}
{
  "common_name": "Sulfuric Acid",
  "chemical_code": "SA-001",
  "category": "acid",
  "quantity": 1000,
  "storage_location": "Lab A - Cabinet 1",
  "expiry_date": "2026-12-31",
  "danger_level": "high",
  "safety_measures": "Use in fume hood",
  "description": "Concentrated sulfuric acid"
}
Expected: 201 Created
Save: {{test_chemical_id}} = 1
```

#### Part 2: Record Usage
```
POST /chemical-usage-logs
Authorization: Bearer {{lab_manager_token}}
{
  "chemical_id": 1,
  "quantity_used": 50,
  "notes": "Used in experiment A"
}
Expected: 201 Created
```

#### Part 3: Verify Inventory Reduced
```
GET /chemicals/1
Authorization: Bearer {{student_token}}
Response: { "data": { "quantity": 950 } }
```

#### Part 4: Check Usage Logs
```
GET /chemical-usage-logs
Authorization: Bearer {{student_token}}
Response: includes the usage record created in Part 2
```

#### Part 5: View Safety Info
```
GET /chemicals/1
Response includes:
- danger_level: "high"
- safety_measures: "Use in fume hood"
```

**Validations:**
- ✅ Chemical created with all details
- ✅ Usage logged correctly
- ✅ Inventory decremented
- ✅ Safety information preserved
- ✅ Usage history maintained

---

## Equipment Borrowing Workflows

### Workflow 9: Complete Borrow-Approve-Return Cycle

**Scenario:** Student borrows equipment, manager approves, student returns it.

**Steps:**

#### Part 1: Student Requests Equipment
```
POST /borrow-records
Authorization: Bearer {{student_token}}
{
  "borrowable_id": 1,
  "quantity": 1,
  "due_at": "2026-05-25",
  "notes": "For lab experiment on plant biology"
}
Expected: 201 Created
Response: { "data": { "id": 100, "status": "pending" } }
Save: {{test_borrow_id}} = 100
```

#### Part 2: Check Pending Requests (Lab Manager)
```
GET /borrow-records/pending
Authorization: Bearer {{lab_manager_token}}
Response: includes borrow request with status "pending"
```

#### Part 3: Lab Manager Approves
```
POST /borrow-records/100/approve
Authorization: Bearer {{lab_manager_token}}
Expected: 200 OK
Response: { "data": { "status": "approved", "borrowed_at": "2026-05-17..." } }
```

#### Part 4: Verify Status Changed
```
GET /borrow-records/100
Response: { "data": { "status": "approved" } }
```

#### Part 5: Check Equipment Status
```
GET /equipment/1
Response: { "data": { "status": "borrowed" } }
```

#### Part 6: Student Returns Equipment
```
POST /borrow-records/100/return
Authorization: Bearer {{student_token}}
Expected: 200 OK
Response: { "data": { "status": "returned", "returned_at": "2026-05-17..." } }
```

#### Part 7: Equipment Back to Available
```
GET /equipment/1
Response: { "data": { "status": "available" } }
```

#### Part 8: View Transaction Record
```
GET /transactions
Response: includes transaction for this borrow-return cycle
```

**Validations:**
- ✅ Request created with "pending" status
- ✅ Only managers can approve
- ✅ Equipment status changes to "borrowed"
- ✅ Return recorded with timestamp
- ✅ Equipment returns to "available"
- ✅ Transaction recorded
- ✅ Activity logged

---

### Workflow 10: Overdue Equipment Detection

**Scenario:** Equipment not returned by due date.

**Steps:**

#### Part 1: Create Borrow Request with Near-Term Due Date
```
POST /borrow-records
Authorization: Bearer {{student_token}}
{
  "borrowable_id": 2,
  "quantity": 1,
  "due_at": "2026-05-18",  // Tomorrow
  "notes": "Quick test"
}
```

#### Part 2: Approve Request
```
POST /borrow-records/{id}/approve
Authorization: Bearer {{lab_manager_token}}
```

#### Part 3: Check Pending (While Borrowed)
```
GET /borrow-records/pending
Authorization: Bearer {{lab_manager_token}}
Response: includes this borrow record
```

#### Part 4: Wait/Manipulate Date to After Due Date
```
// System time passes or update in test
Current Date: 2026-05-19
```

#### Part 5: Check Overdue
```
GET /borrow-records/overdue
Authorization: Bearer {{lab_manager_token}}
Response: includes this borrow record (past due_at)
```

#### Part 6: Send Overdue Notification
```
// Notification should be triggered
GET /notifications
Response: includes overdue equipment notification
```

**Validations:**
- ✅ Overdue items properly identified
- ✅ Lab Manager can see overdue list
- ✅ Notifications sent to responsible users
- ✅ Can still return after due date
- ✅ Late return recorded

---

### Workflow 11: Reject Borrow Request

**Scenario:** Manager rejects a borrow request due to equipment unavailability.

**Steps:**

#### Part 1: Student Requests Equipment
```
POST /borrow-records
Authorization: Bearer {{student_token}}
{
  "borrowable_id": 1,
  "quantity": 1,
  "due_at": "2026-05-25",
  "notes": "Lab work"
}
Expected: 201 Created
Save ID: {{borrow_id}} = 101
```

#### Part 2: Lab Manager Reviews
```
GET /borrow-records/pending
Authorization: Bearer {{lab_manager_token}}
Sees request
```

#### Part 3: Lab Manager Rejects with Reason
```
POST /borrow-records/101/reject
Authorization: Bearer {{lab_manager_token}}
{
  "reason": "Equipment currently under maintenance"
}
Expected: 200 OK
Response: { "data": { "status": "rejected", "rejected_reason": "Equipment currently under maintenance" } }
```

#### Part 4: Student Notified
```
GET /notifications
Authorization: Bearer {{student_token}}
Response: includes rejection notification with reason
```

#### Part 5: Equipment Still Available
```
GET /equipment/1
Response: { "data": { "status": "available" } }
```

**Validations:**
- ✅ Request can be rejected
- ✅ Reason recorded
- ✅ Student notified
- ✅ Equipment remains available

---

## Achievement System Workflows

### Workflow 12: Assign Achievement to User

**Scenario:** Admin assigns achievement to a student for accomplishment.

**Steps:**

#### Part 1: Admin Creates Achievement
```
POST /achievements
Authorization: Bearer {{admin_token}}
{
  "name": "Lab Explorer",
  "description": "Complete 10 lab experiments",
  "icon_url": "https://example.com/icon.png",
  "level": "gold"
}
Expected: 201 Created
Response: { "data": { "id": 50 } }
Save: {{achievement_id}} = 50
```

#### Part 2: Verify Achievement Created
```
GET /achievements/50
Response: shows achievement details
```

#### Part 3: Check All Achievements
```
GET /achievements
Response: includes new achievement
```

#### Part 4: Admin Assigns to Student
```
POST /achievements/50/assign/{{student_id}}
Authorization: Bearer {{admin_token}}
Expected: 200 OK or 201 Created
```

#### Part 5: Student Sees Achievement
```
GET /profile/achievements
Authorization: Bearer {{student_token}}
Response: includes "Lab Explorer" achievement
```

#### Part 6: View Student's Achievements
```
GET /users/{{student_id}}
Response: includes achievements array
```

**Validations:**
- ✅ Achievement created
- ✅ Can be assigned to user
- ✅ Student can view own achievements
- ✅ Admin can view all assignments

---

## Multi-User Workflows

### Workflow 13: Collaborative Lab Work

**Scenario:** Multiple users with different roles working together.

**Steps:**

#### Part 1: Lab Manager Creates Plant Research Template
```
POST /plant-species
Authorization: Bearer {{lab_manager_token}}
{
  "common_name": "Research Template",
  ...
}
```

#### Part 2: Student Views and Uses Template
```
GET /plant-species
Authorization: Bearer {{student_token}}
Sees the template created by Lab Manager
```

#### Part 3: Lab Manager Creates Sample from Template
```
POST /plant-samples
Authorization: Bearer {{lab_manager_token}}
Creates sample for students to work with
```

#### Part 4: Students Create Records Under Sample
```
POST /plant-stocks
Authorization: Bearer {{student_token}}
Creates stock record for their experiment
```

#### Part 5: Lab Manager Reviews All Student Work
```
GET /transactions
Authorization: Bearer {{lab_manager_token}}
Sees all student activities and records
```

#### Part 6: Admin Reviews Everything
```
GET /activity-logs
Authorization: Bearer {{admin_token}}
Sees all actions by all users
```

**Validations:**
- ✅ Data flow between role levels
- ✅ Permissions respected throughout
- ✅ Activity tracked
- ✅ Data integrity maintained

---

## Error Handling Workflows

### Workflow 14: Validation Error Handling

**Scenario:** Test system's response to invalid input at each step.

**Steps:**

#### Part 1: Missing Required Field
```
POST /auth/register
{
  "name": "Test User",
  "email": "test@example.com"
  // Missing password and password_confirmation
}
Expected: 422 Unprocessable Entity
Response: { "debug": { "errors": { "password": ["The password field is required"] } } }
```

#### Part 2: Invalid Email Format
```
POST /auth/register
{
  "name": "Test",
  "email": "invalid.email",
  "password": "Password@123",
  "password_confirmation": "Password@123"
}
Expected: 422 Unprocessable Entity
Response: { "debug": { "errors": { "email": ["The email must be a valid email address"] } } }
```

#### Part 3: Duplicate Entry
```
POST /auth/register
{
  "name": "Admin",
  "email": "admin@example.com",  // Already exists
  "password": "Password@123",
  "password_confirmation": "Password@123"
}
Expected: 422 Unprocessable Entity
Response: { "debug": { "errors": { "email": ["The email has already been taken"] } } }
```

#### Part 4: Invalid Enum
```
POST /plant-species
{
  "common_name": "Test",
  "scientific_name": "Test spp.",
  "growth_type": "invalid_type"
}
Expected: 422 Unprocessable Entity
```

#### Part 5: Non-existent Resource
```
GET /users/99999
Expected: 404 Not Found
Response: { "success": false, "message": "Resource not found" }
```

**Validations:**
- ✅ Clear validation error messages
- ✅ Error details provided
- ✅ 404 for non-existent resources
- ✅ 422 for validation errors
- ✅ 401 for auth failures

---

### Workflow 15: Permission Error Handling

**Scenario:** Test system's response to permission violations.

**Steps:**

#### Part 1: Student Tries to Create User
```
POST /users
Authorization: Bearer {{student_token}}
{
  "name": "New User",
  "email": "new@example.com",
  "password": "Password@123",
  "password_confirmation": "Password@123",
  "role": "student"
}
Expected: 403 Forbidden
Response: { "success": false, "message": "This action is unauthorized" }
```

#### Part 2: Unauthenticated Access
```
GET /users
// No token
Expected: 401 Unauthorized
Response: { "success": false, "message": "Unauthenticated" }
```

#### Part 3: Expired Token
```
GET /users
Authorization: Bearer expired_token_xyz
Expected: 401 Unauthorized
Response: { "success": false, "message": "Token expired" }
```

**Validations:**
- ✅ 403 for insufficient permissions
- ✅ 401 for no/invalid token
- ✅ Clear error messages

---

## Performance Workflow Testing

### Workflow 16: Load Testing Large Collections

**Scenario:** Performance with many records.

**Steps:**

#### Part 1: List with Pagination
```
GET /users?page=1&per_page=50
Authorization: Bearer {{admin_token}}
Expected: 200 OK, response time < 500ms
```

#### Part 2: Filter Results
```
GET /chemicals?danger_level=high&per_page=20
Expected: 200 OK, response time < 500ms
```

#### Part 3: Sort Results
```
GET /equipment?sort=created_at&direction=desc&per_page=25
Expected: 200 OK
```

#### Part 4: Search
```
GET /search?q=tomato
Expected: 200 OK, response time < 1000ms
```

**Validations:**
- ✅ Response time acceptable
- ✅ Pagination works
- ✅ Filtering accurate
- ✅ Sorting correct
- ✅ Search comprehensive

---

## Summary Checklist

- [ ] All authentication flows tested
- [ ] User CRUD workflows verified
- [ ] Plant lifecycle complete
- [ ] Chemical inventory working
- [ ] Equipment borrow/return cycle functional
- [ ] Achievement system operational
- [ ] Multi-user collaboration tested
- [ ] Error handling comprehensive
- [ ] Permission system enforced
- [ ] Performance acceptable
- [ ] Activity logging accurate
- [ ] Notifications working

---

**Last Updated:** May 17, 2026
**Version:** 1.0.0
