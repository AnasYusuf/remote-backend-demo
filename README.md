Demo Backend – Orders API
Project Overview

This is a backend demo project built in PHP + MySQL for managing orders and products.
It demonstrates real-world backend practices suitable for remote work, including:

RESTful API design

Transaction-safe operations

Idempotency for order creation

Row-level locking for stock consistency

Error handling with proper HTTP status codes

The system is designed to support a mobile or web frontend, where admins can manage products and orders, and users can place and view their orders.

Folder Structure
/config       → Database configuration
/controllers  → Business logic & API endpoints
/models       → Database query layer (OrderModel.php, ProductModel.php)
/routes       → Endpoint routing (orders.php)
.gitignore
index.php     → Entry point
README.md
postman_collection.json  → Postman collection with example requests/responses

Database Schema
users

id (PK)

name

email

products

id (PK)

name

price

stock

orders

order_id (PK)

user_id (FK → users.id)

total_amount

status (pending, completed, etc.)

unique_key (for idempotency)

order_items

id (PK)

order_id (FK → orders.order_id)

product_id (FK → products.id)

quantity

price

API Endpoints
1. POST /orders

Description: Create a new order.

Request Body:

{
    "user_id": 1,
    "unique_key": "abc123xyz",
    "items": [
        {"product_id": 1, "quantity": 2},
        {"product_id": 2, "quantity": 1}
    ]
}


Success Response (200 OK):

{
    "success": true,
    "order_id": 5
}


Failure Responses:

Invalid input (missing fields)

Duplicate order (unique key exists)

Insufficient stock

Notes:

Idempotency: Unique key ensures duplicate orders are not created.

Transaction: All inserts/updates are wrapped in a transaction. If any step fails, changes are rolled back.

Stock Validation: Quantity must be >0 and ≤ available stock.

2. GET /orders/:id

Description: Fetch a single order by order_id.

Response:

{
    "order_id": 5,
    "user_id": 1,
    "total_amount": 230,
    "status": "pending",
    "items": [
        {"product_id": 1, "quantity": 2, "price": 100},
        {"product_id": 2, "quantity": 1, "price": 30}
    ]
}


Failure Response (404):

{"error": "Order not found"}

3. GET /orders?user_id=1

Description: Fetch the latest 10 completed orders for a user.

Response:

[
    {
        "order_id": 4,
        "total_amount": 230,
        "status": "completed",
        "created_at": "2026-01-07 10:12:35"
    },
    ...
]

Validation Rules

user_id must exist in users table

items must be an array with at least one product

Product quantity must be >0 and ≤ available stock

Total amount is always calculated server-side

Duplicate orders prevented using unique_key

Transactions & Concurrency

Database transaction wraps all inserts/updates per order

Rollback occurs if any failure happens

Row-level locking ensures stock cannot go negative if multiple users order simultaneously

Postman Collection

postman_collection.json includes:

Example requests/responses for each endpoint

Success and failure cases

Import it in Postman to test the API

Assumptions

Stock cannot be negative

Users can have multiple orders

Orders are processed atomically per request

How to Run

Import the database schema

Update /config/config.php with your DB credentials

Start PHP server:

php -S localhost:8000


Use Postman to test endpoints

All endpoints are prefixed through index.php with simple routing