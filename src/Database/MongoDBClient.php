<?php
namespace App\Database;

use MongoDB\Client;
use MongoDB\Driver\ServerApi;

class MongoDBClient {
    private static ?Client $instance = null;

    // Use the actual database name for your project
    public const DB_NAME = 'finance_manager'; 

    public static function getClient(): Client {
        // Check if the client instance already exists (Singleton Pattern)
        if (self::$instance === null) {
            // 1. Load the environment variables from the .env file
            $dotenv = \Dotenv\Dotenv::createImmutable(__DIR__ . '/../../');
            $dotenv->safeLoad();

            $uri = $_ENV['MONGODB_URI'] ?? null;

            if ($uri === null) {
                throw new \Exception("MONGODB_URI not found in .env file.");
            }

            $apiVersion = new ServerApi(ServerApi::V1);

            // 2. Create the new client connection
            self::$instance = new Client($uri, [], ['serverApi' => $apiVersion]);
        }
        return self::$instance;
    }

    public static function getCollection(string $collectionName): \MongoDB\Collection {
        $client = self::getClient();
        // This selects the main database used for the project
        return $client->selectCollection(self::DB_NAME, $collectionName);
    }
}