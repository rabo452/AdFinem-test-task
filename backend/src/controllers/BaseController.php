<?php

// Abstract base class to provide common functionality for controllers
abstract class BaseController {
    // Static property to store the prefix used in paths for each controller (e.g., "tasks")
    protected static string $prefix = "";
    
    // Static property to store the available actions (paths) mapped to methods for the controller
    protected static array $actions = [];

    // Protected method to check if the provided path matches the controller's prefix
    // Used in child classes to validate incoming request paths
    public static function doesPathMatch(string $path): bool {
        // Returns true if the path starts with the controller's prefix
        return strpos($path, '/' . static::$prefix) === 0;
    }

    // Method to remove the controller's prefix from a path
    // This is useful for handling routes by removing the controller's prefix and accessing the path
    public static function deletePrefix(string $path): string {
        // Replaces the prefix part in the path and returns the remaining path
        return str_replace(['/' . static::$prefix . '/', '/' . static::$prefix], '', $path);
    }

    // Main method to execute the appropriate action based on the provided path
    // If the path exists in the actions array, the corresponding method is called
    public static function executePath(string $path) {
        // Check if the path exists in the actions array
        if (array_key_exists($path, static::$actions)) {
            // Call the method associated with the path
            static::{static::$actions[$path]}();
        } else {
            // If no matching path is found, terminate with an error message
            die('Path not found');
        }
    }

    // Helper method to send a JSON response with a status code
    // This is used to return standardized responses from the controller
    protected static function sendJsonResponse(array $data, int $statusCode = 200): void {
        // Set the response header to indicate content type as JSON
        header('Content-Type: application/json');
        
        // Set the HTTP status code for the response
        http_response_code($statusCode);
        
        // Encode the provided data as JSON and output it
        echo json_encode($data);
        
        // Exit after sending the response to avoid further processing
        exit;
    }
}