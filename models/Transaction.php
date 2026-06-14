<?php
// models/Transaction.php

class Transaction {
  private $db;

  public function __construct($db) {
    $this->db = $db;
  }

  // ১. নতুন ট্রানজেকশন তৈরি করা
  public function create($userId, $categoryId, $amount, $type, $description, $transactionDate) {
    $stmt = $this->db->prepare("
            INSERT INTO transactions (user_id, category_id, amount, type, description, transaction_date) 
            VALUES (:user_id, :category_id, :amount, :type, :description, :transaction_date)
        ");
        
    return $stmt->execute([
      'user_id'          => $userId,
      'category_id'      => $categoryId,
      'amount'           => $amount,
      'type'             => $type,
      'description'      => $description,
      'transaction_date' => $transactionDate
    ]);
  }

  // ২. লগইন করা ইউজারের সব ট্রানজেকশন (ক্যাটাগরি নামসহ) তুলে আনা
  public function getByUserId($userId) {
    $stmt = $this->db->prepare("
            SELECT t.id, t.amount, t.type, t.description, t.transaction_date, c.name as category_name 
            FROM transactions t
            LEFT JOIN categories c ON t.category_id = c.id
            WHERE t.user_id = :user_id 
            ORDER BY t.transaction_date DESC, t.id DESC
        ");
    $stmt->execute(['user_id' => $userId]);
    return $stmt->fetchAll();
  }

  public function getDashboardSummary($userId) {
    // SUM(CASE WHEN...) ব্যবহার করে এক কুয়েরিতেই ইনকাম ও এক্সপেন্সের যোগফল বের করা
    $stmt = $this->db->prepare("
        SELECT 
            COALESCE(SUM(CASE WHEN type = 'income' THEN amount END), 0) AS total_income,
            COALESCE(SUM(CASE WHEN type = 'expense' THEN amount END), 0) AS total_expense
        FROM transactions 
        WHERE user_id = :user_id
    ");
    $stmt->execute(['user_id' => $userId]);
    $result = $stmt->fetch();

    // স্ট্রিং থেকে ফ্লোট (float) ডাটা টাইপে কনভার্ট করা এবং ব্যালেন্স হিসাব করা
    $totalIncome = (float)$result['total_income'];
    $totalExpense = (float)$result['total_expense'];
    $netBalance = $totalIncome - $totalExpense;

    return [
      "total_income"  => $totalIncome,
      "total_expense" => $totalExpense,
      "net_balance"   => $netBalance
    ];
  }

  public function delete($transactionId, $userId) {
    $stmt = $this->db->prepare("DELETE FROM transactions WHERE id = :id AND user_id = :user_id");
    $stmt->execute([
      'id'      => $transactionId,
      'user_id' => $userId
    ]);

    // নিশ্চিত হওয়া যে আসলেই কোনো রো ডিলিট হয়েছে কিনা
    return $stmt->rowCount() > 0;
  }

  public function update($transactionId, $userId, $categoryId, $amount, $type, $description, $transactionDate) {
    $stmt = $this->db->prepare("
        UPDATE transactions 
        SET category_id = :category_id, 
            amount = :amount, 
            type = :type, 
            description = :description, 
            transaction_date = :transaction_date
        WHERE id = :id AND user_id = :user_id
    ");
    
    $stmt->execute([
      'category_id'      => $categoryId,
      'amount'           => $amount,
      'type'             => $type,
      'description'      => $description,
      'transaction_date' => $transactionDate,
      'id'               => $transactionId,
      'user_id'          => $userId
    ]);

    // যদি ডেটাবেজে আসলেই কোনো চেঞ্জ হয় তবে true দেবে, অন্যথায় false
    return $stmt->rowCount() > 0;
  }
}