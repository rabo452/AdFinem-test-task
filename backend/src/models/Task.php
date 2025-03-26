<?php

require_once BASE_DIR . 'models/TaskStatus.php';

class Task {
    private string $title;
    private string $description;
    private TaskStatus $status;

    // Constructor (private so it can't be directly instantiated from outside)
    private function __construct(string $title, string $description, TaskStatus $status) {
        $this->title = $title;
        $this->description = $description;
        $this->status = $status;
    }

    // Getters for the properties
    public function getTitle(): string {
        return $this->title;
    }

    public function getDescription(): string {
        return $this->description;
    }

    public function getStatus(): TaskStatus {
        return $this->status;
    }
}

// Builder Class
class TaskBuilder {
    private string $title;
    private string $description;
    private TaskStatus $status;

    // Constructor that can optionally take an existing Task object to copy values from
    public function __construct(?Task $task) {
        if ($task) {
            // Copy values from the provided Task object
            $this->title = $task->getTitle();
            $this->description = $task->getDescription();
            $this->status = $task->getStatus();
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
        if (!isset($this->title) || !isset($this->description) || !isset($this->status)) {
            throw new Exception("Title, description, and status must be set before building.");
        }

        return new Task($this->title, $this->description, $this->status);
    }
}
