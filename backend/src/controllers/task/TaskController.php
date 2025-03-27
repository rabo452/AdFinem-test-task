<?php 

require_once BASE_DIR . 'controllers/BaseController.php';
require_once BASE_DIR . 'repositories/MYSQLRepository.php';
require_once BASE_DIR . 'services/TaskService/TaskService.php';

class TaskController extends BaseController {
    protected static string $prefix = "tasks";

    public static function executePath(string $path): void
    {
        echo "hello world";
    }

    private static function getAllTasks(): void {

    } 
}