<?php
require_once __DIR__ . '/vendor/autoload.php';

// .env ফাইলটি লোড করার জন্য
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

// ১. ডাটাবেজ কানেকশন (আপনার ডাটাবেজের তথ্যের সাথে পরিবর্তন করে নিন)
// এখন আপনি $_ENV বা getenv() দিয়ে ডাটা রিড করতে পারবেন
$host     = $_ENV['DB_HOST'];
$db       = $_ENV['DB_NAME'];
$user     = $_ENV['DB_USER'];
$password = $_ENV['DB_PASS'];

try {
  $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $password, [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
  ]);

  echo "Database connected successfully!<br>";

  // ২. ফেক ইউজার ডাটা (Users Seed)
  $users = [
    ['name' => 'Anik Rahman', 'email' => 'anik@example.com', 'password' => password_hash('password123', PASSWORD_BCRYPT)],
    ['name' => 'Sumi Akter', 'email' => 'sumi@example.com', 'password' => password_hash('password123', PASSWORD_BCRYPT)],
    ['name' => 'Tanvir Ahmed', 'email' => 'tanvir@example.com', 'password' => password_hash('password123', PASSWORD_BCRYPT)]
  ];

  $stmtUser = $pdo->prepare("INSERT INTO users (name, email, password) VALUES (:name, :email, :password)");
  foreach ($users as $user) {
    $stmtUser->execute($user);
  }
  echo "Users seeded successfully!<br>";

  // ৩. ফেক ক্যাটাগরি ডাটা (Categories Seed)
  // এখানে user_id হিসেবে ১ এবং ২ ব্যবহার করা হয়েছে
  $categories = [
    ['user_id' => 1, 'name' => 'Salary', 'type' => 'income'],
    ['user_id' => 1, 'name' => 'Food & Groceries', 'type' => 'expense'],
    ['user_id' => 1, 'name' => 'Rent', 'type' => 'expense'],
    ['user_id' => 2, 'name' => 'Freelancing', 'type' => 'income'],
    ['user_id' => 2, 'name' => 'Shopping', 'type' => 'expense']
  ];

  $stmtCategory = $pdo->prepare("INSERT INTO categories (user_id, name, type) VALUES (:user_id, :name, :type)");
  foreach ($categories as $category) {
    $stmtCategory->execute($category);
  }
  echo "Categories seeded successfully!<br>";

  // ৪. ফেক ট্রানজেকশন ডাটা (Transactions Seed)
  // এখানে আগের টেবিলগুলোর আইডি (user_id, category_id) এর সাথে মিল রেখে ডাটা দেওয়া হয়েছে
  $transactions = [
    [
      'user_id'          => 1, 
      'category_id'      => 1, 
      'amount'           => 25000.00, 
      'type'             => 'income', 
      'description'      => 'Monthly job salary', 
      'transaction_date' => '2026-06-01'
    ],
    [
      'user_id'          => 1, 
      'category_id'      => 2, 
      'amount'           => 1200.50, 
      'type'             => 'expense', 
      'description'      => 'Weekly bazar and snacks', 
      'transaction_date' => '2026-06-03'
    ],
    [
      'user_id'          => 1, 
      'category_id'      => 3, 
      'amount'           => 8000.00, 
      'type'             => 'expense', 
      'description'      => 'June month house rent', 
      'transaction_date' => '2026-06-05'
    ],
    [
      'user_id'          => 2, 
      'category_id'      => 4, 
      'amount'           => 15000.00, 
      'type'             => 'income', 
      'description'      => 'Upwork project payment', 
      'transaction_date' => '2026-06-10'
    ],
    [
      'user_id'          => 2, 
      'category_id'      => 5, 
      'amount'           => 3500.00, 
      'type'             => 'expense', 
      'description'      => 'Eid shopping, punjabi bought', 
      'transaction_date' => '2026-06-12'
    ]
  ];

  $stmtTransaction = $pdo->prepare("
        INSERT INTO transactions (user_id, category_id, amount, type, description, transaction_date) 
        VALUES (:user_id, :category_id, :amount, :type, :description, :transaction_date)
    ");

  foreach ($transactions as $transaction) {
    $stmtTransaction->execute($transaction);
  }
  echo "Transactions seeded successfully!<br>";

  echo "<h3>All data seeded successfully! 🎉</h3>";
} catch (PDOException $e) {
  die("Database error: " . $e->getMessage());
}
?>