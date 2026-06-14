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

  // ১. ক্যাটাগরির অধীনে কোনো ট্রানজেকশন আছে কিনা চেক করা
  public function hasTransactions($categoryId) {
    $stmt = $this->db->prepare("SELECT id FROM transactions WHERE category_id = :category_id LIMIT 1");
    $stmt->execute(['category_id' => $categoryId]);
    return $stmt->fetch() ? true : false;
  }

  // ২. ক্যাটাগরি ডিলিট করা (নিশ্চিত করা যে এটি এই ইউজারেরই ক্যাটাগরি)
  public function delete($categoryId, $userId) {
    $stmt = $this->db->prepare("DELETE FROM categories WHERE id = :id AND user_id = :user_id");
    return $stmt->execute([
      'id'      => $categoryId,
      'user_id' => $userId
    ]);
  }

  // ১. আপডেট করার সময় অন্য কোনো ক্যাটাগরির সাথে নাম ও টাইপ ডুপ্লিকেট হচ্ছে কিনা চেক করা
  public function isDuplicateForUpdate($userId, $name, $type, $categoryId) {
    $stmt = $this->db->prepare("
        SELECT id FROM categories 
        WHERE user_id = :user_id AND name = :name AND type = :type AND id != :id
    ");
    $stmt->execute([
      'user_id' => $userId,
      'name'    => $name,
      'type'    => $type,
      'id'      => $categoryId
    ]);
    return $stmt->fetch() ? true : false;
  }

  // ২. ক্যাটাগরি আপডেট করা (নিশ্চিত করা যে এটি এই ইউজারেরই ক্যাটাগরি)
  public function update($categoryId, $userId, $name, $type) {
    $stmt = $this->db->prepare("
        UPDATE categories 
        SET name = :name, type = :type 
        WHERE id = :id AND user_id = :user_id
    ");
    $stmt->execute([
      'name'    => $name,
      'type'    => $type,
      'id'      => $categoryId,
      'user_id' => $userId
    ]);

    return $stmt->rowCount() > 0;
  }
}