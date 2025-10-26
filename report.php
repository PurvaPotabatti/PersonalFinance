<?php
// PHP Error Reporting (for debugging only)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Include the database connection handler
require_once __DIR__ . '/DB.php'; 

// CRITICAL: Ensure you include the UTCDateTime class for date formatting
use MongoDB\BSON\UTCDateTime; 

$database = DB::getDatabase();
$dynamicCollection = $database->dynamic_expenses;

$expenses = [];
$message = '';

try {
    // 1. Fetch recent dynamic expenses (e.g., sorted by creation date, limit 50)
    $cursor = $dynamicCollection->find(
        [], 
        [
            'sort' => ['created_at' => -1], // Sort descending by date
            'limit' => 50
        ]
    );
    $expenses = $cursor->toArray();

} catch (Exception $e) {
    $message = "Error fetching expenses: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Expense Report</title>
</head>
<body>

    <h2>Personal Expense Report</h2>
    
    <?php if ($message): ?>
        <p style="color: red; font-weight: bold;"><?= htmlspecialchars($message) ?></p>
    <?php elseif (empty($expenses)): ?>
        <p>No expenses recorded yet.</p>
    <?php else: ?>
        
        <table border="1" cellpadding="10" cellspacing="0">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Category</th>
                    <th>Amount ($)</th>
                    <th>Notes</th>
                    </tr>
            </thead>
            <tbody>
                <?php foreach ($expenses as $expense): 
                    // Convert MongoDB BSON date object to a readable PHP DateTime object
                    $dateTime = $expense['created_at']->toDateTime();
                ?>
                <tr>
                    <td><?= $dateTime->format('Y-m-d H:i:s') ?></td>
                    
                    <td><?= htmlspecialchars($expense['category'] ?? 'N/A') ?></td>
                    
                    <td>**$<?= number_format($expense['amount'], 2) ?>**</td>
                    
                    <td><?= htmlspecialchars($expense['notes'] ?? '—') ?></td>
                    
                    <td><?= htmlspecialchars($expense['user_id'] ?? 'N/A') ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        
    <?php endif; ?>

</body>
</html>