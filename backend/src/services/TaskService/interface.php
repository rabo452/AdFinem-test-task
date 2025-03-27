<?php 

require_once BASE_DIR . 'models/Task/Task.php';
require_once BASE_DIR . 'models/Task/TaskStatus.php';

// Define the interface for the repository
interface TaskServiceRepositoryI {
    public function getAllTasks(): array;
    public function getAllUserTask(int $userId): array;
    
    public function getTaskById(int $taskId): ?Task;
    public function isUserTaskOwner(int $taskId, int $userId): bool;
    
    public function createTask(string $title, string $description, TaskStatus $status, int $userId): bool;
    public function updateTask(int $taskId, ?string $title, ?string $description, ?TaskStatus $status): bool;
    public function deleteTask(int $taskId): bool;

    public function isAdmin(int $userId): bool;
}