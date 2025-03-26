<?php 

require_once BASE_DIR . 'controllers/BaseController.php';

class AuthController extends BaseController {
    protected static string $prefix = "auth";

    public static function executePath(string $path): void
    {
        echo "auth";
    }
}