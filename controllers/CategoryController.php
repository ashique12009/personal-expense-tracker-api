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

  public function delete() {
    if ($_SERVER['REQUEST_METHOD'] !== 'DELETE') {
      http_response_code(405);
      echo json_encode(["message" => "Method Not Allowed"]);
      return;
    }

    // 🔐 টোকেন ভেরিফাই করা
    $loggedUser = AuthMiddleware::authenticate();
    $userId = $loggedUser->id;

    // URL থেকে আইডি নেওয়া (যেমন: /api/categories?id=5)
    $categoryId = isset($_GET['id']) ? (int)$_GET['id'] : null;

    if (!$categoryId) {
      http_response_code(400);
      echo json_encode(["message" => "Category ID is required."]);
      return;
    }

    // ১. চেক করা এই ক্যাটাগরির অধীনে কোনো লেনদেন বা ট্রানজেকশন আছে কিনা
    if ($this->categoryModel->hasTransactions($categoryId)) {
      http_response_code(400); // Bad Request
      echo json_encode([
        "status"  => false,
        "message" => "Cannot delete category. It has active transactions associated with it."
      ]);
      return;
    }

    // ২. ক্যাটাগরি ডিলিট করা
    if ($this->categoryModel->delete($categoryId, $userId)) {
      http_response_code(200);
      echo json_encode([
        "status"  => true,
        "message" => "Category deleted successfully!"
      ]);
    } else {
      http_response_code(500);
      echo json_encode(["message" => "Failed to delete category or category not found."]);
    }
  }

  public function update() {
    // আমরা PUT বা PATCH মেথড ব্যবহার করতে পারি, এখানে PUT ব্যবহার করছি
    if ($_SERVER['REQUEST_METHOD'] !== 'PUT') {
      http_response_code(405);
      echo json_encode(["message" => "Method Not Allowed"]);
      return;
    }

    // 🔐 টোকেন ভেরিফাই করা
    $loggedUser = AuthMiddleware::authenticate();
    $userId = $loggedUser->id;

    // URL থেকে আইডি নেওয়া (যেমন: /api/categories?id=5)
    $categoryId = isset($_GET['id']) ? (int)$_GET['id'] : null;

    if (!$categoryId) {
      http_response_code(400);
      echo json_encode(["message" => "Category ID is required."]);
      return;
    }

    // JSON ইনপুট রিড করা
    $data = json_decode(file_get_contents("php://input"), true);

    // ভ্যালিডেশন
    if (empty($data['name']) || empty($data['type'])) {
      http_response_code(422);
      echo json_encode(["message" => "Category name and type are required for update."]);
      return;
    }

    $name = trim($data['name']);
    $type = strtolower(trim($data['type']));

    if (!in_array($type, ['income', 'expense'])) {
      http_response_code(422);
      echo json_encode(["message" => "Type must be either 'income' or 'expense'."]);
      return;
    }

    // ৩. ডুপ্লিকেট ক্যাটাগরি চেক (নিজের আইডি বাদে অন্য কোথাও এই নাম আছে কিনা)
    if ($this->categoryModel->isDuplicateForUpdate($userId, $name, $type, $categoryId)) {
      http_response_code(422); // Conflict
      echo json_encode(["message" => "Another category with this name and type already exists."]);
      return;
    }

    // ৪. আপডেট এক্সিকিউট করা
    if ($this->categoryModel->update($categoryId, $userId, $name, $type)) {
      http_response_code(200);
      echo json_encode([
        "status"  => true,
        "message" => "Category updated successfully!"
      ]);
    } else {
      http_response_code(404); 
      echo json_encode([
        "status"  => false,
        "message" => "Category not found, unauthorized, or no changes were made."
      ]);
    }
  }
}