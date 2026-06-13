<?php
// controllers/DashboardController.php

require_once dirname(__DIR__) . '/config/database.php';
require_once dirname(__DIR__) . '/models/Transaction.php';
require_once dirname(__DIR__) . '/middlewares/AuthMiddleware.php';

class DashboardController {
  private $transactionModel;

  public function __construct() {
    $db = Database::getInstance()->getConnection();
    $this->transactionModel = new Transaction($db);
  }

  public function getSummary() {
    // শুধুমাত্র GET রিকোয়েস্ট অ্যালাউ করব
    if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
      http_response_code(405);
      echo json_encode(["message" => "Method Not Allowed"]);
      return;
    }

    // 🔐 টোকেন ভেরিফাই করা
    $loggedUser = AuthMiddleware::authenticate();
    $userId = $loggedUser->id;

    // মডেল থেকে সামারি ডাটা নেওয়া
    $summary = $this->transactionModel->getDashboardSummary($userId);

    // সাকসেস রেসপন্স পাঠানো
    http_response_code(200);
    echo json_encode([
      "status" => true,
      "data"   => $summary
    ]);
  }
}