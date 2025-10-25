<?php
session_start();
require __DIR__ . '/vendor/autoload.php';
use App\Database\MongoDBClient;
use MongoDB\BSON\ObjectId;
use MongoDB\BSON\UTCDateTime;

$user_id = $_SESSION['user_id'] ?? 1;
$group_id = $_GET['group_id'] ?? '';

// Validate ObjectId
if (!preg_match('/^[a-f\d]{24}$/i', $group_id)) {
    die("Invalid group ID.");
}
$groupIdObj = new ObjectId($group_id);

// Collections
$groupsCollection = MongoDBClient::getCollection('groups');
$membersCollection = MongoDBClient::getCollection('group_members');

// Fetch group
$group = $groupsCollection->findOne(['_id' => $groupIdObj, 'created_by' => $user_id]);
if (!$group) die("Group not found or access denied.");

// Add member
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['member_name'])) {
    $name = trim($_POST['member_name']);
    $email = trim($_POST['member_email'] ?? '');
    if (!empty($name)) {
        $membersCollection->insertOne([
            'group_id' => $groupIdObj,
            'name' => $name,
            'email' => $email,
            'is_user' => false,
            'added_at' => new UTCDateTime()
        ]);
    }
    header("Location: group_detail.php?group_id=$group_id");
    exit;
}

// Delete member
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['delete_member_id'])) {
    $delete_id = $_POST['delete_member_id'];
    if (preg_match('/^[a-f\d]{24}$/i', $delete_id)) {
        $membersCollection->deleteOne([
            '_id' => new ObjectId($delete_id),
            'group_id' => $groupIdObj
        ]);
    }
    header("Location: group_detail.php?group_id=$group_id");
    exit;
}

// Fetch members
$membersCursor = $membersCollection->find(['group_id' => $groupIdObj]);
$members = iterator_to_array($membersCursor);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Group Details - <?= htmlspecialchars($group['name']) ?></title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
body { background: linear-gradient(to right, #e1f5fe, #fff3e0); font-family: 'Segoe UI', sans-serif; }
.dashboard-card { background: white; border-radius: 24px; padding: 40px; margin-top: 50px; box-shadow: 0 10px 30px rgba(0,0,0,0.1); }
.section-title { font-size: 28px; font-weight: bold; color: #2c3e50; }
.form-label { font-weight: 600; color: #455a64; }
.btn-outline-primary { margin-top: 20px; }
.table th { background-color: #f1f8e9; }
</style>
</head>
<body>
<div class="container">
  <div class="dashboard-card">
    <div class="section-title mb-4">👥 Group: <?= htmlspecialchars($group['name']) ?></div>

    <form method="POST" class="row g-3 mb-4">
      <div class="col-md-5">
        <label class="form-label">Member Name</label>
        <input type="text" class="form-control" name="member_name" placeholder="Enter member name" required>
      </div>
      <div class="col-md-5">
        <label class="form-label">Email (optional)</label>
        <input type="email" class="form-control" name="member_email" placeholder="Enter email">
      </div>
      <div class="col-md-2 d-flex align-items-end">
        <button type="submit" class="btn btn-success w-100">➕ Add</button>
      </div>
    </form>

    <h5 class="mb-3">📋 Group Members</h5>
    <table class="table table-bordered">
      <thead><tr><th>Name</th><th>Email</th><th>Action</th></tr></thead>
      <tbody>
        <?php foreach ($members as $row): ?>
        <tr>
          <td><?= htmlspecialchars($row['name']) ?></td>
          <td><?= htmlspecialchars($row['email']) ?></td>
          <td>
            <form method="POST" style="display:inline;">
              <input type="hidden" name="delete_member_id" value="<?= (string)$row['_id'] ?>">
              <button type="submit" class="btn btn-danger btn-sm">🗑 Remove</button>
            </form>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>

    <div class="text-start mt-3 d-flex gap-3">
      <a href="group_expense.php?group_id=<?= $group_id ?>" class="btn btn-outline-success">➕ Add/View Expenses</a>
      <a href="group_contributions.php?group_id=<?= $group_id ?>" class="btn btn-outline-info">📊 View Contributions</a>
    </div>

    <div class="text-start mt-4">
      <a href="group_module.php" class="btn btn-outline-primary">⬅ Back to Groups</a>
    </div>
  </div>
</div>
</body>
</html>
