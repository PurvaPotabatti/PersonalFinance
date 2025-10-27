<?php
session_start();
// Load Composer dependencies and the custom connection class
require __DIR__ . '/vendor/autoload.php';

use App\Database\MongoDBClient;

// --- INITIALIZATION ---
// Get user ID from session. Based on the SQL dump, user_id is an integer.
$user_id = (int)($_SESSION['user_id'] ?? 58063); 

// Initialize all totals to zero
$total_income = 0.0;
$total_static_expense = 0.0;
$total_dynamic_expense = 0.0;
$total_static_savings = 0.0;
$total_dynamic_savings = 0.0;

// --- AGGREGATION PIPELINE FUNCTION ---
/**
 * Executes a MongoDB Aggregation Pipeline to calculate the sum of the 'amount' field
 * for a specific user_id within a given collection.
 * * @param string $collectionName The name of the collection (e.g., 'income').
 * @param int $userId The user_id to filter the documents by.
 * @return float The calculated total amount.
 */
function get_total_amount(string $collectionName, int $userId): float {
    try {
        $collection = MongoDBClient::getCollection($collectionName);
        
        $pipeline = [
            // 1. Filter: Match documents belonging to the current user
            ['$match' => ['user_id' => $userId]],
            
            // 2. Group: Sum the 'amount' field across all matched documents
            ['$group' => [
                '_id' => null, // Group all results into one document
                'totalAmount' => ['$sum' => '$amount'] // Calculate the sum
            ]]
        ];

        $result = $collection->aggregate($pipeline);
        
        // Fetch the result document
        $doc = $result->toArray();
        
        // Return the totalAmount, or 0.0 if no documents were found
        return isset($doc[0]['totalAmount']) ? (float)$doc[0]['totalAmount'] : 0.0;

    } catch (Exception $e) {
        // Log the error but continue execution for other reports
        error_log("MongoDB Aggregation Error in $collectionName: " . $e->getMessage());
        return 0.0; 
    }
}

// --- FETCHING ALL TOTALS ---

// Income
$total_income = get_total_amount('income', $user_id);

// Expenses
$total_static_expense = get_total_amount('static_expenses', $user_id);
$total_dynamic_expense = get_total_amount('dynamic_expenses', $user_id);

// Savings
// Note: Savings collection needs an extra filter by 'type' ('static' or 'dynamic')

try {
    $savingsCollection = MongoDBClient::getCollection('savings');

    // 1. Total Static Savings
    $pipelineStatic = [
        ['$match' => ['user_id' => $user_id, 'type' => 'static']],
        ['$group' => ['_id' => null, 'totalAmount' => ['$sum' => '$amount']]]
    ];
    $resultStatic = $savingsCollection->aggregate($pipelineStatic)->toArray();
    $total_static_savings = isset($resultStatic[0]['totalAmount']) ? (float)$resultStatic[0]['totalAmount'] : 0.0;

    // 2. Total Dynamic Savings
    $pipelineDynamic = [
        ['$match' => ['user_id' => $user_id, 'type' => 'dynamic']],
        ['$group' => ['_id' => null, 'totalAmount' => ['$sum' => '$amount']]]
    ];
    $resultDynamic = $savingsCollection->aggregate($pipelineDynamic)->toArray();
    $total_dynamic_savings = isset($resultDynamic[0]['totalAmount']) ? (float)$resultDynamic[0]['totalAmount'] : 0.0;

} catch (Exception $e) {
    error_log("MongoDB Savings Aggregation Error: " . $e->getMessage());
}


// --- CALCULATING FINAL BALANCE ---
$total_expenses = $total_static_expense + $total_dynamic_expense;
$total_savings = $total_static_savings + $total_dynamic_savings;

// Remaining Balance = Income - Expenses - Total Savings Committed
$remaining_balance = $total_income - $total_expenses - $total_savings;
?>

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

    <!-- Income & Expense Totals -->
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
    
    <!-- Savings & Detailed Expenses -->
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
    
    <!-- Final Balance -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="metric-card balance-bg">
                <div class="metric-label"><i class="fas fa-balance-scale text-primary"></i> NET REMAINING BALANCE</div>
                <div class="metric-value <?= $remaining_balance >= 0 ? 'balance-positive' : 'balance-negative' ?>">
                    ₹ <?= number_format($remaining_balance, 2) ?>
                </div>
                <div class="text-muted small mt-2">
                    (Income - Total Expenses - Total Savings)
                </div>
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