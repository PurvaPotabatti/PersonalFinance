<?php
namespace App\Database;

// ✅ Load Composer autoloader so Dotenv and MongoDB classes are available
require_once __DIR__ . '/../../vendor/autoload.php';

use MongoDB\Client;
use MongoDB\Driver\ServerApi;
use Dotenv\Dotenv;

class MongoDBClient {
    private static ?Client $instance = null;

    public const DB_NAME = 'finance_manager';

    public static function getClient(): Client {
        if (self::$instance === null) {
            // ✅ Load environment variables from the project root .env file
            $dotenv = Dotenv::createImmutable(__DIR__ . '/../../');
            $dotenv->safeLoad();

            $uri = $_ENV['MONGODB_URI'] ?? null;

            if ($uri === null) {
                throw new \Exception("MONGODB_URI not found in .env file.");
            }

            $apiVersion = new ServerApi(ServerApi::V1);
            self::$instance = new Client($uri, [], ['serverApi' => $apiVersion]);
        }
        return self::$instance;
    }

    public static function getCollection(string $collectionName): \MongoDB\Collection {
        $client = self::getClient();
        return $client->selectCollection(self::DB_NAME, $collectionName);
    }
}
