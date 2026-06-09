# 🎓 Laboratory Inventory Management System (Backend)

[![Laravel](https://img.shields.io/badge/Laravel-11.x-FF2D20?style=for-the-badge&logo=laravel&logoColor=white)](https://laravel.com)
[![PHP](https://img.shields.io/badge/PHP-8.3-777BB4?style=for-the-badge&logo=php&logoColor=white)](https://php.net)
[![MySQL](https://img.shields.io/badge/MySQL-8.0-4479A1?style=for-the-badge&logo=mysql&logoColor=white)](https://mysql.com)

A robust, enterprise-grade RESTful API serving as the backbone for the Laboratory Inventory Management System. Built as a Final Year Capstone Project, this backend focuses on strict architectural boundaries, comprehensive security, high performance, and academic excellence.

---

## 🏛️ Architecture Overview

This project implements **Domain-Driven Design (DDD)** concepts and **Modular Architecture**, diverging from the standard Laravel MVC structure to ensure scalability, maintainability, and clean separation of concerns.

The application is split into distinct logical modules located in `app/Modules`:

### Core Module (`app/Modules/Core`)
Handles system-wide concerns and foundational abstractions.
- **Authentication:** JWT-based stateless authentication with strict refresh workflows.
- **Authorization:** `spatie/laravel-permission` integration for granular Role-Based Access Control (RBAC).
- **Base Abstractions:** Base Controllers, FormRequests, and standardized JSON API response formats.
- **Traits:** Reusable concerns like `HasActivityLogging`, `HasTransactions`, `HasImageUpload`.

### Inventory Module (`app/Modules/Inventory`)
The core domain of the application, managing all laboratory assets.
- **Asset Types:** Manages Equipment, Chemicals, and Plant Samples (with complex Species/Variety taxonomies).
- **Borrow Lifecycle:** Complex state machine managing Equipment borrowing requests (`Pending -> Approved/Rejected -> Borrowed -> Overdue/Returned`).
- **Stock Management:** Atomic stock mutations using database row-level locking (`lockForUpdate`) and transaction wrapping (`StockService`).
- **Usage Tracking:** Granular logging of chemical consumption by lab members.

---

## 🔒 Security & Performance Features

- **Pessimistic Locking:** Critical inventory transactions (borrowing, chemical usage) use pessimistic row locks (`SELECT ... FOR UPDATE`) to eliminate race conditions under concurrent load.
- **Strict Authorization:** 100% of API routes are protected by Laravel Policies, validating both Roles (Admin, Lab Manager, Student) and granular permissions (e.g., `borrows.approve`, `chemicals.create`).
- **JWT Security:** Configured with hardened security constraints, short TTLs, and explicit guard scoping.
- **Rate Limiting:** Authentication and highly sensitive endpoints are protected by strictly configured `ThrottleRequests` middleware to prevent brute-force attacks.
- **Centralized Error Handling:** Standardized, uniform JSON error responses across all exceptions (Validation, Authentication, Authorization, Server Errors).

---

## 🚀 Getting Started

### Prerequisites
- PHP 8.3+
- Composer 2.x
- MySQL 8.0+ / SQLite (for testing)

### Installation

1. **Clone & Setup:**
   ```bash
   git clone <repository-url>
   cd Inventory_backend
   cp .env.example .env
   composer install
   ```

2. **Configuration:**
   Update your `.env` file with the appropriate database credentials.
   ```env
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=inventory_db
   DB_USERNAME=root
   DB_PASSWORD=
   ```

3. **Initialize Application:**
   Generate the application key and JWT secret:
   ```bash
   php artisan key:generate
   php artisan jwt:secret
   ```

4. **Database Migration & Seeding:**
   Run migrations and populate the database with required roles, permissions, and an initial Admin user:
   ```bash
   php artisan migrate:fresh --seed
   ```
   *Note: The default admin credentials are defined in `Database\Seeders\UserSeeder` (usually `admin@example.com` / `Admin@123`).*

5. **Serve:**
   ```bash
   php artisan serve
   ```

---

## 🧪 Testing

The application features a comprehensive test suite covering critical domain logic, API endpoints, and authorization boundaries.

Run the test suite using PHPUnit / Artisan:
```bash
php artisan test
```

**Key Test Areas:**
- `AuthFeatureTest`: Verifies the authentication lifecycle (login, register, logout, token refresh, rate limiting).
- `BorrowLifecycleFeatureTest`: Tests the entire state machine of equipment borrowing (Request -> Approve -> Return), enforcing authorization rules.
- `ChemicalUsageFeatureTest`: Validates the chemical consumption workflow and stock deductions.
- `StockServiceTest`: Unit tests for atomic inventory mutations and race-condition prevention.
- `Policy Tests`: Ensures `BorrowRecordPolicy`, `ChemicalUsagePolicy` correctly deny unauthorized access based on Spatie roles.

---

## 📚 Code Quality & Standards

- **Strict Types:** `declare(strict_types=1);` enforced on all PHP files.
- **Static Analysis:** Ready for tools like PHPStan (Level 9).
- **DocBlocks:** Extensive inline documentation for complex domain logic.
- **Clean Code:** Adheres strictly to SOLID principles, avoiding "Fat Controllers" by delegating business logic to single-responsibility Service classes.

---

*This project was developed to exceed the requirements of a University Capstone Project, demonstrating enterprise readiness, security awareness, and advanced framework mastery.*
