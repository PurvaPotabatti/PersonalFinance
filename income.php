<?php
session_start();
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

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Add income
   if (isset($_POST['source'], $_POST['amount'], $_POST['date_received']) && is_numeric($_POST['amount'])) {
    $source = $_POST['source'];
    $amount = $_POST['amount'];
    $date_received = $_POST['date_received']; // ✅ Required line
    $month_year = date('Y-m', strtotime($date_received));

    $stmt = $conn->prepare("INSERT INTO Income (user_id, source, amount, month_year, date_received) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("isdss", $user_id, $source, $amount, $month_year, $date_received);
    $stmt->execute();
    $stmt->close();
}


    // Delete income entry
    if (isset($_POST['delete_id'])) {
        $delete_id = $_POST['delete_id'];
        $conn->query("DELETE FROM Income WHERE income_id = '$delete_id' AND user_id = '$user_id'");
    }
}

$result = $conn->query("SELECT * FROM Income WHERE user_id = '$user_id' ORDER BY date_received DESC");
$totalResult = $conn->query("SELECT SUM(amount) AS total FROM Income WHERE user_id = '$user_id'");
$totalRow = $totalResult->fetch_assoc();
$totalIncome = $totalRow['total'] ?? 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Income Manager</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
  <style>
    body {
      background: linear-gradient(to right, #e0f7fa, #f1f8e9);
      font-family: 'Segoe UI', sans-serif;
    }
    .dashboard-card {
      background: white;
      border-radius: 24px;
      padding: 50px;
      box-shadow: 0 10px 30px rgba(0,0,0,0.1);
      margin-top: 60px;
    }
    .section-title {
      font-size: 32px;
      font-weight: 800;
      color: #2c3e50;
      margin-bottom: 30px;
    }
    .btn-main {
      font-size: 16px;
      font-weight: 600;
      padding: 14px 28px;
      border-radius: 12px;
      transition: all 0.3s ease;
      margin: 12px;
      min-width: 200px;
    }
    .form-label {
      font-weight: 600;
      color: #37474f;
    }
    .table th {
      background-color: #f1f3f4;
    }
    .icon-title {
      font-size: 40px;
      color: #43a047;
      margin-bottom: 10px;
    }
    .total-banner {
      background: #e8f5e9;
      border-left: 8px solid #4caf50;
      padding: 20px;
      font-size: 20px;
      font-weight: bold;
      margin-bottom: 20px;
      color: #2e7d32;
      border-radius: 12px;
    }
  </style>
</head>
<body>



<div class="container">


  <div class="dashboard-card text-center">

    <div class="icon-title"><i class="fas fa-wallet"></i></div>
    <div class="section-title">Income Manager</div>

    <!-- Total Income Banner -->
    <div class="total-banner text-start">
      💰 Total Income: ₹ <?= number_format($totalIncome, 2) ?>
    </div>

    <!-- Add Income Form -->
    <form method="POST" class="text-start mb-5">
      <div class="row">
        <div class="col-md-4 mb-3">
          <label class="form-label">Income Source</label>
          <input type="text" class="form-control" name="source" placeholder="e.g. Salary, Freelance" required>
        </div>
        <div class="col-md-4 mb-3">
          <label class="form-label">Amount (₹)</label>
          <input type="number" step="0.01" class="form-control" name="amount" required>
        </div>
        <div class="col-md-4 mb-3">
          <label class="form-label">Date Received</label>
          <input type="date" class="form-control" name="date_received" required>
        </div>
      </div>
      <button type="submit" class="btn btn-success">➕ Add Income</button>
            <div class="text-start mb-3">
      <a href="dashboard.php" class="btn btn-outline-primary btn-main">
        ⬅ Back to Dashboard
      </a>
    </div>
    </form>

    <!-- Income Table -->
    <h5 class="mb-3 text-start">Your Income Entries</h5>
    <table class="table table-bordered text-start">
      <thead>
        <tr>
          <th>Source</th>
          <th>Amount (₹)</th>
          <th>Date</th>
          <th>Month-Year</th>
          <th>Action</th>
        </tr>
      </thead>
      <tbody>
        <?php while($row = $result->fetch_assoc()): ?>
        <tr>
          <td><?= htmlspecialchars($row['source']) ?></td>
          <td><?= htmlspecialchars($row['amount']) ?></td>
          <td><?= htmlspecialchars($row['date_received']) ?></td>
          <td><?= htmlspecialchars($row['month_year']) ?></td>
          <td>
            <form method="POST" style="display:inline;">
              <input type="hidden" name="delete_id" value="<?= $row['income_id'] ?>">
              <button type="submit" class="btn btn-danger btn-sm">🗑 Delete</button>
            </form>
          </td>
        </tr>
        <?php endwhile; ?>
        <tr class="table-secondary">
          <td><strong>Total</strong></td>
          <td><strong>₹ <?= number_format($totalIncome, 2) ?></strong></td>
          <td colspan="3"></td>
        </tr>
      </tbody>
    </table>
  </div>
</div>
</body>
</html>
