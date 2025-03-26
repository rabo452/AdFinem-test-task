<?php

enum TaskStatus: string
{
    case PENDING = 'pending';
    case IN_PROGRESS = 'in_progress';
    case FINISHED = 'finished';

    // Method to convert a string to the corresponding TaskStatus
    public static function fromString(string $status): TaskStatus
    {
        $status = strtolower($status);
        return match ($status) {
            'pending' => self::PENDING,
            'in_progress' => self::IN_PROGRESS,
            'finished' => self::FINISHED,
            default => throw new InvalidArgumentException("Invalid status: $status"),
        };
    }
}

?>
