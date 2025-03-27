<?php 

class Config {
    // Define configuration properties
    private string $dbHost;
    private string $dbPort;
    private string $dbUsername;
    private string $dbPassword;
    private string $jwtSignKey;
    private bool $isDev;

    // Constructor to initialize the properties
    public function __construct() {
        // Fetch environment variables and initialize properties
        $this->dbHost = getenv('DB_HOST') ?: ''; // Default to localhost if not set
        $this->dbPort = getenv('DB_PORT') ?: ''; // Default to MySQL default port if not set
        $this->dbUsername = getenv('DB_USERNAME') ?: ''; // You might want to set a default or handle it better
        $this->dbPassword = getenv('DB_PASSWORD') ?: ''; // Same for password
        $this->jwtSignKey = getenv('JWT_SIGN_KEY') ?: '12345'; // Default to empty if not provided
        $this->isDev = (getenv('IS_DEV') ?: 'true') === 'true'; // Default to true if not set
    }

    // Getter for DB Host
    public function getDbHost(): string {
        return $this->dbHost;
    }

    // Getter for DB Port
    public function getDbPort(): string {
        return $this->dbPort;
    }

    // Getter for DB Username
    public function getDbUsername(): string {
        return $this->dbUsername;
    }

    // Getter for DB Password
    public function getDbPassword(): string {
        return $this->dbPassword;
    }

    // Getter for JWT Sign Key
    public function getJwtSignKey(): string {
        return $this->jwtSignKey;
    }

    // Getter for environment (development or production)
    public function isDev(): bool {
        return $this->isDev;
    }
}
