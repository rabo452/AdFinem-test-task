<?php 

// Include necessary model and interface dependencies
require_once BASE_DIR . 'models/User/User.php';
require_once BASE_DIR . 'models/User/UserRole.php';
require_once BASE_DIR . 'services/UserService/interface.php';

// UserService class handles user-related operations and business logic
class UserService {
    // Dependency injection of the repository interface for user-related database operations
    private UserServiceRepositoryI $repository;

    // Constructor to initialize the repository dependency
    public function __construct(UserServiceRepositoryI $repository) {
        $this->repository = $repository;
    }

    // Method to create a new user with the provided username, password, and role
    public function createUser(string $username, string $password, UserRole $role): User {
        // Check if the username is already taken
        if (empty($this->repository->getUserByUsername($username))) {
            // If the username is not taken, create a new user
            return $this->repository->createUser($username, static::hashPassword($password), $role);
        }

        // If the username already exists, throw an exception
        throw new Exception("user $username already exists!");
    }

    // Method to retrieve a user by username and password, ensuring the password matches
    public function getUser(string $username, string $password): ?User {
        // Get the user by username from the repository
        $user = $this->repository->getUserByUsername($username);

        // Check if the user exists and if the password matches the hashed version
        return isset($user) && $user->getPassword() === static::hashPassword($password)
            ? $user  // If valid, return the user object
            : null;  // If invalid, return null
    }

    // Private method to hash the password using SHA-256 hashing algorithm
    private static function hashPassword(string $password) {
        return hash('sha256', $password);  // Return the hashed password
    }
}