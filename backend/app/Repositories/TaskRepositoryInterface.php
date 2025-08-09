<?php

namespace App\Repositories;

use App\Models\Task;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

interface TaskRepositoryInterface
{
    /**
     * Get all tasks for a user
     */
    public function getAllForUser(int $userId): Collection;

    /**
     * Get paginated tasks for a user
     */
    public function getPaginatedForUser(int $userId, int $perPage = 15): LengthAwarePaginator;

    /**
     * Get tasks by status for a user
     */
    public function getByStatusForUser(int $userId, string $status): Collection;

    /**
     * Get tasks by priority for a user
     */
    public function getByPriorityForUser(int $userId, string $priority): Collection;

    /**
     * Create a new task
     */
    public function create(array $data): Task;

    /**
     * Update a task
     */
    public function update(int $id, array $data): Task;

    /**
     * Delete a task
     */
    public function delete(int $id): bool;

    /**
     * Find task by ID
     */
    public function findById(int $id): ?Task;

    /**
     * Find task by ID for a specific user
     */
    public function findByIdForUser(int $id, int $userId): ?Task;

    /**
     * Update task order
     */
    public function updateOrder(int $id, int $order): bool;

    /**
     * Get tasks older than specified days
     */
    public function getTasksOlderThan(int $days): Collection;

    /**
     * Search tasks by title or description
     */
    public function searchForUser(int $userId, string $query): Collection;

    /**
     * Get all tasks for admin
     */
    public function getAllForAdmin(): LengthAwarePaginator;

    /**
     * Get all tasks for admin with filters
     */
    public function getAllForAdminWithFilters(array $filters): LengthAwarePaginator;

    /**
     * Get task statistics for a user
     */
    public function getStatisticsForUser(int $userId): array;

    /**
     * Get task statistics for all users (admin)
     */
    public function getAllUsersStatistics(): array;
}
