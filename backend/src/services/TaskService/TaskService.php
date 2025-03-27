<?php 

// Include necessary model and interface dependencies
require_once BASE_DIR . 'models/Task/Task.php';
require_once BASE_DIR . 'models/Task/TaskStatus.php';
require_once BASE_DIR . 'services/TaskService/interface.php';

// TaskService class handles task-related operations and business logic
class TaskService {
    // Dependency injection of the repository interface for task-related database operations
    private TaskServiceRepositoryI $repository;

    // Constructor to initialize the repository dependency
    public function __construct(TaskServiceRepositoryI $repository) {
        $this->repository = $repository;
    }
    
    // Method to get all tasks for a specific user, or all tasks if the user is an admin
    public function getAllTasks(int $userId): array {
        // If the user is an admin, return all tasks
        if ($this->repository->isAdmin($userId)) {
            return $this->repository->getAllTasks();
        }

        // Otherwise, return only the tasks assigned to the user
        return $this->repository->getAllUserTask($userId);
    }

    // Method to create a new task with provided details (title, description, status, and user ID)
    public function createTask(string $title, string $description, TaskStatus $status, int $userId): Task {
        // Call the repository to persist the new task and return the task object
        return $this->repository->createTask($title, $description, $status, $userId);
    }

    // Method to retrieve a task by its ID, ensuring that the user is either an admin or the task owner
    public function getTask(int $taskId, int $userId): ?Task {
        // Check if the user is an admin or the task owner
        if ($this->repository->isAdmin($userId) || $this->repository->isUserTaskOwner($taskId, $userId)) {
            // If authorized, return the task by ID
            return $this->repository->getTaskById($taskId);
        }

        // If the user is not authorized, return null
        return null;
    }

    // Method to update an existing task (title, description, and status) if the user is the owner or an admin
    public function updateTask(int $userId, int $taskId, ?string $title, ?string $description, ?TaskStatus $status): Task {
        // Check if the user is authorized to update the task (admin or task owner)
        if ($this->repository->isAdmin($userId) || $this->repository->isUserTaskOwner($taskId, $userId)) {
            // Attempt to update the task through the repository
            $result = $this->repository->updateTask($taskId, $title, $description, $status);
            
            // If the update fails, throw an exception
            if (!$result) {
                throw new Exception("unable to update task");
            }

            // Return the updated task object
            return $this->repository->getTaskById($taskId);
        }

        // If not authorized, throw an exception
        throw new Exception("unable to update task");
    }

    // Method to delete a task if the user is the task owner or an admin
    public function deleteTask(int $userId, int $taskId): bool {
        // Check if the user is authorized to delete the task (admin or task owner)
        if ($this->repository->isAdmin($userId) || $this->repository->isUserTaskOwner($taskId, $userId)) {
            // If authorized, call the repository to delete the task and return the result
            return $this->repository->deleteTask($taskId);
        }

        // If not authorized, return false
        return false;
    }
}
