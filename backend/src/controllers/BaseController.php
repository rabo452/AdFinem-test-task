<?php

abstract class BaseController {
    protected static string $prefix = "";

    // Protected method to allow access in child classes
    public static function doesPathMatch(string $path): bool {
        return strpos($path, '/' . static::$prefix) === 0;
    }

    abstract public static function executePath(string $path): void;
}
