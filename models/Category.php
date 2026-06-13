<?php
// models/Category.php

class Category {
  private $db;

  public function __construct($db) {
    $this->db = $db;
  }

  // ১. একই ইউজারের একই নামের ক্যাটাগরি অলরেডি আছে কিনা চেক করা
  public function isDuplicate($userId, $name, $type) {
    $stmt = $this->db->prepare("SELECT id FROM categories WHERE user_id = :user_id AND name = :name AND type = :type");
    $stmt->execute([
      'user_id' => $userId,
      'name'    => $name,
      'type'    => $type
    ]);
    return $stmt->fetch() ? true : false;
  }

  // ২. নতুন ক্যাটাগরি তৈরি করা
  public function create($userId, $name, $type) {
    $stmt = $this->db->prepare("INSERT INTO categories (user_id, name, type) VALUES (:user_id, :name, :type)");
    return $stmt->execute([
      'user_id' => $userId,
      'name'    => $name,
      'type'    => $type
    ]);
  }

  // ৩. শুধুমাত্র লগইন করা ইউজারের ক্যাটাগরি লিস্ট তুলে আনা
  public function getByUserId($userId) {
    $stmt = $this->db->prepare("SELECT id, name, type, created_at FROM categories WHERE user_id = :user_id ORDER BY id DESC");
    $stmt->execute(['user_id' => $userId]);
    return $stmt->fetchAll();
  }
}