<?php
session_start();

// For testing purposes, use dummy user_id
// In real implementation, ensure user login stores $_SESSION['user_id']
$user_id = $_SESSION['user_id'] ?? 1;

// MySQL connection
$host = 'localhost';
$db = 'finance_manager';
$user = 'root';
$pass = '';
$port = 3307;

$conn = new mysqli($host, $user, $pass, $db, $port);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle form submission to create a new group
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['group_name'])) {
    $group_name = trim($_POST['group_name']);
    if (!empty($group_name)) {
        $stmt = $conn->prepare("INSERT INTO Groups (group_name, created_by) VALUES (?, ?)");
        $stmt->bind_param("si", $group_name, $user_id);
        $stmt->execute();
        $stmt->close();
    }
    header("Location: group_module.php");
    exit;
}

// Fetch user's groups
$stmt = $conn->prepare("SELECT group_id, group_name FROM Groups WHERE created_by = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$groups = $stmt->get_result();
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Group Tracker</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body {
      background: linear-gradient(to right, #f3e5f5, #e8f5e9);
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
    .btn-custom {
      border-radius: 12px;
    }
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
        <?php while($group = $groups->fetch_assoc()): ?>
        <tr>
          <td><?= htmlspecialchars($group['group_name']) ?></td>
          <td>
            <a href="group_detail.php?group_id=<?= $group['group_id'] ?>" class="btn btn-info btn-sm">🔍 View Details</a>
          </td>
        </tr>
        <?php endwhile; ?>
      </tbody>
    </table>
  </div>
</div>
</body>
</html>
