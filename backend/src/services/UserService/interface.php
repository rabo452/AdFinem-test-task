<?php 

require_once BASE_DIR . 'models/User/User.php';
require_once BASE_DIR . 'models/User/UserRole.php';

interface UserServiceRepositoryI {
    public function createUser(string $username, string $password, UserRole $role): User;
    public function getUserByUsername(string $username): ?User;
}