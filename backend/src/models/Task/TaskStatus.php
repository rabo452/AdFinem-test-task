<?php

enum TaskStatus: int
{
    case PENDING = 1;
    case IN_PROGRESS = 2;
    case FINISHED = 3;

    // Method to convert a string to the corresponding TaskStatus
    public static function fromString(int $statusCode): TaskStatus
    {
        return match ($statusCode) {
            1 => self::PENDING,
            2 => self::IN_PROGRESS,
            3 => self::FINISHED,
            default => throw new InvalidArgumentException("Invalid status: $statusCode"),
        };
    }
}

?>
