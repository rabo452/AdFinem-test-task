<?php

// PDO_CONNECTION class manages the creation and configuration of a PDO instance
// for interacting with the MySQL database.
require_once BASE_DIR . 'Config.php';

class PDO_CONNECTION {
    private PDO $instance; // Holds the PDO instance for database interaction

    // Constructor initializes the PDO connection using dynamic configuration values
    public function __construct() {
        // Set the database name (hardcoded for now)
        $dbname = 'task';

        // Create a new Config object to fetch configuration settings
        $config = new Config();
        
        // Retrieve the necessary configuration values for the database connection
        $host = $config->getDbHost();   // Get the database host from the config
        $port = $config->getDbPort();   // Get the database port from the config
        $username = $config->getDbUsername(); // Get the database username from the config
        $password = $config->getDbPassword(); // Get the database password from the config

        // Construct the Data Source Name (DSN) string for MySQL connection
        // The DSN includes the host, port, database name, and character set
        $dsn = "mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4";

        // Create a new PDO instance with the DSN, username, and password
        // Assign it to the instance property for future use
        $this->instance = new PDO($dsn, $username, $password);

        // Set the error handling mode to throw exceptions on error (better for debugging)
        $this->instance->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

    // Method to return the PDO instance
    // This allows other classes to interact with the database through this connection
    public function getPDO(): PDO {
        return $this->instance;
    }
}
