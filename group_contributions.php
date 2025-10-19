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

$user_id = $_SESSION['user_id'];
$group_id = $_GET['group_id'] ?? 0;

// Fetch group name
$group_name = '';
$stmt = $conn->prepare("SELECT group_name FROM groups WHERE group_id = ? AND created_by = ?");
$stmt->bind_param("ii", $group_id, $user_id);
$stmt->execute();
$stmt->bind_result($group_name);
$stmt->fetch();
$stmt->close();

if (!$group_name) {
    echo "Group not found or access denied.";
    exit;
}

// Fetch members
$members = [];
$res = $conn->query("SELECT member_id, name FROM group_members WHERE group_id = $group_id");
while ($row = $res->fetch_assoc()) {
    $members[$row['member_id']] = [
        'name' => $row['name'],
        'paid' => 0
    ];
}

// Total up all expenses and calculate who paid how much
$total_expense = 0;
$res = $conn->query("SELECT amount, paid_by FROM group_expenses WHERE group_id = $group_id");
while ($row = $res->fetch_assoc()) {
    $amount = $row['amount'];
    $paid_by = $row['paid_by'];
    $total_expense += $amount;
    if (isset($members[$paid_by])) {
        $members[$paid_by]['paid'] += $amount;
    }
}

// Calculate fair share for each member
$member_count = count($members);
$fair_share = $member_count > 0 ? $total_expense / $member_count : 0;

// Compute net balances
$balances = [];
foreach ($members as $id => $data) {
    $net = $data['paid'] - $fair_share;
    $balances[] = [
        'name' => $data['name'],
        'paid' => $data['paid'],
        'owed' => $fair_share,
        'net' => $net
    ];
}

// header("Location: group_contributions.php");
// exit();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Contribution Summary - <?= htmlspecialchars($group_name) ?></title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body {
      background: linear-gradient(to right, #fce4ec, #e0f7fa);
      font-family: 'Segoe UI', sans-serif;
    }
    .dashboard-card {
      background: white;
      border-radius: 24px;
      padding: 40px;
      margin-top: 50px;
      box-shadow: 0 10px 30px rgba(0,0,0,0.1);
    }
    .section-title {
      font-size: 28px;
      font-weight: bold;
      color: #2c3e50;
    }
    .table th {
      background-color: #f3e5f5;
    }
  </style>
</head>
<body>
<div class="container">
  <div class="dashboard-card">
    <div class="section-title mb-4">🔁 Contribution Summary - <?= htmlspecialchars($group_name) ?></div>

    <table class="table table-bordered">
      <thead>
        <tr>
          <th>Member</th>
          <th>Total Paid</th>
          <th>Total Owed</th>
          <th>Net Balance</th>
          <th>Status</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($balances as $b): ?>
        <tr>
          <td><?= htmlspecialchars($b['name']) ?></td>
          <td>₹ <?= number_format($b['paid'], 2) ?></td>
          <td>₹ <?= number_format($b['owed'], 2) ?></td>
          <td class="fw-bold <?= $b['net'] >= 0 ? 'text-success' : 'text-danger' ?>">
            ₹ <?= number_format($b['net'], 2) ?>
          </td>
          <td>
            <?php if ($b['net'] > 0): ?>
              <span class="text-success">is owed ₹<?= number_format($b['net'], 2) ?></span>
            <?php elseif ($b['net'] < 0): ?>
              <span class="text-danger">needs to pay ₹<?= number_format(abs($b['net']), 2) ?></span>
            <?php else: ?>
              <span class="text-muted">Settled</span>
            <?php endif; ?>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>

    <div class="text-start mt-4">
      <a href="group_detail.php?group_id=<?= $group_id ?>" class="btn btn-outline-primary">⬅ Back to Group</a>
    </div>
  </div>
</div>
</body>
</html>
