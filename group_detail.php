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

// Add member
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['member_name']) && isset($_POST['member_email'])) {
    $name = trim($_POST['member_name']);
    $email = trim($_POST['member_email']);
    if (!empty($name)) {
        $stmt = $conn->prepare("INSERT INTO group_members (group_id, name, email, is_user) VALUES (?, ?, ?, 0)");
        $stmt->bind_param("iss", $group_id, $name, $email);
        $stmt->execute();
        $stmt->close();
    }
    header("Location: group_detail.php?group_id=$group_id");
    exit;
}

// Delete member
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['delete_member_id'])) {
    $delete_id = $_POST['delete_member_id'];
    $stmt = $conn->prepare("DELETE FROM group_members WHERE member_id = ? AND group_id = ?");
    $stmt->bind_param("ii", $delete_id, $group_id);
    $stmt->execute();
    $stmt->close();
    header("Location: group_detail.php?group_id=$group_id");
    exit;
}

// Fetch members
$members = $conn->query("SELECT * FROM group_members WHERE group_id = $group_id");
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Group Details - <?= htmlspecialchars($group_name) ?></title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body {
      background: linear-gradient(to right, #e1f5fe, #fff3e0);
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
    .form-label {
      font-weight: 600;
      color: #455a64;
    }
    .btn-outline-primary {
      margin-top: 20px;
    }
    .table th {
      background-color: #f1f8e9;
    }
  </style>
</head>
<body>
<div class="container">
  <div class="dashboard-card">
    <div class="section-title mb-4">👥 Group: <?= htmlspecialchars($group_name) ?></div>

    <!-- Add Member Form -->
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

    <!-- Member List Table -->
    <h5 class="mb-3">📋 Group Members</h5>
    <table class="table table-bordered">
      <thead>
        <tr>
          <th>Name</th>
          <th>Email</th>
          <th>Action</th>
        </tr>
      </thead>
      <tbody>
        <?php while($row = $members->fetch_assoc()): ?>
        <tr>
          <td><?= htmlspecialchars($row['name']) ?></td>
          <td><?= htmlspecialchars($row['email']) ?></td>
          <td>
            <form method="POST" style="display:inline;">
              <input type="hidden" name="delete_member_id" value="<?= $row['member_id'] ?>">
              <button type="submit" class="btn btn-danger btn-sm">🗑 Remove</button>
            </form>
          </td>
        </tr>
        <?php endwhile; ?>
      </tbody>
    </table>


    <div class="text-start mt-3 d-flex gap-3">
    <a href="group_expense.php?group_id=<?= $group_id ?>" class="btn btn-outline-success">
      ➕ Add/View Expenses
    </a>
    <a href="group_contributions.php?group_id=<?= $group_id ?>" class="btn btn-outline-info">
      📊 View Contributions
    </a>
    </div>

    <div class="text-start mt-4">
      <a href="group_module.php" class="btn btn-outline-primary">⬅ Back to Groups</a>
    </div>
  </div>
</div>
</body>
</html>
