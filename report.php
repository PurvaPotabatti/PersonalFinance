<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$host = 'localhost';
$db = 'finance_manager';
$user = 'root';
$pass = '';
$port = 3307;

$conn = new mysqli($host, $user, $pass, $db, $port);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$user_id = $_SESSION['user_id'] ?? 1;

// Fetch total from table with optional type
function fetch_total($conn, $table, $has_type = false, $type_value = null) {
    global $user_id;

    if ($has_type && $type_value !== null) {
        $query = "SELECT SUM(amount) AS total FROM $table WHERE user_id = ? AND type = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("is", $user_id, $type_value);
    } else {
        $query = "SELECT SUM(amount) AS total FROM $table WHERE user_id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $user_id);
    }

    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_assoc()['total'] ?? 0;
}

// Fetch totals
$total_income = fetch_total($conn, "income");
$static_expenses = fetch_total($conn, "static_expenses");
$dynamic_expenses = fetch_total($conn, "dynamic_expenses");
$static_savings = fetch_total($conn, "savings", true, "static");
$dynamic_savings = fetch_total($conn, "savings", true, "dynamic");

$total_expenses = $static_expenses + $dynamic_expenses;
$total_savings = $static_savings + $dynamic_savings;
$remaining_income = $total_income - ($total_expenses + $total_savings);

// Fetch Group Contribution Data
$group_contributions = [];
$group_query = "SELECT g.group_name, SUM(e.amount) AS total_paid, SUM(s.share_amount) AS total_owed 
                FROM groups g 
                LEFT JOIN group_expenses e ON g.group_id = e.group_id 
                LEFT JOIN expense_shares s ON e.expense_id = s.expense_id
                WHERE g.created_by = ? GROUP BY g.group_id";
$stmt = $conn->prepare($group_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->store_result();
$stmt->bind_result($group_name, $total_paid, $total_owed);

while ($stmt->fetch()) {
    $group_contributions[] = [
        'group_name' => $group_name,
        'total_paid' => $total_paid,
        'total_owed' => $total_owed,
        'net_balance' => $total_paid - $total_owed
    ];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Finance Report</title>
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
      padding: 30px 20px;
    }
    .module-title {
      font-size: 18px;
      font-weight: 700;
      margin-top: 15px;
      color: #333;
    }
    .icon-box {
      font-size: 36px;
    }
    .income-icon { color: #4caf50; }
    .expense-icon { color: #e53935; }
    .saving-icon { color: #f9a825; }
    .remaining-icon { color: #1976d2; }
  </style>
</head>
<body>

<div class="container my-5">
  <h2 class="text-center dashboard-title">📊 Financial Report - <?php echo htmlspecialchars($_SESSION['user_name']); ?></h2>

  <div class="row g-4 justify-content-center">

    <!-- Total Income -->
    <div class="col-md-4 col-sm-6">
      <div class="card shadow-sm">
        <div class="card-body">
          <div class="icon-box income-icon"><i class="fas fa-wallet"></i></div>
          <div class="module-title">Total Income</div>
          <h4 class="mt-2">₹ <?= number_format($total_income, 2) ?></h4>
        </div>
      </div>
    </div>

    <!-- Static Expenses -->
    <div class="col-md-4 col-sm-6">
      <div class="card shadow-sm">
        <div class="card-body">
          <div class="icon-box expense-icon"><i class="fas fa-money-bill"></i></div>
          <div class="module-title">Static Expenses</div>
          <h4 class="mt-2">₹ <?= number_format($static_expenses, 2) ?></h4>
        </div>
      </div>
    </div>

    <!-- Dynamic Expenses -->
    <div class="col-md-4 col-sm-6">
      <div class="card shadow-sm">
        <div class="card-body">
          <div class="icon-box expense-icon"><i class="fas fa-coins"></i></div>
          <div class="module-title">Dynamic Expenses</div>
          <h4 class="mt-2">₹ <?= number_format($dynamic_expenses, 2) ?></h4>
        </div>
      </div>
    </div>

    <!-- Static Savings -->
    <div class="col-md-4 col-sm-6">
      <div class="card shadow-sm">
        <div class="card-body">
          <div class="icon-box saving-icon"><i class="fas fa-piggy-bank"></i></div>
          <div class="module-title">Static Savings</div>
          <h4 class="mt-2">₹ <?= number_format($static_savings, 2) ?></h4>
        </div>
      </div>
    </div>

    <!-- Dynamic Savings -->
    <div class="col-md-4 col-sm-6">
      <div class="card shadow-sm">
        <div class="card-body">
          <div class="icon-box saving-icon"><i class="fas fa-chart-line"></i></div>
          <div class="module-title">Dynamic Savings</div>
          <h4 class="mt-2">₹ <?= number_format($dynamic_savings, 2) ?></h4>
        </div>
      </div>
    </div>

    <!-- Remaining Income -->
    <div class="col-md-4 col-sm-6">
      <div class="card shadow-sm">
        <div class="card-body">
          <div class="icon-box remaining-icon"><i class="fas fa-balance-scale"></i></div>
          <div class="module-title">Remaining Income</div>
          <h4 class="mt-2">₹ <?= number_format($remaining_income, 2) ?></h4>
        </div>
      </div>
    </div>

  </div>

  <!-- Group Contribution Summary Section -->
  <h3 class="text-center mt-5 mb-4">🔁 Group Contribution Summary</h3>
  <div class="row g-4 justify-content-center">
    <?php foreach ($group_contributions as $contribution): ?>
      <div class="col-md-4 col-sm-6">
        <div class="card shadow-sm">
          <div class="card-body">
            <div class="icon-box income-icon"><i class="fas fa-users"></i></div>
            <div class="module-title"><?= htmlspecialchars($contribution['group_name']) ?></div>
            <h5 class="mt-2">Total Paid: ₹ <?= number_format($contribution['total_paid'], 2) ?></h5>
            <h5>Total Owed: ₹ <?= number_format($contribution['total_owed'], 2) ?></h5>
            <h5 class="fw-bold <?= $contribution['net_balance'] >= 0 ? 'text-success' : 'text-danger' ?>">
              Net Balance: ₹ <?= number_format($contribution['net_balance'], 2) ?>
            </h5>
            <h6>Status: <?= $contribution['net_balance'] >= 0 ? 'You are owed money' : 'You owe money' ?></h6>
          </div>
        </div>
      </div>
    <?php endforeach; ?>
  </div>

</div>

  </div>
                <div class="text-start mb-3">
      <a href="dashboard.php" class="btn btn-outline-primary btn-main">
        ⬅ Back to Dashboard
      </a>
    </div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php
$conn->close();
?>
