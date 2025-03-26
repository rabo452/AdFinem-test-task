<?php

class Serializator {
    // Serializes the User object into an associative array
    public static function serialize(User $user): array {
        return [
            'id' => $user->getId(),
            'username' => $user->getUsername(),
            'role' => $user->getRole()->getTitle()
        ];
    }
}
?>
