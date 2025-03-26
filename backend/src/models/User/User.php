<?php

class User {
    private int $id;
    private string $username;
    private string $password;
    private UserRole $role;

    // Constructor
    public function __construct(int $id, string $username, string $password, UserRole $role) {
        $this->id = $id;
        $this->username = $username;
        $this->password = $password;
        $this->role = $role;
    }

    // Getters for user details
    public function getId(): int {
        return $this->id;
    }

    public function getUsername(): string {
        return $this->username;
    }

    public function getPassword(): string {
        return $this->password;
    }

    public function getRole(): UserRole {
        return $this->role;
    }
}
?>
