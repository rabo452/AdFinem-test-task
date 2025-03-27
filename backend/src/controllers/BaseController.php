<?php

abstract class BaseController {
    protected static string $prefix = "";
    protected static array $actions = [];

    // Protected method to allow access in child classes
    public static function doesPathMatch(string $path): bool {
        return strpos($path, '/' . static::$prefix) === 0;
    }

    public static function deletePrefix(string $path): string {
        return str_replace(['/' . static::$prefix . '/', '/' . static::$prefix], '', $path);
    }

    public static function executePath(string $path) {
        if (array_key_exists($path, static::$actions)) {
            static::{static::$actions[$path]}();
        } else {
            die('Path not found');
        }
    }

    // Helper method to send a JSON response with status code
    protected static function sendJsonResponse(array $data, int $statusCode = 200): void {
        header('Content-Type: application/json');
        http_response_code($statusCode);
        echo json_encode($data);
        exit;
    }
}
