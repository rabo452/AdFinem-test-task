<?php 

class TaskSerializator {
    public static function serialize(Task $task): array {
        // Return an associative array with task properties
        return [
            'title' => $task->getTitle(),
            'description' => $task->getDescription(),
            'status' => strtolower($task->getStatus()->value),
        ];
    }
}
