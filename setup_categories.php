<?php
// CRITICAL: Ensure this path is correct for your project setup
require_once __DIR__ . '/DB.php'; 

use MongoDB\BSON\UTCDateTime;

// --- CONFIGURATION ---
$database = DB::getDatabase();
$staticCollection = $database->static_expenses;
$user_id = 58063; // Use the placeholder ID you used previously

// 1. Define the documents to insert, including all required SQL fields: user_id, name, amount, created_at
$categories = [
    [
        'user_id' => $user_id,
        'name' => 'Rent/Mortgage', 
        'amount' => 15000.00, // Example fixed cost
        'created_at' => new UTCDateTime()
    ],
    [
        'user_id' => $user_id,
        'name' => 'Car Payment', 
        'amount' => 5000.00, 
        'created_at' => new UTCDateTime()
    ],
    [
        'user_id' => $user_id,
        'name' => 'Insurance', 
        'amount' => 1200.00, 
        'created_at' => new UTCDateTime()
    ],
    [
        'user_id' => $user_id,
        'name' => 'Utilities', 
        'amount' => 3000.00, 
        'created_at' => new UTCDateTime()
    ],
];

// 2. Perform insertion only if the collection is empty
try {
    if ($staticCollection->countDocuments() === 0) {
        $staticCollection->insertMany($categories);
        echo "✅ **SUCCESS:** `static_expenses` collection initialized with 4 documents!";
    } else {
        echo "ℹ️ **INFO:** `static_expenses` already exists and contains data. Skipping insertion.";
    }
} catch (Exception $e) {
    die("❌ **ERROR:** Failed to initialize collection: " . htmlspecialchars($e->getMessage()));
}
?>