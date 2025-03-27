<?php 

require_once BASE_DIR . 'controllers/BaseController.php';
require_once BASE_DIR . 'models/User/UserRole.php';
require_once BASE_DIR . 'repositories/MYSQLRepository.php';
require_once BASE_DIR . 'services/JWTService.php';
require_once BASE_DIR . 'services/UserService/UserService.php';
require_once BASE_DIR . 'PDO_CONNECTION.php';
require_once BASE_DIR . 'Config.php';

class AuthController extends BaseController {
    protected static string $prefix = "auth";

    // Define the mapping of paths to method names
    protected static array $actions = [
        'sign-up' => 'signUp',
        'login' => 'logIn',
    ];

    // Validate username and password
    private static function validateCredentials(string $username, string $password): void {
        // Validate username length
        if (strlen($username) < 8 || strlen($username) > 40) {
            self::sendJsonResponse(['message' => 'Username must be between 8 and 40 characters.'], 400);
        }

        // Validate password length (similar to username length check)
        if (strlen($password) < 8 || strlen($password) > 40) {
            self::sendJsonResponse(['message' => 'Password must be between 8 and 40 characters.'], 400);
        }

        // Check if the username is alphanumeric (if needed for validation)
        if (!ctype_alnum($username)) {
            self::sendJsonResponse(['message' => 'Username must only contain letters and numbers.'], 400);
        }

        // Check if the password is alphanumeric (if needed for validation)
        if (!ctype_alnum($password)) {
            self::sendJsonResponse(['message' => 'Password must only contain letters and numbers.'], 400);
        }
    }

    // Sign-up method
    protected static function signUp(): void {
        // Check if the request method is POST
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            self::sendJsonResponse(['message' => 'Please submit the registration form.'], 405);
        } 

        // Get the POST data for username and password
        $username = $_POST['username'] ?? '';
        $password = $_POST['password'] ?? '';

        // Validate the username and password
        self::validateCredentials($username, $password);

        // Set a default role (e.g., participant) when creating a user
        $defaultRole = UserRole::PARTICIPANT;

        $pdo = (new PDO_CONNECTION())->getPDO();
        $repository = new MYSQLRepository($pdo);
        $userService = new UserService($repository);
        $user = null;

        try {
            $user = $userService->createUser($username, $password, $defaultRole);
        } catch(Exception $e) {
            self::sendJsonResponse(['message' => "User [$username] already exists!"], 409);
        }

        // Create JWT token for the user
        $payload = ['user_id' => $user->getId()];
        $jwtSignKey = (new Config())->getJwtSignKey();
        $jwtDuration = 60 * 60; // one hour
        $jwt = JWTService::createJWT($jwtSignKey, $jwtDuration, $payload);

        // Respond with the JWT
        self::sendJsonResponse(['jwt' => $jwt], 201);
    }

    // Login method
    protected static function logIn(): void {
        // Check if the request method is POST
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            self::sendJsonResponse(['message' => 'Please submit the login form.'], 405);
        }

        // Get the POST data for username and password
        $username = $_POST['username'] ?? '';
        $password = $_POST['password'] ?? '';

        // Validate the username and password
        self::validateCredentials($username, $password);

        $pdo = (new PDO_CONNECTION())->getPDO();
        $repository = new MYSQLRepository($pdo);
        $userService = new UserService($repository);
        $user = null;

        try {
            $user = $userService->getUser($username, $password);
        } catch (Exception $e) {
            self::sendJsonResponse(['message' => 'Server error, please try again later.'], 500);
        }

        if (!isset($user)) {
            self::sendJsonResponse(['message' => "User [$username] does not exist!"], 404);
        }

        // Create JWT token for the user
        $payload = ['user_id' => $user->getId()];
        $jwtSignKey = (new Config())->getJwtSignKey();
        $jwtDuration = 60 * 60; // one hour
        $jwt = JWTService::createJWT($jwtSignKey, $jwtDuration, $payload);

        // Respond with the JWT
        self::sendJsonResponse(['jwt' => $jwt], 200);
    }
}