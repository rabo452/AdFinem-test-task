<?php 

class TaskSerializator {
    public static function serialize(Task $task): array {
        // Return an associative array with task properties
        return [
            'id' => $task->getId(),
            'user_id' => $task->getUserId(),
            'title' => $task->getTitle(),
            'description' => $task->getDescription(),
            'status' => $task->getStatus()->value,
        ];
    }
}
