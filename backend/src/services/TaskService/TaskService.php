<?php 

require_once BASE_DIR . 'models/Task/Task.php';
require_once BASE_DIR . 'models/Task/TaskStatus.php';
require_once BASE_DIR . 'services/TaskService/interface.php';

class TaskService {
    private TaskServiceRepositoryI $repository;

    // Constructor that initializes the repository dependency
    public function __construct(TaskServiceRepositoryI $repository) {
        $this->repository = $repository;
    }
    
    // Get all tasks for the user (or all tasks if user is an admin)
    public function getAllTasks(int $userId): array {
        if ($this->repository->isAdmin($userId)) {
            return $this->repository->getAllTasks();
        }

        return $this->repository->getAllUserTask($userId);
    }

    // Create a new task
    public function createTask(string $title, string $description, TaskStatus $status, int $userId): bool {
        return $this->repository->createTask($title, $description, $status, $userId);
    }

    // Get a task by ID (if user is the owner or an admin)
    public function getTask(int $taskId, int $userId): ?Task {
        if ($this->repository->isAdmin($userId) || $this->repository->isUserTaskOwner($taskId, $userId)) {
            return $this->repository->getTaskById($taskId);
        }

        return null;
    }

    // Update a task (if user is the owner or an admin)
    public function updateTask(int $userId, int $taskId, ?string $title, ?string $description, ?TaskStatus $status): bool {
        if ($this->repository->isAdmin($userId) || $this->repository->isUserTaskOwner($taskId, $userId)) {
            return $this->repository->updateTask($taskId, $title, $description, $status);
        }

        return false;
    }

    // Delete a task (if user is the owner or an admin)
    public function deleteTask(int $userId, int $taskId): bool {
        if ($this->repository->isAdmin($userId) || $this->repository->isUserTaskOwner($taskId, $userId)) {
            return $this->repository->deleteTask($taskId);
        }

        return false;
    }
}
