<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\TaskResource;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Services\TaskService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AdminController extends Controller
{
    protected TaskService $taskService;

    public function __construct(TaskService $taskService)
    {
        $this->taskService = $taskService;
    }

    /**
     * Get admin dashboard data
     */
    public function dashboard(): JsonResponse
    {
        try {
            $usersStats = $this->taskService->getAllUsersStatistics();
            $totalUsers = User::count();
            $totalTasks = \App\Models\Task::count();
            $completedTasks = \App\Models\Task::completed()->count();
            $pendingTasks = \App\Models\Task::pending()->count();

            return response()->json([
                'data' => [
                    'overview' => [
                        'total_users' => $totalUsers,
                        'total_tasks' => $totalTasks,
                        'completed_tasks' => $completedTasks,
                        'pending_tasks' => $pendingTasks,
                        'completion_rate' => $totalTasks > 0 ? round(($completedTasks / $totalTasks) * 100, 2) : 0,
                    ],
                    'users_statistics' => $usersStats,
                ],
                'message' => 'Dashboard data retrieved successfully'
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to retrieve dashboard data', ['error' => $e->getMessage()]);
            
            return response()->json([
                'message' => 'Failed to retrieve dashboard data',
                'error' => 'Unable to fetch dashboard information'
            ], 500);
        }
    }

    /**
     * Get all users with their task statistics
     */
    public function users(Request $request): JsonResponse
    {
        try {
            $perPage = $request->get('per_page', 15);
            
            $users = User::with(['tasks' => function ($query) {
                $query->select('id', 'user_id', 'status', 'priority', 'created_at');
            }])->paginate($perPage);

            return response()->json([
                'data' => UserResource::collection($users->items()),
                'meta' => [
                    'current_page' => $users->currentPage(),
                    'last_page' => $users->lastPage(),
                    'per_page' => $users->perPage(),
                    'total' => $users->total(),
                ],
                'message' => 'Users retrieved successfully'
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to retrieve users', ['error' => $e->getMessage()]);
            
            return response()->json([
                'message' => 'Failed to retrieve users',
                'error' => 'Unable to fetch users'
            ], 500);
        }
    }

    /**
     * Get all tasks for admin view
     */
    public function tasks(Request $request): JsonResponse
    {
        try {
            $filters = [
                'search' => $request->get('search'),
                'status' => $request->get('status'),
                'priority' => $request->get('priority'),
                'user_id' => $request->get('user_id'),
                'page' => $request->get('page', 1),
                'per_page' => $request->get('per_page', 15),
            ];

            $tasks = $this->taskService->getAllTasksForAdminWithFilters($filters);

            return response()->json([
                'data' => TaskResource::collection($tasks->items()),
                'current_page' => $tasks->currentPage(),
                'last_page' => $tasks->lastPage(),
                'per_page' => $tasks->perPage(),
                'total' => $tasks->total(),
                'from' => $tasks->firstItem(),
                'to' => $tasks->lastItem(),
                'message' => 'Tasks retrieved successfully'
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to retrieve admin tasks', ['error' => $e->getMessage()]);
            
            return response()->json([
                'message' => 'Failed to retrieve tasks',
                'error' => 'Unable to fetch tasks'
            ], 500);
        }
    }

    /**
     * Get specific user with their tasks
     */
    public function userTasks(int $userId): JsonResponse
    {
        try {
            $user = User::with('tasks')->findOrFail($userId);

            return response()->json([
                'data' => [
                    'user' => new UserResource($user),
                    'tasks' => TaskResource::collection($user->tasks),
                    'statistics' => $this->taskService->getTaskStatistics($userId),
                ],
                'message' => 'User tasks retrieved successfully'
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to retrieve user tasks', ['user_id' => $userId, 'error' => $e->getMessage()]);
            
            if (str_contains($e->getMessage(), 'No query results')) {
                return response()->json([
                    'message' => 'User not found'
                ], 404);
            }
            
            return response()->json([
                'message' => 'Failed to retrieve user tasks',
                'error' => 'Unable to fetch user tasks'
            ], 500);
        }
    }

    /**
     * Delete any task (admin privilege)
     */
    public function deleteTask(int $taskId): JsonResponse
    {
        try {
            $task = \App\Models\Task::findOrFail($taskId);
            $task->delete();

            Log::info('Admin deleted task', ['task_id' => $taskId, 'admin_id' => auth()->id()]);

            return response()->json([
                'message' => 'Task deleted successfully'
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to delete task as admin', ['task_id' => $taskId, 'error' => $e->getMessage()]);
            
            if (str_contains($e->getMessage(), 'No query results')) {
                return response()->json([
                    'message' => 'Task not found'
                ], 404);
            }
            
            return response()->json([
                'message' => 'Failed to delete task',
                'error' => 'Unable to delete task'
            ], 500);
        }
    }

    /**
     * Update user admin status
     */
    public function updateUserRole(Request $request, int $userId): JsonResponse
    {
        try {
            $request->validate([
                'is_admin' => 'required|boolean',
            ]);

            $user = User::findOrFail($userId);
            $user->update(['is_admin' => $request->input('is_admin')]);

            Log::info('Admin updated user role', [
                'user_id' => $userId,
                'is_admin' => $request->input('is_admin'),
                'admin_id' => auth()->id()
            ]);

            return response()->json([
                'data' => new UserResource($user),
                'message' => 'User role updated successfully'
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to update user role', ['user_id' => $userId, 'error' => $e->getMessage()]);
            
            if (str_contains($e->getMessage(), 'No query results')) {
                return response()->json([
                    'message' => 'User not found'
                ], 404);
            }
            
            return response()->json([
                'message' => 'Failed to update user role',
                'error' => 'Unable to update user'
            ], 500);
        }
    }

    /**
     * Get system statistics
     */
    public function statistics(): JsonResponse
    {
        try {
            $stats = [
                'users' => [
                    'total' => User::count(),
                    'admins' => User::where('is_admin', true)->count(),
                    'regular_users' => User::where('is_admin', false)->count(),
                ],
                'tasks' => [
                    'total' => \App\Models\Task::count(),
                    'completed' => \App\Models\Task::completed()->count(),
                    'pending' => \App\Models\Task::pending()->count(),
                    'by_priority' => [
                        'high' => \App\Models\Task::where('priority', 'high')->count(),
                        'medium' => \App\Models\Task::where('priority', 'medium')->count(),
                        'low' => \App\Models\Task::where('priority', 'low')->count(),
                    ],
                ],
                'recent_activity' => [
                    'tasks_created_today' => \App\Models\Task::whereDate('created_at', today())->count(),
                    'tasks_completed_today' => \App\Models\Task::completed()->whereDate('updated_at', today())->count(),
                    'new_users_today' => User::whereDate('created_at', today())->count(),
                ],
            ];

            return response()->json([
                'data' => $stats,
                'message' => 'System statistics retrieved successfully'
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to retrieve system statistics', ['error' => $e->getMessage()]);
            
            return response()->json([
                'message' => 'Failed to retrieve system statistics',
                'error' => 'Unable to fetch statistics'
            ], 500);
        }
    }
}
