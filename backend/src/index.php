<?php

define('BASE_DIR', __DIR__ . '/');

require_once BASE_DIR . 'config.php';
require_once BASE_DIR . 'controllers/task/TaskController.php';

$path = $_SERVER['REQUEST_URI'];
$controllers = [TaskController::class];

try {
    $isControllerFound = false;

    foreach ($controllers as $controller) {
        if ($controller::doesPathMatch($path)) {
            $controller::executePath($path);
            $isControllerFound = true;
            break;
        }
    }

    if (!$isControllerFound) {
        die("Page not found.");
    }
} catch(Exception $e) {
    if ($IS_DEV) {
        throw $e;
    }
    http_response_code(500);
}

?>
