<?php

enum TaskStatus: string
{
    case OPEN = 'open';
    case PENDING = 'pending';
    case FINISHED = 'finished';

    // Method to convert a string to the corresponding TaskStatus
    public static function fromString(string $status): TaskStatus
    {
        return match ($status) {
            'open' => self::OPEN,
            'pending' => self::PENDING,
            'finished' => self::FINISHED,
            default => throw new InvalidArgumentException("Invalid status: $status"),
        };
    }
}

?>
