<?php
// PHP Error Reporting (Keep this at the very top for debugging)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Include the database connection handler
require_once __DIR__ . '/DB.php'; 

// CRITICAL: Use statements must be here to recognize MongoDB classes
use MongoDB\BSON\UTCDateTime; 

$database = DB::getDatabase();
$staticCollection = $database->static_expenses;
$dynamicCollection = $database->dynamic_expenses;

$message = ''; // Variable for user feedback
$staticCategories = [];

// 1. Fetch Static Categories for the Form Dropdown (Reads from static_expenses)
try {
    // Find all documents, only projecting the 'name' field
    $cursor = $staticCollection->find([], ['projection' => ['name' => 1]]);
    $staticCategories = $cursor->toArray();
} catch (Exception $e) {
    $message = "Could not load categories: " . $e->getMessage();
}

// 2. Handle Form Submission (Writes to dynamic_expenses)
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submit_expense'])) {
    
    $userId = 1; // IMPORTANT: Replace with Member 1's actual session user ID later
    
    // Get and validate inputs
    $amount = filter_input(INPUT_POST, 'amount', FILTER_VALIDATE_FLOAT);
    $category = htmlspecialchars(trim($_POST['expense_category']));
    $notes = htmlspecialchars(trim($_POST['notes']));

    if ($amount > 0 && !empty($category)) { 
        
        // Prepare the document for insertion
        $expenseDocument = [
            'user_id' => $userId,
            'amount' => $amount,
            'category' => $category,
            'notes' => $notes,
            // CRITICAL FIX: UTCDateTime() is now correctly defined via 'use' statement
            'created_at' => new UTCDateTime(), 
        ];

        // 3. Insert into the dynamic_expenses collection
        try {
            $dynamicCollection->insertOne($expenseDocument);
            $message = "Expense of **$amount** recorded successfully!";
            // Optionally clear the inputs
            $_POST = array(); 
        } 
        catch (Exception $e) { 
            // FIX: Correct variable reference and concatenation
            $message = "Failed to record expense: " . $e->getMessage();
        } 
        
    } else { 
        $message = "Please enter a valid amount and select a category.";
    }
} 
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Record Expense</title>
    <!-- Load Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            /* Inverse gradient for expenses */
            background: linear-gradient(to right, #fbe9e7, #ffebee);
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
            color: #d32f2f; /* Red tone for expenses */
        }
        .btn-expense {
            background-color: #e57373; /* Light red button */
            color: white;
            transition: background-color 0.3s;
        }
        .btn-expense:hover {
            background-color: #d32f2f;
            color: white;
        }
        .text-expense {
            color: #d32f2f;
        }
    </style>
</head>
<body>

<div class="container">
    <div class="dashboard-card">
        <div class="section-title text-center">💸 Record Personal Expense</div>

        <?php if (!empty($message)): ?>
            <!-- Display success/error messages -->
            <div class="alert alert-info text-center"><?= htmlspecialchars($message) ?></div>
        <?php endif; ?>

        <!-- Expense Form -->
        <h5 class="mb-3 text-expense">➖ Enter New Expense</h5>
        <form method="POST" class="row g-3 mb-5 p-3 border rounded">
            <input type="hidden" name="action" value="add">
            
            <div class="col-md-5">
                <label class="form-label">Category</label>
                <!-- PHP logic populates the options here -->
                <select class="form-select" name="expense_category" id="expense_category" required>
                    <option value="">-- Select Category --</option>
                    <?php foreach ($staticCategories as $category): ?>
                        <option value="<?= htmlspecialchars($category['name']) ?>">
                            <?= htmlspecialchars($category['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="col-md-3">
                <label class="form-label">Amount (₹)</label>
                <input type="number" step="0.01" class="form-control" name="amount" required>
            </div>
            
            <div class="col-md-4">
                <label class="form-label">Notes (Optional)</label>
                <input type="text" class="form-control" name="notes" placeholder="e.g., Dinner with client">
            </div>
            
            <div class="col-12 d-flex justify-content-end mt-4">
                <button type="submit" name="submit_expense" class="btn btn-expense">
                    💾 Record Expense
                </button>
            </div>
        </form>
        
        <div class="text-start mt-4">
            <!-- Link to the Expense Report page you created -->
            <a href="report.php" class="btn btn-outline-danger">📋 View Expense History</a>
             <!-- Link back to the main dashboard (if created) -->
            <a href="dashboard.php" class="btn btn-outline-secondary">⬅ Back to Dashboard</a>
        </div>
        
    </div>
</div>
<!-- Bootstrap JS (optional, for some components) -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>