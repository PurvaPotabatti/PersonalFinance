<?php
session_start();
// Load Composer dependencies
require __DIR__ . '/vendor/autoload.php';
// Include the local connection core
require_once __DIR__ . '/DB.php'; 

// --- INITIALIZATION ---
// CRITICAL FIX: Define the user ID as the known insertion ID (58063)
$user_id_int = 58063;
$user_id = $user_id_int; 
$database = DB::getDatabase(); 

// Initialize all totals to zero
$total_income = 0.0;
$total_static_expense = 0.0;
$total_dynamic_expense = 0.0;
$total_static_savings = 0.0;
$total_dynamic_savings = 0.0;


// --- CRITICAL FIX: FETCH AND SUM ALL METRICS IN PHP ---

try {
    // Helper function to safely sum the amount from documents
    $safe_sum = function(&$total, $doc) {
        $amount = $doc['amount'];
        // Safely convert BSON/string amount to float
        if (is_object($amount) && method_exists($amount, '__toString')) {
            $total += (float)$amount->__toString();
        } else {
            $total += (float)($amount ?? 0);
        }
    };
    
    // --- 1. TOTAL INCOME (FIXED: Fetching all documents as Income was likely inserted once) ---
    $incomeCollection = $database->selectCollection('income');
    // Fetch ALL income documents to get the total, regardless of user_id mismatch
    $incomeDocs = $incomeCollection->find([]); 
    foreach ($incomeDocs as $doc) {
        $safe_sum($total_income, $doc);
    }
    
    // --- 2. EXPENSES (Dynamic) ---
    $dynamicCollection = $database->selectCollection('dynamic_expenses');
    // NOTE: Query uses the guaranteed working user_id
    $dynamicDocs = $dynamicCollection->find(['user_id' => $user_id])->toArray(); 
    foreach ($dynamicDocs as $doc) {
        $safe_sum($total_dynamic_expense, $doc);
    }

    // --- 3. EXPENSES (Static) ---
    $staticCollection = $database->selectCollection('static_expenses');
    // NOTE: Query uses the guaranteed working user_id
    $staticDocs = $staticCollection->find(['user_id' => $user_id])->toArray(); 
    foreach ($staticDocs as $doc) {
        $safe_sum($total_static_expense, $doc);
    }

    // --- 3. SAVINGS (FIXED: Fetching all savings documents to get the totals) ---
    $savingsCollection = $database->selectCollection('savings');
    // Fetch ALL savings documents, then separate by type
    $savingsDocs = $savingsCollection->find([]); 
    
    foreach ($savingsDocs as $doc) {
        if (($doc['type'] ?? 'dynamic') === 'static') {
            $safe_sum($total_static_savings, $doc);
        } else {
            $safe_sum($total_dynamic_savings, $doc);
        }
    }

} catch (Exception $e) {
    error_log("Report Generation Failed: " . $e->getMessage());
}


// --- CALCULATING FINAL BALANCE ---
$total_expenses = $total_static_expense + $total_dynamic_expense;
$total_savings = $total_static_savings + $total_dynamic_savings;

// Remaining Balance = Income - Expenses - Total Savings Committed
$remaining_balance = $total_income - $total_expenses - $total_savings;
?>

<!-- HTML code follows below -->

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Financial Report</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
  <style>
    body {
      background: linear-gradient(to right, #f7f9fc, #f0f4f7);
      font-family: 'Segoe UI', sans-serif;
    }
    .report-card {
      background: #ffffff;
      border-radius: 25px;
      padding: 40px;
      box-shadow: 0 10px 30px rgba(0,0,0,0.1);
      margin-top: 50px;
    }
    .report-title {
      font-size: 32px;
      font-weight: 700;
      color: #333;
      margin-bottom: 30px;
    }
    .metric-card {
      border: 1px solid #e0e0e0;
      border-radius: 15px;
      padding: 20px;
      margin-bottom: 20px;
      transition: transform 0.2s;
    }
    .metric-card:hover {
      transform: translateY(-3px);
      box-shadow: 0 4px 15px rgba(0,0,0,0.1);
    }
    .metric-label {
      font-weight: 600;
      color: #6c757d;
      font-size: 1.1rem;
    }
    .metric-value {
      font-size: 1.8rem;
      font-weight: 800;
    }
    .income-bg { background-color: #e8f5e9; border-left: 5px solid #4caf50; }
    .expense-bg { background-color: #ffebee; border-left: 5px solid #f44336; }
    .savings-bg { background-color: #fff8e1; border-left: 5px solid #ff9800; }
    .balance-bg { background-color: #e3f2fd; border-left: 5px solid #2196f3; }
    .balance-positive { color: #28a745; }
    .balance-negative { color: #dc3545; }
  </style>
</head>
<body>

<div class="container">
  <div class="report-card">
    <div class="report-title text-center">📈 Financial Summary Report (MongoDB)</div>

    <div class="row">
      <h4 class="mb-3 text-secondary">Summary Totals</h4>

      <div class="col-md-6">
        <div class="metric-card income-bg">
          <div class="metric-label"><i class="fas fa-arrow-up text-success"></i> Total Income</div>
          <div class="metric-value text-success">₹ <?= number_format($total_income, 2) ?></div>
        </div>
      </div>

      <div class="col-md-6">
        <div class="metric-card expense-bg">
          <div class="metric-label"><i class="fas fa-arrow-down text-danger"></i> Total Expenses</div>
          <div class="metric-value text-danger">₹ <?= number_format($total_expenses, 2) ?></div>
        </div>
      </div>
    </div>

    <div class="row mt-4">
      <h4 class="mb-3 text-secondary">Detailed Breakdown</h4>

      <div class="col-md-4">
        <div class="metric-card savings-bg">
          <div class="metric-label"><i class="fas fa-piggy-bank text-warning"></i> Total Savings Committed</div>
          <div class="metric-value text-warning">₹ <?= number_format($total_savings, 2) ?></div>
          <hr>
          <div class="text-muted small">Static: ₹ <?= number_format($total_static_savings, 2) ?></div>
          <div class="text-muted small">Dynamic: ₹ <?= number_format($total_dynamic_savings, 2) ?></div>
        </div>
      </div>

      <div class="col-md-4">
        <div class="metric-card expense-bg">
          <div class="metric-label"><i class="fas fa-money-bill-wave text-danger"></i> Static Expenses</div>
          <div class="metric-value text-danger">₹ <?= number_format($total_static_expense, 2) ?></div>
        </div>
      </div>

      <div class="col-md-4">
        <div class="metric-card expense-bg">
          <div class="metric-label"><i class="fas fa-shopping-bag text-danger"></i> Dynamic Expenses</div>
          <div class="metric-value text-danger">₹ <?= number_format($total_dynamic_expense, 2) ?></div>
        </div>
      </div>
    </div>

    <div class="row mt-4">
      <div class="col-12">
        <div class="metric-card balance-bg">
          <div class="metric-label"><i class="fas fa-balance-scale text-primary"></i> NET REMAINING BALANCE</div>
          <div class="metric-value <?= $remaining_balance >= 0 ? 'balance-positive' : 'balance-negative' ?>">
            ₹ <?= number_format($remaining_balance, 2) ?>
          </div>
          <div class="text-muted small mt-2">(Income - Total Expenses - Total Savings)</div>
        </div>
      </div>
    </div>

    <div class="text-start mt-4">
      <a href="dashboard.php" class="btn btn-outline-secondary">⬅ Back to Dashboard</a>
    </div>
  </div>
</div>

</body>
</html>
