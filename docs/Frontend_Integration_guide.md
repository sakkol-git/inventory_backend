# Frontend API Integration & Error Handling Guide

This document is designed to equip frontend developers with the complete knowledge required to integrate with the Inventory Backend robustly. The backend employs an enterprise-grade Defensive Exception Architecture that guarantees predictability, traceability, and secure data handling.

---

## 1. The Standard Error Contract

The API has been hardened so that **100% of all errors**, ranging from 422 Validation failures to 500 Infrastructure outages, conform to a strict, unified JSON schema.

You will **never** receive an unexpected HTML stack trace or raw SQL error payload.

### The Unified JSON Schema
```typescript
interface StandardErrorResponse {
    /** Always false for error responses. Makes success/failure detection trivial. */
    success: boolean;
    
    /** The programmatic name of the exception thrown on the backend. */
    error: string;
    
    /** A predictable, static UPPER_SNAKE_CASE string meant to be parsed by frontend logic. */
    code: string;
    
    /** A human-readable message suitable for display or logging. */
    message: string;
    
    /** Extra context. For validation errors, this contains the field-level errors. */
    details: Record<string, any>;
    
    /** A unique request identifier. MUST be sent to support when reporting bugs. */
    correlation_id: string;
    
    /** ISO8601 timestamp of when the error occurred. */
    timestamp: string;
}
```

### Example: Validation Error (422 Unprocessable Entity)
```json
{
    "success": false,
    "error": "ValidationException",
    "code": "VALIDATION_ERROR",
    "message": "Validation failed",
    "details": {
        "errors": {
            "quantity": ["The quantity field must be at least 1."],
            "borrow_date": ["The borrow date must be a future date."]
        }
    },
    "correlation_id": "f5b6e679-8d14-41d3-a0e4-239611ab1922",
    "timestamp": "2026-06-12T14:43:23+00:00"
}
```

---

## 2. Global Error Codes Dictionary

The frontend should primarily branch its error-handling logic based on the `code` string, **not** the HTTP status code (as multiple errors can share the same status code).

### 2.1 Infrastructure & Security Errors

| `code` | HTTP Status | Description & Recommended Frontend Action |
| :--- | :--- | :--- |
| `UNAUTHENTICATED` | 401 | User is not logged in or token expired. **Action:** Redirect to Login. |
| `FORBIDDEN` | 403 | User is authenticated but lacks permission. **Action:** Show "Access Denied" component. |
| `ENDPOINT_NOT_FOUND` | 404 | The requested API route does not exist. **Action:** Report to frontend team. |
| `RESOURCE_NOT_FOUND` | 404 | The specific item (e.g., Equipment ID 99) does not exist. **Action:** Show 404 component. |
| `TOO_MANY_REQUESTS` | 429 | Rate limit exceeded. **Action:** Show "Please wait" toast. Implement exponential backoff. |
| `VALIDATION_ERROR` | 422 | Input data failed rules. **Action:** Map `details.errors` to form inputs. |
| `DATABASE_ERROR` | 500 | An internal query failed. **Action:** Show a generic error toast and display `correlation_id`. |
| `STORAGE_ERROR` | 500 | Failed to save an uploaded file. **Action:** Prompt the user to retry the upload. |
| `EXTERNAL_SERVICE_FAILURE` | 502 | A 3rd party integration is down. **Action:** Show "Service degraded" banner. |
| `INTERNAL_ERROR` / `ERROR` | 500 | A generic catch-all exception. **Action:** Show generic fatal error screen. |

---

### 2.2 Domain Business Rule Errors (409 & 422)

The backend enforces strict business rules (e.g., inventory limits, lifecycle transitions). These errors signify that the requested operation is currently impossible due to the application's state.

| `code` | `error` (Exception) | Trigger Condition |
| :--- | :--- | :--- |
| `INSUFFICIENT_STOCK` | `InsufficientStockException` | Attempted to borrow/consume more items than are physically available. |
| `STOCK_CANNOT_BE_NEGATIVE` | `StockCannotBeNegativeException` | An operation would push an inventory count below zero. |
| `EXCEEDS_MAX_BORROW_LIMIT` | `ExceedsMaxBorrowLimitException` | User has reached their global limit for concurrently borrowed items. |
| `INVALID_BORROW_STATUS_TRANSITION` | `InvalidBorrowStatusTransitionException` | E.g., Attempting to "Return" an item that is still "Pending Approval". |
| `BORROW_REQUEST_ALREADY_PROCESSED` | `BorrowRequestAlreadyProcessedException` | E.g., Attempting to "Approve" an already "Approved" or "Rejected" request. |
| `CHEMICAL_EXPIRED` | `ChemicalExpiredException` | Attempting to dispatch a chemical that has surpassed its expiration date. |
| `EQUIPMENT_NOT_AVAILABLE` | `EquipmentNotAvailableException` | Equipment is currently under maintenance or broken. |
| `STATE_CONFLICT` | `ConflictException` | A generic conflict indicating the data was modified by another transaction. |
| `UNAUTHORIZED_ACCESS` | `UnauthorizedAccessException` | Attempting to mutate a resource owned by another user without Admin overrides. |

---

## 3. Best Practices for Frontend Axios/Fetch Interceptors

To provide the best UX, handle these errors globally via an HTTP interceptor rather than in individual components.

### Recommended Axios Interceptor Implementation

```javascript
import axios from 'axios';
import { toast } from 'react-toastify'; // Or your notification library

const apiClient = axios.create({
    baseURL: '/api/v1',
});

apiClient.interceptors.response.use(
    (response) => response, // Pass through successful responses
    (error) => {
        // Did we receive a response from the backend?
        if (error.response) {
            const payload = error.response.data; // This is the StandardErrorResponse
            
            // 1. Handle Unauthenticated globally
            if (payload.code === 'UNAUTHENTICATED') {
                window.location.href = '/login';
                return Promise.reject(error);
            }
            
            // 2. Handle 500+ Infrastructure Errors globally
            if (error.response.status >= 500) {
                toast.error(`A system error occurred. Reference ID: ${payload.correlation_id}`);
                return Promise.reject(error);
            }

            // 3. Handle Generic Domain Rule Violations (Optional Global Handling)
            // For example, if it's a domain error but NOT a validation error, toast it automatically.
            if (payload.code !== 'VALIDATION_ERROR' && error.response.status === 422) {
                 toast.warning(payload.message);
            }
            
            return Promise.reject(payload); // Reject with the formatted payload
        }
        
        // No response received (Network Error)
        toast.error("Unable to reach the server. Please check your connection.");
        return Promise.reject(error);
    }
);
```

---

## 4. Troubleshooting & Developer Workflow

### 1. The Power of the `correlation_id`
When a user experiences a 500 error in production, the frontend will receive the `correlation_id` inside the JSON response. 

**Instruction to Frontend:** Always display the `correlation_id` when rendering a generic fallback error screen (e.g., "Something went wrong. Please provide this code to support: `f5b6e679-8d14...`").

**Instruction to Backend/SRE:** You can paste that exact `correlation_id` into the logging aggregation system (e.g., Datadog, Kibana, Laravel Telescope) to immediately pull up the full stack trace, HTTP request context, User ID, and raw SQL error that triggered the failure.

### 2. Preventing Race Conditions
Due to the defensive programming architecture on the backend, the database is strictly protected by constraints and transactions. 
If the frontend sends two concurrent identical requests (e.g., user rapidly double-clicks "Submit"), the backend will likely trigger a `STATE_CONFLICT` or `DATABASE_ERROR` to prevent duplicate data corruption. 
**Instruction to Frontend:** Always implement button debouncing or `isLoading` disabling states on form submissions.


