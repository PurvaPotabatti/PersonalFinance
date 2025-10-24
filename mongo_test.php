<?php
// 1. Load Composer Autoloader
// This makes the MongoDB\Client class and the Dotenv package available.
require __DIR__ . '/vendor/autoload.php';

// Import necessary classes
use App\Database\MongoDBClient; // Your custom connection class
use MongoDB\BSON\UTCDateTime;

echo "<h1>MongoDB Connection Test</h1>";

try {
    // 2. Load the Atlas URI from the local .env file
    // Note: The MONGODB_URI is loaded inside the getClient() method if you used the
    // MongoDBClient class structure provided previously. We'll proceed with that assumption.
    
    // 3. Get the collection handler (This initiates the connection)
    $collection = MongoDBClient::getCollection('dynamic_expenses'); 
    
    // 4. Prepare a test document (based on your original dynamic_expenses table)
    // NOTE: This uses dummy data for user_id=1.
    $testDocument = [
        'user_id' => 1, 
        'name' => 'Connection Test Entry',
        'amount' => 1.01,
        'created_at' => new UTCDateTime()
    ];
    
    // 5. Insert the document to confirm read/write access
    $result = $collection->insertOne($testDocument);

    // 6. Output success message
    echo "<p style='color: green; font-weight: bold;'>SUCCESS! Connection and Insert Successful.</p>";
    echo "<p>Connected to Database: **" . MongoDBClient::DB_NAME . "**</p>";
    echo "<p>Inserted Document ID: <code>{$result->getInsertedId()}</code></p>";

} catch (MongoDB\Driver\Exception\ConnectionTimeoutException $e) {
    echo "<p style='color: red; font-weight: bold;'>FAILURE: Connection Timeout!</p>";
    echo "<p>Action Required: Check your **Atlas IP Access List** or verify the **password** in your .env file.</p>";
    echo "<p>Detail: " . $e->getMessage() . "</p>";

} catch (\Exception $e) {
    echo "<p style='color: red; font-weight: bold;'>FAILURE: An unexpected error occurred.</p>";
    echo "<p>Detail: " . $e->getMessage() . "</p>";
}
?>