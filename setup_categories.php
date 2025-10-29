<?php
// CRITICAL: Ensure this path is correct for your project setup
require_once __DIR__ . '/DB.php'; 

use MongoDB\BSON\UTCDateTime;

// --- CONFIGURATION ---
$database = DB::getDatabase();
// FIX: Target the new, single 'expenses' collection
$expensesCollection = $database->selectCollection('expenses');
$user_id = 58063; // Placeholder user ID

// 1. Define the documents to insert, including all required SQL fields: 
// user_id, type, name, amount, created_at
$initial_expenses = [
    // STATIC EXPENSES (Type: 'static')
    [
        'user_id' => $user_id,
        'type' => 'static', // Matches SQL 'type' column
        'name' => 'Rent/Mortgage', 
        'amount' => 15000.00, 
        'created_at' => new UTCDateTime()
    ],
    [
        'user_id' => $user_id,
        'type' => 'static',
        'name' => 'Car Payment', 
        'amount' => 5000.00, 
        'created_at' => new UTCDateTime()
    ],
    [
        'user_id' => $user_id,
        'type' => 'static',
        'name' => 'Insurance', 
        'amount' => 1200.00, 
        'created_at' => new UTCDateTime()
    ],
    // DYNAMIC EXPENSES (Type: 'dynamic')
    [
        'user_id' => $user_id,
        'type' => 'dynamic', // Matches SQL 'type' column
        'name' => 'Groceries', 
        'amount' => 3000.00, 
        'created_at' => new UTCDateTime()
    ],
    [
        'user_id' => $user_id,
        'type' => 'dynamic',
        'name' => 'Coffee & Lunch', 
        'amount' => 500.00, 
        'created_at' => new UTCDateTime()
    ],
];

// 2. Perform insertion only if the collection is empty
try {
    if ($expensesCollection->countDocuments() === 0) {
        $expensesCollection->insertMany($initial_expenses);
        echo "✅ **SUCCESS:** `expenses` collection initialized with static and dynamic documents!";
    } else {
        echo "ℹ️ **INFO:** `expenses` already exists and contains data. Skipping insertion.";
    }
} catch (Exception $e) {
    die("❌ **ERROR:** Failed to initialize collection: " . htmlspecialchars($e->getMessage()));
}
?>