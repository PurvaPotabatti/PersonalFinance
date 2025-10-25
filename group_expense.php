<?php
session_start();
require __DIR__ . '/vendor/autoload.php';
use App\Database\MongoDBClient;
use MongoDB\BSON\ObjectId;
use MongoDB\BSON\UTCDateTime;

$user_id = $_SESSION['user_id'] ?? 1;
$group_id = $_GET['group_id'] ?? '';

// Validate group ID
if (!preg_match('/^[a-f\d]{24}$/i', $group_id)) die("Invalid group ID.");
$groupIdObj = new ObjectId($group_id);

$groupsCollection = MongoDBClient::getCollection('groups');
$membersCollection = MongoDBClient::getCollection('group_members');
$expensesCollection = MongoDBClient::getCollection('group_expenses');

// Fetch group
$group = $groupsCollection->findOne(['_id' => $groupIdObj, 'created_by' => $user_id]);
if (!$group) die("Group not found or access denied.");

// Fetch members
$membersCursor = $membersCollection->find(['group_id' => $groupIdObj]);
$all_members = iterator_to_array($membersCursor);

// Add expense
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['title'], $_POST['amount'], $_POST['paid_by'])) {
    $title = trim($_POST['title']);
    $amount = floatval($_POST['amount']);
    $paid_by = new ObjectId($_POST['paid_by']);
    $expense_date = $_POST['expense_date'] ?? date('Y-m-d');

    // Insert expense
    $sharesArray = [];
    foreach ($_POST['shares'] as $member_id => $share) {
        $sharesArray[] = ['member_id' => new ObjectId($member_id), 'share_amount' => floatval($share)];
    }

    $expensesCollection->insertOne([
        'group_id' => $groupIdObj,
        'title' => $title,
        'amount' => $amount,
        'paid_by' => $paid_by,
        'expense_date' => new UTCDateTime(strtotime($expense_date) * 1000),
        'shares' => $sharesArray
    ]);

    header("Location: group_expense.php?group_id=$group_id");
    exit;
}

// Fetch expenses
$expensesCursor = $expensesCollection->find(['group_id' => $groupIdObj], ['sort' => ['expense_date' => -1]]);
$expenses = iterator_to_array($expensesCursor);
?>

<!DOCTYPE html>
<html>
<head>
<title>Group Expenses - <?= htmlspecialchars($group['name']) ?></title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body style="background: linear-gradient(to right, #e3f2fd, #fce4ec);">
<div class="container my-5">
  <div class="bg-white p-4 rounded shadow">
    <h3 class="mb-4">💸 Group Expenses - <?= htmlspecialchars($group['name']) ?></h3>

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
            <option value="<?= (string)$member['_id'] ?>"><?= htmlspecialchars($member['name']) ?></option>
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
            <label><?= htmlspecialchars($member['name']) ?></label>
            <input type="number" step="0.01" name="shares[<?= (string)$member['_id'] ?>]" class="form-control" required>
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
        <?php foreach ($expenses as $row): 
            $payer = $membersCollection->findOne(['_id' => $row['paid_by']]);
        ?>
          <tr>
            <td><?= htmlspecialchars($row['title']) ?></td>
            <td>₹<?= number_format($row['amount'], 2) ?></td>
            <td><?= htmlspecialchars($payer['name'] ?? 'Unknown') ?></td>
            <td><?= date('Y-m-d', $row['expense_date']->toDateTime()->getTimestamp()) ?></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>

    <a href="group_detail.php?group_id=<?= $group_id ?>" class="btn btn-outline-primary mt-3">⬅ Back to Group</a>
  </div>
</div>
</body>
</html>
