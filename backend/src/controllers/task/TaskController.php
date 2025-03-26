<?php 

require_once BASE_DIR . 'controllers/BaseController.php';

class TaskController extends BaseController {
    protected static string $prefix = "tasks";

    public static function executePath(string $path): void
    {
        echo "hello world";
    }
}