<?php
session_start();

require_once __DIR__ . '/src/Database/MongoDBClient.php';
use App\Database\MongoDBClient;

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

try {
    // Get MongoDB collection
    $collection = MongoDBClient::getCollection('user');

    // Fetch current user details
    $user = $collection->findOne([
        '_id' => new MongoDB\BSON\ObjectId($_SESSION['user_id'])
    ]);

    if (!$user) {
        // If user not found, log out
        session_destroy();
        header("Location: login.php");
        exit;
    }

    // Get username safely
    $user_name = htmlspecialchars($user['name'] ?? 'User');
} catch (Exception $e) {
    die("Error connecting to MongoDB: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Dashboard - Personal Finance Manager</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
  <style>
    body {
      background: linear-gradient(to right, #e0f7fa, #fce4ec);
      font-family: 'Segoe UI', sans-serif;
    }
    .dashboard-title {
      font-weight: 800;
      font-size: 32px;
      color: #2e2e2e;
      margin-bottom: 30px;
    }
    .card {
      border: none;
      border-radius: 20px;
      transition: transform 0.3s, box-shadow 0.3s;
      background: linear-gradient(to bottom right, #ffffff, #f3f4f6);
    }
    .card:hover {
      transform: translateY(-5px);
      box-shadow: 0 10px 25px rgba(0,0,0,0.1);
    }
    .card-body {
      text-align: center;
      padding: 40px 20px;
    }
    .module-title {
      font-size: 18px;
      font-weight: 700;
      margin-top: 15px;
      color: #333;
    }
    .icon-box {
      font-size: 36px;
      color: #007bff;
    }
    .income-icon { color: #4caf50; }
    .expense-icon { color: #e53935; }
    .saving-icon { color: #f9a825; }
    .group-icon { color: #6a1b9a; }
  </style>
</head>
<body>

<div class="container my-5">
  <h2 class="text-center dashboard-title">Welcome, <?php echo $user_name; ?> 👋</h2>

  <div class="d-flex justify-content-end mb-3">
    <a href="logout.php" class="btn btn-outline-danger">
      <i class="fas fa-sign-out-alt me-2"></i> Log Out
    </a>
  </div>

  <div class="row g-4 justify-content-center">

    <!-- Income Card -->
    <div class="col-md-3 col-6">
      <div class="card shadow-sm" onclick="location.href='income.php'">
        <div class="card-body">
          <div class="icon-box income-icon"><i class="fas fa-wallet"></i></div>
          <div class="module-title">Income</div>
        </div>
      </div>
    </div>

    <!-- Expenses Card -->
    <div class="col-md-3 col-6">
      <div class="card shadow-sm" onclick="location.href='expense.php'">
        <div class="card-body">
          <div class="icon-box expense-icon"><i class="fas fa-money-bill-wave"></i></div>
          <div class="module-title">Expenses</div>
        </div>
      </div>
    </div>

    <!-- Savings Card -->
    <div class="col-md-3 col-6">
      <div class="card shadow-sm" onclick="location.href='saving.php'">
        <div class="card-body">
          <div class="icon-box saving-icon"><i class="fas fa-piggy-bank"></i></div>
          <div class="module-title">Savings</div>
        </div>
      </div>
    </div>

    <!-- Report Card -->
    <div class="col-md-3 col-6">
      <div class="card shadow-sm" onclick="location.href='report.php'">
        <div class="card-body">
          <div class="icon-box text-info"><i class="fas fa-chart-line"></i></div>
          <div class="module-title">Report</div>
        </div>
      </div>
    </div>

    <!-- Group Module Card -->
    <div class="col-md-3 col-6">
      <div class="card shadow-sm" onclick="location.href='group_module.php'">
        <div class="card-body">
          <div class="icon-box group-icon"><i class="fas fa-users"></i></div>
          <div class="module-title">Group Tracker</div>
        </div>
      </div>
    </div>

  </div>
</div>

</body>
</html>
