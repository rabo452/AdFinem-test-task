<?php

require_once BASE_DIR . 'models/Task.php';
require_once BASE_DIR . 'models/TaskStatus.php';

class MYSQLRepository {
    private $pdo;

    // Constructor to initialize PDO connection
    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    public function getAllTasks() {
        $query = "SELECT id, title, description, status FROM tasks";
        $stmt = $this->pdo->query($query);
        
        $tasks = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $tasks[] = new Task(
                $row['title'], 
                $row['description'], 
                TaskStatus::fromString($row['status'])
            );
        }
        
        return $tasks;
    }

    public function getTaskById(int $id) {
        $query = "SELECT id, title, description, status FROM tasks WHERE id = :id";
        $stmt = $this->pdo->prepare($query);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row) {
            return new Task(
                $row['title'], 
                $row['description'], 
                TaskStatus::fromString($row['status'])
            );
        }

        return null; // Task not found
    }

    public function createTask(string $title, string $description = '', TaskStatus $status) {
        $query = "INSERT INTO tasks (title, description, status) VALUES (:title, :description, :status)";
        $stmt = $this->pdo->prepare($query);
        $stmt->bindParam(':title', $title);
        $stmt->bindParam(':description', $description);
        $stmt->bindParam(':status', $status->value);

        $stmt->execute();
        $id = $this->pdo->lastInsertId(); // Get the last inserted ID

        return $this->getTaskById((int)$id);
    }

    public function updateTask(int $id, string $title, string $description = '', TaskStatus $status) {
        $query = "UPDATE tasks SET title = :title, description = :description, status = :status WHERE id = :id";
        $stmt = $this->pdo->prepare($query);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->bindParam(':title', $title);
        $stmt->bindParam(':description', $description);
        $stmt->bindParam(':status', $status->value);

        $stmt->execute();

        return $this->getTaskById($id); // Return updated task
    }
    
    public function deleteTask(int $id) {
        $query = "DELETE FROM tasks WHERE id = :id";
        $stmt = $this->pdo->prepare($query);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        
        $stmt->execute();

        // Return confirmation message
        return ["message" => "Task with ID {$id} has been deleted."];
    }
}
?>
