<?php

// Load Composer's autoloader for packages like MongoDB\Client
// In DB.php
require __DIR__ . '/vendor/autoload.php';

use MongoDB\Client;
use Dotenv\Dotenv;

class DB {
    private static $client = null;
    private static $database = null;

    // The name of your main database (from your URI)
    const DB_NAME = 'finance_manager'; 

    /**
     * Get the MongoDB Client instance (singleton pattern)
     * @return \MongoDB\Client
     */
    private static function getClient(): Client {
        if (self::$client === null) {
            // Load environment variables from .env
            $dotenv = Dotenv::createImmutable(__DIR__);
            $dotenv->load();

            // Get the URI from the environment
            $uri = $_ENV['MONGODB_URI'] ?? null;

            if (!$uri) {
                throw new Exception("MONGODB_URI not found in .env file.");
            }

            // Create the MongoDB Client
            self::$client = new Client($uri);
        }
        return self::$client;
    }

    /**
     * Get the specific database instance
     * @return \MongoDB\Database
     */
    public static function getDatabase(): \MongoDB\Database {
        if (self::$database === null) {
            $client = self::getClient();
            self::$database = $client->selectDatabase(self::DB_NAME);
        }
        return self::$database;
    }
}

?>