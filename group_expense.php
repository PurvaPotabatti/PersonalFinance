<?php
session_start();
$host = 'localhost';
$db = 'finance_manager';
$user = 'root';
$pass = '';
$port = 3307;

$conn = new mysqli($host, $user, $pass, $db, $port);
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

$user_id = $_SESSION['user_id'];
$group_id = $_GET['group_id'] ?? 0;

// Fetch group name
$stmt = $conn->prepare("SELECT group_name FROM groups WHERE group_id = ? AND created_by = ?");
$stmt->bind_param("ii", $group_id, $user_id);
$stmt->execute();
$stmt->bind_result($group_name);
$stmt->fetch();
$stmt->close();

// Add expense
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['title'], $_POST['amount'], $_POST['paid_by'])) {
    $title = $_POST['title'];
    $amount = floatval($_POST['amount']);
    $paid_by = intval($_POST['paid_by']);
    $expense_date = $_POST['expense_date'] ?? date('Y-m-d');

    $conn->query("INSERT INTO group_expenses (group_id, title, amount, paid_by, expense_date)
                  VALUES ('$group_id', '$title', '$amount', '$paid_by', '$expense_date')");
    $expense_id = $conn->insert_id;

    // Save shares
    foreach ($_POST['shares'] as $member_id => $share_amount) {
        $stmt = $conn->prepare("INSERT INTO expense_shares (expense_id, member_id, share_amount) VALUES (?, ?, ?)");
        $stmt->bind_param("iid", $expense_id, $member_id, $share_amount);
        $stmt->execute();
    }
    header("Location: group_expense.php?group_id=$group_id");
    exit;
}

// Get group members
$members = $conn->query("SELECT * FROM group_members WHERE group_id = $group_id");
$all_members = $members->fetch_all(MYSQLI_ASSOC);

// Get expenses
$expenses = $conn->query("
  SELECT e.*, m.name as payer_name 
  FROM group_expenses e 
  JOIN group_members m ON e.paid_by = m.member_id 
  WHERE e.group_id = $group_id
  ORDER BY e.expense_date DESC
");
?>

<!DOCTYPE html>
<html>
<head>
  <title>Group Expenses - <?= $group_name ?></title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body style="background: linear-gradient(to right, #e3f2fd, #fce4ec);">
<div class="container my-5">
  <div class="bg-white p-4 rounded shadow">
    <h3 class="mb-4">💸 Group Expenses - <?= htmlspecialchars($group_name) ?></h3>

    <form method="POST" class="row g-3 mb-4">
      <div class="col-md-3">
        <input type="text" name="title" class="form-control" placeholder="Expense Title" required>
      </div>
      <div class="col-md-2">
        <input type="number" name="amount" step="0.01" class="form-control" placeholder="Amount" required>
      </div>
      <div class="col-md-3">
        <select name="paid_by" class="form-select" required>
          <option value="">Paid By</option>
          <?php foreach ($all_members as $member): ?>
            <option value="<?= $member['member_id'] ?>"><?= $member['name'] ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-md-2">
        <input type="date" name="expense_date" class="form-control" value="<?= date('Y-m-d') ?>">
      </div>
      <div class="col-12 mt-3">
        <h6>Shares:</h6>
        <div class="row">
        <?php foreach ($all_members as $member): ?>
          <div class="col-md-3 mb-2">
            <label><?= $member['name'] ?></label>
            <input type="number" step="0.01" name="shares[<?= $member['member_id'] ?>]" class="form-control" required>
          </div>
        <?php endforeach; ?>
        </div>
      </div>
      <div class="col-12 text-end">
        <button class="btn btn-success">➕ Add Expense</button>
      </div>
    </form>

    <h5 class="mt-4">📋 All Expenses</h5>
    <table class="table table-bordered">
      <thead><tr><th>Title</th><th>Amount</th><th>Paid By</th><th>Date</th></tr></thead>
      <tbody>
        <?php while ($row = $expenses->fetch_assoc()): ?>
          <tr>
            <td><?= $row['title'] ?></td>
            <td>₹<?= number_format($row['amount'], 2) ?></td>
            <td><?= $row['payer_name'] ?></td>
            <td><?= $row['expense_date'] ?></td>
          </tr>
        <?php endwhile; ?>
      </tbody>
    </table>

    <a href="group_detail.php?group_id=<?= $group_id ?>" class="btn btn-outline-primary mt-3">⬅ Back to Group</a>
  </div>
</div>
</body>
</html>
