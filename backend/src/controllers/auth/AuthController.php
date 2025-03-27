<?php 

// Including necessary files for the controller's dependencies
require_once BASE_DIR . 'controllers/BaseController.php';
require_once BASE_DIR . 'models/User/UserRole.php';
require_once BASE_DIR . 'repositories/MYSQLRepository.php';
require_once BASE_DIR . 'services/JWTService.php';
require_once BASE_DIR . 'services/UserService/UserService.php';
require_once BASE_DIR . 'PDO_CONNECTION.php';
require_once BASE_DIR . 'Config.php';

// AuthController handles user authentication operations such as login and registration
class AuthController extends BaseController {
    // Define the prefix used for routing (e.g., "auth")
    protected static string $prefix = "auth";

    // Mapping of path names to methods in the controller
    protected static array $actions = [
        'sign-up' => 'signUp',  // sign-up path is mapped to the signUp method
        'login' => 'logIn',     // login path is mapped to the logIn method
    ];

    // Method to validate the username and password input
    private static function validateCredentials(string $username, string $password): void {
        // Validate that the username length is between 8 and 40 characters
        if (strlen($username) < 8 || strlen($username) > 40) {
            self::sendJsonResponse(['message' => 'Username must be between 8 and 40 characters.'], 400);
        }

        // Validate that the password length is between 8 and 40 characters
        if (strlen($password) < 8 || strlen($password) > 40) {
            self::sendJsonResponse(['message' => 'Password must be between 8 and 40 characters.'], 400);
        }

        // Validate that the username contains only alphanumeric characters
        if (!ctype_alnum($username)) {
            self::sendJsonResponse(['message' => 'Username must only contain letters and numbers.'], 400);
        }

        // Validate that the password contains only alphanumeric characters
        if (!ctype_alnum($password)) {
            self::sendJsonResponse(['message' => 'Password must only contain letters and numbers.'], 400);
        }
    }

    // Method to handle user registration (sign-up)
    protected static function signUp(): void {
        // Ensure that the request method is POST
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            self::sendJsonResponse(['message' => 'Please submit the registration form.'], 405);
        }

        // Retrieve the username and password from the POST request data
        $username = $_POST['username'] ?? '';
        $password = $_POST['password'] ?? '';

        // Validate the credentials (username and password)
        self::validateCredentials($username, $password);

        // Set a default role for the new user (e.g., participant)
        $defaultRole = UserRole::PARTICIPANT;

        // Establish a PDO connection and instantiate necessary services and repository
        $pdo = (new PDO_CONNECTION())->getPDO();
        $repository = new MYSQLRepository($pdo);
        $userService = new UserService($repository);
        $user = null;

        // Attempt to create a new user and handle potential exceptions
        try {
            $user = $userService->createUser($username, $password, $defaultRole);
        } catch(Exception $e) {
            // If the user already exists, return an error message
            self::sendJsonResponse(['message' => "User [$username] already exists!"], 409);
        }

        // Create a JWT token for the newly created user with a 1-hour expiration
        $payload = ['user_id' => $user->getId()];
        $jwtSignKey = (new Config())->getJwtSignKey();
        $jwtDuration = 60 * 60; // one hour duration
        $jwt = JWTService::createJWT($jwtSignKey, $jwtDuration, $payload);

        // Respond with the generated JWT
        self::sendJsonResponse(['jwt' => $jwt], 201);
    }

    // Method to handle user login (log-in)
    protected static function logIn(): void {
        // Ensure that the request method is POST
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            self::sendJsonResponse(['message' => 'Please submit the login form.'], 405);
        }

        // Retrieve the username and password from the POST request data
        $username = $_POST['username'] ?? '';
        $password = $_POST['password'] ?? '';

        // Validate the credentials (username and password)
        self::validateCredentials($username, $password);

        // Establish a PDO connection and instantiate necessary services and repository
        $pdo = (new PDO_CONNECTION())->getPDO();
        $repository = new MYSQLRepository($pdo);
        $userService = new UserService($repository);
        $user = null;

        // Attempt to fetch the user from the database using the provided credentials
        try {
            $user = $userService->getUser($username, $password);
        } catch (Exception $e) {
            // Handle server errors
            self::sendJsonResponse(['message' => 'Server error, please try again later.'], 500);
        }

        // If the user doesn't exist, return an error message
        if (!isset($user)) {
            self::sendJsonResponse(['message' => "User [$username] does not exist!"], 404);
        }

        // Create a JWT token for the logged-in user with a 1-hour expiration
        $payload = ['user_id' => $user->getId()];
        $jwtSignKey = (new Config())->getJwtSignKey();
        $jwtDuration = 60 * 60; // one hour duration
        $jwt = JWTService::createJWT($jwtSignKey, $jwtDuration, $payload);

        // Respond with the generated JWT
        self::sendJsonResponse(['jwt' => $jwt], 200);
    }
}
