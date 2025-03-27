<?php 

// Config class handles the loading of configuration settings from environment variables
class Config {
    // Define private configuration properties for database connection and JWT signing key
    private string $dbHost;
    private string $dbPort;
    private string $dbUsername;
    private string $dbPassword;
    private string $jwtSignKey;
    private bool $isDev;

    // Constructor to initialize configuration properties from environment variables
    public function __construct() {
        // Fetch environment variables and initialize the class properties
        // Default to empty string or specific fallback values if not set
        $this->dbHost = getenv('DB_HOST') ?: ''; // Fetch database host or default to an empty string
        $this->dbPort = getenv('DB_PORT') ?: ''; // Fetch database port or default to an empty string
        $this->dbUsername = getenv('DB_USERNAME') ?: ''; // Fetch database username or default to an empty string
        $this->dbPassword = getenv('DB_PASSWORD') ?: ''; // Fetch database password or default to an empty string
        $this->jwtSignKey = getenv('JWT_SIGN_KEY') ?: '12345'; // Fetch JWT sign key or use a default fallback value
        $this->isDev = (getenv('IS_DEV') ?: 'true') === 'true'; // Check if the environment is set to 'dev' or default to 'true'
    }

    // Getter method for database host
    public function getDbHost(): string {
        return $this->dbHost; // Return the database host configuration value
    }

    // Getter method for database port
    public function getDbPort(): string {
        return $this->dbPort; // Return the database port configuration value
    }

    // Getter method for database username
    public function getDbUsername(): string {
        return $this->dbUsername; // Return the database username configuration value
    }

    // Getter method for database password
    public function getDbPassword(): string {
        return $this->dbPassword; // Return the database password configuration value
    }

    // Getter method for JWT sign key
    public function getJwtSignKey(): string {
        return $this->jwtSignKey; // Return the JWT sign key configuration value
    }

    // Getter method to check if the environment is set to development
    public function isDev(): bool {
        return $this->isDev; // Return whether the application is running in development mode
    }
}
