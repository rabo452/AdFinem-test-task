<?php

require_once BASE_DIR . 'models/Task/Task.php';
require_once BASE_DIR . 'models/Task/TaskStatus.php';
require_once BASE_DIR . 'models/User/User.php';
require_once BASE_DIR . 'models/User/UserRole.php';
require_once BASE_DIR . 'services/TaskService/interface.php';
require_once BASE_DIR . 'services/UserService/interface.php';

class MYSQLRepository implements TaskServiceRepositoryI, UserServiceRepositoryI {
    private $pdo;

    // Constructor to initialize PDO connection
    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    // Get all tasks (admin can get all, else only user tasks)
    public function getAllTasks(): array {
        $query = "SELECT id, title, description, status, user_id FROM tasks";
        $stmt = $this->pdo->query($query);
        
        $tasks = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $tasks[] = new Task(
                $row['id'], 
                $row['title'], 
                $row['description'], 
                TaskStatus::fromString((int) $row['status']),
                $row['user_id']
            );
        }
        
        return $tasks;
    }

    // Get all tasks for a specific user
    public function getAllUserTask(int $userId): array {
        $query = "SELECT id, title, description, status, user_id FROM tasks WHERE user_id = :user_id";
        $stmt = $this->pdo->prepare($query);
        $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
        $stmt->execute();
        
        $tasks = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $tasks[] = new Task(
                $row['id'], 
                $row['title'], 
                $row['description'], 
                TaskStatus::fromString($row['status']),
                $row['user_id']
            );
        }
        
        return $tasks;
    }

    // Get task by ID
    public function getTaskById(int $taskId): ?Task {
        $query = "SELECT id, title, description, status, user_id FROM tasks WHERE id = :task_id";
        $stmt = $this->pdo->prepare($query);
        $stmt->bindParam(':task_id', $taskId, PDO::PARAM_INT);
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row) {
            return new Task(
                $row['id'], 
                $row['title'], 
                $row['description'], 
                TaskStatus::fromString((int) $row['status']),
                $row['user_id']
            );
        }

        return null; // Task not found
    }

    // Check if user is the owner of the task
    public function isUserTaskOwner(int $taskId, int $userId): bool {
        $query = "SELECT user_id FROM tasks WHERE id = :task_id";
        $stmt = $this->pdo->prepare($query);
        $stmt->bindParam(':task_id', $taskId, PDO::PARAM_INT);
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row && (int) $row['user_id'] === $userId;
    }

    // Create a task
    public function createTask(string $title, string $description, TaskStatus $status, int $userId): Task {
        $query = "INSERT INTO tasks (title, description, status, user_id) VALUES (:title, :description, :status, :user_id)";
        $stmt = $this->pdo->prepare($query);
        $stmt->bindParam(':title', $title);
        $stmt->bindParam(':description', $description);
        $statusVal = (int) $status->value;
        $stmt->bindParam(':status', $statusVal, PDO::PARAM_INT);
        $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);

        $stmt->execute();
        $id = $this->pdo->lastInsertId();

        return new Task($id, $title, $description, $status, $userId);
    }

    // Update task
    public function updateTask(int $taskId, ?string $title, ?string $description, ?TaskStatus $status): bool {
        $query = 'UPDATE tasks SET';
        $parameters = [
            ...(isset($title) ? [[ 'key' => 'title', 'value' => $title]] : []),
            ...(isset($description) ? [[ 'key' => 'description', 'value' => $description]] : []),
            ...(isset($status) ? [[ 'key' => 'status', 'value' => $status->value]] : []),
        ];

        $updateSQL = implode(',', array_map(fn($param) => $param['key'] . " = :" . $param['key'], $parameters));
        $query = "UPDATE tasks SET ". $updateSQL ." WHERE id = :task_id";

        $stmt = $this->pdo->prepare($query);
        $stmt->bindParam(':task_id', $taskId, PDO::PARAM_INT);

        
        foreach($parameters as $param) {
            $stmt->bindParam(':' . $param['key'], $param['value']);
        }

        return (bool) $stmt->execute();
    }

    // Delete task
    public function deleteTask(int $taskId): bool {
        $query = "DELETE FROM tasks WHERE id = :task_id";
        $stmt = $this->pdo->prepare($query);
        $stmt->bindParam(':task_id', $taskId, PDO::PARAM_INT);
        
        return $stmt->execute();
    }

    // Check if user is admin
    public function isAdmin(int $userId): bool {
        $query = "SELECT r.title AS role FROM users u LEFT JOIN roles r ON u.role_id = r.id WHERE u.id = :user_id";
        $stmt = $this->pdo->prepare($query);
        $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row && $row['role'] === 'admin';
    }

    // Create a user
    public function createUser(string $username, string $password, UserRole $role): User {
        $query = "INSERT INTO users (username, password, role_id) VALUES (:username, :password, :role_id)";
        $stmt = $this->pdo->prepare($query);
        $stmt->bindParam(':username', $username);
        $stmt->bindParam(':password', $password);
        $roleVal = (int) $role->value;
        $stmt->bindParam(':role_id', $roleVal, PDO::PARAM_INT);

        $stmt->execute();
        $id = $this->pdo->lastInsertId(); // Get the last inserted ID
        
        return new User($id, $username, $password, $role);
    }

    // Get user by username
    public function getUserByUsername(string $username): ?User {
        $query = "SELECT id, username, password, role_id FROM users WHERE username = :username";
        $stmt = $this->pdo->prepare($query);
        $stmt->bindParam(':username', $username);
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row) {
            return new User(
                $row['id'],
                $row['username'],
                $row['password'],
                UserRole::fromInt((int) $row['role_id'])
            );
        }

        return null; // User not found
    }

    // Get user by ID
    private function getUserById(int $userId): ?User {
        $query = "SELECT id, username, password, role_id FROM users WHERE id = :user_id";
        $stmt = $this->pdo->prepare($query);
        $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row) {
            return new User(
                $row['id'],
                $row['username'],
                $row['password'],
                UserRole::fromInt($row['role_id'])
            );
        }

        return null;
    }
}