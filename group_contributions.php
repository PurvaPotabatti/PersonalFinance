<?php
session_start();
require __DIR__ . '/vendor/autoload.php';
use App\Database\MongoDBClient;
use MongoDB\BSON\ObjectId;

$user_id = $_SESSION['user_id'] ?? 1;
$group_id = $_GET['group_id'] ?? '';

if (!preg_match('/^[a-f\d]{24}$/i', $group_id)) die("Invalid group ID.");
$groupIdObj = new ObjectId($group_id);

$groupsCollection = MongoDBClient::getCollection('groups');
$membersCollection = MongoDBClient::getCollection('group_members');
$expensesCollection = MongoDBClient::getCollection('group_expenses');

$group = $groupsCollection->findOne(['_id' => $groupIdObj, 'created_by' => $user_id]);
if (!$group) die("Group not found or access denied.");

// Members
$membersCursor = $membersCollection->find(['group_id' => $groupIdObj]);
$members = [];
foreach ($membersCursor as $m) {
    $members[(string)$m['_id']] = ['name' => $m['name'], 'paid' => 0];
}

// Expenses
$expensesCursor = $expensesCollection->find(['group_id' => $groupIdObj]);
$total_expense = 0;
foreach ($expensesCursor as $exp) {
    $total_expense += $exp['amount'];
    $paid_by_id = (string)$exp['paid_by'];
    if (isset($members[$paid_by_id])) $members[$paid_by_id]['paid'] += $exp['amount'];
}

// Fair share and net
$member_count = count($members);
$fair_share = $member_count > 0 ? $total_expense / $member_count : 0;
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
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Contribution Summary - <?= htmlspecialchars($group['name']) ?></title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
body { background: linear-gradient(to right, #fce4ec, #e0f7fa); font-family: 'Segoe UI', sans-serif; }
.dashboard-card { background: white; border-radius: 24px; padding: 40px; margin-top: 50px; box-shadow: 0 10px 30px rgba(0,0,0,0.1); }
.section-title { font-size: 28px; font-weight: bold; color: #2c3e50; }
.table th { background-color: #f3e5f5; }
</style>
</head>
<body>
<div class="container">
  <div class="dashboard-card">
    <div class="section-title mb-4">🔁 Contribution Summary - <?= htmlspecialchars($group['name']) ?></div>

    <table class="table table-bordered">
      <thead><tr><th>Member</th><th>Total Paid</th><th>Total Owed</th><th>Net Balance</th><th>Status</th></tr></thead>
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
