<?php

namespace App\Repositories;

use App\Models\Task;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class TaskRepository implements TaskRepositoryInterface
{
    /**
     * Get all tasks for a user
     */
    public function getAllForUser(int $userId): Collection
    {
        return Cache::remember("user_{$userId}_tasks", 300, function () use ($userId) {
            return Task::where('user_id', $userId)
                ->ordered()
                ->get();
        });
    }

    /**
     * Get paginated tasks for a user
     */
    public function getPaginatedForUser(int $userId, int $perPage = 15): LengthAwarePaginator
    {
        return Task::where('user_id', $userId)
            ->ordered()
            ->paginate($perPage);
    }

    /**
     * Get tasks by status for a user
     */
    public function getByStatusForUser(int $userId, string $status): Collection
    {
        return Task::where('user_id', $userId)
            ->byStatus($status)
            ->ordered()
            ->get();
    }

    /**
     * Get tasks by priority for a user
     */
    public function getByPriorityForUser(int $userId, string $priority): Collection
    {
        return Task::where('user_id', $userId)
            ->byPriority($priority)
            ->ordered()
            ->get();
    }

    /**
     * Create a new task
     */
    public function create(array $data): Task
    {
        // Get the next order number for this user
        $maxOrder = Task::where('user_id', $data['user_id'])->max('order') ?? 0;
        $data['order'] = $maxOrder + 1;

        $task = Task::create($data);
        
        // Clear cache
        Cache::forget("user_{$data['user_id']}_tasks");
        
        return $task;
    }

    /**
     * Update a task
     */
    public function update(int $id, array $data): Task
    {
        $task = Task::findOrFail($id);
        $task->update($data);
        
        // Clear cache
        Cache::forget("user_{$task->user_id}_tasks");
        
        return $task->fresh();
    }

    /**
     * Delete a task
     */
    public function delete(int $id): bool
    {
        $task = Task::findOrFail($id);
        $userId = $task->user_id;
        
        $result = $task->delete();
        
        // Clear cache
        Cache::forget("user_{$userId}_tasks");
        
        return $result;
    }

    /**
     * Find task by ID
     */
    public function findById(int $id): ?Task
    {
        return Task::find($id);
    }

    /**
     * Find task by ID for a specific user
     */
    public function findByIdForUser(int $id, int $userId): ?Task
    {
        return Task::where('id', $id)
            ->where('user_id', $userId)
            ->first();
    }

    /**
     * Update task order
     */
    public function updateOrder(int $id, int $order): bool
    {
        $task = Task::findOrFail($id);
        $result = $task->update(['order' => $order]);
        
        // Clear cache
        Cache::forget("user_{$task->user_id}_tasks");
        
        return $result;
    }

    /**
     * Get tasks older than specified days
     */
    public function getTasksOlderThan(int $days): Collection
    {
        return Task::where('created_at', '<', now()->subDays($days))->get();
    }

    /**
     * Search tasks by title or description
     */
    public function searchForUser(int $userId, string $query): Collection
    {
        return Task::where('user_id', $userId)
            ->where(function ($q) use ($query) {
                $q->where('title', 'LIKE', "%{$query}%")
                  ->orWhere('description', 'LIKE', "%{$query}%");
            })
            ->ordered()
            ->get();
    }

    /**
     * Get all tasks for admin
     */
    public function getAllForAdmin(): LengthAwarePaginator
    {
        return Task::with('user')
            ->orderBy('created_at', 'desc')
            ->paginate(20);
    }

    /**
     * Get all tasks for admin with filters
     */
    public function getAllForAdminWithFilters(array $filters): LengthAwarePaginator
    {
        $query = Task::with('user');

        // Apply search filter
        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Apply status filter
        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        // Apply priority filter
        if (!empty($filters['priority'])) {
            $query->where('priority', $filters['priority']);
        }

        // Apply user filter
        if (!empty($filters['user_id'])) {
            $query->where('user_id', $filters['user_id']);
        }

        // Apply pagination
        $perPage = $filters['per_page'] ?? 15;

        return $query->orderBy('created_at', 'desc')->paginate($perPage);
    }

    /**
     * Get task statistics for a user
     */
    public function getStatisticsForUser(int $userId): array
    {
        return Cache::remember("user_{$userId}_stats", 300, function () use ($userId) {
            $total = Task::where('user_id', $userId)->count();
            $completed = Task::where('user_id', $userId)->completed()->count();
            $pending = Task::where('user_id', $userId)->pending()->count();
            
            $priorityStats = Task::where('user_id', $userId)
                ->select('priority', DB::raw('count(*) as count'))
                ->groupBy('priority')
                ->pluck('count', 'priority')
                ->toArray();

            return [
                'total' => $total,
                'completed' => $completed,
                'pending' => $pending,
                'completion_rate' => $total > 0 ? round(($completed / $total) * 100, 2) : 0,
                'by_priority' => [
                    'low' => $priorityStats['low'] ?? 0,
                    'medium' => $priorityStats['medium'] ?? 0,
                    'high' => $priorityStats['high'] ?? 0,
                ],
            ];
        });
    }

    /**
     * Get task statistics for all users (admin)
     */
    public function getAllUsersStatistics(): array
    {
        return Cache::remember('all_users_stats', 300, function () {
            $users = User::with(['tasks' => function ($query) {
                $query->select('user_id', 'status', 'priority');
            }])->get();

            return $users->map(function ($user) {
                $tasks = $user->tasks;
                $total = $tasks->count();
                $completed = $tasks->where('status', Task::STATUS_COMPLETED)->count();
                $pending = $tasks->where('status', Task::STATUS_PENDING)->count();

                return [
                    'user_id' => $user->id,
                    'user_name' => $user->name,
                    'user_email' => $user->email,
                    'total_tasks' => $total,
                    'completed_tasks' => $completed,
                    'pending_tasks' => $pending,
                    'completion_rate' => $total > 0 ? round(($completed / $total) * 100, 2) : 0,
                ];
            })->toArray();
        });
    }
}
