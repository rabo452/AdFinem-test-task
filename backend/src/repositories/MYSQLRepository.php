<?php

require_once BASE_DIR . 'models/Task/Task.php';
require_once BASE_DIR . 'models/Task/TaskStatus.php';
require_once BASE_DIR . 'models/User/User.php';
require_once BASE_DIR . 'models/User/UserRole.php';

class MYSQLRepository {
    private $pdo;

    // Constructor to initialize PDO connection
    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    // Get all tasks
    public function getAllTasks(): array {
        $query = "SELECT id, title, description, status, user_id FROM tasks";
        $stmt = $this->pdo->query($query);
        
        $tasks = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $tasks[] = new Task(
                $row['title'], 
                $row['description'], 
                TaskStatus::fromString($row['status']),
                $row['user_id'] ? $this->getUserById($row['user_id']) : null
            );
        }
        
        return $tasks;
    }

    // Get task by ID
    public function getTaskById(int $id): ?Task {
        $query = "SELECT id, title, description, status, user_id FROM tasks WHERE id = :id";
        $stmt = $this->pdo->prepare($query);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row) {
            return new Task(
                $row['title'], 
                $row['description'], 
                TaskStatus::fromString($row['status']),
                $row['user_id'] ? $this->getUserById($row['user_id']) : null
            );
        }

        return null; // Task not found
    }

    // Create a new task
    public function createTask(string $title, string $description = '', TaskStatus $status, User $user): Task {
        $query = "INSERT INTO tasks (title, description, status, user_id) VALUES (:title, :description, :status, :user_id)";
        $stmt = $this->pdo->prepare($query);
        $stmt->bindParam(':title', $title);
        $stmt->bindParam(':description', $description);
        $stmt->bindParam(':status', $status->value);
        $stmt->bindParam(':user_id', $user->getId(), PDO::PARAM_INT);

        $stmt->execute();
        $id = $this->pdo->lastInsertId(); // Get the last inserted ID

        return $this->getTaskById((int)$id);
    }

    // Update an existing task
    public function updateTask(int $id, string $title, string $description = '', TaskStatus $status, ?User $user = null): Task {
        $query = "UPDATE tasks SET title = :title, description = :description, status = :status, user_id = :user_id WHERE id = :id";
        $stmt = $this->pdo->prepare($query);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->bindParam(':title', $title);
        $stmt->bindParam(':description', $description);
        $stmt->bindParam(':status', $status->value);
        $stmt->bindParam(':user_id', $user ? $user->getId() : null, PDO::PARAM_INT);

        $stmt->execute();

        return $this->getTaskById($id); // Return updated task
    }

    // Delete a task
    public function deleteTask(int $taskId, int $userId): void {
        // Check if the user is an admin or the task owner
        $query = "
            SELECT 
                t.user_id, r.title AS role
            FROM 
                tasks t
            LEFT JOIN users u ON u.id = t.user_id
            LEFT JOIN roles r ON r.id = u.role_id
            WHERE 
                t.id = :task_id";
        
        $stmt = $this->pdo->prepare($query);
        $stmt->bindParam(':task_id', $taskId, PDO::PARAM_INT);
        $stmt->execute();

        $task = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($task) {
            // Check if user is the task owner or admin
            if ($task['user_id'] === $userId || $task['role'] === 'admin') {
                // User is allowed to delete the task, proceed with the deletion
                $deleteQuery = "DELETE FROM tasks WHERE id = :task_id";
                $deleteStmt = $this->pdo->prepare($deleteQuery);
                $deleteStmt->bindParam(':task_id', $taskId, PDO::PARAM_INT);
                $deleteStmt->execute();
            } else {
                throw new Exception("You do not have permission to delete this task.");
            }
        }
    }

    // Create a user
    public function createUser(string $username, string $password, UserRole $role): User {
        $query = "INSERT INTO users (username, password, role_id) VALUES (:username, :password, :role_id)";
        $stmt = $this->pdo->prepare($query);
        $hashedPassword = hash('sha256', $password);  // Ensure password is hashed with SHA256
        $stmt->bindParam(':username', $username);
        $stmt->bindParam(':password', $hashedPassword);
        $stmt->bindParam(':role_id', $role->value, PDO::PARAM_INT);

        $stmt->execute();
        $id = $this->pdo->lastInsertId(); // Get the last inserted ID

        return $this->getUserById((int)$id);
    }

    // Get user by ID
    public function getUserById(int $id): ?User {
        $query = "SELECT id, username, password, role_id FROM users WHERE id = :id";
        $stmt = $this->pdo->prepare($query);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
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

        return null; // User not found
    }
}