<?php
// controllers/TransactionController.php

require_once dirname(__DIR__) . '/config/database.php';
require_once dirname(__DIR__) . '/models/Transaction.php';
require_once dirname(__DIR__) . '/middlewares/AuthMiddleware.php';

class TransactionController {
  private $transactionModel;

  public function __construct() {
    $db = Database::getInstance()->getConnection();
    $this->transactionModel = new Transaction($db);
  }

  // 💰 নতুন ট্রানজেকশন যোগ করা (POST)
  public function create() {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
      http_response_code(405);
      echo json_encode(["message" => "Method Not Allowed"]);
      return;
    }

    // 🔐 টোকেন ভেরিফাই করা
    $loggedUser = AuthMiddleware::authenticate();
    $userId = $loggedUser->id;

    // JSON ইনপুট রিড করা
    $data = json_decode(file_get_contents("php://input"), true);

    // ভ্যালিডেশন
    if (empty($data['category_id']) || empty($data['amount']) || empty($data['type'])) {
      http_response_code(422);
      echo json_encode(["message" => "Category ID, Amount, and Type are required."]);
      return;
    }

    $categoryId = (int)$data['category_id'];
    $amount = (float)$data['amount'];
    $type = strtolower(trim($data['type']));
    $description = isset($data['description']) ? trim($data['description']) : null;
    // যদি ইউজার ডেট না দেয়, তবে আজকের ডেট (YYYY-MM-DD) বসে যাবে
    $transactionDate = !empty($data['transaction_date']) ? $data['transaction_date'] : date('Y-m-d');

    if (!in_array($type, ['income', 'expense'])) {
      http_response_code(422);
      echo json_encode(["message" => "Type must be either 'income' or 'expense'."]);
      return;
    }

    // ডাটাবেজে সেভ করা
    if ($this->transactionModel->create($userId, $categoryId, $amount, $type, $description, $transactionDate)) {
      http_response_code(201);
      echo json_encode([
        "status"  => true,
        "message" => "Transaction recorded successfully!"
      ]);
    } else {
      http_response_code(500);
      echo json_encode(["message" => "Failed to record transaction."]);
    }
  }

  // 📋 সব ট্রানজেকশন দেখার মেথড (GET)
  public function index() {
    if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
      http_response_code(405);
      echo json_encode(["message" => "Method Not Allowed"]);
      return;
    }

    // 🔐 টোকেন ভেরিফাই করা
    $loggedUser = AuthMiddleware::authenticate();
    $userId = $loggedUser->id;

    $transactions = $this->transactionModel->getByUserId($userId);

    http_response_code(200);
    echo json_encode([
      "status" => true,
      "data"   => $transactions
    ]);
  }

  public function delete() {
    if ($_SERVER['REQUEST_METHOD'] !== 'DELETE') {
      http_response_code(405);
      echo json_encode(["message" => "Method Not Allowed"]);
      return;
    }

    // 🔐 টোকেন ভেরিফাই করা
    $loggedUser = AuthMiddleware::authenticate();
    $userId = $loggedUser->id;

    // URL থেকে ট্রানজেকশন আইডি নেওয়া (যেমন: /api/transactions?id=5)
    $transactionId = isset($_GET['id']) ? (int)$_GET['id'] : null;

    if (!$transactionId) {
      http_response_code(422);
      echo json_encode(["message" => "Transaction ID is required."]);
      return;
    }

    // ডাটাবেজ থেকে ডিলিট করার চেষ্টা করা
    if ($this->transactionModel->delete($transactionId, $userId)) {
      http_response_code(200);
      echo json_encode([
        "status"  => true,
        "message" => "Transaction deleted successfully!"
      ]);
    } else {
      http_response_code(404);
      echo json_encode([
        "status"  => false,
        "message" => "Transaction not found or you are not authorized to delete it."
      ]);
    }
  }
}