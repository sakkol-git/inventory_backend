# Simple ERD

```mermaid
flowchart LR
    subgraph Core
        USERS[users]
        ACHIEVEMENTS[achievements]
        USER_ACHIEVEMENTS[user_achievements]
        ROLES[roles]
        PERMISSIONS[permissions]
        MODEL_HAS_ROLES[model_has_roles]
        MODEL_HAS_PERMISSIONS[model_has_permissions]
        ROLE_HAS_PERMISSIONS[role_has_permissions]
    end

    subgraph Plant
        PLANT_SPECIES[plant_species]
        PLANT_VARIETIES[plant_varieties]
        PLANT_SAMPLES[plant_samples]
        PLANT_STOCKS[plant_stocks]
    end

    subgraph Inventory
        CHEMICALS[chemicals]
        CHEMICAL_BATCHES[chemical_batches]
        CHEMICAL_USAGE_LOGS[chemical_usage_logs]
        EQUIPMENT[equipment]
        MAINTENANCE_RECORDS[maintenance_records]
        BORROW_RECORDS[borrow_records]
        TRANSACTIONS[transactions]
    end

    subgraph System
        ACTIVITY_LOG[activity_log]
        PERSONAL_ACCESS_TOKENS[personal_access_tokens]
        CACHE[cache]
        CACHE_LOCKS[cache_locks]
    end

    PLANT_SPECIES -->|1 to many| PLANT_VARIETIES
    PLANT_SPECIES -->|1 to many| PLANT_SAMPLES
    PLANT_SPECIES -->|1 to many| PLANT_STOCKS
    PLANT_VARIETIES -->|1 to many| PLANT_SAMPLES
    PLANT_VARIETIES -->|1 to many| PLANT_STOCKS
    PLANT_SAMPLES -->|1 to many| PLANT_STOCKS

    CHEMICALS -->|1 to many| CHEMICAL_BATCHES
    CHEMICALS -->|1 to many| CHEMICAL_USAGE_LOGS
    CHEMICAL_BATCHES -->|optional 1 to many| CHEMICAL_USAGE_LOGS

    EQUIPMENT -->|1 to many| MAINTENANCE_RECORDS

    USERS -->|1 to many| USER_ACHIEVEMENTS
    ACHIEVEMENTS -->|1 to many| USER_ACHIEVEMENTS

    USERS -->|1 to many| BORROW_RECORDS
    USERS -->|1 to many| CHEMICAL_USAGE_LOGS
    USERS -->|1 to many| MAINTENANCE_RECORDS
    USERS -->|1 to many| TRANSACTIONS

    EQUIPMENT -->|borrowable polymorphic| BORROW_RECORDS
    CHEMICALS -->|borrowable polymorphic| BORROW_RECORDS
    PLANT_SAMPLES -->|borrowable polymorphic| BORROW_RECORDS

    PLANT_SPECIES -->|transactionable polymorphic| TRANSACTIONS
    PLANT_VARIETIES -->|transactionable polymorphic| TRANSACTIONS
    PLANT_SAMPLES -->|transactionable polymorphic| TRANSACTIONS
    PLANT_STOCKS -->|transactionable polymorphic| TRANSACTIONS
    CHEMICALS -->|transactionable polymorphic| TRANSACTIONS
    EQUIPMENT -->|transactionable polymorphic| TRANSACTIONS
    ACHIEVEMENTS -->|transactionable polymorphic| TRANSACTIONS

    ROLES -->|many to many| ROLE_HAS_PERMISSIONS
    PERMISSIONS -->|many to many| ROLE_HAS_PERMISSIONS
    ROLES -->|polymorphic support| MODEL_HAS_ROLES
    PERMISSIONS -->|polymorphic support| MODEL_HAS_PERMISSIONS

```

Notes:
- `activity_log` and `personal_access_tokens` are polymorphic system tables, so they are shown as standalone tables instead of being tied to one specific model.
- `model_has_roles` and `model_has_permissions` are polymorphic support tables from the permission package.
- `cache` and `cache_locks` have no table relationships, so they stay as standalone system tables.
