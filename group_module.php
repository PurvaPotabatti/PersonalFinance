<?php
session_start();
require __DIR__ . '/vendor/autoload.php';

use App\Database\MongoDBClient;
use MongoDB\BSON\UTCDateTime;
use MongoDB\BSON\ObjectId;

// Dummy user_id for testing (replace with login session in production)
$user_id = $_SESSION['user_id'] ?? 1;

// Collections
$groupsCollection = MongoDBClient::getCollection('groups');

// Create new group
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['group_name'])) {
    $group_name = trim($_POST['group_name']);
    if (!empty($group_name)) {
        $groupsCollection->insertOne([
            'name' => $group_name,
            'created_by' => $user_id,
            'created_at' => new UTCDateTime()
        ]);
    }
    header("Location: group_module.php");
    exit;
}

// Fetch user's groups
$groupsCursor = $groupsCollection->find(
    ['created_by' => $user_id],
    ['sort' => ['created_at' => -1]]
);
$groups = iterator_to_array($groupsCursor);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Group Tracker</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
body { background: linear-gradient(to right, #f3e5f5, #e8f5e9); font-family: 'Segoe UI', sans-serif; }
.dashboard-card { background: white; border-radius: 24px; padding: 40px; margin-top: 50px; box-shadow: 0 10px 30px rgba(0,0,0,0.1); }
.section-title { font-size: 28px; font-weight: bold; color: #2c3e50; }
.btn-custom { border-radius: 12px; }
</style>
</head>
<body>
<div class="container">
  <div class="dashboard-card">
    <div class="section-title mb-4">👥 Group Tracker</div>

    <!-- Add New Group -->
    <form method="POST" class="row g-3 mb-4">
      <div class="col-md-9">
        <label class="form-label">New Group Name</label>
        <input type="text" name="group_name" class="form-control" placeholder="Enter group name" required>
      </div>
      <div class="col-md-3 d-flex align-items-end">
        <button type="submit" class="btn btn-primary w-100 btn-custom">➕ Create</button>
      </div>
    </form>

    <!-- List of Groups -->
    <h5 class="mb-3">📋 Your Groups</h5>
    <table class="table table-bordered">
      <thead>
        <tr>
          <th>Group Name</th>
          <th>Action</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($groups as $group): ?>
        <tr>
          <td><?= htmlspecialchars($group['name']) ?></td>
          <td>
            <a href="group_detail.php?group_id=<?= (string)$group['_id'] ?>" class="btn btn-info btn-sm">🔍 View Details</a>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>
</body>
</html>
