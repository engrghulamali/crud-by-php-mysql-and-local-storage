<?php
    /*
        Database Connection
        This involves establishing a database connection and checking for the existence of the database or its tables.
    */
    class Database {
        private static $instance;
        private $conn;
        // The constructor is private, meaning it cannot be directly instantiated from outside the class
        private function __construct() {
           // Load database credentials from the .env file
            $envFile = __DIR__ . '\..\.env';
            if (!file_exists($envFile)) {
                die('.env file not found. Please create one.');
            }

            $env = parse_ini_file($envFile);
            $host = $env['DB_HOST'];
            $username = $env['DB_USER'];
            $password = $env['DB_PASSWORD'];
            $databaseName = $env['DB_NAME'];


            // Create a connection without specifying the database name
            $this->conn = new mysqli($host, $username, $password);

            if ($this->conn->connect_error) {
                die("Connection failed: " . $this->conn->connect_error);
            }

            // Check if the database exists, if not, create it
            $this->createDatabase($databaseName);

            // Select the created database
            $this->conn->select_db($databaseName);

            // Check if the 'users' table exists, if not, create it
            $tableName = 'users';
            $this->createUsersTable($tableName);
        }

        // automactically create database, if already not exist.
        private function createDatabase($databaseName) {
            $createDatabaseQuery = "CREATE DATABASE IF NOT EXISTS $databaseName";
            $this->conn->query($createDatabaseQuery);
        }
        // create users table if not exist
        private function createUsersTable($tableName) {
            $createTableQuery = "CREATE TABLE IF NOT EXISTS $tableName (
                id INT AUTO_INCREMENT PRIMARY KEY,
                firstName VARCHAR(100) NOT NULL,
                lastName VARCHAR(100) NOT NULL,
                dob VARCHAR(100) NOT NULL,
                phone VARCHAR(15) NOT NULL,
                email VARCHAR(150) NOT NULL UNIQUE,
                is_deleted INT(1) NOT NULL DEFAULT 0,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            )";

            $this->conn->query($createTableQuery);
        }

        public static function getInstance() {
            if (!self::$instance) {
                self::$instance = new self();
            }
            return self::$instance;
        }

        public function getConnection() {
            return $this->conn;
        }
    }
?>
