<?php
require 'vendor/autoload.php'; // Composer autoload

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

try {
    $uri = $_ENV['MONGODB_URI'];
    $client = new MongoDB\Client($uri);
    
    // Select a database
    $db = $client->finance_manager;
    
    // Select a collection
    $collection = $db->test_collection;
    
    // Insert a test document
    $insertResult = $collection->insertOne(['name' => 'Test', 'status' => 'Connected']);
    
    echo "Connected to MongoDB successfully! Inserted ID: " . $insertResult->getInsertedId();
} catch (Exception $e) {
    echo "Connection failed: " . $e->getMessage();
}
?>
