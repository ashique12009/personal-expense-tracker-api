# 💰 Expense Tracker API

A secure, fully-featured **RESTful API** built from scratch using **Pure PHP** and structured around the **MVC (Model-View-Controller)** architecture. This API allows users to systematically track their daily income and expenses. It utilizes **JWT (JSON Web Token)** authentication to safeguard all user-specific data.

---

## ✨ Features

* **User Authentication:** Secure registration and login flows powered by PHP's native password hashing (`password_hash` / `password_verify`) and JWT tokens.
* **Custom Route Guard:** A dedicated `AuthMiddleware` layer that acts as a gatekeeper to intercept and authorize protected endpoints.
* **Category Management (CRUD):** User-isolated custom categories for income and expenses. Prevents duplicate entries and includes a safety check to block the deletion of categories that have active transactions.
* **Transaction Tracking (CRUD):** Record, view, update, and delete financial entries mapped strictly to designated categories.
* **Real-time Dashboard Summary:** Calculates total income, total expenses, and the current net balance in real-time through a single API execution.

---

## 🛠️ Tech Stack

* **Backend Language:** PHP (Pure/Vanilla PHP)
* **Architecture:** MVC (Model-View-Controller)
* **Database:** MySQL (via PDO Driver for safe, parameterized queries)
* **Token Management:** `firebase/php-jwt`
* **Environment Configuration:** `vlucas/phpdotenv`

---

## 📁 Project Structure

```text
expense-tracker-api/
├── config/
│   └── database.php        # Database Connection (Singleton Pattern)
├── database/
│   ├── schema.php          # Database Tables Setup & Schema
│   └── seed.php            # Dummy/Initial Data Seeder
├── controllers/
│   ├── AuthController.php
│   ├── CategoryController.php
│   ├── DashboardController.php
│   └── TransactionController.php
├── middleware/
│   └── AuthMiddleware.php  # JWT Route Guard
├── models/
│   ├── User.php
│   ├── Category.php
│   └── Transaction.php
├── public/
│   └── index.php           # Single Entry Point & Custom Router
├── .env                    # Environment Variables Configuration
├── .htaccess               # Apache URL Rewriting (Pretty URLs)
├── composer.json
└── README.md
```

## 📁 API Endpoints

{
  "name": "Ashique",
  "email": "ashique@example.com",
  "password": "securepassword123"
}
```

* **Success Response (201 Created):**
```json
{
    "status": true,
    "message": "User registered successfully!"
}
```

#### 2. Login User
* **URL:** `/api/login`
* **Method:** `POST`
* **Headers:** `Content-Type: application/json`
* **Request Body:**
```json
{
    "email": "ashique@example.com",
    "password": "securepassword123"
}
```

* **Success Response (200 OK):**
```json
{
    "status": true,
    "message": "Login successful!",
    "token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...",
    "user": {
        "id": 4,
        "name": "Ashique",
        "email": "ashique@example.com"
    }
}
```

---

### 📂 Category Management

#### 1. Create Category
* **URL:** `/api/categories`
* **Method:** `POST`
* **Headers:** `Content-Type: application/json`, `Authorization: Bearer <token>`
* **Request Body:**
```json
{
    "name": "Food & Groceries",
    "type": "expense" 
}
```
    
*(Note: `type` can only be `income` or `expense`)*
* **Success Response (201 Created):**
```json
{
    "status": true,
    "message": "Category created successfully!"
}
```

#### 2. List Categories
* **URL:** `/api/categories`
* **Method:** `GET`
* **Headers:** `Authorization: Bearer <token>`
* **Success Response (200 OK):**
```json
{
    "status": true,
    "data": [
        {
            "id": 7,
            "name": "Part Time",
            "type": "income",
            "created_at": "2026-06-14 08:50:49"
        }
    ]
}
```

#### 3. Update Category
* **URL:** `/api/categories?id={category_id}`
* **Method:** `PUT`
* **Headers:** `Content-Type: application/json`, `Authorization: Bearer <token>`
* **Request Body:**
```json
{
    "name": "Office Snacks",
    "type": "expense"
}
```

* **Success Response (200 OK):**
```json
{
    "status": true,
    "message": "Category updated successfully!"
}
```

#### 4. Delete Category
* **URL:** `/api/categories?id={category_id}`
* **Method:** `DELETE`
* **Headers:** `Authorization: Bearer <token>`
* **Success Response (200 OK):**
```json
{
    "status": true,
    "message": "Category deleted successfully!"
}
```

---

### 💰 Transaction Management

#### 1. Add Transaction
* **URL:** `/api/transactions`
* **Method:** `POST`
* **Headers:** `Content-Type: application/json`, `Authorization: Bearer <token>`
* **Request Body:**
```json
{
    "category_id": 1,
    "amount": 550.00,
    "type": "expense",
    "description": "Dinner with team",
    "transaction_date": "2026-06-14"
}
```

*(Note: If `transaction_date` is omitted, the system defaults to the current date)*
* **Success Response (201 Created):**
```json
{
    "status": true,
    "message": "Transaction recorded successfully!"
}
```

#### 2. List Transactions
* **URL:** `/api/transactions`
* **Method:** `GET`
* **Headers:** `Authorization: Bearer <token>`
* **Success Response (200 OK):**
```json
{
    "status": true,
    "data": [
        {
            "id": 12,
            "amount": "550.00",
            "type": "expense",
            "description": "Dinner with team",
            "transaction_date": "2026-06-14",
            "category_name": "Food & Groceries"
        }
    ]
}
```

#### 3. Update Transaction
* **URL:** `/api/transactions?id={transaction_id}`
* **Method:** `PUT`
* **Headers:** `Content-Type: application/json`, `Authorization: Bearer <token>`
* **Request Body:**
```json
{
    "category_id": 1,
    "amount": 600.00,
    "type": "expense",
    "description": "Dinner with team (Updated price)",
    "transaction_date": "2026-06-14"
}
```

* **Success Response (200 OK):**
```json
{
    "status": true,
    "message": "Transaction updated successfully!"
}
```

#### 4. Delete Transaction
* **URL:** `/api/transactions?id={transaction_id}`
* **Method:** `DELETE`
* **Headers:** `Authorization: Bearer <token>`
* **Success Response (200 OK):**
```json
{
    "status": true,
    "message": "Transaction deleted successfully!"
}
```

---

### 📊 Dashboard Metrics

#### 1. Get Dashboard Summary
* **URL:** `/api/dashboard/summary`
* **Method:** `GET`
* **Headers:** `Authorization: Bearer <token>`
* **Success Response (200 OK):**
```json
{
    "status": true,
    "data": {
        "total_income": 45000.00,
        "total_expense": 12500.50,
        "net_balance": 32499.50
    }
}
```

---

## 🔒 Security & Data Integrity
* **Data Isolation:** Multi-tenancy isolation is hardcoded via structured SQL query parameters (`WHERE user_id = :user_id`). Users can never access, read, overwrite, or delete data belonging to another account.
* **Affirmative Row Modification Check:** Utilizes PHP's `rowCount()` validation pattern on all mutating SQL execution requests (`PUT` & `DELETE`). This prevents false-positive API success responses if an unowned, compromised, or missing resource ID is targeted.
* **Robust Encryption:** High-entropy password handling is managed exclusively via modern cryptographic routines natively implemented using PHP's native `password_hash()` algorithm.