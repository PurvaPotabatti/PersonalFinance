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
    // Add Saving
    $type = $_POST['type'] ?? '';
    $amount = $_POST['amount'] ?? 0;
    $name = $_POST['name'] ?? '';

    if (!empty($type) && !empty($name) && $amount > 0) {
        $stmt = $conn->prepare("INSERT INTO Savings (user_id, type, name, amount) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("issd", $user_id, $type, $name, $amount);
        $stmt->execute();
        $stmt->close();
    }

    // Delete Saving
if (isset($_POST['delete_id'])) {
    $id = $_POST['delete_id'];
    $stmt = $conn->prepare("DELETE FROM Savings WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $id, $user_id);
    $stmt->execute();
    $stmt->close();
}


}

$staticSavings = $conn->query("SELECT * FROM Savings WHERE user_id = '$user_id' AND type = 'static'");
$dynamicSavings = $conn->query("SELECT * FROM Savings WHERE user_id = '$user_id' AND type = 'dynamic'");

$totalResult = $conn->query("SELECT SUM(amount) AS total FROM Savings WHERE user_id = '$user_id'");
$totalRow = $totalResult->fetch_assoc();
$totalSavings = $totalRow['total'] ?? 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Savings Manager</title>
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
    .form-label {
      font-weight: 600;
      color: #37474f;
    }
    .table th {
      background-color: #f1f3f4;
    }
    .icon-title {
      font-size: 40px;
      color: #f9a825;
      margin-bottom: 10px;
    }
    .total-banner {
      background: #fff8e1;
      border-left: 8px solid #f9a825;
      padding: 20px;
      font-size: 20px;
      font-weight: bold;
      margin-bottom: 20px;
      color: #bf360c;
      border-radius: 12px;
    }
  </style>
</head>
<body>
<div class="container">
  <div class="dashboard-card text-center">
    <div class="icon-title"><i class="fas fa-piggy-bank"></i></div>
    <div class="section-title">Savings Manager</div>

    <!-- Total Savings Banner -->
    <div class="total-banner text-start">
      🐖 Total Savings: ₹ <?= number_format($totalSavings, 2) ?>
    </div>

    <!-- Add Savings Form -->
    <form method="POST" class="text-start mb-5">
      <div class="row">
        <div class="col-md-4 mb-3">
          <label class="form-label">Saving Type (static/dynamic)</label>
          <input type="text" class="form-control" name="type" placeholder="e.g. static" required>
        </div>
        <div class="col-md-4 mb-3">
          <label class="form-label">Saving Name</label>
          <input type="text" class="form-control" name="name" placeholder="e.g. LIC, FD" required>
        </div>
        <div class="col-md-4 mb-3">
          <label class="form-label">Amount (₹)</label>
          <input type="number" step="0.01" class="form-control" name="amount" required>
        </div>
      </div>
      <button type="submit" class="btn btn-success">➕ Add Saving</button>
      <br><br><br>
        <div class="text-start mb-3">
      <a href="dashboard.php" class="btn btn-outline-primary btn-main">
        ⬅ Back to Dashboard
      </a>
    </div>
    </form>

    <!-- Static Savings Table -->
    <h5 class="mb-3 text-start">📌 Static Savings</h5>
    <table class="table table-bordered text-start">
      <thead>
        <tr>
          <th>Type</th>
          <th>Name</th>
          <th>Amount (₹)</th>
          <th>Action</th>
        </tr>
      </thead>
      <tbody>
        <?php while($row = $staticSavings->fetch_assoc()): ?>
        <tr>
          <td><?= htmlspecialchars($row['type']) ?></td>
          <td><?= htmlspecialchars($row['name']) ?></td>
          <td><?= htmlspecialchars($row['amount']) ?></td>
          <td>
            <form method="POST" style="display:inline;">
              <input type="hidden" name="delete_id" value="<?= $row['id'] ?>">
              <button type="submit" class="btn btn-danger btn-sm">🗑 Delete</button>
            </form>
          </td>
        </tr>
        <?php endwhile; ?>
      </tbody>
    </table>

    <!-- Dynamic Savings Table -->
    <h5 class="mb-3 text-start mt-5">📌 Dynamic Savings</h5>
    <table class="table table-bordered text-start">
      <thead>
        <tr>
          <th>Type</th>
          <th>Name</th>
          <th>Amount (₹)</th>
          <th>Action</th>
        </tr>
      </thead>
      <tbody>
        <?php while($row = $dynamicSavings->fetch_assoc()): ?>
        <tr>
          <td><?= htmlspecialchars($row['type']) ?></td>
          <td><?= htmlspecialchars($row['name']) ?></td>
          <td><?= htmlspecialchars($row['amount']) ?></td>
          <td>
            <form method="POST" style="display:inline;">
              <input type="hidden" name="delete_id" value="<?= $row['id'] ?>">
              <button type="submit" class="btn btn-danger btn-sm">🗑 Delete</button>
            </form>
          </td>
        </tr>
        <?php endwhile; ?>
        <tr class="table-secondary">
          <td><strong>Total</strong></td>
          <td></td>
          <td><strong>₹ <?= number_format($totalSavings, 2) ?></strong></td>
          <td></td>
        </tr>
      </tbody>
    </table>
  </div>
</div>
</body>
</html>
