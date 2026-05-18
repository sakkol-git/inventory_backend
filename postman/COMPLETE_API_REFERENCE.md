# Complete API Endpoint Reference - Inventory Management System v2.0

## Overview
This document provides a complete mapping of all API endpoints available in the Inventory Management System. The collection is organized by module with comprehensive CRUD operations, workflows, and edge cases.

## Quick Stats
- **Total Endpoints**: 120+
- **Authentication Modules**: 7 endpoints
- **User Management**: 5 endpoints
- **Profile Management**: 5 endpoints
- **Plant Module**: 20 endpoints (4 sub-modules with full CRUD)
- **Chemical Module**: 8 endpoints (2 sub-modules)
- **Equipment Module**: 5 endpoints
- **Borrowing Module**: 7 endpoints with state transitions
- **Achievements Module**: 6 endpoints
- **Documents Module**: 5 endpoints
- **Dashboard & Analytics**: 3 endpoints
- **Notifications & Activity**: 7 endpoints
- **Search**: 1 endpoint
- **Admin - Roles & Permissions**: 15 endpoints
- **Security Tests**: 6 endpoints
- **Edge Cases**: 6 endpoints
- **Advanced Workflows**: 3 multi-step workflows

---

## 1. Authentication & Setup (7 Endpoints)

### POST /auth/register
Creates a new user account
- **Access**: Public
- **Body**: `name`, `email`, `password`, `password_confirmation`, `phone`
- **Response**: User object + JWT token
- **Test**: Validates token capture and user creation

### POST /auth/login
Authenticates user and returns JWT token
- **Access**: Public
- **Body**: `email`, `password`
- **Response**: User object + JWT token
- **Roles**: Supports all 3 roles (admin, lab_manager, student)
- **Environment Capture**: Sets `token`, `{role}_token`, `{role}_id`

### GET /auth/profile
Retrieves current authenticated user's profile
- **Access**: Authenticated
- **Response**: Full user object with role and permissions
- **Used by**: All authenticated endpoints for context

### POST /auth/logout
Invalidates current token
- **Access**: Authenticated
- **Response**: Success message

### POST /auth/refresh
Refreshes JWT token before expiry
- **Access**: Authenticated
- **Response**: New JWT token
- **Test**: Captures new token for environment

---

## 2. User Management - Complete (5 Endpoints)

### POST /users
Create new user (Admin only)
- **Access**: Admin
- **Body**: `name`, `email`, `password`, `password_confirmation`, `phone`, `role`
- **Response**: User object with ID
- **Test**: Captures `last_created_user_id` for chaining

### GET /users
List all users with pagination
- **Access**: Admin
- **Query**: `page`, `per_page` (default 20)
- **Response**: Paginated user array

### GET /users/{id}
Retrieve specific user by ID
- **Access**: Admin or self
- **Response**: Full user object
- **Test**: Uses `admin_id` variable

### PUT /users/{id}
Update user information
- **Access**: Admin or self
- **Body**: `name`, `phone`, and other editable fields
- **Response**: Updated user object

### DELETE /users/{id}
Soft delete user (not permanent)
- **Access**: Admin
- **Response**: Success confirmation

---

## 3. Profile Management - Complete (5 Endpoints)

### GET /profile
Get current authenticated user's profile
- **Access**: Authenticated
- **Response**: User object with profile details
- **Note**: Same as /auth/profile but part of Core module

### PUT /profile
Update current user's profile
- **Access**: Authenticated
- **Body**: `name`, `phone`, and profile fields
- **Response**: Updated profile object

### GET /profile/contributions
Get user's contributions (entries created/edited)
- **Access**: Authenticated
- **Response**: Array of contribution objects
- **Usage**: Displays user activity dashboard

### GET /profile/achievements
Get all achievements earned by user
- **Access**: Authenticated
- **Response**: Array of achievement objects with metadata
- **Usage**: User gamification/progress tracking

### GET /profile/activity
Get user's recent activity log
- **Access**: Authenticated
- **Response**: Paginated activity log entries
- **Includes**: All CRUD operations performed by user

---

## 4. Plant Module - Complete (20 Endpoints)

### 4.1 Plant Species - CRUD (5 Endpoints)

#### POST /plant-species
Create new plant species
- **Access**: Lab Manager+
- **Body**: 
  - `common_name` (string, required)
  - `khmer_name` (string)
  - `scientific_name` (string)
  - `family` (string)
  - `growth_type` (enum: annual, biennial, perennial)
  - `native_region` (string)
  - `propagation_method` (enum: seed, cutting, grafting)
  - `description` (text)
- **Response**: Species object with ID
- **Relationships**: Links to varieties, samples, stocks

#### GET /plant-species
List all plant species
- **Access**: All authenticated users (read-only for students)
- **Query**: `page=1&per_page=15`
- **Response**: Paginated species array

#### GET /plant-species/{id}
Get single species with all related data
- **Access**: All authenticated users
- **Response**: Species with varieties, samples, stocks relationships
- **Includes**: Total count of related items

#### PUT /plant-species/{id}
Update species information
- **Access**: Lab Manager+
- **Body**: Any updatable field
- **Response**: Updated species object

#### DELETE /plant-species/{id}
Delete plant species (soft delete)
- **Access**: Admin
- **Response**: Success confirmation

---

### 4.2 Plant Varieties - CRUD (5 Endpoints)

#### POST /plant-varieties
Create plant variety (type/cultivar of a species)
- **Access**: Lab Manager+
- **Body**:
  - `family_id` (int, required - links to Plant Species)
  - `variety_name` (string)
  - `description` (text)
- **Response**: Variety object with relationships

#### GET /plant-varieties
List all varieties with species info
- **Access**: All authenticated users
- **Query**: `page=1&per_page=15`
- **Response**: Paginated variety array with species details

#### GET /plant-varieties/{id}
Get single variety with samples
- **Access**: All authenticated users
- **Response**: Variety with linked species and samples

#### PUT /plant-varieties/{id}
Update variety details
- **Access**: Lab Manager+
- **Body**: `variety_name`, `description`
- **Response**: Updated variety object

#### DELETE /plant-varieties/{id}
Delete plant variety (soft delete)
- **Access**: Admin
- **Response**: Success confirmation

---

### 4.3 Plant Samples - CRUD (5 Endpoints)

#### POST /plant-samples
Create plant sample (physical specimen)
- **Access**: Lab Manager+
- **Body**:
  - `variety_id` (int, required)
  - `growth_stage` (enum: seedling, vegetative, flowering, fruiting, mature)
  - `health_status` (enum: healthy, diseased, stressed, recovering)
  - `notes` (text)
- **Response**: Sample object with relationships

#### GET /plant-samples
List all plant samples
- **Access**: All authenticated users
- **Query**: `page=1&per_page=15`
- **Response**: Paginated samples with variety and species info

#### GET /plant-samples/{id}
Get sample with all associated stocks
- **Access**: All authenticated users
- **Response**: Sample with linked variety, species, stocks

#### PUT /plant-samples/{id}
Update sample information
- **Access**: Lab Manager+
- **Body**: `growth_stage`, `health_status`, `notes`
- **Response**: Updated sample object

#### DELETE /plant-samples/{id}
Delete plant sample (soft delete)
- **Access**: Admin
- **Response**: Success confirmation

---

### 4.4 Plant Stocks - CRUD (5 Endpoints)

#### POST /plant-stocks
Create plant stock entry
- **Access**: Lab Manager+
- **Body**:
  - `plant_sample_id` (int, required)
  - `quantity` (int)
  - `reserved_quantity` (int)
  - `status` (enum: healthy, damaged, compromised)
  - `location` (string)
- **Response**: Stock object with inventory details

#### GET /plant-stocks
List all plant stocks
- **Access**: All authenticated users
- **Query**: `page=1&per_page=15`
- **Response**: Paginated stocks with full hierarchy (species→variety→sample→stock)

#### GET /plant-stocks/{id}
Get stock details with full genealogy
- **Access**: All authenticated users
- **Response**: Stock with complete parent relationships

#### PUT /plant-stocks/{id}
Update stock quantity and status
- **Access**: Lab Manager+
- **Body**: `quantity`, `reserved_quantity`, `status`, `location`
- **Response**: Updated stock object

#### DELETE /plant-stocks/{id}
Delete stock record (soft delete)
- **Access**: Admin
- **Response**: Success confirmation

---

## 5. Chemical Module - Complete (8 Endpoints)

### 5.1 Chemicals - CRUD (5 Endpoints)

#### POST /chemicals
Create chemical inventory entry
- **Access**: Lab Manager+
- **Body**:
  - `common_name` (string)
  - `chemical_code` (string, unique)
  - `category` (enum: acid, base, salt, organic, inorganic)
  - `quantity` (decimal)
  - `storage_location` (string)
  - `expiry_date` (date)
  - `danger_level` (enum: low, medium, high, extreme)
  - `safety_measures` (text)
  - `description` (text)
- **Response**: Chemical object with tracking info

#### GET /chemicals
List all chemicals in inventory
- **Access**: All authenticated users
- **Query**: `page=1&per_page=15`
- **Response**: Paginated chemicals with current quantity and expiry warnings

#### GET /chemicals/{id}
Get chemical details with usage history
- **Access**: All authenticated users
- **Response**: Chemical object with recent usage logs

#### PUT /chemicals/{id}
Update chemical information
- **Access**: Lab Manager+
- **Body**: `quantity`, `storage_location`, `expiry_date`, `danger_level`
- **Response**: Updated chemical object

#### DELETE /chemicals/{id}
Delete chemical record (soft delete)
- **Access**: Admin
- **Response**: Success confirmation

---

### 5.2 Chemical Usage Logs - Create & View (3 Endpoints)

#### POST /chemical-usage-logs
Log chemical usage
- **Access**: Lab Manager+
- **Body**:
  - `chemical_id` (int, required)
  - `quantity_used` (decimal)
  - `unit` (string: ml, g, liters, kg)
  - `used_by_id` (int)
  - `notes` (text)
  - `date` (date)
- **Response**: Usage log object
- **Side Effect**: Automatically decrements chemical quantity

#### GET /chemical-usage-logs
List all usage logs with full details
- **Access**: All authenticated users
- **Query**: `page=1&per_page=20`
- **Response**: Paginated logs with user and chemical info

#### GET /chemical-usage-logs/{id}
Get specific usage log entry
- **Access**: All authenticated users
- **Response**: Complete usage record with chemical and user details

---

## 6. Equipment Module - Complete (5 Endpoints)

#### POST /equipment
Create equipment inventory entry
- **Access**: Lab Manager+
- **Body**:
  - `equipment_name` (string)
  - `equipment_code` (string, unique)
  - `category` (enum: microscope, spectrophotometer, thermometer, balance, etc.)
  - `status` (enum: available, in_use, maintenance, retired)
  - `condition` (enum: excellent, good, normal, poor)
  - `location` (string)
  - `manufacturer` (string)
  - `model_name` (string)
  - `serial_number` (string)
  - `purchase_date` (date)
  - `purchase_price` (decimal)
  - `description` (text)
- **Response**: Equipment object with metadata

#### GET /equipment
List all equipment
- **Access**: All authenticated users
- **Query**: `page=1&per_page=15`
- **Filters**: By status, category, condition
- **Response**: Paginated equipment with availability

#### GET /equipment/{id}
Get equipment details
- **Access**: All authenticated users
- **Response**: Equipment with complete specifications and borrow history

#### PUT /equipment/{id}
Update equipment information
- **Access**: Lab Manager+
- **Body**: `status`, `condition`, `location`, maintenance notes
- **Response**: Updated equipment object

#### DELETE /equipment/{id}
Delete equipment record (soft delete)
- **Access**: Admin
- **Response**: Success confirmation

---

## 7. Borrowing Module - Complete (7 Endpoints)

#### POST /borrow-records
Create borrow request
- **Access**: All authenticated users
- **Body**:
  - `borrowable_id` (int) - can be equipment or plant stock
  - `quantity` (int)
  - `due_at` (datetime)
  - `notes` (text)
- **Response**: Borrow record with pending status
- **Initial State**: `pending` (awaiting lab manager approval)

#### GET /borrow-records
List all borrow records
- **Access**: All authenticated users
- **Query**: `page=1&per_page=20`
- **Response**: Paginated records with user, item, and status info

#### GET /borrow-records/{id}
Get specific borrow record
- **Access**: All authenticated users
- **Response**: Complete borrow record with timeline

#### GET /borrow-records/pending
Get all pending borrow requests
- **Access**: Lab Manager+
- **Response**: Array of pending requests awaiting approval

#### GET /borrow-records/overdue
Get all overdue items
- **Access**: Lab Manager+
- **Response**: Array of records with `due_at` < now

#### POST /borrow-records/{id}/approve
Approve borrow request
- **Access**: Lab Manager+
- **State Change**: `pending` → `approved`
- **Response**: Updated record with approval timestamp
- **Side Effect**: Decrements equipment availability / reserves plant stock

#### POST /borrow-records/{id}/reject
Reject borrow request
- **Access**: Lab Manager+
- **Body**: `reason` (optional)
- **State Change**: `pending` → `rejected`
- **Response**: Updated record with rejection reason

#### POST /borrow-records/{id}/return
Return borrowed item
- **Access**: Item borrower or Lab Manager
- **Body**: `condition` (enum: excellent, good, damaged), `notes`
- **State Change**: `approved` → `returned`
- **Response**: Updated record with return info
- **Side Effect**: Updates equipment availability, marks transaction complete

---

## 8. Achievements Module - Complete (6 Endpoints)

#### POST /achievements
Create achievement definition
- **Access**: Admin
- **Body**:
  - `name` (string)
  - `description` (text)
  - `icon_url` (string)
  - `level` (enum: bronze, silver, gold, platinum, diamond)
- **Response**: Achievement object

#### GET /achievements
List all available achievements
- **Access**: All authenticated users
- **Response**: Array of achievements with earning criteria

#### GET /achievements/{id}
Get achievement details
- **Access**: All authenticated users
- **Response**: Achievement with list of users who earned it

#### PUT /achievements/{id}
Update achievement
- **Access**: Admin
- **Body**: `name`, `description`, `icon_url`, `level`
- **Response**: Updated achievement

#### DELETE /achievements/{id}
Delete achievement
- **Access**: Admin
- **Response**: Success confirmation

#### POST /achievements/{id}/assign/{userId}
Manually assign achievement to user
- **Access**: Admin
- **Response**: Achievement assignment record
- **Trigger**: Gamification system or manual admin action

#### DELETE /achievements/{id}/revoke/{userId}
Remove achievement from user
- **Access**: Admin
- **Response**: Success confirmation
- **Usage**: For correcting awards or demoting

---

## 9. User Documents Module (5 Endpoints)

#### POST /user-documents
Upload user document
- **Access**: Authenticated
- **Body**: Form-data multipart
  - `title` (string)
  - `document_type` (enum: report, certificate, transcript, other)
  - `description` (text)
  - `file` (file upload, max 10MB)
- **Response**: Document object with file URL
- **Storage**: Stored in storage/app/documents/

#### GET /user-documents
List user's documents
- **Access**: Authenticated (own docs) or Admin (all docs)
- **Query**: `page=1&per_page=20`
- **Response**: Paginated documents with metadata

#### GET /user-documents/{id}
Get document details
- **Access**: Document owner or Admin
- **Response**: Document object with file metadata

#### GET /user-documents/{id}/download
Download document file
- **Access**: Document owner or Admin
- **Response**: Binary file with proper headers
- **Headers**: Content-Disposition: attachment; filename=...

#### DELETE /user-documents/{id}
Delete document
- **Access**: Document owner or Admin
- **Response**: Success confirmation
- **Side Effect**: Deletes physical file from storage

---

## 10. Dashboard & Analytics (3 Endpoints)

#### GET /dashboard
Get dashboard overview
- **Access**: All authenticated users
- **Response**: Summary statistics:
  - Total plant species/varieties/samples/stocks
  - Equipment availability status
  - Pending borrow requests
  - Chemical inventory alerts
  - User achievements
  - Recent activity

#### GET /transactions
List all transactions
- **Access**: All authenticated users
- **Query**: `page=1&per_page=20`
- **Response**: Paginated transaction records

#### GET /transactions/{id}
Get transaction details
- **Access**: All authenticated users
- **Response**: Complete transaction object

---

## 11. Notifications & Activity (7 Endpoints)

#### GET /notifications
Get all notifications for user
- **Access**: Authenticated
- **Response**: Array of notifications with read/unread status
- **Types**: approval_needed, item_returned, achievement_earned, etc.

#### GET /notifications/unread-count
Get count of unread notifications
- **Access**: Authenticated
- **Response**: `{"data": {"unread_count": 5}}`

#### POST /notifications/{id}/read
Mark single notification as read
- **Access**: Authenticated
- **Response**: Updated notification

#### POST /notifications/read-all
Mark all notifications as read
- **Access**: Authenticated
- **Response**: Success confirmation

#### DELETE /notifications/{id}
Delete notification
- **Access**: Authenticated
- **Response**: Success confirmation

#### GET /activity-logs
Get activity log entries
- **Access**: All authenticated users
- **Query**: `page=1&per_page=30`
- **Response**: Paginated activity entries with:
  - User who performed action
  - Model affected (Plant, Chemical, etc.)
  - Action type (create, update, delete)
  - Changes made (before/after)
  - Timestamp

#### GET /activity-logs/{id}
Get specific activity log entry
- **Access**: All authenticated users
- **Response**: Complete activity entry with context

---

## 12. Search & Global Operations (1 Endpoint)

#### GET /search
Global search across all resources
- **Access**: All authenticated users
- **Query Parameters**:
  - `q` (string) - search query
  - `type` (string, optional) - filter by type: plant, chemical, equipment, user
- **Response**: Array of results from all matching resources
- **Example**: `/search?q=tomato&type=plant`

---

## 13. Admin - Roles & Permissions (15 Endpoints)

### 13.1 Roles Management (10 Endpoints)

#### POST /roles
Create new role
- **Access**: Admin only
- **Body**: `name` (string), `description` (text)
- **Response**: Role object
- **Default Roles**: admin, lab_manager, student (cannot delete)

#### GET /roles
List all roles
- **Access**: Admin only
- **Response**: Array of all roles with permission counts

#### GET /roles/{id}
Get role details
- **Access**: Admin only
- **Response**: Role object with permissions listed

#### PUT /roles/{id}
Update role
- **Access**: Admin only
- **Body**: `name`, `description`
- **Response**: Updated role

#### DELETE /roles/{id}
Delete role (if not system default)
- **Access**: Admin only
- **Response**: Success confirmation

#### GET /roles/{id}/permissions
Get all permissions assigned to role
- **Access**: Admin only
- **Response**: Array of permission objects

#### POST /roles/{id}/permissions
Assign permission to role
- **Access**: Admin only
- **Body**: `permission` (string, e.g., "plants.create")
- **Response**: Updated role with new permissions

#### DELETE /roles/{id}/permissions/{permission}
Revoke permission from role
- **Access**: Admin only
- **Response**: Updated role with removed permission

#### GET /roles/{id}/users
Get all users with this role
- **Access**: Admin only
- **Response**: Paginated array of users

#### POST /roles/{id}/users
Assign role to user
- **Access**: Admin only
- **Body**: `user_id` (int)
- **Response**: Updated user-role relationship

#### DELETE /roles/{id}/users/{userId}
Revoke role from user
- **Access**: Admin only
- **Response**: Success confirmation

---

### 13.2 Permissions Management (5 Endpoints)

#### POST /permissions
Create custom permission
- **Access**: Admin only
- **Body**: `name` (string, e.g., "custom.action"), `description` (text)
- **Response**: Permission object

#### GET /permissions
List all permissions
- **Access**: Admin only
- **Response**: Array of system and custom permissions

#### GET /permissions/{id}
Get permission details
- **Access**: Admin only
- **Response**: Permission with roles that have it

#### PUT /permissions/{id}
Update permission
- **Access**: Admin only
- **Body**: `name`, `description`
- **Response**: Updated permission

#### DELETE /permissions/{id}
Delete custom permission
- **Access**: Admin only
- **Response**: Success confirmation
- **Note**: Cannot delete system permissions

---

## 14. Security & Negative Tests (6 Endpoints)

These endpoints test security validations and error handling:

1. **SQL Injection Test** - POST /auth/login with SQL injection payload
2. **XSS Payload Test** - POST /auth/register with script tags
3. **Missing Required Field** - POST /auth/login without password
4. **Invalid Token** - GET /auth/profile with malformed token
5. **Unauthorized Role Access** - Student trying to access /roles
6. **Unauthenticated Access** - GET /users without any token

---

## 15. Edge Cases & Validation (6 Endpoints)

Test validation and error handling:

1. **Empty String** - Register with empty name
2. **Invalid Email** - Register with non-email string
3. **Weak Password** - Register with short password
4. **Duplicate Email** - Register with existing email
5. **Invalid Enum** - Create plant with invalid growth_type
6. **Non-existent Resource** - Get user with ID 99999

---

## 16. Advanced Workflows (3 Scenarios)

### Workflow 1: Complete Plant Research Lifecycle
```
1. Lab Manager creates plant species
   ↓
2. Lab Manager creates variety (cultivar)
   ↓
3. Lab Manager creates sample (physical specimen)
   ↓
4. Lab Manager records stock (inventory)
   ↓
5. Student views all data (read-only)
```

### Workflow 2: Equipment Borrow-Approve-Return
```
1. Admin creates equipment
   ↓
2. Student requests to borrow
   ↓
3. Lab Manager approves request
   ↓
4. Student returns equipment (with condition report)
```

### Workflow 3: Chemical Inventory Management
```
1. Lab Manager creates chemical entry
   ↓
2. Lab Manager logs usage (reduces quantity)
   ↓
3. System updates inventory (automatic decrement)
   ↓
4. Alert triggered if expiry near or stock low
```

---

## Permission Model

### Admin Role
- **All permissions**: Can perform any action
- **Restricted to**: Role/permission management (admin-only endpoints)

### Lab Manager Role
- **Create/Update**: All plant modules, chemicals, equipment
- **Approve/Reject**: Borrow requests
- **View**: All resources
- **Cannot**: Delete (soft delete only), manage roles/permissions

### Student Role
- **View/Read**: All resources (read-only)
- **Create**: Only borrow requests and documents
- **Cannot**: Create/update plants, chemicals, equipment
- **Can**: View profile, achievements, contributions

---

## Environment Variables Setup

Run authentication endpoints in this order to populate environment:

1. Login (Admin) → Captures: `admin_token`, `admin_id`, `token`
2. Login (Lab Manager) → Captures: `lab_manager_token`, `lab_manager_id`
3. Login (Student) → Captures: `student_token`, `student_id`

Then use in:
- Workflow tests (specify `Authorization: Bearer {{role_token}}`)
- Resource tests (use `{{role_id}}` for user references)
- Pagination (use `{{page}}` and `{{per_page}}` variables)

---

## Error Response Format

All errors follow standardized format:
```json
{
  "success": false,
  "message": "Error description",
  "data": null,
  "errors": {
    "field_name": ["validation error message"]
  }
}
```

---

## Success Response Format

All successful responses follow:
```json
{
  "success": true,
  "message": "Operation successful",
  "data": {
    // Resource object or array
  }
}
```

---

## API Testing Checklist

- [x] Authentication: All 7 endpoints
- [x] User Management: CRUD operations
- [x] Profile Management: All 5 endpoints
- [x] Plant Module: 20 endpoints (4 sub-modules)
- [x] Chemical Module: 8 endpoints (2 sub-modules)
- [x] Equipment Module: 5 endpoints
- [x] Borrowing: Full workflow with state transitions
- [x] Achievements: Create, assign, revoke
- [x] Documents: Upload, download, delete
- [x] Dashboard: Overview statistics
- [x] Notifications: Lifecycle management
- [x] Activity Logs: Historical tracking
- [x] Search: Global search
- [x] Admin: Full RBAC management
- [x] Security: Injection, XSS, auth tests
- [x] Edge Cases: Validation tests
- [x] Workflows: Multi-module scenarios

---

## Updated Collection Statistics

- **Total Requests**: 120+
- **Total Folders**: 16
- **Total Test Scripts**: 40+
- **Total Workflows**: 3 complete scenarios
- **Documentation**: Comprehensive

**Version**: 2.0.0  
**Last Updated**: 2026-05-17  
**Status**: ✅ Complete & Production-Ready
