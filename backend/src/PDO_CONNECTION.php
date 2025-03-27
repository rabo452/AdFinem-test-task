<?php

require_once BASE_DIR . 'Config.php';

class PDO_CONNECTION {
    private PDO $instance;

    public function __construct() {
        // Database name
        $dbname = 'task';

        // Get configuration values
        $config = new Config();
        $host = $config->getDbHost();
        $port = $config->getDbPort();
        $username = $config->getDbUsername();
        $password = $config->getDbPassword();

        // Create PDO instance with dynamic values
        $dsn = "mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4";

        // Create the PDO object and assign it to the instance property
        $this->instance = new PDO($dsn, $username, $password);

        // Set the error mode to exception for better error handling
        $this->instance->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

    // Method to get the PDO instance
    public function getPDO(): PDO {
        return $this->instance;
    }
}
