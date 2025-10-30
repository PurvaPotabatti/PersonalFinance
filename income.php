<?php
session_start();
// Load Composer dependencies and the custom connection class
require __DIR__ . '/vendor/autoload.php';

use App\Database\MongoDBClient;
use MongoDB\BSON\UTCDateTime;
use MongoDB\BSON\ObjectId;

// --- INITIALIZATION ---
// Get user ID from session. Based on the SQL dump, user_id is an integer.
//$user_id = (int)($_SESSION['user_id'] ?? 58063); // Use a realistic dummy ID for testing
$user_id = $_SESSION['user_id'] ?? null; // Keeps the unique MongoDB ID string
// The query is then filtered by this unique string.
// Get the MongoDB collection handler
try {
    $incomeCollection = MongoDBClient::getCollection('income');
    $message = '';
} catch (Exception $e) {
    // If connection or .env fails, display error and stop execution
    die("<div class='alert alert-danger'>Connection Error: " . htmlspecialchars($e->getMessage()) . "</div>");
}

// --- CRUD OPERATIONS ---
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action'])) {
    $action = $_POST['action'];

    if ($action === 'add') {
        $source = trim($_POST['source']);
        $amount = floatval($_POST['amount']);
        $date_received = $_POST['date_received'];
        $month_year = substr($date_received, 0, 7); // YYYY-MM

        if (!empty($source) && $amount > 0) {
            $document = [
                'user_id' => $user_id,
                'source' => $source,
                'amount' => $amount,
                'month_year' => $month_year,
                // Convert YYYY-MM-DD string to a BSON UTCDateTime object (multiplying by 1000 for milliseconds)
                'date_received' => new UTCDateTime(strtotime($date_received) * 1000)
            ];

            $incomeCollection->insertOne($document);
            $message = "Income from '{$source}' added successfully.";
        } else {
            $message = "Error: Invalid source or amount.";
        }
    } 
    elseif ($action === 'delete' && isset($_POST['id'])) {
        $id = $_POST['id'];
        
        // Use the ID (which is the MongoDB ObjectId string) for deletion
        $deleteResult = $incomeCollection->deleteOne([
            '_id' => new ObjectId($id),
            'user_id' => $user_id
        ]);
        
        if ($deleteResult->getDeletedCount() === 1) {
            $message = "Income record deleted successfully.";
        } else {
            $message = "Error: Record not found or access denied.";
        }
    }
    
    // Redirect to prevent form resubmission
    header("Location: income.php");
    exit();
}

// --- READ OPERATION (Fetch Data for Display) ---
// Find all income documents for the current user, sorted by date received (descending)
$incomeDocuments = $incomeCollection->find(
    ['user_id' => $user_id],
    ['sort' => ['date_received' => -1]]
);

$total_income = 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Income Manager</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(to right, #e8f5e9, #f0f4c3);
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
            color: #1b5e20;
        }
        th { background-color: #c8e6c9; }
        .btn-income {
            background-color: #4caf50;
            color: white;
        }
        .table-data {
            font-size: 0.95rem;
        }
        .total-row {
            font-weight: bold;
            background-color: #e0f2f1;
        }
    </style>
</head>
<body>

<div class="container">
    <div class="dashboard-card">
        <div class="section-title text-center">💰 Income Manager (MongoDB)</div>

        <?php if (!empty($message)): ?>
            <div class="alert alert-success"><?= htmlspecialchars($message) ?></div>
        <?php endif; ?>

        <!-- Add Income Form -->
        <h5 class="mb-3 text-primary">➕ Add New Income</h5>
        <form method="POST" class="row g-3 mb-5 p-3 border rounded">
            <input type="hidden" name="action" value="add">
            
            <div class="col-md-4">
                <label class="form-label">Source</label>
                <input type="text" class="form-control" name="source" placeholder="e.g., Salary, Gift" required>
            </div>
            
            <div class="col-md-3">
                <label class="form-label">Amount (₹)</label>
                <input type="number" step="0.01" class="form-control" name="amount" required>
            </div>
            
            <div class="col-md-3">
                <label class="form-label">Date Received</label>
                <input type="date" class="form-control" name="date_received" value="<?= date('Y-m-d') ?>" required>
            </div>
            
            <div class="col-md-2 d-flex align-items-end">
                <button type="submit" class="btn btn-income w-100">💾 Record Income</button>
            </div>
        </form>
        
        <h5 class="mt-4 mb-3 text-secondary">📋 Income History</h5>
        <table class="table table-bordered table-hover table-data">
            <thead>
                <tr>
                    <th>Source</th>
                    <th>Amount (₹)</th>
                    <th>Month/Year</th>
                    <th>Date Received</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($incomeDocuments as $doc): 
                    $total_income += $doc['amount'];
                ?>
                <tr>
                    <td><?= htmlspecialchars($doc['source']) ?></td>
                    <td><?= number_format((float)$doc['amount'], 2) ?></td>
                    <td><?= htmlspecialchars($doc['month_year']) ?></td>
                    <td>
                        <?php 
                        // Convert BSON UTCDateTime object back to a readable string
                        echo date('Y-m-d', $doc['date_received']->toDateTime()->getTimestamp());
                        ?>
                    </td>
                    <td>
                        <form method="POST" class="d-inline">
                            <input type="hidden" name="action" value="delete">
                            <!-- Use the BSON ObjectId as the ID for deletion -->
                            <input type="hidden" name="id" value="<?= $doc['_id'] ?>">
                            <button class="btn btn-sm btn-danger" onclick="return confirm('Delete this income record?')" title="Delete">🗑️</button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
                <tr class="total-row">
                    <td colspan="4" class="text-end">Total Income Recorded:</td>
                    <td colspan="1">₹ <?= number_format($total_income, 2) ?></td>
                </tr>
            </tbody>
        </table>

        <div class="text-start mt-4">
            <a href="dashboard.php" class="btn btn-outline-primary">⬅ Back to Dashboard</a>
        </div>
    </div>
</div>

</body>
</html>
