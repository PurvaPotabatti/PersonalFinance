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

$user_id = $_SESSION['user_id'] ?? 1; // Replace with actual session logic
$message = '';

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action'])) {
    $action = $_POST['action'];
    $type = $_POST['type'];

    $table = $type === 'static' ? 'static_expenses' : 'dynamic_expenses';

    if ($action === 'add') 
    {
        $name = $_POST['name'];
        $amount = $_POST['amount'];
        $stmt = $conn->prepare("INSERT INTO $table (user_id, name, amount) VALUES (?, ?, ?)");
        $stmt->bind_param("isd", $user_id, $name, $amount);
        $stmt->execute();
        $stmt->close();
        $message = ucfirst($type) . " expense added.";
    } 
    elseif ($action === 'update') 
    {
        $id = $_POST['id'];
        $name = $_POST['name'];
        $amount = $_POST['amount'];
        $stmt = $conn->prepare("UPDATE $table SET name=?, amount=? WHERE id=? AND user_id=?");
        $stmt->bind_param("sdii", $name, $amount, $id, $user_id);
        $stmt->execute();
        $stmt->close();
        $message = ucfirst($type) . " expense updated.";
    } 
    elseif ($action === 'delete') 
    {
        $id = $_POST['id'];
        $stmt = $conn->prepare("DELETE FROM $table WHERE id=? AND user_id=?");
        $stmt->bind_param("ii", $id, $user_id);
        $stmt->execute();
        $stmt->close();
        $message = ucfirst($type) . " expense deleted.";
    }
    header("Location: expense.php");
exit();

}

$static = $conn->query("SELECT id, name, amount FROM static_expenses WHERE user_id = $user_id");
$dynamic = $conn->query("SELECT id, name, amount FROM dynamic_expenses WHERE user_id = $user_id");
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Enhanced Expense Manager</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body {
      background: linear-gradient(to bottom, #f0f2f5, #e3e9ef);
      font-family: 'Segoe UI', sans-serif;
      color: #333;
    }
    .dashboard-card {
      background: #ffffff;
      border-radius: 20px;
      padding: 40px;
      box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
      margin-top: 50px;
    }
    .form-area, .tables-section {
      display: none;
      background: #fefefe;
      border-radius: 16px;
      padding: 25px;
      box-shadow: 0 6px 12px rgba(0,0,0,0.05);
      margin-top: 30px;
    }
    .section-title {
      font-size: 28px;
      font-weight: 600;
      margin-bottom: 30px;
      color: #2c3e50;
    }
    .btn-expense {
      width: 220px;
      margin: 12px;
      padding: 14px 18px;
      font-size: 16px;
      font-weight: 600;
      border-radius: 12px;
      transition: 0.3s ease;
    }
    .btn-outline-primary:hover,
    .btn-outline-success:hover,
    .btn-info:hover {
      transform: translateY(-2px);
      box-shadow: 0 6px 12px rgba(0,0,0,0.1);
    }
    .btn-info {
      background-color: #17a2b8;
      color: white;
    }
    th { background-color: #ecf0f1; }
    .table th, .table td { vertical-align: middle; }
    .form-label { font-weight: 500; }
    h5 { font-weight: 600; color: #34495e; }
  </style>
</head>
<body>

<div class="container">
  <div class="dashboard-card text-center">
    <div class="section-title">💸 Enhanced Expense Manager</div>

    <?php if ($message): ?>
      <div class="alert alert-success"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>

    <!-- Static Form -->
    <div class="form-area" id="static-form">
      <h5>Static Expense Entry</h5>
      <form method="POST">
        <input type="hidden" name="action" value="add">
        <input type="hidden" name="type" value="static">
        <div class="mb-3 text-start">
          <label class="form-label">Expense Name</label>
          <input type="text" class="form-control" name="name" required>
        </div>
        <div class="mb-3 text-start">
          <label class="form-label">Amount</label>
          <input type="number" step="0.01" class="form-control" name="amount" required>
        </div>
        <button type="submit" class="btn btn-primary btn-expense">💾 Add Static Expense</button>
      </form>
    </div>

    <!-- Dynamic Form -->
    <div class="form-area" id="dynamic-form">
      <h5>Dynamic Expense Entry</h5>
      <form method="POST">
        <input type="hidden" name="action" value="add">
        <input type="hidden" name="type" value="dynamic">
        <div class="mb-3 text-start">
          <label class="form-label">Expense Name</label>
          <input type="text" class="form-control" name="name" required>
        </div>
        <div class="mb-3 text-start">
          <label class="form-label">Amount</label>
          <input type="number" step="0.01" class="form-control" name="amount" required>
        </div>
        <button type="submit" class="btn btn-success btn-expense">💾 Add Dynamic Expense</button>
      </form>
    </div>

    <!-- Form Buttons -->
    <div class="mt-4 mb-3">
      <button class="btn btn-outline-primary btn-expense" onclick="toggleForm('static')">➕ Static Expense</button>
      <button class="btn btn-outline-success btn-expense" onclick="toggleForm('dynamic')">➕ Dynamic Expense</button>
    </div>

    <!-- Tables -->
    <div class="mt-5">
      <button class="btn btn-info btn-expense" onclick="toggleTable()">📊 View Expenses</button>
    </div>
                <div class="text-start mb-3">
      <a href="dashboard.php" class="btn btn-outline-primary btn-main">
        ⬅ Back to Dashboard
      </a>
    </div>

    <div class="tables-section mt-4" id="tables-section">
      <div class="row mt-4">
        <div class="col-md-6">
          <h5 class="text-center">Static Expenses</h5>
          <table class="table table-bordered">
            <thead><tr><th>Name</th><th>Amount</th><th>Actions</th></tr></thead>
            <tbody>
              <?php
              $total_static = 0;
              while($row = $static->fetch_assoc()):
              $total_static += $row['amount'];
              ?>
              <tr>
                <td><?= htmlspecialchars($row['name']) ?></td>
                <td>₹<?= htmlspecialchars($row['amount']) ?></td>
                <td>
                  <form method="POST" class="d-inline">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="type" value="static">
                    <input type="hidden" name="id" value="<?= $row['id'] ?>">
                    <button class="btn btn-sm btn-danger" onclick="return confirm('Delete this expense?')">🗑</button>
                  </form>
                </td>
              </tr>
              <?php endwhile; ?>
              <tr><th>Total</th><th>₹<?= $total_static ?></th><th></th></tr>
            </tbody>
          </table>
        </div>

        <div class="col-md-6">
          <h5 class="text-center">Dynamic Expenses</h5>
          <table class="table table-bordered">
            <thead><tr><th>Name</th><th>Amount</th><th>Actions</th></tr></thead>
            <tbody>
              <?php
              $total_dynamic = 0;
              while($row = $dynamic->fetch_assoc()):
              $total_dynamic += $row['amount'];
              ?>
              <tr>
                <td><?= htmlspecialchars($row['name']) ?></td>
                <td>₹<?= htmlspecialchars($row['amount']) ?></td>
                <td>
                  <form method="POST" class="d-inline">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="type" value="dynamic">
                    <input type="hidden" name="id" value="<?= $row['id'] ?>">
                    <button class="btn btn-sm btn-danger" onclick="return confirm('Delete this expense?')">🗑</button>
                  </form>
                </td>
              </tr>
              <?php endwhile; ?>
              <tr><th>Total</th><th>₹<?= $total_dynamic ?></th><th></th></tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
  function toggleForm(type) {
    document.getElementById('static-form').style.display = (type === 'static') ? 'block' : 'none';
    document.getElementById('dynamic-form').style.display = (type === 'dynamic') ? 'block' : 'none';
    document.getElementById('tables-section').style.display = 'none';
  }

  function toggleTable() {
    document.getElementById('static-form').style.display = 'none';
    document.getElementById('dynamic-form').style.display = 'none';
    document.getElementById('tables-section').style.display = 'block';
  }
</script>

</body>
</html>