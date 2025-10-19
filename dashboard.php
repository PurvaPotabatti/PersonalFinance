<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
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
  <h2 class="text-center dashboard-title">Welcome, <?php echo htmlspecialchars($_SESSION['user_name']); ?> 👋</h2>

  <div class="d-flex justify-content-end mb-3">
    <a href="signup.php" class="btn btn-outline-danger">
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
