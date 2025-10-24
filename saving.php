<?php
session_start();
// Load Composer dependencies and the custom connection class
require __DIR__ . '/vendor/autoload.php';

use App\Database\MongoDBClient;
use MongoDB\BSON\ObjectId;

// --- INITIALIZATION ---
// Get user ID from session. Based on the SQL dump, user_id is an integer.
$user_id = (int)($_SESSION['user_id'] ?? 58063); // Use a realistic dummy ID for testing

// Get the MongoDB collection handler
try {
    $savingsCollection = MongoDBClient::getCollection('savings');
    $message = '';
} catch (Exception $e) {
    die("<div class='alert alert-danger'>Connection Error: " . htmlspecialchars($e->getMessage()) . "</div>");
}

// --- CRUD OPERATIONS ---
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action'])) {
    $action = $_POST['action'];
    $type = $_POST['type'] ?? 'static'; // Default to static if not set

    if ($action === 'add') {
        $name = trim($_POST['name']);
        $amount = floatval($_POST['amount']);

        if (!empty($name) && $amount > 0) {
            $document = [
                'user_id' => $user_id,
                'type' => $type,
                'name' => $name,
                'amount' => $amount,
                'created_at' => new \MongoDB\BSON\UTCDateTime()
            ];

            $savingsCollection->insertOne($document);
            $message = ucfirst($type) . " saving goal added successfully.";
        } else {
            $message = "Error: Invalid name or amount.";
        }
    } 
    elseif ($action === 'delete' && isset($_POST['id'])) {
        $id = $_POST['id'];
        
        // Use the ID (which is the MongoDB ObjectId string) for deletion
        $deleteResult = $savingsCollection->deleteOne([
            '_id' => new ObjectId($id),
            'user_id' => $user_id
        ]);
        
        if ($deleteResult->getDeletedCount() === 1) {
            $message = "Savings record deleted successfully.";
        } else {
            $message = "Error: Record not found or access denied.";
        }
    }
    
    // Redirect to prevent form resubmission and clear post data
    header("Location: saving.php");
    exit();
}

// --- READ OPERATION (Fetch Data for Display) ---
// Find all savings documents for the current user
$savingsDocuments = $savingsCollection->find(['user_id' => $user_id]);

// Separate into static and dynamic savings for display
$staticSavings = [];
$dynamicSavings = [];
$total_static = 0;
$total_dynamic = 0;

foreach ($savingsDocuments as $doc) {
    if ($doc['type'] === 'static') {
        $staticSavings[] = $doc;
        $total_static += (float)$doc['amount'];
    } elseif ($doc['type'] === 'dynamic') {
        $dynamicSavings[] = $doc;
        $total_dynamic += (float)$doc['amount'];
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Savings Manager</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body {
      background: linear-gradient(to bottom, #fff8e1, #e3f2fd);
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
    .section-title {
      font-size: 28px;
      font-weight: 600;
      margin-bottom: 30px;
      color: #f9a825;
    }
    .form-area {
      background: #fefefe;
      border-radius: 16px;
      padding: 25px;
      box-shadow: 0 6px 12px rgba(0,0,0,0.05);
      margin-top: 30px;
    }
    th { background-color: #fce4ec; }
    .table-data { font-size: 0.95rem; }
    .total-row { font-weight: bold; background-color: #ffe0b2; }
  </style>
</head>
<body>

<div class="container">
  <div class="dashboard-card">
    <div class="section-title text-center">💸 Savings Manager (MongoDB)</div>

    <?php if (!empty($message)): ?>
      <div class="alert alert-success"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>

    <!-- Add Savings Form -->
    <div class="form-area">
      <h5 class="mb-3 text-secondary">➕ Add New Savings Goal</h5>
      <form method="POST" class="row g-3">
        <input type="hidden" name="action" value="add">
        
        <div class="col-md-4">
          <label class="form-label">Type</label>
          <select name="type" class="form-select" required>
            <option value="static">Static (Fixed Goals/Investments)</option>
            <option value="dynamic">Dynamic (Flexible Savings)</option>
          </select>
        </div>

        <div class="col-md-4">
          <label class="form-label">Goal/Source Name</label>
          <input type="text" class="form-control" name="name" placeholder="e.g., FD, Car Down Payment" required>
        </div>
        
        <div class="col-md-2">
          <label class="form-label">Amount (₹)</label>
          <input type="number" step="0.01" class="form-control" name="amount" required>
        </div>
        
        <div class="col-md-2 d-flex align-items-end">
          <button type="submit" class="btn btn-warning w-100 text-white">💾 Record Saving</button>
        </div>
      </form>
    </div>

    <!-- Savings Tables -->
    <div class="row mt-5">
      <div class="col-md-6">
        <h5 class="text-center text-secondary">Static Savings (Fixed)</h5>
        <table class="table table-bordered table-hover table-data">
          <thead>
            <tr><th>Name</th><th>Amount (₹)</th><th>Actions</th></tr>
          </thead>
          <tbody>
            <?php foreach ($staticSavings as $doc): ?>
            <tr>
              <td><?= htmlspecialchars($doc['name']) ?></td>
              <td><?= number_format($doc['amount'], 2) ?></td>
              <td>
                <form method="POST" class="d-inline">
                  <input type="hidden" name="action" value="delete">
                  <input type="hidden" name="id" value="<?= $doc['_id'] ?>">
                  <input type="hidden" name="type" value="static">
                  <button class="btn btn-sm btn-danger" onclick="return confirm('Delete this static saving goal?')">🗑️</button>
                </form>
              </td>
            </tr>
            <?php endforeach; ?>
            <tr class="total-row"><th>Total</th><th>₹<?= number_format($total_static, 2) ?></th><th></th></tr>
          </tbody>
        </table>
      </div>

      <div class="col-md-6">
        <h5 class="text-center text-secondary">Dynamic Savings (Flexible)</h5>
        <table class="table table-bordered table-hover table-data">
          <thead>
            <tr><th>Name</th><th>Amount (₹)</th><th>Actions</th></tr>
          </thead>
          <tbody>
            <?php foreach ($dynamicSavings as $doc): ?>
            <tr>
              <td><?= htmlspecialchars($doc['name']) ?></td>
              <td><?= number_format($doc['amount'], 2) ?></td>
              <td>
                <form method="POST" class="d-inline">
                  <input type="hidden" name="action" value="delete">
                  <input type="hidden" name="id" value="<?= $doc['_id'] ?>">
                  <input type="hidden" name="type" value="dynamic">
                  <button class="btn btn-sm btn-danger" onclick="return confirm('Delete this dynamic savings record?')">🗑️</button>
                </form>
              </td>
            </tr>
            <?php endforeach; ?>
            <tr class="total-row"><th>Total</th><th>₹<?= number_format($total_dynamic, 2) ?></th><th></th></tr>
          </tbody>
        </table>
      </div>
    </div>

    <div class="text-start mt-4">
      <a href="dashboard.php" class="btn btn-outline-primary">⬅ Back to Dashboard</a>
    </div>
  </div>
</div>
</body>
</html>
