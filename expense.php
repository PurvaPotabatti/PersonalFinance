<?php
// PHP Error Reporting (Keep this at the top for debugging)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Include the database connection handler
require_once __DIR__ . '/DB.php'; 

// CRITICAL FIX: The use statement must be here, near the top!
use MongoDB\BSON\UTCDateTime; 

// Use the connection handler to get your database instance
$database = DB::getDatabase();
// Define the two collections you'll be working with
$staticCollection = $database->static_expenses;
$dynamicCollection = $database->dynamic_expenses;

$message = ''; // Variable for user feedback

// 1. Fetch Static Categories for the Form Dropdown
$staticCategories = [];
try {
    // Find all documents, only projecting the 'name' field
    $cursor = $staticCollection->find([], ['projection' => ['name' => 1]]);
    $staticCategories = $cursor->toArray();
} catch (Exception $e) {
    $message = "Could not load categories: " . $e->getMessage();
}

// 2. Handle Form Submission (Save to dynamic_expenses)
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submit_expense'])) {
    
    // **IMPORTANT: Replace 1 with Member 1's actual session user ID logic later**
    $userId = 1; 
    
    // Get and validate inputs (these should be defined BEFORE the IF check)
    $amount = filter_input(INPUT_POST, 'amount', FILTER_VALIDATE_FLOAT);
    $category = htmlspecialchars(trim($_POST['expense_category']));
    $notes = htmlspecialchars(trim($_POST['notes']));

    if ($amount > 0 && !empty($category)) { // Opening bracket for IF
        
        // Prepare the document for insertion
        $expenseDocument = [
            'user_id' => $userId,
            'amount' => $amount,
            'category' => $category,
            'notes' => $notes,
            'created_at' => new UTCDateTime(), // Record the time (Note: UTCDateTime() is used here)
        ];

        // 3. Insert into the dynamic_expenses collection
        // 3. Insert into the dynamic_expenses collection
        try { // Start of TRY (Line 57)
            $dynamicCollection->insertOne($expenseDocument);
            $message = "Expense of **$amount** recorded successfully!";
            // Optionally clear the form or redirect here
        } 
        // NO SEMICOLON HERE! 
        catch (Exception $e) { // Start of CATCH (Line 64)
            $message = "Failed to record expense: " . $e->getMessage();
        }
        // The closing brace for the inner IF (Line 63)
        
    } else { // This is the ELSE block for the IF on line 40
        $message = "Please enter a valid amount and select a category.";
    }
} 
// The closing brace for the main IF (if ($_SERVER["REQUEST_METHOD"] == "POST"...))
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Record Expense</title>
</head>
<body>

    <h2>Record New Personal Expense</h2>
    
    <?php if ($message): ?>
        <p style="color: green; font-weight: bold;"><?= $message ?></p>
    <?php endif; ?>

    <form method="POST" action="expense.php">
        
        <label for="expense_category">Category:</label><br>
        <select name="expense_category" id="expense_category" required>
            <option value="">-- Select Category --</option>
            <?php foreach ($staticCategories as $category): ?>
                <option value="<?= htmlspecialchars($category['name']) ?>">
                    <?= htmlspecialchars($category['name']) ?>
                </option>
            <?php endforeach; ?>
        </select>
        <br><br>
        
        <label for="amount">Amount ($):</label><br>
        <input type="number" step="0.01" name="amount" id="amount" required><br><br>
        
        <label for="notes">Notes:</label><br>
        <textarea name="notes" id="notes" rows="3"></textarea><br><br>
        
        <button type="submit" name="submit_expense">Record Expense</button>
        
    </form>

</body>
</html>