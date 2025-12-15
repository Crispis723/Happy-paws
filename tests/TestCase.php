<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    /**
     * Ensure the testing database exists when using a MySQL/MariaDB connection.
     */
    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        $dbConnection = getenv('DB_CONNECTION') ?: getenv('DB');
        if (! $dbConnection) {
            return;
        }

        if (strtolower($dbConnection) === 'mariadb' || strtolower($dbConnection) === 'mysql') {
            $host = getenv('DB_HOST') ?: '127.0.0.1';
            $port = getenv('DB_PORT') ?: '3306';
            $user = getenv('DB_USERNAME') ?: 'root';
            $pass = getenv('DB_PASSWORD') ?: '';
            $db   = getenv('DB_DATABASE') ?: 'testing';

            try {
                $pdo = new \PDO("mysql:host={$host};port={$port}", $user, $pass);
                $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
                $pdo->exec("CREATE DATABASE IF NOT EXISTS `{$db}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;");
            } catch (\Throwable $e) {
                // Non-fatal; tests will report a clear error. Write to STDERR for visibility.
                fwrite(STDERR, "[tests] Could not ensure test database exists: " . $e->getMessage() . PHP_EOL);
            }
        }
    }

}
