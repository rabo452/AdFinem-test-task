<?php

// set the base directory for absolute imports
define('BASE_DIR', __DIR__ . '/');

require_once BASE_DIR . 'Config.php';
require_once BASE_DIR . 'controllers/task/TaskController.php';
require_once BASE_DIR . 'controllers/auth/AuthController.php';
require_once BASE_DIR . 'models/Task/TaskStatus.php';

$path = $_SERVER['REQUEST_URI'];
$controllers = [TaskController::class, AuthController::class];

try {
    $isControllerFound = false;

    foreach ($controllers as $controller) {
        if ($controller::doesPathMatch($path)) {
            $path = $controller::deletePrefix($path);
            $controller::executePath($path);
            $isControllerFound = true;
            break;
        }
    }

    if (!$isControllerFound) {
        die("Page not found.");
    }
} catch(Exception $e) {
    // in development show the error
    if ((new Config())->isDev()) {
        throw $e;
    }
    // the nginx will give the error html page to the user
    http_response_code(500);
}