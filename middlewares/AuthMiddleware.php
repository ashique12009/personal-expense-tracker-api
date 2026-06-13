<?php
// middleware/AuthMiddleware.php

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

require_once __DIR__ . '/../vendor/autoload.php';

class AuthMiddleware {
  public static function authenticate() {
    // ১. রিকোয়েস্ট হেডার থেকে Authorization হেডার খুঁজে বের করা
    $headers = apache_request_headers();
    $authHeader = $headers['Authorization'] ?? $headers['authorization'] ?? null;

    // যদি হেডার না থাকে
    if (!$authHeader) {
      http_response_code(401);
      echo json_encode([
        "status"  => false,
        "message" => "Access Denied. No token provided."
      ]);
      exit();
    }

    // ২. "Bearer <token>" ফরম্যাট থেকে শুধুমাত্র টোকেনটা আলাদা করা
    // $authHeader দেখতে এমন হয়: "Bearer eyJ0eXAiOiJKV1Qi..."
    $token = null;
    if (preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
      $token = $matches[1];
    }

    // টোকেন যদি ঠিকঠাক ফরম্যাটে না থাকে
    if (!$token) {
      http_response_code(401);
      echo json_encode([
        "status"  => false,
        "message" => "Invalid token format. Use 'Bearer <token>'"
      ]);
      exit();
    }

    try {
      // ৩. .env থেকে সিক্রেট কি নিয়ে টোকেন ডিকোড (Decode) করা
      $secretKey = $_ENV['JWT_SECRET'];
            
      // firebase/php-jwt এর লেটেস্ট নিয়মে Key অবজেক্ট ব্যবহার করতে হয়
      $decoded = JWT::decode($token, new Key($secretKey, 'HS256'));

      // ৪. ডিকোড করা ইউজারের ডাটা রিটার্ন করা (যা কন্ট্রোলারে ব্যবহার করব)
      // $decoded->data এর ভেতর id, name, email থাকবে যা আমরা লগইনের সময় পে-লোডে দিয়েছিলাম
      return $decoded->data;
    } catch (Exception $e) {
      // টোকেন এক্সপায়ার হয়ে গেলে বা ভুল হলে এই ক্যাচ ব্লকে আসবে
      http_response_code(401);
      echo json_encode([
        "status"  => false,
        "message" => "Access Denied. Invalid or expired token.",
        "error"   => $e->getMessage()
      ]);
      exit();
    }
  }
}