<?php
// controllers/AuthController.php
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/User.php';

class AuthController {
  private $userModel;

  public function __construct() {
    $db = Database::getInstance()->getConnection();
    $this->userModel = new User($db);
  }

  public function register() {
    // শুধুমাত্র POST রিকোয়েস্ট অ্যালাউ করব
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
      http_response_code(405);
      echo json_encode(["message" => "Method Not Allowed"]);
      return;
    }

    // JSON ইনপুট রিড করা
    $data = json_decode(file_get_contents("php://input"), true);

    // ভ্যালিডেশন
    if (empty($data['name']) || empty($data['email']) || empty($data['password'])) {
      http_response_code(422);
      echo json_encode(["message" => "Name, Email, and Password are required."]);
      return;
    }

    $name = trim($data['name']);
    $email = trim($data['email']);
    $password = $data['password'];

    // ইমেইল ফরম্যাট চেক
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
      http_response_code(422);
      echo json_encode(["message" => "Invalid email format."]);
      return;
    }

    // ইমেইল ডুপ্লিকেট চেক
    if ($this->userModel->emailExists($email)) {
      http_response_code(409); // Conflict
      echo json_encode(["message" => "Email already registered."]);
      return;
    }

    // ইউজার ক্রিয়েট করা
    if ($this->userModel->create($name, $email, $password)) {
      http_response_code(201); // Created
      echo json_encode(["message" => "User registered successfully!"]);
    } else {
      http_response_code(500);
      echo json_encode(["message" => "Something went wrong. Failed to register user."]);
    }
  }

  public function login() {
    // শুধুমাত্র POST রিকোয়েস্ট অ্যালাউ করব
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
      http_response_code(405);
      echo json_encode(["message" => "Method Not Allowed"]);
      return;
    }

    // JSON ইনপুট রিড করা
    $data = json_decode(file_get_contents("php://input"), true);

    if (empty($data['email']) || empty($data['password'])) {
      http_response_code(400);
      echo json_encode(["message" => "Email and Password are required."]);
      return;
    }

    $email = trim($data['email']);
    $password = $data['password'];

    // ১. ডাটাবেজে ইউজার খোঁজা
    $user = $this->userModel->getUserByEmail($email);

    // ২. ইউজার না থাকলে বা পাসওয়ার্ড না মিললে এরর দেওয়া
    if (!$user || !password_verify($password, $user['password'])) {
      http_response_code(401); // Unauthorized
      echo json_encode(["message" => "Invalid email or password."]);
      return;
    }

    // ৩. JWT Payload তৈরি করা
    $secretKey = $_ENV['JWT_SECRET'];
    $issuedAt = time();
    $expireAt = $issuedAt + (int)$_ENV['JWT_EXPIRY'];

    $payload = [
      'iat'  => $issuedAt,           // কখন টোকেন ইস্যু হলো
      'exp'  => $expireAt,           // কখন টোকেন এক্সপায়ার হবে
      'data' => [                    // ইউজারের পাবলিক ইনফরমেশন (পাসওয়ার্ড ছাড়া)
        'id'    => $user['id'],
        'name'  => $user['name'],
        'email' => $user['email']
      ]
    ];

    // ৪. টোকেন জেনারেট করা
    $jwt = JWT::encode($payload, $secretKey, 'HS256');

    // ৫. সাকসেস রেসপন্স পাঠানো
    http_response_code(200);
    echo json_encode([
      "status"  => true,
      "message" => "Login successful!",
      "token"   => $jwt,
      "user"    => [
        "id"    => $user['id'],
        "name"  => $user['name'],
        "email" => $user['email']
      ]
    ]);
  }
}