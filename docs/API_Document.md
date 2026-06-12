# Project API Documentation

## Overview
- **Purpose**: Core backend for the Inventory System.
- **Base URL**: `/api/v1`

## Standard Response Format
All errors return the `StandardErrorResponse` JSON:
```json
{
  "success": false,
  "error": "ExceptionName",
  "code": "ERROR_CODE",
  "message": "Description",
  "details": {},
  "correlation_id": "uuid",
  "timestamp": "ISO8601"
}
```

## Endpoint Documentation

### /api/v1/auth/register

- **Method**: `POST`
- **Route**: `/api/v1/auth/register`
- **Authentication Required**: No

**Validation Rules:**
```json
{
    "name": [
        "required",
        "string",
        "max:255"
    ],
    "email": [
        "required",
        "string",
        "email",
        "max:255",
        "unique:users"
    ],
    "password": [
        "required",
        "string",
        {},
        "confirmed"
    ],
    "phone": [
        "nullable",
        "string",
        "max:20"
    ],
    "image": [
        "nullable",
        "image",
        "mimes:jpg,jpeg,png,webp",
        "max:2048"
    ],
    "image_url": [
        "nullable",
        "url",
        "max:2048"
    ],
    "profile_image_url": [
        "nullable",
        "url",
        "max:2048"
    ]
}
```

---
### /api/v1/auth/login

- **Method**: `POST`
- **Route**: `/api/v1/auth/login`
- **Authentication Required**: No

**Validation Rules:**
```json
{
    "email": [
        "required",
        "string",
        "email",
        "max:255"
    ],
    "password": [
        "required",
        "string",
        "min:6"
    ]
}
```

---
### /api/v1/auth/profile

- **Method**: `GET`
- **Route**: `/api/v1/auth/profile`
- **Authentication Required**: Yes (Sanctum/JWT)

**Validation Rules:**
None or standard CRUD parameters.

---
### /api/v1/auth/logout

- **Method**: `POST`
- **Route**: `/api/v1/auth/logout`
- **Authentication Required**: Yes (Sanctum/JWT)

**Validation Rules:**
None or standard CRUD parameters.

---
### /api/v1/auth/refresh

- **Method**: `POST`
- **Route**: `/api/v1/auth/refresh`
- **Authentication Required**: Yes (Sanctum/JWT)

**Validation Rules:**
None or standard CRUD parameters.

---
### search

- **Method**: `GET`
- **Route**: `/api/v1/search`
- **Authentication Required**: Yes (Sanctum/JWT)

**Validation Rules:**
None.

---
### notifications.index

- **Method**: `GET`
- **Route**: `/api/v1/notifications`
- **Authentication Required**: Yes (Sanctum/JWT)

**Validation Rules:**
None or standard CRUD parameters.

---
### notifications.unread-count

- **Method**: `GET`
- **Route**: `/api/v1/notifications/unread-count`
- **Authentication Required**: Yes (Sanctum/JWT)

**Validation Rules:**
None or standard CRUD parameters.

---
### notifications.read

- **Method**: `POST`
- **Route**: `/api/v1/notifications/{id}/read`
- **Authentication Required**: Yes (Sanctum/JWT)

**Validation Rules:**
None or standard CRUD parameters.

---
### notifications.read-all

- **Method**: `POST`
- **Route**: `/api/v1/notifications/read-all`
- **Authentication Required**: Yes (Sanctum/JWT)

**Validation Rules:**
None or standard CRUD parameters.

---
### notifications.destroy

- **Method**: `DELETE`
- **Route**: `/api/v1/notifications/{id}`
- **Authentication Required**: Yes (Sanctum/JWT)

**Validation Rules:**
None or standard CRUD parameters.

---
### activity-logs.index

- **Method**: `GET`
- **Route**: `/api/v1/activity-logs`
- **Authentication Required**: Yes (Sanctum/JWT)

**Validation Rules:**
None or standard CRUD parameters.

---
### activity-logs.show

- **Method**: `GET`
- **Route**: `/api/v1/activity-logs/{id}`
- **Authentication Required**: Yes (Sanctum/JWT)

**Validation Rules:**
None or standard CRUD parameters.

---
### profile.show

- **Method**: `GET`
- **Route**: `/api/v1/profile`
- **Authentication Required**: Yes (Sanctum/JWT)

**Validation Rules:**
None or standard CRUD parameters.

---
### profile.update

- **Method**: `PUT`
- **Route**: `/api/v1/profile`
- **Authentication Required**: Yes (Sanctum/JWT)

**Validation Rules:**
```json
{
    "name": [
        "sometimes",
        "required",
        "string",
        "max:255"
    ],
    "email": [
        "sometimes",
        "required",
        "email",
        "max:255",
        {}
    ],
    "password": [
        "sometimes",
        "string",
        "confirmed",
        {}
    ],
    "phone": [
        "nullable",
        "string",
        "max:20"
    ],
    "timezone": [
        "nullable",
        "string",
        "max:100"
    ],
    "image": [
        "nullable",
        "image",
        "mimes:jpg,jpeg,png,webp",
        "max:2048"
    ],
    "image_url": [
        "nullable",
        "url",
        "max:2048"
    ],
    "profile_image_url": [
        "nullable",
        "url",
        "max:2048"
    ]
}
```

---
### profile.contributions

- **Method**: `GET`
- **Route**: `/api/v1/profile/contributions`
- **Authentication Required**: Yes (Sanctum/JWT)

**Validation Rules:**
None or standard CRUD parameters.

---
### profile.achievements

- **Method**: `GET`
- **Route**: `/api/v1/profile/achievements`
- **Authentication Required**: Yes (Sanctum/JWT)

**Validation Rules:**
None or standard CRUD parameters.

---
### profile.activity

- **Method**: `GET`
- **Route**: `/api/v1/profile/activity`
- **Authentication Required**: Yes (Sanctum/JWT)

**Validation Rules:**
None or standard CRUD parameters.

---
### users.index

- **Method**: `GET`
- **Route**: `/api/v1/users`
- **Authentication Required**: Yes (Sanctum/JWT)

**Validation Rules:**
None or standard CRUD parameters.

---
### users.store

- **Method**: `POST`
- **Route**: `/api/v1/users`
- **Authentication Required**: Yes (Sanctum/JWT)

**Validation Rules:**
```json
{
    "name": [
        "required",
        "string",
        "max:255"
    ],
    "email": [
        "required",
        "email",
        "max:255",
        {}
    ],
    "password": [
        "required",
        "string",
        "confirmed",
        {}
    ],
    "phone": [
        "nullable",
        "string",
        "max:20"
    ],
    "role": [
        "required",
        {}
    ],
    "image": [
        "nullable",
        "image",
        "mimes:jpg,jpeg,png,webp",
        "max:2048"
    ],
    "image_url": [
        "nullable",
        "url",
        "max:2048"
    ],
    "profile_image_url": [
        "nullable",
        "url",
        "max:2048"
    ]
}
```

---
### users.show

- **Method**: `GET`
- **Route**: `/api/v1/users/{user}`
- **Authentication Required**: Yes (Sanctum/JWT)

**Validation Rules:**
None or standard CRUD parameters.

---
### users.update

- **Method**: `PUT|PATCH`
- **Route**: `/api/v1/users/{user}`
- **Authentication Required**: Yes (Sanctum/JWT)

**Validation Rules:**
```json
{
    "name": [
        "sometimes",
        "required",
        "string",
        "max:255"
    ],
    "email": [
        "sometimes",
        "required",
        "email",
        "max:255",
        {}
    ],
    "password": [
        "sometimes",
        "string",
        "confirmed",
        {}
    ],
    "phone": [
        "nullable",
        "string",
        "max:20"
    ],
    "role": [
        "sometimes",
        {}
    ],
    "image": [
        "nullable",
        "image",
        "mimes:jpg,jpeg,png,webp",
        "max:2048"
    ],
    "image_url": [
        "nullable",
        "url",
        "max:2048"
    ],
    "profile_image_url": [
        "nullable",
        "url",
        "max:2048"
    ]
}
```

---
### users.destroy

- **Method**: `DELETE`
- **Route**: `/api/v1/users/{user}`
- **Authentication Required**: Yes (Sanctum/JWT)

**Validation Rules:**
None or standard CRUD parameters.

---
### /api/v1/roles/{id}/permissions

- **Method**: `GET`
- **Route**: `/api/v1/roles/{id}/permissions`
- **Authentication Required**: Yes (Sanctum/JWT)

**Validation Rules:**
None or standard CRUD parameters.

---
### /api/v1/roles/{id}/permissions

- **Method**: `POST`
- **Route**: `/api/v1/roles/{id}/permissions`
- **Authentication Required**: Yes (Sanctum/JWT)

**Validation Rules:**
None or standard CRUD parameters.

---
### /api/v1/roles/{id}/permissions/{permission}

- **Method**: `DELETE`
- **Route**: `/api/v1/roles/{id}/permissions/{permission}`
- **Authentication Required**: Yes (Sanctum/JWT)

**Validation Rules:**
None or standard CRUD parameters.

---
### permissions.index

- **Method**: `GET`
- **Route**: `/api/v1/permissions`
- **Authentication Required**: Yes (Sanctum/JWT)

**Validation Rules:**
None or standard CRUD parameters.

---
### permissions.store

- **Method**: `POST`
- **Route**: `/api/v1/permissions`
- **Authentication Required**: Yes (Sanctum/JWT)

**Validation Rules:**
```json
{
    "name": [
        "required",
        "string",
        "max:100",
        "unique:permissions,name"
    ]
}
```

---
### permissions.show

- **Method**: `GET`
- **Route**: `/api/v1/permissions/{permission}`
- **Authentication Required**: Yes (Sanctum/JWT)

**Validation Rules:**
None or standard CRUD parameters.

---
### permissions.update

- **Method**: `PUT|PATCH`
- **Route**: `/api/v1/permissions/{permission}`
- **Authentication Required**: Yes (Sanctum/JWT)

**Validation Rules:**
```json
{
    "name": [
        "required",
        "string",
        "max:100",
        "unique:permissions,name,"
    ]
}
```

---
### permissions.destroy

- **Method**: `DELETE`
- **Route**: `/api/v1/permissions/{permission}`
- **Authentication Required**: Yes (Sanctum/JWT)

**Validation Rules:**
None or standard CRUD parameters.

---
### dashboard

- **Method**: `GET`
- **Route**: `/api/v1/dashboard`
- **Authentication Required**: Yes (Sanctum/JWT)

**Validation Rules:**
None.

---
### plant-species.index

- **Method**: `GET`
- **Route**: `/api/v1/plant-species`
- **Authentication Required**: Yes (Sanctum/JWT)

**Validation Rules:**
None or standard CRUD parameters.

---
### plant-species.store

- **Method**: `POST`
- **Route**: `/api/v1/plant-species`
- **Authentication Required**: Yes (Sanctum/JWT)

**Validation Rules:**
```json
{
    "common_name": [
        "required",
        "string",
        "max:255"
    ],
    "khmer_name": [
        "nullable",
        "string",
        "max:255"
    ],
    "scientific_name": [
        "required",
        "string",
        "max:255",
        {}
    ],
    "family": [
        "nullable",
        "string",
        "max:255"
    ],
    "growth_type": [
        "required",
        {}
    ],
    "native_region": [
        "nullable",
        "string",
        "max:255"
    ],
    "propagation_method": [
        "nullable",
        "string",
        "max:255"
    ],
    "description": [
        "nullable",
        "string"
    ],
    "image": [
        "nullable",
        "image",
        "mimes:jpg,jpeg,png,webp",
        "max:2048"
    ],
    "image_url": [
        "nullable",
        "url",
        "max:2048"
    ],
    "profile_image_url": [
        "nullable",
        "url",
        "max:2048"
    ]
}
```

---
### plant-species.show

- **Method**: `GET`
- **Route**: `/api/v1/plant-species/{plantSpecies}`
- **Authentication Required**: Yes (Sanctum/JWT)

**Validation Rules:**
None or standard CRUD parameters.

---
### plant-species.update

- **Method**: `PUT|PATCH`
- **Route**: `/api/v1/plant-species/{plantSpecies}`
- **Authentication Required**: Yes (Sanctum/JWT)

**Validation Rules:**
```json
{
    "common_name": [
        "sometimes",
        "required",
        "string",
        "max:255"
    ],
    "khmer_name": [
        "nullable",
        "string",
        "max:255"
    ],
    "scientific_name": [
        "sometimes",
        "required",
        "string",
        "max:255",
        {}
    ],
    "family": [
        "nullable",
        "string",
        "max:255"
    ],
    "growth_type": [
        "sometimes",
        "required",
        {}
    ],
    "native_region": [
        "nullable",
        "string",
        "max:255"
    ],
    "propagation_method": [
        "nullable",
        "string",
        "max:255"
    ],
    "description": [
        "nullable",
        "string"
    ],
    "image": [
        "nullable",
        "image",
        "mimes:jpg,jpeg,png,webp",
        "max:2048"
    ],
    "image_url": [
        "nullable",
        "url",
        "max:2048"
    ],
    "profile_image_url": [
        "nullable",
        "url",
        "max:2048"
    ]
}
```

---
### plant-species.destroy

- **Method**: `DELETE`
- **Route**: `/api/v1/plant-species/{plantSpecies}`
- **Authentication Required**: Yes (Sanctum/JWT)

**Validation Rules:**
None or standard CRUD parameters.

---
### plant-varieties.index

- **Method**: `GET`
- **Route**: `/api/v1/plant-varieties`
- **Authentication Required**: Yes (Sanctum/JWT)

**Validation Rules:**
None or standard CRUD parameters.

---
### plant-varieties.store

- **Method**: `POST`
- **Route**: `/api/v1/plant-varieties`
- **Authentication Required**: Yes (Sanctum/JWT)

**Validation Rules:**
```json
{
    "plant_species_id": [
        "required",
        "integer",
        "exists:plant_species,id"
    ],
    "name": [
        "required",
        "string",
        "max:255"
    ],
    "variety_code": [
        "required",
        "string",
        "max:100",
        {}
    ],
    "description": [
        "nullable",
        "string"
    ],
    "image": [
        "nullable",
        "image",
        "mimes:jpg,jpeg,png,webp",
        "max:2048"
    ],
    "image_url": [
        "nullable",
        "url",
        "max:2048"
    ],
    "profile_image_url": [
        "nullable",
        "url",
        "max:2048"
    ]
}
```

---
### plant-varieties.show

- **Method**: `GET`
- **Route**: `/api/v1/plant-varieties/{plantVariety}`
- **Authentication Required**: Yes (Sanctum/JWT)

**Validation Rules:**
None or standard CRUD parameters.

---
### plant-varieties.update

- **Method**: `PUT|PATCH`
- **Route**: `/api/v1/plant-varieties/{plantVariety}`
- **Authentication Required**: Yes (Sanctum/JWT)

**Validation Rules:**
```json
{
    "plant_species_id": [
        "sometimes",
        "integer",
        "exists:plant_species,id"
    ],
    "name": [
        "sometimes",
        "required",
        "string",
        "max:255"
    ],
    "variety_code": [
        "sometimes",
        "required",
        "string",
        "max:100",
        {}
    ],
    "description": [
        "nullable",
        "string"
    ],
    "image": [
        "nullable",
        "image",
        "mimes:jpg,jpeg,png,webp",
        "max:2048"
    ],
    "image_url": [
        "nullable",
        "url",
        "max:2048"
    ],
    "profile_image_url": [
        "nullable",
        "url",
        "max:2048"
    ]
}
```

---
### plant-varieties.destroy

- **Method**: `DELETE`
- **Route**: `/api/v1/plant-varieties/{plantVariety}`
- **Authentication Required**: Yes (Sanctum/JWT)

**Validation Rules:**
None or standard CRUD parameters.

---
### plant-samples.index

- **Method**: `GET`
- **Route**: `/api/v1/plant-samples`
- **Authentication Required**: Yes (Sanctum/JWT)

**Validation Rules:**
None or standard CRUD parameters.

---
### plant-samples.store

- **Method**: `POST`
- **Route**: `/api/v1/plant-samples`
- **Authentication Required**: Yes (Sanctum/JWT)

**Validation Rules:**
```json
{
    "sample_name": [
        "required",
        "string",
        "max:255"
    ],
    "sample_code": [
        "required",
        "string",
        "max:100",
        {}
    ],
    "plant_variety_id": [
        "nullable",
        "integer",
        "exists:plant_varieties,id"
    ],
    "user_id": [
        "nullable",
        "integer",
        "exists:users,id"
    ],
    "department": [
        "nullable",
        "string",
        "max:255"
    ],
    "origin_location": [
        "nullable",
        "string",
        "max:255"
    ],
    "brought_at": [
        "nullable",
        "date"
    ],
    "lab_location": [
        "nullable",
        {}
    ],
    "status": [
        "required",
        {}
    ],
    "description": [
        "nullable",
        "string"
    ],
    "image": [
        "nullable",
        "image",
        "mimes:jpg,jpeg,png,webp",
        "max:2048"
    ],
    "image_url": [
        "nullable",
        "url",
        "max:2048"
    ],
    "profile_image_url": [
        "nullable",
        "url",
        "max:2048"
    ]
}
```

---
### plant-samples.show

- **Method**: `GET`
- **Route**: `/api/v1/plant-samples/{plantSample}`
- **Authentication Required**: Yes (Sanctum/JWT)

**Validation Rules:**
None or standard CRUD parameters.

---
### plant-samples.update

- **Method**: `PUT|PATCH`
- **Route**: `/api/v1/plant-samples/{plantSample}`
- **Authentication Required**: Yes (Sanctum/JWT)

**Validation Rules:**
```json
{
    "sample_name": [
        "sometimes",
        "required",
        "string",
        "max:255"
    ],
    "sample_code": [
        "sometimes",
        "required",
        "string",
        "max:100",
        {}
    ],
    "plant_variety_id": [
        "nullable",
        "integer",
        "exists:plant_varieties,id"
    ],
    "user_id": [
        "nullable",
        "integer",
        "exists:users,id"
    ],
    "department": [
        "nullable",
        "string",
        "max:255"
    ],
    "origin_location": [
        "nullable",
        "string",
        "max:255"
    ],
    "brought_at": [
        "nullable",
        "date"
    ],
    "lab_location": [
        "nullable",
        {}
    ],
    "status": [
        "sometimes",
        "required",
        {}
    ],
    "quantity": [
        "sometimes",
        "integer",
        "min:0"
    ],
    "description": [
        "nullable",
        "string"
    ],
    "image": [
        "nullable",
        "image",
        "mimes:jpg,jpeg,png,webp",
        "max:2048"
    ],
    "image_url": [
        "nullable",
        "url",
        "max:2048"
    ],
    "profile_image_url": [
        "nullable",
        "url",
        "max:2048"
    ]
}
```

---
### plant-samples.destroy

- **Method**: `DELETE`
- **Route**: `/api/v1/plant-samples/{plantSample}`
- **Authentication Required**: Yes (Sanctum/JWT)

**Validation Rules:**
None or standard CRUD parameters.

---
### plant-stocks.index

- **Method**: `GET`
- **Route**: `/api/v1/plant-stocks`
- **Authentication Required**: Yes (Sanctum/JWT)

**Validation Rules:**
None or standard CRUD parameters.

---
### plant-stocks.store

- **Method**: `POST`
- **Route**: `/api/v1/plant-stocks`
- **Authentication Required**: Yes (Sanctum/JWT)

**Validation Rules:**
```json
{
    "plant_sample_id": [
        "required",
        "integer",
        "exists:plant_samples,id"
    ],
    "quantity": [
        "required",
        "integer",
        "min:0"
    ],
    "reserved_quantity": [
        "required",
        "integer",
        "min:0",
        "lte:quantity"
    ],
    "status": [
        "required",
        {}
    ]
}
```

---
### plant-stocks.show

- **Method**: `GET`
- **Route**: `/api/v1/plant-stocks/{plantStock}`
- **Authentication Required**: Yes (Sanctum/JWT)

**Validation Rules:**
None or standard CRUD parameters.

---
### plant-stocks.update

- **Method**: `PUT|PATCH`
- **Route**: `/api/v1/plant-stocks/{plantStock}`
- **Authentication Required**: Yes (Sanctum/JWT)

**Validation Rules:**
```json
{
    "plant_sample_id": [
        "nullable",
        "integer",
        "exists:plant_samples,id"
    ],
    "quantity": [
        "sometimes",
        "integer",
        "min:0"
    ],
    "reserved_quantity": [
        "sometimes",
        "integer",
        "min:0",
        "lte:quantity"
    ],
    "status": [
        "sometimes",
        "required",
        {}
    ]
}
```

---
### plant-stocks.destroy

- **Method**: `DELETE`
- **Route**: `/api/v1/plant-stocks/{plantStock}`
- **Authentication Required**: Yes (Sanctum/JWT)

**Validation Rules:**
None or standard CRUD parameters.

---
### chemicals.index

- **Method**: `GET`
- **Route**: `/api/v1/chemicals`
- **Authentication Required**: Yes (Sanctum/JWT)

**Validation Rules:**
None or standard CRUD parameters.

---
### chemicals.store

- **Method**: `POST`
- **Route**: `/api/v1/chemicals`
- **Authentication Required**: Yes (Sanctum/JWT)

**Validation Rules:**
```json
{
    "common_name": [
        "required",
        "string",
        "max:255"
    ],
    "chemical_code": [
        "nullable",
        "string",
        "max:100",
        {}
    ],
    "category": [
        "required",
        {}
    ],
    "quantity": [
        "required",
        "integer",
        "min:0"
    ],
    "storage_location": [
        "nullable",
        "string",
        "max:255"
    ],
    "expiry_date": [
        "nullable",
        "date"
    ],
    "danger_level": [
        "required",
        {}
    ],
    "safety_measures": [
        "nullable",
        "string"
    ],
    "description": [
        "nullable",
        "string"
    ],
    "image": [
        "nullable",
        "image",
        "mimes:jpg,jpeg,png,webp",
        "max:2048"
    ],
    "image_url": [
        "nullable",
        "url",
        "max:2048"
    ],
    "profile_image_url": [
        "nullable",
        "url",
        "max:2048"
    ]
}
```

---
### chemicals.show

- **Method**: `GET`
- **Route**: `/api/v1/chemicals/{chemical}`
- **Authentication Required**: Yes (Sanctum/JWT)

**Validation Rules:**
None or standard CRUD parameters.

---
### chemicals.update

- **Method**: `PUT|PATCH`
- **Route**: `/api/v1/chemicals/{chemical}`
- **Authentication Required**: Yes (Sanctum/JWT)

**Validation Rules:**
```json
{
    "common_name": [
        "sometimes",
        "required",
        "string",
        "max:255"
    ],
    "chemical_code": [
        "sometimes",
        "nullable",
        "string",
        "max:100",
        {}
    ],
    "category": [
        "sometimes",
        {}
    ],
    "quantity": [
        "sometimes",
        "integer",
        "min:0"
    ],
    "storage_location": [
        "nullable",
        "string",
        "max:255"
    ],
    "expiry_date": [
        "nullable",
        "date"
    ],
    "danger_level": [
        "sometimes",
        {}
    ],
    "safety_measures": [
        "nullable",
        "string"
    ],
    "description": [
        "nullable",
        "string"
    ],
    "image": [
        "nullable",
        "image",
        "mimes:jpg,jpeg,png,webp",
        "max:2048"
    ],
    "image_url": [
        "nullable",
        "url",
        "max:2048"
    ],
    "profile_image_url": [
        "nullable",
        "url",
        "max:2048"
    ]
}
```

---
### chemicals.destroy

- **Method**: `DELETE`
- **Route**: `/api/v1/chemicals/{chemical}`
- **Authentication Required**: Yes (Sanctum/JWT)

**Validation Rules:**
None or standard CRUD parameters.

---
### chemical-usage-logs.use

- **Method**: `POST`
- **Route**: `/api/v1/chemical-usage-logs/use`
- **Authentication Required**: Yes (Sanctum/JWT)

**Validation Rules:**
```json
{
    "chemical_id": [
        "required",
        "integer",
        "exists:chemicals,id"
    ],
    "quantity_used": [
        "required",
        "numeric",
        "min:0.01"
    ],
    "unit": [
        "nullable",
        "string",
        "max:20"
    ],
    "purpose": [
        "required",
        "string",
        "max:255"
    ],
    "experiment_name": [
        "nullable",
        "string",
        "max:255"
    ],
    "used_at": [
        "required",
        "date"
    ],
    "notes": [
        "nullable",
        "string"
    ]
}
```

---
### chemical-usage-logs.add

- **Method**: `POST`
- **Route**: `/api/v1/chemical-usage-logs/add`
- **Authentication Required**: Yes (Sanctum/JWT)

**Validation Rules:**
```json
{
    "chemical_id": [
        "required",
        "integer",
        "exists:chemicals,id"
    ],
    "quantity_used": [
        "required",
        "numeric",
        "min:0.01"
    ],
    "unit": [
        "nullable",
        "string",
        "max:20"
    ],
    "purpose": [
        "required",
        "string",
        "max:255"
    ],
    "experiment_name": [
        "nullable",
        "string",
        "max:255"
    ],
    "used_at": [
        "required",
        "date"
    ],
    "notes": [
        "nullable",
        "string"
    ]
}
```

---
### chemical-usage-logs.index

- **Method**: `GET`
- **Route**: `/api/v1/chemical-usage-logs`
- **Authentication Required**: Yes (Sanctum/JWT)

**Validation Rules:**
None or standard CRUD parameters.

---
### chemical-usage-logs.show

- **Method**: `GET`
- **Route**: `/api/v1/chemical-usage-logs/{chemicalUsageLog}`
- **Authentication Required**: Yes (Sanctum/JWT)

**Validation Rules:**
None or standard CRUD parameters.

---
### equipment.index

- **Method**: `GET`
- **Route**: `/api/v1/equipment`
- **Authentication Required**: Yes (Sanctum/JWT)

**Validation Rules:**
None or standard CRUD parameters.

---
### equipment.store

- **Method**: `POST`
- **Route**: `/api/v1/equipment`
- **Authentication Required**: Yes (Sanctum/JWT)

**Validation Rules:**
```json
{
    "equipment_name": [
        "required",
        "string",
        "max:255"
    ],
    "equipment_code": [
        "nullable",
        "string",
        "max:100",
        {}
    ],
    "category": [
        "required",
        {}
    ],
    "status": [
        "required",
        {}
    ],
    "condition": [
        "required",
        {}
    ],
    "location": [
        "nullable",
        "string",
        "max:255"
    ],
    "manufacturer": [
        "nullable",
        "string",
        "max:255"
    ],
    "model_name": [
        "nullable",
        "string",
        "max:255"
    ],
    "serial_number": [
        "nullable",
        "string",
        "max:255"
    ],
    "purchase_date": [
        "nullable",
        "date"
    ],
    "purchase_price": [
        "nullable",
        "decimal:0,2",
        "min:0"
    ],
    "description": [
        "nullable",
        "string"
    ],
    "image": [
        "nullable",
        "image",
        "mimes:jpg,jpeg,png,webp",
        "max:2048"
    ],
    "image_url": [
        "nullable",
        "url",
        "max:2048"
    ],
    "profile_image_url": [
        "nullable",
        "url",
        "max:2048"
    ]
}
```

---
### equipment.show

- **Method**: `GET`
- **Route**: `/api/v1/equipment/{equipment}`
- **Authentication Required**: Yes (Sanctum/JWT)

**Validation Rules:**
None or standard CRUD parameters.

---
### equipment.update

- **Method**: `PUT|PATCH`
- **Route**: `/api/v1/equipment/{equipment}`
- **Authentication Required**: Yes (Sanctum/JWT)

**Validation Rules:**
```json
{
    "equipment_name": [
        "sometimes",
        "required",
        "string",
        "max:255"
    ],
    "equipment_code": [
        "sometimes",
        "nullable",
        "string",
        "max:100",
        {}
    ],
    "category": [
        "sometimes",
        {}
    ],
    "status": [
        "sometimes",
        {}
    ],
    "condition": [
        "sometimes",
        {}
    ],
    "location": [
        "nullable",
        "string",
        "max:255"
    ],
    "manufacturer": [
        "nullable",
        "string",
        "max:255"
    ],
    "model_name": [
        "nullable",
        "string",
        "max:255"
    ],
    "serial_number": [
        "nullable",
        "string",
        "max:255"
    ],
    "purchase_date": [
        "nullable",
        "date"
    ],
    "purchase_price": [
        "nullable",
        "decimal:0,2",
        "min:0"
    ],
    "description": [
        "nullable",
        "string"
    ],
    "image": [
        "nullable",
        "image",
        "mimes:jpg,jpeg,png,webp",
        "max:2048"
    ],
    "image_url": [
        "nullable",
        "url",
        "max:2048"
    ],
    "profile_image_url": [
        "nullable",
        "url",
        "max:2048"
    ]
}
```

---
### equipment.destroy

- **Method**: `DELETE`
- **Route**: `/api/v1/equipment/{equipment}`
- **Authentication Required**: Yes (Sanctum/JWT)

**Validation Rules:**
None or standard CRUD parameters.

---
### borrow-records.overdue

- **Method**: `GET`
- **Route**: `/api/v1/borrow-records/overdue`
- **Authentication Required**: Yes (Sanctum/JWT)

**Validation Rules:**
None or standard CRUD parameters.

---
### borrow-records.pending

- **Method**: `GET`
- **Route**: `/api/v1/borrow-records/pending`
- **Authentication Required**: Yes (Sanctum/JWT)

**Validation Rules:**
None or standard CRUD parameters.

---
### borrow-records.return

- **Method**: `POST`
- **Route**: `/api/v1/borrow-records/{borrowRecord}/return`
- **Authentication Required**: Yes (Sanctum/JWT)

**Validation Rules:**
None or standard CRUD parameters.

---
### borrow-records.approve

- **Method**: `POST`
- **Route**: `/api/v1/borrow-records/{borrowRecord}/approve`
- **Authentication Required**: Yes (Sanctum/JWT)

**Validation Rules:**
None or standard CRUD parameters.

---
### borrow-records.reject

- **Method**: `POST`
- **Route**: `/api/v1/borrow-records/{borrowRecord}/reject`
- **Authentication Required**: Yes (Sanctum/JWT)

**Validation Rules:**
```json
{
    "rejected_reason": [
        "nullable",
        "string",
        "max:1000"
    ]
}
```

---
### borrow-records.index

- **Method**: `GET`
- **Route**: `/api/v1/borrow-records`
- **Authentication Required**: Yes (Sanctum/JWT)

**Validation Rules:**
None or standard CRUD parameters.

---
### borrow-records.store

- **Method**: `POST`
- **Route**: `/api/v1/borrow-records`
- **Authentication Required**: Yes (Sanctum/JWT)

**Validation Rules:**
```json
{
    "borrowable_type": [
        "required",
        {}
    ],
    "borrowable_id": [
        "required",
        "integer"
    ],
    "quantity": [
        "required",
        "integer",
        "min:1",
        "max:10000"
    ],
    "due_at": [
        "required",
        "date",
        "after:today"
    ],
    "notes": [
        "nullable",
        "string",
        "max:1000"
    ]
}
```

---
### borrow-records.show

- **Method**: `GET`
- **Route**: `/api/v1/borrow-records/{borrow_record}`
- **Authentication Required**: Yes (Sanctum/JWT)

**Validation Rules:**
None or standard CRUD parameters.

---
### transactions.index

- **Method**: `GET`
- **Route**: `/api/v1/transactions`
- **Authentication Required**: Yes (Sanctum/JWT)

**Validation Rules:**
None or standard CRUD parameters.

---
### transactions.show

- **Method**: `GET`
- **Route**: `/api/v1/transactions/{transaction}`
- **Authentication Required**: Yes (Sanctum/JWT)

**Validation Rules:**
None or standard CRUD parameters.

---
### achievements.index

- **Method**: `GET`
- **Route**: `/api/v1/achievements`
- **Authentication Required**: Yes (Sanctum/JWT)

**Validation Rules:**
None or standard CRUD parameters.

---
### achievements.store

- **Method**: `POST`
- **Route**: `/api/v1/achievements`
- **Authentication Required**: Yes (Sanctum/JWT)

**Validation Rules:**
```json
{
    "achievement_name": [
        "required",
        "string",
        "max:255"
    ],
    "description": [
        "nullable",
        "string"
    ],
    "criteria_type": [
        "required",
        "string",
        "max:100"
    ],
    "criteria_value": [
        "required",
        "integer",
        "min:1"
    ],
    "user_ids": [
        "sometimes",
        "array"
    ],
    "user_ids.*": [
        "integer",
        "distinct",
        "exists:users,id"
    ],
    "image": [
        "nullable",
        "image",
        "mimes:jpg,jpeg,png,webp",
        "max:2048"
    ],
    "image_url": [
        "nullable",
        "url",
        "max:2048"
    ],
    "profile_image_url": [
        "nullable",
        "url",
        "max:2048"
    ]
}
```

---
### achievements.show

- **Method**: `GET`
- **Route**: `/api/v1/achievements/{achievement}`
- **Authentication Required**: Yes (Sanctum/JWT)

**Validation Rules:**
None or standard CRUD parameters.

---
### achievements.update

- **Method**: `PUT|PATCH`
- **Route**: `/api/v1/achievements/{achievement}`
- **Authentication Required**: Yes (Sanctum/JWT)

**Validation Rules:**
```json
{
    "achievement_name": [
        "sometimes",
        "required",
        "string",
        "max:255"
    ],
    "description": [
        "sometimes",
        "nullable",
        "string"
    ],
    "criteria_type": [
        "sometimes",
        "required",
        "string",
        "max:100"
    ],
    "criteria_value": [
        "sometimes",
        "required",
        "integer",
        "min:1"
    ],
    "user_ids": [
        "sometimes",
        "array"
    ],
    "user_ids.*": [
        "integer",
        "distinct",
        "exists:users,id"
    ],
    "image": [
        "nullable",
        "image",
        "mimes:jpg,jpeg,png,webp",
        "max:2048"
    ],
    "image_url": [
        "nullable",
        "url",
        "max:2048"
    ],
    "profile_image_url": [
        "nullable",
        "url",
        "max:2048"
    ]
}
```

---
### achievements.destroy

- **Method**: `DELETE`
- **Route**: `/api/v1/achievements/{achievement}`
- **Authentication Required**: Yes (Sanctum/JWT)

**Validation Rules:**
None or standard CRUD parameters.

---
### achievements.assign

- **Method**: `POST`
- **Route**: `/api/v1/achievements/{achievement}/assign/{user}`
- **Authentication Required**: Yes (Sanctum/JWT)

**Validation Rules:**
None or standard CRUD parameters.

---
### achievements.revoke

- **Method**: `DELETE`
- **Route**: `/api/v1/achievements/{achievement}/revoke/{user}`
- **Authentication Required**: Yes (Sanctum/JWT)

**Validation Rules:**
None or standard CRUD parameters.

---
### user-documents.download

- **Method**: `GET`
- **Route**: `/api/v1/user-documents/{userDocument}/download`
- **Authentication Required**: Yes (Sanctum/JWT)

**Validation Rules:**
None or standard CRUD parameters.

---
### user-documents.index

- **Method**: `GET`
- **Route**: `/api/v1/user-documents`
- **Authentication Required**: Yes (Sanctum/JWT)

**Validation Rules:**
None or standard CRUD parameters.

---
### user-documents.store

- **Method**: `POST`
- **Route**: `/api/v1/user-documents`
- **Authentication Required**: Yes (Sanctum/JWT)

**Validation Rules:**
```json
{
    "title": [
        "required",
        "string",
        "max:255"
    ],
    "file": [
        "required",
        "file",
        "max:10240"
    ],
    "file_type": [
        "nullable",
        "string",
        "max:50",
        "in:document,pdf,image,certificate,other"
    ],
    "description": [
        "nullable",
        "string"
    ]
}
```

---
### user-documents.show

- **Method**: `GET`
- **Route**: `/api/v1/user-documents/{userDocument}`
- **Authentication Required**: Yes (Sanctum/JWT)

**Validation Rules:**
None or standard CRUD parameters.

---
### user-documents.destroy

- **Method**: `DELETE`
- **Route**: `/api/v1/user-documents/{userDocument}`
- **Authentication Required**: Yes (Sanctum/JWT)

**Validation Rules:**
None or standard CRUD parameters.

---
