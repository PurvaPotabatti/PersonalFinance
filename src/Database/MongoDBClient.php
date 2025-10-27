<?php
namespace App\Database;

// ✅ Correct path — only go two levels up, not three
require_once __DIR__ . '/../../vendor/autoload.php';

use MongoDB\Client;
use MongoDB\Driver\ServerApi;

class MongoDBClient {
    private static ?Client $instance = null;

    public static function getClient(): Client {
        if (self::$instance === null) {
            // Load .env from project root
            $dotenv = \Dotenv\Dotenv::createImmutable(__DIR__ . '/../../');
            $dotenv->safeLoad();

            $uri = $_ENV['MONGODB_URI'] ?? null;
            if (!$uri) {
                throw new \Exception("❌ MONGODB_URI not found in .env file.");
            }

            $apiVersion = new ServerApi(ServerApi::V1);
            self::$instance = new Client($uri, [], ['serverApi' => $apiVersion]);
        }
        return self::$instance;
    }

    public static function getCollection(string $collectionName): \MongoDB\Collection {
        $client = self::getClient();
        $dbName = $_ENV['DB_NAME'] ?? 'finance_manager';
        return $client->selectCollection($dbName, $collectionName);
    }
}
