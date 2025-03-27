<?php

// Including necessary model and service files
require_once BASE_DIR . 'models/Task/Task.php'; // Task model
require_once BASE_DIR . 'models/Task/TaskStatus.php'; // Task status model
require_once BASE_DIR . 'models/User/User.php'; // User model
require_once BASE_DIR . 'models/User/UserRole.php'; // User role model
require_once BASE_DIR . 'services/TaskService/interface.php'; // Task service interface
require_once BASE_DIR . 'services/UserService/interface.php'; // User service interface

// MYSQLRepository class implements TaskServiceRepositoryI and UserServiceRepositoryI interfaces
class MYSQLRepository implements TaskServiceRepositoryI, UserServiceRepositoryI {
    private $pdo; // PDO instance for database interaction

    // Constructor to initialize PDO connection
    public function __construct($pdo) {
        $this->pdo = $pdo; // Assigning the PDO object to the class property
    }

    // Get all tasks (admin can get all tasks, others get their own tasks)
    public function getAllTasks(): array {
        // SQL query to select all tasks
        $query = "SELECT id, title, description, status, user_id FROM tasks";
        $stmt = $this->pdo->query($query); // Execute query
        
        $tasks = [];
        // Fetch all tasks and convert each row to a Task object
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $tasks[] = new Task(
                $row['id'], 
                $row['title'], 
                $row['description'], 
                TaskStatus::fromString((int) $row['status']), // Convert status string to TaskStatus enum
                $row['user_id']
            );
        }
        
        return $tasks; // Return array of tasks
    }

    // Get all tasks assigned to a specific user
    public function getAllUserTask(int $userId): array {
        // SQL query to select tasks by user ID
        $query = "SELECT id, title, description, status, user_id FROM tasks WHERE user_id = :user_id";
        $stmt = $this->pdo->prepare($query); // Prepare the query
        $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT); // Bind the user ID
        $stmt->execute(); // Execute the query
        
        $tasks = [];
        // Fetch user-specific tasks and convert each row to a Task object
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $tasks[] = new Task(
                $row['id'], 
                $row['title'], 
                $row['description'], 
                TaskStatus::fromString($row['status']),
                $row['user_id']
            );
        }
        
        return $tasks; // Return array of user tasks
    }

    // Get a task by its ID
    public function getTaskById(int $taskId): ?Task {
        // SQL query to select a task by its ID
        $query = "SELECT id, title, description, status, user_id FROM tasks WHERE id = :task_id";
        $stmt = $this->pdo->prepare($query); // Prepare the query
        $stmt->bindParam(':task_id', $taskId, PDO::PARAM_INT); // Bind the task ID
        $stmt->execute(); // Execute the query

        // Fetch the task and return it as a Task object
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

        return null; // Return null if task is not found
    }

    // Check if a user is the owner of a task
    public function isUserTaskOwner(int $taskId, int $userId): bool {
        // SQL query to select the user ID of the task
        $query = "SELECT user_id FROM tasks WHERE id = :task_id";
        $stmt = $this->pdo->prepare($query); // Prepare the query
        $stmt->bindParam(':task_id', $taskId, PDO::PARAM_INT); // Bind the task ID
        $stmt->execute(); // Execute the query

        // Fetch the result and check if the user ID matches the task owner
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row && (int) $row['user_id'] === $userId; // Return true if user is the owner
    }

    // Create a new task
    public function createTask(string $title, string $description, TaskStatus $status, int $userId): Task {
        // SQL query to insert a new task
        $query = "INSERT INTO tasks (title, description, status, user_id) VALUES (:title, :description, :status, :user_id)";
        $stmt = $this->pdo->prepare($query); // Prepare the query
        $stmt->bindParam(':title', $title); // Bind the task title
        $stmt->bindParam(':description', $description); // Bind the task description
        $statusVal = (int) $status->value; // Convert TaskStatus enum to integer value
        $stmt->bindParam(':status', $statusVal, PDO::PARAM_INT); // Bind the task status
        $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT); // Bind the user ID

        $stmt->execute(); // Execute the insert query
        $id = $this->pdo->lastInsertId(); // Get the last inserted ID

        // Return a new Task object with the inserted data
        return new Task($id, $title, $description, $status, $userId);
    }

    // Update an existing task
    public function updateTask(int $taskId, ?string $title, ?string $description, ?TaskStatus $status): bool {
        // SQL query to update task fields (only provided fields are updated)
        $query = 'UPDATE tasks SET';
        $parameters = [
            ...(isset($title) ? [[ 'key' => 'title', 'value' => $title]] : []),
            ...(isset($description) ? [[ 'key' => 'description', 'value' => $description]] : []),
            ...(isset($status) ? [[ 'key' => 'status', 'value' => $status->value]] : []),
        ];

        // Generate dynamic SQL for updating only specified fields
        $updateSQL = implode(',', array_map(fn($param) => $param['key'] . " = :" . $param['key'], $parameters));
        $query = "UPDATE tasks SET ". $updateSQL ." WHERE id = :task_id";

        $stmt = $this->pdo->prepare($query); // Prepare the update query
        $stmt->bindParam(':task_id', $taskId, PDO::PARAM_INT); // Bind the task ID
        
        // Bind the values for each parameter
        foreach($parameters as $param) {
            $stmt->bindParam(':' . $param['key'], $param['value']);
        }

        return (bool) $stmt->execute(); // Execute the query and return success status
    }

    // Delete a task by its ID
    public function deleteTask(int $taskId): bool {
        // SQL query to delete a task by its ID
        $query = "DELETE FROM tasks WHERE id = :task_id";
        $stmt = $this->pdo->prepare($query); // Prepare the delete query
        $stmt->bindParam(':task_id', $taskId, PDO::PARAM_INT); // Bind the task ID
        
        return $stmt->execute(); // Execute the delete query and return success status
    }

    // Check if a user is an admin
    public function isAdmin(int $userId): bool {
        // SQL query to get the user's role (admin or other)
        $query = "SELECT r.title AS role FROM users u LEFT JOIN roles r ON u.role_id = r.id WHERE u.id = :user_id";
        $stmt = $this->pdo->prepare($query); // Prepare the query
        $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT); // Bind the user ID
        $stmt->execute(); // Execute the query

        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row && $row['role'] === 'admin'; // Return true if user is an admin
    }

    // Create a new user
    public function createUser(string $username, string $password, UserRole $role): User {
        // SQL query to insert a new user
        $query = "INSERT INTO users (username, password, role_id) VALUES (:username, :password, :role_id)";
        $stmt = $this->pdo->prepare($query); // Prepare the query
        $stmt->bindParam(':username', $username); // Bind the username
        $stmt->bindParam(':password', $password); // Bind the password
        $roleVal = (int) $role->value; // Convert UserRole enum to integer value
        $stmt->bindParam(':role_id', $roleVal, PDO::PARAM_INT); // Bind the user role

        $stmt->execute(); // Execute the insert query
        $id = $this->pdo->lastInsertId(); // Get the last inserted ID
        
        // Return a new User object with the inserted data
        return new User($id, $username, $password, $role);
    }

    // Get a user by their username
    public function getUserByUsername(string $username): ?User {
        // SQL query to select a user by username
        $query = "SELECT id, username, password, role_id FROM users WHERE username = :username";
        $stmt = $this->pdo->prepare($query); // Prepare the query
        $stmt->bindParam(':username', $username); // Bind the username
        $stmt->execute(); // Execute the query

        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row) {
            // Return the user as a User object
            return new User(
                $row['id'],
                $row['username'],
                $row['password'],
                UserRole::fromInt((int) $row['role_id'])
            );
        }

        return null; // Return null if user not found
    }

    // Get a user by their ID
    private function getUserById(int $userId): ?User {
        // SQL query to select a user by ID
        $query = "SELECT id, username, password, role_id FROM users WHERE id = :user_id";
        $stmt = $this->pdo->prepare($query); // Prepare the query
        $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT); // Bind the user ID
        $stmt->execute(); // Execute the query

        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row) {
            // Return the user as a User object
            return new User(
                $row['id'],
                $row['username'],
                $row['password'],
                UserRole::fromInt($row['role_id'])
            );
        }

        return null; // Return null if user not found
    }
}