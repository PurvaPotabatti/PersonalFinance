<?php
// 1. Correctly include the new DB connection file
require_once __DIR__ . '/DB.php';

// 2. Get the database instance
$database = DB::getDatabase();
$staticCollection = $database->static_expenses;

$categories = [
    ['name' => 'Rent/Mortgage', 'type' => 'Housing'],
    ['name' => 'Groceries', 'type' => 'Food'],
    ['name' => 'Utilities', 'type' => 'Bills'],
    ['name' => 'Transportation', 'type' => 'Travel'],
    ['name' => 'Entertainment', 'type' => 'Leisure'],
];

try {
    // Only insert if the collection is currently empty
    if ($staticCollection->countDocuments() === 0) {
        $staticCollection->insertMany($categories);
        echo "✅ Static categories collection initialized successfully!";
    } else {
        echo "ℹ️ Static categories collection already contains data. Skipping insertion.";
    }
} catch (Exception $e) {
    echo "❌ Error initializing collection: " . $e->getMessage();
}
?>