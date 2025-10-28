<?php
// PHP Error Reporting (for debugging only, remove these lines later)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// --- INITIALIZATION ---
require_once __DIR__ . '/DB.php'; 
use MongoDB\BSON\UTCDateTime; 
use MongoDB\BSON\ObjectId; 

$database = DB::getDatabase(); 
$staticCollection = $database->static_expenses;
$dynamicCollection = $database->dynamic_expenses;

$message = ''; 
$user_id = 58063; // Placeholder user ID

// --- CRUD OPERATIONS (Handling POST Requests) ---
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submit_expense'])) {
    
    $type = $_POST['expense_type'] ?? 'dynamic'; 
    $name = htmlspecialchars(trim($_POST['expense_name'])); 
    $amount = filter_input(INPUT_POST, 'amount', FILTER_VALIDATE_FLOAT);
    $notes = htmlspecialchars(trim($_POST['notes']));

    // 1. Determine the destination collection
    $targetCollection = ($type === 'static') ? $staticCollection : $dynamicCollection;

    if ($amount > 0 && !empty($name)) { 
        
        $expenseDocument = [
            'user_id' => $user_id,
            'amount' => $amount,
            'name' => $name,      
            'notes' => $notes,    
            'created_at' => new UTCDateTime(), 
        ];

        try {
            $targetCollection->insertOne($expenseDocument);
            $message = ucfirst($type) . " expense of **₹" . number_format($amount, 2) . "** recorded successfully!";
            header("Location: expense.php");
            exit();
        } 
        catch (Exception $e) { 
            $message = "Failed to record expense: " . $e->getMessage();
        } 
    } else { 
        $message = "Please enter a valid amount and expense name.";
    }
} 


// --- READ OPERATIONS (Fetch Data for Tables) ---
$allStaticDocs = $staticCollection->find(['user_id' => $user_id]);
$allDynamicDocs = $dynamicCollection->find(['user_id' => $user_id]);

// Labels and arrays updated to Static/Dynamic
$staticExpenses = iterator_to_array($allStaticDocs);
$dynamicExpenses = iterator_to_array($allDynamicDocs); 
$total_static = 0.00; 
$total_dynamic = 0.00; 

foreach ($staticExpenses as $doc) {
    $total_static += floatval($doc['amount'] ?? 0); 
}

foreach ($dynamicExpenses as $doc) {
    $total_dynamic += floatval($doc['amount'] ?? 0); 
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