<?php

// Import necessary dependencies and classes
require_once BASE_DIR . 'controllers/BaseController.php';
require_once BASE_DIR . 'models/Task/TaskStatus.php';
require_once BASE_DIR . 'services/TaskService/TaskService.php';
require_once BASE_DIR . 'models/Task/TaskSerializator.php';
require_once BASE_DIR . 'repositories/MYSQLRepository.php';
require_once BASE_DIR . 'services/JWTService.php';
require_once BASE_DIR . 'PDO_CONNECTION.php';
require_once BASE_DIR . 'Config.php';

class TaskController extends BaseController {
    // Define the prefix for task-related routes
    protected static string $prefix = "tasks";

    // Main method to handle incoming requests and map them to the correct action
    public static function executePath(string $path) {
        // Retrieve the JWT signing key from the configuration
        $JWTsignKey = (new Config())->getJwtSignKey();

        // Check for the presence of a valid JWT in the request header
        if (empty($authHeader = $_SERVER['HTTP_AUTHORIZATION'] ?? '') 
            || empty($jwt = str_replace([' ', 'Bearer'], '', $authHeader)) 
            || !JWTService::isValidJWT($JWTsignKey, $jwt) 
            || empty($payload = JWTService::getJWTPayload($JWTsignKey, $jwt))
            || empty($userId = (int) $payload['user_id'])) 
        {
            // If the JWT is invalid or not present, return a 403 Unauthorized response
            static::sendJsonResponse(['message' => 'not authorized'], 403);
        }

        // Handle request when no specific task ID is provided (list all tasks or create task)
        if (empty($path)) {
            if ($_SERVER['REQUEST_METHOD'] === 'GET') {
                // Fetch and return all tasks for the user
                static::getAllTasks($userId);
            } else if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                // Create a new task for the user
                static::createTask($userId);
            } else {
                // Return 404 if the request method is unsupported
                static::sendJsonResponse(['message' => 'Unable to find the page.'], 404);
            }
        } else if (($path = str_replace([' ', '/'], '', $path)) && is_numeric($path) && ctype_digit($path)) {
            // Handle request for a specific task by ID
            $taskId = (int) $path;
            if ($_SERVER['REQUEST_METHOD'] === 'GET') {
                // Fetch and return a specific task
                static::getTask($taskId, $userId);
            } else if ($_SERVER['REQUEST_METHOD'] === 'PUT') {
                // Update an existing task
                static::updateTask($taskId, $userId);
            } else if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
                // Delete a task
                static::deleteTask($taskId, $userId);
            } else {
                // Return 404 if the request method is unsupported
                static::sendJsonResponse(['message' => 'Unable to find the page.'], 404);
            }
        } else {
            // Return 404 if the path is not valid
            static::sendJsonResponse(['message' => 'Unable to find the page.'], 404);
        }
    }

    // Method to create a new task
    private static function createTask(int $userId) {
        // Retrieve POST data for task title, description, and status
        $title = $_POST['title'] ?? '';
        $description = $_POST['description'] ?? '';
        try {
            // Attempt to parse the task status from the input
            $taskStatus = TaskStatus::fromString((int) ($_POST['status'] ?? '1')); 
        } catch (Exception $e) {
            // Return error if task status is invalid
            static::sendJsonResponse(['message' => 'invalid task status'], 400);
        }

        // Check if required fields are missing
        if (empty($title)) {
            static::sendJsonResponse(['message' => 'Missing required fields.'], 400);
        }

        // Instantiate the necessary objects and call the service method to create the task
        $pdo = (new PDO_CONNECTION())->getPDO();
        $repository = new MYSQLRepository($pdo);
        $taskService = new TaskService($repository);
        $task = null;

        try {
            // Create the task and return it
            $task = $taskService->createTask($title, $description, $taskStatus, $userId);
        } catch (Exception $e) {
            // Return error if task creation fails
            static::sendJsonResponse(['message' => 'Error creating task.'], 500);
        }

        // Return the newly created task as a JSON response
        static::sendJsonResponse(TaskSerializator::serialize($task), 201);
    }

    // Method to fetch all tasks for a user
    private static function getAllTasks(int $userId) {
        // Instantiate the necessary objects and call the service method to get all tasks
        $pdo = (new PDO_CONNECTION())->getPDO();
        $repository = new MYSQLRepository($pdo);
        $taskService = new TaskService($repository);

        try {
            // Retrieve all tasks for the user
            $tasks = $taskService->getAllTasks($userId);
        } catch (Exception $e) {
            // Return error if fetching tasks fails
            static::sendJsonResponse(['message' => 'Error fetching tasks.'], 500);
        }

        // Serialize the tasks before returning them
        $tasks = array_map(fn(Task $task) => TaskSerializator::serialize($task), $tasks);
        static::sendJsonResponse($tasks, 200);
    }

    // Method to fetch a specific task by ID
    private static function getTask(int $taskId, int $userId) {
        // Instantiate the necessary objects and call the service method to get the task
        $pdo = (new PDO_CONNECTION())->getPDO();
        $repository = new MYSQLRepository($pdo);
        $taskService = new TaskService($repository);
        $task = null;

        try {
            // Retrieve the specific task
            $task = $taskService->getTask((int)$taskId, (int)$userId);
        } catch (Exception $e) {
            // Return error if fetching the task fails
            static::sendJsonResponse(['message' => 'Error fetching task.'], 500);
        }

        // Return the task if found, otherwise return a 404 response
        if ($task) {
            static::sendJsonResponse(TaskSerializator::serialize($task), 200);
        } else {
            static::sendJsonResponse(['message' => 'Task not found.'], 404);
        }
    }

    // Method to update an existing task
    private static function updateTask(int $taskId, int $userId) {
        // Parse the incoming PUT request body
        parse_str(file_get_contents("php://input"), $PUT_VARS);

        // Retrieve the updated task data from the PUT variables
        $title = $PUT_VARS['title'] ?? null;
        $description = $PUT_VARS['description'] ?? null;
        $taskStatus = $PUT_VARS['status'] ?? null;

        // Check if there is no data to update
        if ((empty($title)) && empty($description) && empty($status)) {
            static::sendJsonResponse(['message' => 'Invalid request'], 400);
        }
        try {
            // Parse the task status if provided
            $taskStatus = $taskStatus ? TaskStatus::fromString((int) $taskStatus) : null;
        } catch (Exception $e) {
            // Return error if task status is invalid
            static::sendJsonResponse(['message' => 'invalid task status'], 400);
        }

        // Instantiate the necessary objects and call the service method to update the task
        $pdo = (new PDO_CONNECTION())->getPDO();
        $repository = new MYSQLRepository($pdo);
        $taskService = new TaskService($repository);
        $task = null;

        try {
            // Update the task and return it
            $task = $taskService->updateTask($userId, $taskId, $title, $description, $taskStatus);
        } catch (Exception $e) {
            // Return error if task update fails
            static::sendJsonResponse(['message' => 'Unable to update task'], 400);
        }

        // Return the updated task as a JSON response
        static::sendJsonResponse(TaskSerializator::serialize($task));
    }

    // Method to delete a task
    private static function deleteTask(int $taskId, int $userId) {
        // Instantiate the necessary objects and call the service method to delete the task
        $pdo = (new PDO_CONNECTION())->getPDO();
        $repository = new MYSQLRepository($pdo);
        $taskService = new TaskService($repository);
        $isTaskDeleted = false;

        try {
            // Attempt to delete the task
            $isTaskDeleted = $taskService->deleteTask($userId, $taskId);
        } catch (Exception $e) {
            // Return error if task deletion fails
            static::sendJsonResponse(['message' => 'Error deleting task.'], 500);
        }

        // Return the result of the deletion (success or failure)
        if ($isTaskDeleted) {
            static::sendJsonResponse(['message' => "task $taskId was deleted"]);
        } else {
            static::sendJsonResponse(['message' => "task $taskId was not deleted"], 400);
        }
    }
}