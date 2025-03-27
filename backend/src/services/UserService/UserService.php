<?php 

require_once BASE_DIR . 'models/User/User.php';
require_once BASE_DIR . 'models/User/UserRole.php';
require_once BASE_DIR . 'services/UserService/interface.php';

class UserService {
    private UserServiceRepositoryI $repository;

    public function __construct(UserServiceRepositoryI $repository) {
        $this->repository = $repository;
    }

    public function createUser(string $username, string $password, UserRole $role): User {
        if (empty($this->repository->getUserByUsername($username))) {
            return $this->repository->createUser($username, static::hashPassword($password), $role);
        }

        throw new Exception("user $username already exists!");
    }

    public function getUser(string $username, string $password): ?User {
        $user = $this->repository->getUserByUsername($username);

        return isset($user) && $user->getPassword() === static::hashPassword($password)
            ? $user
            : null; 
    }

    private static function hashPassword(string $password) {
        return hash('sha256', $password);
    }
}