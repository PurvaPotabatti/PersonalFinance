<?php
session_start();
// Load Composer dependencies
require __DIR__ . '/vendor/autoload.php';
// Include the local connection core
require_once __DIR__ . '/DB.php'; 
use MongoDB\BSON\UTCDateTime; 
use MongoDB\BSON\ObjectId; 

// --- INITIALIZATION ---
$database = DB::getDatabase(); 
$expensesCollection = $database->selectCollection('expenses'); 

$message = ''; 
$user_id = (int)($_SESSION['user_id'] ?? 0); 

// Initialize ALL display variables
$staticExpenses = [];
$dynamicExpenses = [];
$total_static = 0.00; 
$total_dynamic = 0.00; 


// --- CRUD OPERATIONS (Handling POST Requests) ---
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    $action = $_POST['action'] ?? (isset($_POST['submit_expense']) ? 'add' : null);

    if ($action === 'add') {
        $type = $_POST['expense_type'] ?? 'dynamic'; 
        $name = htmlspecialchars(trim($_POST['expense_name'] ?? ''));
        $amount = filter_input(INPUT_POST, 'amount', FILTER_VALIDATE_FLOAT);
        $notes = htmlspecialchars(trim($_POST['notes'] ?? ''));

        if ($amount > 0 && !empty($name)) { 
            
            $expenseDocument = [
                'user_id' => $user_id,
                'type' => $type,
                'name' => $name,
                'amount' => $amount,
                'notes' => $notes,
                'created_at' => new UTCDateTime()
            ];

            try {
                $expensesCollection->insertOne($expenseDocument);
                
                // CRITICAL FIX: Set success message in session for redirect
                $_SESSION['message'] = ucfirst($type) . " expense of **₹" . number_format($amount, 2) . "** recorded successfully!";
                header("Location: expense.php");
                exit();
            } 
            catch (Exception $e) { 
                $message = "Failed to record expense: " . $e->getMessage();
            } 
        } else { 
            $message = "Error: Invalid name or amount.";
        }
    } 
    elseif ($action === 'delete' && isset($_POST['id'])) {
        $id = $_POST['id'];
        
        try {
            $expensesCollection->deleteOne([
                '_id' => new ObjectId($id),
                'user_id' => $user_id 
            ]);
            $_SESSION['message'] = "Expense record deleted successfully.";
        } catch (Exception $e) {
            $_SESSION['message'] = "Deletion failed: " . $e->getMessage();
        }
        
        // CRITICAL FIX: Redirect after delete
        header("Location: expense.php");
        exit();
    }
} 

// CRITICAL FIX: Check for and display messages after redirect
if (isset($_SESSION['message'])) {
    $message = $_SESSION['message'];
    unset($_SESSION['message']); // Clear the message after displaying it once
}


// --- READ OPERATION (Fetch Data for Tables) ---
try {
    $allUserExpenses = $expensesCollection->find(['user_id' => $user_id])->toArray();

    // Separate into static and dynamic expenses for display
    foreach ($allUserExpenses as $doc) {
        $amount = floatval($doc['amount'] ?? 0); 
        
        if (($doc['type'] ?? 'dynamic') === 'static') {
            $staticExpenses[] = $doc;
            $total_static += $amount;
        } else {
            $dynamicExpenses[] = $doc;
            $total_dynamic += $amount;
        }
    }
} catch (Exception $e) {
    error_log("Database Fetch Error: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Expense Manager</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(to right, #ffebee, #fbe9e7); 
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
            color: #c62828; 
        }
        .form-area {
            background: #fefefe;
            border-radius: 16px;
            padding: 25px;
            box-shadow: 0 6px 12px rgba(0,0,0,0.05);
            margin-top: 30px;
        }
        th { background-color: #ffcdd2; }
        .table-data { font-size: 0.95rem; }
        .total-row { font-weight: bold; background-color: #ffccbc; }
        .btn-expense {
            background-color: #d32f2f;
            color: white;
            transition: background-color 0.3s;
        }
        .btn-expense:hover {
            background-color: #c62828;
            color: white;
        }
        .text-expense {
            color: #c62828;
        }
    </style>
</head>
<body>

<div class="container">
    <div class="dashboard-card">
        <div class="section-title text-center">💸 Expense Manager (MongoDB)</div>

        <?php if (!empty($message)): ?>
            <div class="alert alert-info text-center"><?= htmlspecialchars($message) ?></div>
        <?php endif; ?>

        <div class="form-area">
            <h5 class="mb-3 text-secondary">➕ Record New Expense</h5>
            
            <form method="POST" class="row g-3">
                <input type="hidden" name="submit_expense" value="1">
                
                <div class="col-md-3">
                    <label class="form-label">Type</label>
                    <select name="expense_type" class="form-select" required>
                        <option value="dynamic">Dynamic Expense (Daily/Flexible)</option>
                        <option value="static">Static Expense (Fixed/Monthly)</option>
                    </select>
                </div>

                <div class="col-md-3">
                    <label class="form-label">Expense Name</label>
                    <input type="text" class="form-control" name="expense_name" placeholder="e.g., Groceries, Rent, Car Loan" required>
                </div>
                
                <div class="col-md-2">
                    <label class="form-label">Amount (₹)</label>
                    <input type="number" step="0.01" class="form-control" name="amount" required>
                </div>
                
                <div class="col-md-2">
                    <label class="form-label">Notes (Optional)</label>
                    <input type="text" class="form-control" name="notes" placeholder="e.g., Paid electric bill">
                </div>
                
                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" name="submit_expense" class="btn btn-expense w-100">
                        💾 Record Expense
                    </button>
                </div>
            </form>
        </div>

        <div class="row mt-5">
            <div class="col-md-6">
                <h5 class="text-center text-secondary">Static Expenses</h5>
                <table class="table table-bordered table-hover table-data">
                    <thead>
                        <tr><th>Name</th><th>Amount (₹)</th><th>Actions</th></tr>
                    </thead>
                    <tbody>
                        <?php foreach ($staticExpenses as $doc): ?>
                        <tr>
                            <td><?= htmlspecialchars($doc['name']) ?></td>
                            <td><?= number_format(floatval($doc['amount'] ?? 0), 2) ?></td>
                            <td>
                                <form method="POST" class="d-inline">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="id" value="<?= $doc['_id'] ?>">
                                    <input type="hidden" name="collection" value="static">
                                    <button class="btn btn-sm btn-danger" onclick="return confirm('Delete this static expense goal?')">🗑️</button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <tr class="total-row"><th>Total</th><th>₹<?= number_format($total_static, 2) ?></th><th></th></tr>
                    </tbody>
                </table>
            </div>

            <div class="col-md-6">
                <h5 class="text-center text-secondary">Dynamic Expenses</h5>
                <table class="table table-bordered table-hover table-data">
                    <thead>
                        <tr><th>Name</th><th>Amount (₹)</th><th>Actions</th></tr>
                    </thead>
                    <tbody>
                        <?php foreach ($dynamicExpenses as $doc): ?>
                        <tr>
                            <td><?= htmlspecialchars($doc['name'] ?? 'N/A') ?></td>
                            <td><?= number_format(floatval($doc['amount'] ?? 0), 2) ?></td>
                            <td>
                                <form method="POST" class="d-inline">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="id" value="<?= $doc['_id'] ?>">
                                    <input type="hidden" name="collection" value="dynamic">
                                    <button class="btn btn-sm btn-danger" onclick="return confirm('Delete this dynamic expense?')">🗑️</button>
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
            <a href="dashboard.php" class="btn btn-outline-secondary">⬅ Back to Dashboard</a>
        </div>
        
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>