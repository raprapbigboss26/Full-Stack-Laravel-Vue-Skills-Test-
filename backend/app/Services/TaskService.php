<?php

namespace App\Services;

use App\Models\Task;
use App\Repositories\TaskRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class TaskService
{
    protected TaskRepositoryInterface $taskRepository;

    public function __construct(TaskRepositoryInterface $taskRepository)
    {
        $this->taskRepository = $taskRepository;
    }

    /**
     * Get all tasks for the authenticated user
     */
    public function getAllTasksForUser(?int $userId = null): Collection
    {
        $userId = $userId ?? Auth::id();
        return $this->taskRepository->getAllForUser($userId);
    }

    /**
     * Get paginated tasks for the authenticated user
     */
    public function getPaginatedTasksForUser(?int $userId = null, int $perPage = 15): LengthAwarePaginator
    {
        $userId = $userId ?? Auth::id();
        return $this->taskRepository->getPaginatedForUser($userId, $perPage);
    }

    /**
     * Get tasks by status for the authenticated user
     */
    public function getTasksByStatus(string $status, ?int $userId = null): Collection
    {
        $userId = $userId ?? Auth::id();
        return $this->taskRepository->getByStatusForUser($userId, $status);
    }

    /**
     * Get tasks by priority for the authenticated user
     */
    public function getTasksByPriority(string $priority, ?int $userId = null): Collection
    {
        $userId = $userId ?? Auth::id();
        return $this->taskRepository->getByPriorityForUser($userId, $priority);
    }

    /**
     * Create a new task
     */
    public function createTask(array $data): Task
    {
        $data['user_id'] = $data['user_id'] ?? Auth::id();
        
        // Validate status and priority
        $this->validateTaskData($data);
        
        return $this->taskRepository->create($data);
    }

    /**
     * Update a task
     */
    public function updateTask(int $id, array $data): Task
    {
        $task = $this->taskRepository->findById($id);
        
        if (!$task) {
            throw new \Exception('Task not found');
        }

        // Check if user owns the task or is admin
        if (!Auth::user()->isAdmin() && $task->user_id !== Auth::id()) {
            throw new \Exception('Unauthorized to update this task');
        }

        // Validate status and priority if provided
        if (isset($data['status']) || isset($data['priority'])) {
            $this->validateTaskData($data);
        }

        return $this->taskRepository->update($id, $data);
    }

    /**
     * Delete a task
     */
    public function deleteTask(int $id): bool
    {
        $task = $this->taskRepository->findById($id);
        
        if (!$task) {
            throw new \Exception('Task not found');
        }

        // Check if user owns the task or is admin
        if (!Auth::user()->isAdmin() && $task->user_id !== Auth::id()) {
            throw new \Exception('Unauthorized to delete this task');
        }

        return $this->taskRepository->delete($id);
    }

    /**
     * Get a task by ID
     */
    public function getTaskById(int $id): ?Task
    {
        $task = $this->taskRepository->findById($id);
        
        if (!$task) {
            return null;
        }

        // Check if user owns the task or is admin
        if (!Auth::user()->isAdmin() && $task->user_id !== Auth::id()) {
            return null; // Return null instead of throwing exception for 404 response
        }

        return $task;
    }

    /**
     * Update task order for drag and drop
     */
    public function updateTaskOrder(int $id, int $order): bool
    {
        $task = $this->taskRepository->findById($id);
        
        if (!$task) {
            throw new \Exception('Task not found');
        }

        // Check if user owns the task
        if ($task->user_id !== Auth::id()) {
            throw new \Exception('Unauthorized to reorder this task');
        }

        return $this->taskRepository->updateOrder($id, $order);
    }

    /**
     * Reorder multiple tasks
     */
    public function reorderTasks(array $taskOrders): bool
    {
        try {
            foreach ($taskOrders as $taskOrder) {
                $this->updateTaskOrder($taskOrder['id'], $taskOrder['order']);
            }
            return true;
        } catch (\Exception $e) {
            Log::error('Failed to reorder tasks: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Search tasks
     */
    public function searchTasks(string $query, ?int $userId = null): Collection
    {
        $userId = $userId ?? Auth::id();
        return $this->taskRepository->searchForUser($userId, $query);
    }

    /**
     * Get task statistics for user
     */
    public function getTaskStatistics(?int $userId = null): array
    {
        $userId = $userId ?? Auth::id();
        return $this->taskRepository->getStatisticsForUser($userId);
    }

    /**
     * Mark task as completed
     */
    public function markAsCompleted(int $id): Task
    {
        return $this->updateTask($id, ['status' => Task::STATUS_COMPLETED]);
    }

    /**
     * Mark task as pending
     */
    public function markAsPending(int $id): Task
    {
        return $this->updateTask($id, ['status' => Task::STATUS_PENDING]);
    }

    /**
     * Get all tasks for admin
     */
    public function getAllTasksForAdmin(): LengthAwarePaginator
    {
        if (!Auth::user()->isAdmin()) {
            throw new \Exception('Unauthorized access');
        }

        return $this->taskRepository->getAllForAdmin();
    }

    /**
     * Get all tasks for admin with filters
     */
    public function getAllTasksForAdminWithFilters(array $filters): LengthAwarePaginator
    {
        if (!Auth::user()->isAdmin()) {
            throw new \Exception('Unauthorized access');
        }

        return $this->taskRepository->getAllForAdminWithFilters($filters);
    }

    /**
     * Get all users statistics for admin
     */
    public function getAllUsersStatistics(): array
    {
        if (!Auth::user()->isAdmin()) {
            throw new \Exception('Unauthorized access');
        }

        return $this->taskRepository->getAllUsersStatistics();
    }

    /**
     * Clean up old tasks (for scheduled job)
     */
    public function cleanupOldTasks(int $days = 30): int
    {
        $oldTasks = $this->taskRepository->getTasksOlderThan($days);
        $deletedCount = 0;

        foreach ($oldTasks as $task) {
            if ($this->taskRepository->delete($task->id)) {
                $deletedCount++;
                Log::info("Deleted old task: {$task->id} - {$task->title}");
            }
        }

        Log::info("Cleanup completed. Deleted {$deletedCount} old tasks.");
        return $deletedCount;
    }

    /**
     * Validate task data
     */
    private function validateTaskData(array $data): void
    {
        if (isset($data['status']) && !in_array($data['status'], [Task::STATUS_PENDING, Task::STATUS_COMPLETED])) {
            throw new \Exception('Invalid task status');
        }

        if (isset($data['priority']) && !in_array($data['priority'], [Task::PRIORITY_LOW, Task::PRIORITY_MEDIUM, Task::PRIORITY_HIGH])) {
            throw new \Exception('Invalid task priority');
        }
    }
}
