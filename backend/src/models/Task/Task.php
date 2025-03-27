<?php

require_once BASE_DIR . 'models/Task/TaskStatus.php';

class Task {
    private int $id;
    private string $title;
    private string $description;
    private TaskStatus $status;
    private int $user_id;

    // Constructor (private so it can't be directly instantiated from outside)
    public function __construct(int $id, string $title, string $description, TaskStatus $status, int $user_id) {
        $this->id = $id;
        $this->title = $title;
        $this->description = $description;
        $this->status = $status;
        $this->user_id = $user_id;
    }

    // Getters for the properties
    public function getId(): int {
        return $this->id;
    }

    public function getTitle(): string {
        return $this->title;
    }

    public function getDescription(): string {
        return $this->description;
    }

    public function getStatus(): TaskStatus {
        return $this->status;
    }

    public function getUserId(): int {
        return $this->user_id;
    }
}

// Builder Class
class TaskBuilder {
    private int $id;
    private string $title;
    private string $description;
    private TaskStatus $status;
    private int $user_id;

    // Constructor that can optionally take an existing Task object to copy values from
    public function __construct(?Task $task = null) {
        if ($task) {
            // Copy values from the provided Task object
            $this->id = $task->getId();
            $this->title = $task->getTitle();
            $this->description = $task->getDescription();
            $this->status = $task->getStatus();
            $this->user_id = $task->getUserId();
        }
    }

    // Set the title for the task
    public function setTitle(string $title): self {
        $this->title = $title;
        return $this;
    }

    // Set the description for the task
    public function setDescription(string $description): self {
        $this->description = $description;
        return $this;
    }

    // Set the status for the task
    public function setStatus(TaskStatus $status): self {
        $this->status = $status;
        return $this;
    }

    // Build and return the Task object
    public function build(): Task {
        // Ensure all required fields are set
        if (!isset($this->id) || !isset($this->title) || !isset($this->description) || !isset($this->status) || !isset($this->user_id)) {
            throw new Exception("Id, title, description, status, and user_id must be set before building.");
        }

        return new Task($this->id, $this->title, $this->description, $this->status, $this->user_id);
    }
}
