<?php
require 'vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

try {
    $uri = $_ENV['MONGODB_URI'];
    $client = new MongoDB\Client($uri);
    $collection = $client->testdb->testcol;
    $insertResult = $collection->insertOne(['test' => 'success']);
    echo "Connected to MongoDB successfully! Inserted ID: " . $insertResult->getInsertedId();
} catch (Exception $e) {
    echo "Connection failed: " . $e->getMessage();
}
?>