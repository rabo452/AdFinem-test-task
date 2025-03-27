<?php


require_once BASE_DIR . 'controllers/BaseController.php';
require_once BASE_DIR . 'models/Task/TaskStatus.php';
require_once BASE_DIR . 'services/TaskService/TaskService.php';
require_once BASE_DIR . 'models/Task/TaskSerializator.php';
require_once BASE_DIR . 'repositories/MYSQLRepository.php';
require_once BASE_DIR . 'services/JWTService.php';
require_once BASE_DIR . 'PDO_CONNECTION.php';
require_once BASE_DIR . 'Config.php';

class TaskController extends BaseController {
    protected static string $prefix = "tasks";

    public static function executePath(string $path) {
        $JWTsignKey = (new Config())->getJwtSignKey();

        // check for JWT 
        // if it is not presented, then give an error message
        if (empty($authHeader = $_SERVER['HTTP_AUTHORIZATION'] ?? '') 
            || empty($jwt = str_replace([' ', 'Bearer'], '', $authHeader)) 
            || !JWTService::isValidJWT($JWTsignKey, $jwt) 
            || empty($payload = JWTService::getJWTPayload($JWTsignKey, $jwt))
            || empty($userId = (int) $payload['user_id'])) 
        {
            static::sendJsonResponse(['message' => 'not authorized'], 403);
        }

        // Check if the path is empty, meaning list all tasks
        if (empty($path)) {
            if ($_SERVER['REQUEST_METHOD'] === 'GET') {
                static::getAllTasks($userId);
            } else if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                static::createTask($userId);
            } else {
                static::sendJsonResponse(['message' => 'Unable to find the page.'], 404);
            }
        } else if (($path = str_replace([' ', '/'], '', $path)) && is_numeric($path) && ctype_digit($path)) {
            $taskId = (int) $path;
            // Path refers to a specific task
            if ($_SERVER['REQUEST_METHOD'] === 'GET') {
                static::getTask($taskId, $userId);
            } else if ($_SERVER['REQUEST_METHOD'] === 'PUT') {
                static::updateTask($taskId, $userId);
            } else if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
                static::deleteTask($taskId, $userId);
            } else {
                static::sendJsonResponse(['message' => 'Unable to find the page.'], 404);
            }
        } else {
            static::sendJsonResponse(['message' => 'Unable to find the page.'], 404);
        }
    }

    private static function createTask(int $userId) {
        // Get POST data for the task
        $title = $_POST['title'] ?? '';
        $description = $_POST['description'] ?? '';
        try {
            $taskStatus = TaskStatus::fromString((int) ($_POST['status'] ?? '1')); 
        } catch (Exception $e) {
            static::sendJsonResponse(['message' => 'invalid task status'], 400);
        }
        

        if (empty($title)) {
            static::sendJsonResponse(['message' => 'Missing required fields.'], 400);
        }

        // Instantiate required classes and call service method
        $pdo = (new PDO_CONNECTION())->getPDO();
        $repository = new MYSQLRepository($pdo);
        $taskService = new TaskService($repository);
        $task = null;

        try {
            $task = $taskService->createTask($title, $description, $taskStatus, $userId);
        } catch (Exception $e) {
            static::sendJsonResponse(['message' => 'Error creating task.'], 500);
        }

        static::sendJsonResponse(TaskSerializator::serialize($task), 201);
    }

    private static function getAllTasks(int $userId) {
        // Instantiate required classes and call service method
        $pdo = (new PDO_CONNECTION())->getPDO();
        $repository = new MYSQLRepository($pdo);
        $taskService = new TaskService($repository);

        try {
            $tasks = $taskService->getAllTasks($userId);
        } catch (Exception $e) {
            static::sendJsonResponse(['message' => 'Error fetching tasks.'], 500);
        }

        $tasks = array_map(fn(Task $task) => TaskSerializator::serialize($task), $tasks);
        static::sendJsonResponse($tasks, 200);
    }

    private static function getTask(int $taskId, int $userId) {
        // Instantiate required classes and call service method
        $pdo = (new PDO_CONNECTION())->getPDO();
        $repository = new MYSQLRepository($pdo);
        $taskService = new TaskService($repository);
        $task = null;

        try {
            $task = $taskService->getTask((int)$taskId, (int)$userId);
        } catch (Exception $e) {
            static::sendJsonResponse(['message' => 'Error fetching task.'], 500);
        }

        if ($task) {
            static::sendJsonResponse(TaskSerializator::serialize($task), 200);
        } else {
            static::sendJsonResponse(['message' => 'Task not found.'], 404);
        }
    }

    private static function updateTask(int $taskId, int $userId) {
        parse_str(file_get_contents("php://input"), $PUT_VARS);

        $title = $PUT_VARS['title'] ?? null;
        $description = $PUT_VARS['description'] ?? null;
        $taskStatus = $PUT_VARS['status'] ?? null;

        if ((empty($title)) && empty($description) && empty($status)) {
            static::sendJsonResponse(['message' => 'Invalid request'], 400);
        }
        try {
            $taskStatus = $taskStatus ? TaskStatus::fromString((int) $taskStatus) : null;
        } catch (Exception $e) {
            static::sendJsonResponse(['message' => 'invalid task status'], 400);
        }

        // Instantiate required classes and call service method
        $pdo = (new PDO_CONNECTION())->getPDO();
        $repository = new MYSQLRepository($pdo);
        $taskService = new TaskService($repository);
        $task = null;

        try {
            $task = $taskService->updateTask($userId, $taskId, $title, $description, $taskStatus);
        } catch (Exception $e) {
            static::sendJsonResponse(['message' => 'Unable to update task'], 400);
        }

        static::sendJsonResponse(TaskSerializator::serialize($task));
    }

    private static function deleteTask(int $taskId, int $userId) {
        // Instantiate required classes and call service method
        $pdo = (new PDO_CONNECTION())->getPDO();
        $repository = new MYSQLRepository($pdo);
        $taskService = new TaskService($repository);
        $isTaskDeleted = false;

        try {
            $isTaskDeleted = $taskService->deleteTask($userId, $taskId);
        } catch (Exception $e) {
            static::sendJsonResponse(['message' => 'Error deleting task.'], 500);
        }

        if ($isTaskDeleted) {
            static::sendJsonResponse(['message' => "task $taskId was deleted"]);
        } else {
            static::sendJsonResponse(['message' => "task $taskId was not deleted"], 400);
        }
    }
}
