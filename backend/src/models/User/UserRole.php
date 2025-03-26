<?php

enum UserRole: int {
    case ADMIN = 1;
    case PARTICIPANT = 2;

    // Get the title of the role
    public function getTitle(): string {
        return match ($this) {
            self::ADMIN => 'admin',
            self::PARTICIPANT => 'participant',
        };
    }

    // Get role from an integer value
    public static function fromInt(int $value): self {
        return match ($value) {
            1 => self::ADMIN,
            2 => self::PARTICIPANT,
            default => throw new InvalidArgumentException("Invalid role ID: $value"),
        };
    }
}