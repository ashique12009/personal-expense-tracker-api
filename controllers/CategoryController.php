<?php
// controllers/CategoryController.php

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/Category.php';
require_once __DIR__ . '/../middlewares/AuthMiddleware.php';

class CategoryController {
  private $categoryModel;

  public function __construct() {
    $db = Database::getInstance()->getConnection();
    $this->categoryModel = new Category($db);
  }

  // ➕ ক্যাটাগরি তৈরি করার মেথড (POST)
  public function create() {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
      http_response_code(405);
      echo json_encode(["message" => "Method Not Allowed"]);
      return;
    }

    // 🔐 ১. টোকেন ভেরিফাই করা এবং লগইন করা ইউজারের ডাটা নেওয়া
    $loggedUser = AuthMiddleware::authenticate();
    $userId = $loggedUser->id; // টোকেন থেকে ইউজার আইডি পেয়ে গেলাম

    // JSON ইনপুট রিড করা
    $data = json_decode(file_get_contents("php://input"), true);

    // ভ্যালিডেশন
    if (empty($data['name']) || empty($data['type'])) {
      http_response_code(400);
      echo json_encode(["message" => "Category name and type are required."]);
      return;
    }

    $name = trim($data['name']);
    $type = strtolower(trim($data['type']));

    // টাইপ শুধুমাত্র income অথবা expense হতে পারবে
    if (!in_array($type, ['income', 'expense'])) {
      http_response_code(400);
      echo json_encode(["message" => "Type must be either 'income' or 'expense'."]);
      return;
    }

    // ডুপ্লিকেট ক্যাটাগরি চেক
    if ($this->categoryModel->isDuplicate($userId, $name, $type)) {
      http_response_code(409);
      echo json_encode(["message" => "Category with this name and type already exists for this user."]);
      return;
    }

    // ডাটাবেজে ইনসার্ট করা
    if ($this->categoryModel->create($userId, $name, $type)) {
      http_response_code(201);
      echo json_encode([
        "status"  => true,
        "message" => "Category created successfully!"
      ]);
    } else {
      http_response_code(500);
      echo json_encode(["message" => "Failed to create category."]);
    }
  }

  // 📄 ক্যাটাগরি লিস্ট দেখার মেথড (GET)
  public function index() {
    if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
      http_response_code(405);
      echo json_encode(["message" => "Method Not Allowed"]);
      return;
    }

    // 🔐 টোকেন ভেরিফাই করা
    $loggedUser = AuthMiddleware::authenticate();
    $userId = $loggedUser->id;

    // ইউজারের ক্যাটাগরিগুলো তুলে আনা
    $categories = $this->categoryModel->getByUserId($userId);

    http_response_code(200);
    echo json_encode([
      "status" => true,
      "data"   => $categories
    ]);
  }
}