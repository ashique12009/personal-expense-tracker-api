<?php
// public/index.php

// API Headers সেট করা (যাতে JSON রেসপন্স পায় এবং CORS হ্যান্ডেল হয়)
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

// OPTIONS রিকোয়েস্ট আসলে এখানেই শেষ করে দেওয়া (CORS এর জন্য প্রি-ফ্লাইট রিকোয়েস্ট)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
  http_response_code(200);
  exit();
}

// ইউআরএল পাথ বের করা
$requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// শুধু নিশ্চিত করার জন্য ডান ও বাম পাশের বাড়তি স্ল্যাশ (/) ট্রিম করে ক্লিন করে নিচ্ছি।
$route = '/' . trim($requestUri, '/');

// কন্ট্রোলার ইমপোর্ট করা
require_once __DIR__ . '/../controllers/AuthController.php';
require_once __DIR__ . '/../controllers/CategoryController.php';
require_once __DIR__ . '/../controllers/TransactionController.php';
require_once __DIR__ . '/../controllers/DashboardController.php';

// কাস্টম রাউটার
switch ($route) {
  // এখন ইউআরএল /api/register বা /api/register/ যাই হোক না কেন, এটি নিখুঁত কাজ করবে
  case '/api/register':
    $auth = new AuthController();
    $auth->register();
    break;

  case '/api/login':
    $auth = new AuthController();
    $auth->login();
    break;

  case '/api/categories':
    $category = new CategoryController();
        
    // রিকোয়েস্ট মেথড অনুযায়ী আলাদা মেথড কল হবে
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
      $category->create();
    } elseif ($_SERVER['REQUEST_METHOD'] === 'GET') {
      $category->index();
    } elseif ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
      $category->delete();
    } elseif ($_SERVER['REQUEST_METHOD'] === 'PUT') {
      $category->update();
    } else {
      http_response_code(405);
      echo json_encode(["message" => "Method Not Allowed"]);
    }
    break;

  case '/api/transactions':
    $transaction = new TransactionController();
    
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
      $transaction->create();
    } elseif ($_SERVER['REQUEST_METHOD'] === 'GET') {
      $transaction->index();
    } elseif ($_SERVER['REQUEST_METHOD'] === 'DELETE') { 
      $transaction->delete();
    } else {
      http_response_code(405);
      echo json_encode(["message" => "Method Not Allowed"]);
    }
    break;

  case '/api/dashboard/summary':
    $dashboard = new DashboardController();
    $dashboard->getSummary();
    break;

  default:
    http_response_code(404);
    echo json_encode([
      "status"          => false,
      "message"         => "Endpoint Not Found",
      "requested_route" => $route
    ]);
    break;
}