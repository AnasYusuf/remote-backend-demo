# Demo Backend – Orders API

## Project Overview
This is a backend demo project built in **PHP + MySQL** for managing orders and products.  
It demonstrates **real-world backend practices** suitable for remote work, including:

- RESTful API design
- Transaction-safe operations
- Idempotency for order creation
- Row-level locking for stock consistency
- Error handling with proper HTTP status codes

The system is designed to support a **mobile or web frontend**, where admins can manage products and orders, and users can place and view their orders.

---

## Folder Structure
/config
  └── Database configuration

/controllers
  └── Business logic & API endpoints

/models
  └── Database query layer
      ├── OrderModel.php
      └── ProductModel.php

/routes
  └── Endpoint routing (orders.php)

.gitignore

index.php          → Entry point

README.md

postman_collection.json → Postman collection

---

## Database Schema

### `users`
- `id` (PK)
- `name`
- `email`

### `products`
- `id` (PK)
- `name`
- `price`
- `stock`

### `orders`
- `order_id` (PK)
- `user_id` (FK → users.id)
- `total_amount`
- `status` (`pending`, `completed`, etc.)
- `unique_key` (for idempotency)

### `order_items`
- `id` (PK)
- `order_id` (FK → orders.order_id)
- `product_id` (FK → products.id)
- `quantity`
- `price`

---

## API Endpoints

### 1. Create Order  
**POST `/orders`** <br>
**Description:** Creates a new order with full validation, idempotency, and transaction safety. 

**Request Body:**
```json
 {
     "user_id": 1,
     "unique_key": "abc123xyz",
     "items": [
    { "product_id": 1, "quantity": 2 },
    { "product_id": 2, "quantity": 1 }
  ]
 }
```

**Success Response (200 OK):**
```json
{
  "success": true,
  "order_id": 5
}
```

**Possible Failure Responses:**
- `Missing or invalid request fields`
- `409 – Order already exists (duplicate unique key)`
- `400 – Invalid input / insufficient stock`

**Notes:**
- `Total amount is calculated server-side`
- `Idempotency: A unique key is stored with each order. If the same request is retried, the existing order is returned and no duplicate is created.`
- `Transaction: All related database operations are wrapped in a transaction. If any step fails, the transaction is rolled back.`
- `Stock Validation: Product quantity must be greater than zero and less than or equal to available stock.`
- `Stock updates are concurrency-safe`

---

### 2. Get All Orders for a User
**GET `/orders?user_id={userid}`** <br>
**Description:** Returns all orders associated with a user.

**Response (200 OK):**
```json
 {
    "success": true,
    "orders": [
        {
            "order_id": 12,
            "total_amount": "50.00",
            "status": "pending",
            "unique_key": "order_2026_01_07_1147",
            "items": [
                {
                    "product_id": 1,
                    "quantity": 1,
                    "price": "50.00"
                }
            ]
        },
        {
            "order_id": 5,
            "total_amount": "110.00",
            "status": "pending",
            "unique_key": "order_2026_01_07_1145",
            "items": [
                {
                    "product_id": 1,
                    "quantity": 1,
                    "price": "50.00"
                },
                {
                    "product_id": 2,
                    "quantity": 2,
                    "price": "60.00"
                }
            ]
        }
    ]
}
```

---

### 3. Get Single Order Details
**GET `/orders?order_id={orderid}`** <br>
**Description:** Fetches full details of a single order, including its items.

**Response (200 OK):**
```json
 {
    "order_id": 1,
    "user_id": 1,
    "total_amount": 230,
    "status": "pending",
    "items": [
        { "product_id": 1, "quantity": 2, "price": 100 },
        { "product_id": 2, "quantity": 1, "price": 30 }
    ]
}
```

**Failure Response (404)**
```json
{
    "error": "Order not found"
}
```

---

### 4. Get Last 10 orders for a user
**GET `/orders?user_id=1&status=pending&limit=10`** <br>
**Description:** Fetches the latest N orders for a user, optionally filtered by status.

### `Query Parameters`
- `user_id` (required)
- `status` (optional)
- `limit` (optional, default 10)

**Response (200 OK):**
```json
 [
    {
        "order_id": 7,
        "total_amount": 180,
        "status": "pending",
        "created_at": "2026-01-08 09:42:10"
    }
]
```

---

### `Validation Rules`
- `user_id` must exist
- `items` must be a non-empty array
- Each product must exist
- Quantity must be > 0 and ≤ available stock
- Total amount is never trusted from the client
- Duplicate orders prevented via `unique_key`

---

### `Transactions & Concurrency`
- All order creation logic runs inside a `database transaction`
- Any failure triggers a `rollback`
- `Row-level locking` (SELECT ... FOR UPDATE) ensures:
  - Stock is updated by only one request at a time
  - Negative stock values are prevented
- Ensures consistency even under concurrent requests

---

### `HTTP Status Codes Used`
| Code | Meaning                            |
| ---- | ---------------------------------- |
| 200  | Success                            |
| 400  | Invalid request / validation error |
| 404  | Resource not found                 |
| 409  | Duplicate request (idempotency)    |

---

### `Postman Collection`
The repository includes `postman_collection.json` with:
- Sample requests for all endpoints
- Success and failure cases
- Ready-to-import collection for testing

---

### `How to Run Locally`
**1.** Import the database schema <br>
**2.** Update database credentials in /config/database.php <br>
**3.** Start the PHP development server: <br>
```json
php -S localhost:8000
```
**4.** Test endpoints using Postman

---

### Assumptions
- Stock cannot be negative
- One user can have multiple orders
- Order creation is atomic
- Read-only APIs do not require transactions

---

### Why This Project Matters
This demo project reflects `real backend problems:`
 - Duplicate requests
 - Concurrent updates
 - Partial failures
 - Data integrity under load

It is designed to clearly demonstrate `production-level backend thinking` in interviews.
