<?php
// models/User.php
class User {
  private $db;

  public function __construct($db) {
    $this->db = $db;
  }

  // ইমেইল ইতিমধ্যে আছে কিনা চেক করা
  public function emailExists($email) {
    $stmt = $this->db->prepare("SELECT id FROM users WHERE email = :email");
    $stmt->execute(['email' => $email]);
    return $stmt->fetch() ? true : false;
  }

  // নতুন ইউজার তৈরি করা
  public function create($name, $email, $password) {
    $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
        
    $stmt = $this->db->prepare("INSERT INTO users (name, email, password) VALUES (:name, :email, :password)");
    return $stmt->execute([
      'name'     => $name,
      'email'    => $email,
      'password' => $hashedPassword
    ]);
  }

  public function getUserByEmail($email) {
    $stmt = $this->db->prepare("SELECT id, name, email, password FROM users WHERE email = :email");
    $stmt->execute(['email' => $email]);
    return $stmt->fetch(); // ইউজার থাকলে অ্যারে দেবে, না থাকলে false দেবে
  }
}