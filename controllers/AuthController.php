<?php
// controllers/AuthController.php
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
}