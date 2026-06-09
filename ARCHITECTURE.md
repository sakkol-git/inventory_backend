# 🏗️ Architecture Documentation

This document outlines the architectural decisions, design patterns, and systemic approaches utilized in the Laboratory Inventory Management System Backend.

## 1. High-Level Architecture

The system strictly adheres to a **Modular Monolith** architecture with strong influences from **Domain-Driven Design (DDD)**. 

Unlike traditional Laravel applications that group files by technical concern (e.g., all Controllers in `app/Http/Controllers`, all Models in `app/Models`), this application groups code by **Business Domain** within the `app/Modules/` directory.

### Module Structure
```
app/
└── Modules/
    ├── Core/          # Foundation, Auth, Shared Concerns
    └── Inventory/     # Equipment, Chemicals, Borrows
```

## 2. Core Patterns & Principles

### Service Pattern
Controllers are kept extremely thin, strictly responsible for HTTP routing, request validation, and response formatting. Business logic is encapsulated inside dedicated **Service Classes**.
- Example: `BorrowRecordController@store` delegates entirely to `BorrowService`.

### Pessimistic Locking (Concurrency Control)
To prevent race conditions during high-concurrency inventory operations (e.g., two users requesting the last microscope at the exact same millisecond), the system implements database row-level locking via Eloquent's `lockForUpdate()`.
- Implemented in `StockService` (for Plant/Chemical stock).
- Implemented in `ApproveRequestService` (for Equipment checkout).

### State Machine Pattern
The `BorrowStatus` is not loosely updated. The transition of states (`Pending` -> `Approved` -> `Borrowed` -> `Returned`) is governed by dedicated services (`ApproveRequestService`, `ReturnEquipmentService`) which strictly validate valid transitions.

## 3. Security & Authorization

### RBAC (Role-Based Access Control)
Powered by `spatie/laravel-permission`, the application employs a highly granular permission model.
- **Roles:** `admin`, `lab_manager`, `student`.
- **Permissions:** Actions are broken down into specific capabilities (e.g., `borrows.approve`, `chemicals.create`).

### Laravel Policies
100% of RESTful endpoints are guarded by Laravel Policies. Middleware is not used for specific resource checks; instead, the Controller invokes `$this->authorize('action', Resource::class)`, ensuring the logic remains centralized in classes like `BorrowRecordPolicy`.

## 4. Testing Strategy

The test suite relies on PHPUnit and Laravel's robust testing helpers, specifically leveraging:
- **RefreshDatabase:** Ensuring a clean state for every feature test.
- **Faker & Factories:** Heavy reliance on Model Factories to generate complex relational states.
- **Guard Enforcement:** Explicit tests verifying that `api` guard auth failures return `401 Unauthorized` and policy failures return `403 Forbidden`.

## 5. Extensibility

The modular setup ensures that if a new domain is introduced (e.g., `Billing` or `Analytics`), it can be added as a standalone module inside `app/Modules/` without tangling with existing Inventory logic.
