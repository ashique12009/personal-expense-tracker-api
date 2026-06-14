# 💰 Expense Tracker API

A secure, fully-featured **RESTful API** built from scratch using **Pure PHP** and structured around the **MVC (Model-View-Controller)** architecture. This API allows users to systematically track their daily income and expenses. It utilizes **JWT (JSON Web Token)** authentication to safeguard all user-specific data.

---

## ✨ Features

*   **User Authentication:** Secure registration and login flows powered by PHP's native password hashing (`password_hash` / `password_verify`) and JWT tokens.
*   **Custom Route Guard:** A dedicated `AuthMiddleware` layer that acts as a gatekeeper to intercept and authorize protected endpoints.
*   **Category Management (CRUD):** User-isolated custom categories for income and expenses. Prevents duplicate entries and includes a safety check to block the deletion of categories that have active transactions.
*   **Transaction Tracking (CRUD):** Record, view, update, and delete financial entries mapped strictly to designated categories.
*   **Real-time Dashboard Summary:** Calculates total income, total expenses, and the current net balance in real-time through a single API execution.

---

## 🛠️ Tech Stack

*   **Backend Language:** PHP (Pure/Vanilla PHP)
*   **Architecture:** MVC (Model-View-Controller)
*   **Database:** MySQL (via PDO Driver for safe, parameterized queries)
*   **Token Management:** `firebase/php-jwt`
*   **Environment Configuration:** `vlucas/phpdotenv`

---

## 📁 Project Structure

```text
expense-tracker-api/
├── config/
│   └── database.php        # Database Connection (Singleton Pattern)
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
├── composer.json
└── README.md