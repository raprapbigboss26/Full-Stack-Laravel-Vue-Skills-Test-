<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\TaskRequest;
use App\Http\Resources\TaskResource;
use App\Services\TaskService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class TaskController extends Controller
{
    protected TaskService $taskService;

    public function __construct(TaskService $taskService)
    {
        $this->taskService = $taskService;
    }

    /**
     * Display a listing of tasks for the authenticated user
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $perPage = $request->get('per_page', 15);
            $status = $request->get('status');
            $priority = $request->get('priority');
            $search = $request->get('search');

            if ($search) {
                $tasks = $this->taskService->searchTasks($search);
                return response()->json([
                    'data' => TaskResource::collection($tasks),
                    'message' => 'Tasks retrieved successfully'
                ]);
            }

            if ($status) {
                $tasks = $this->taskService->getTasksByStatus($status);
                return response()->json([
                    'data' => TaskResource::collection($tasks),
                    'message' => 'Tasks retrieved successfully'
                ]);
            }

            if ($priority) {
                $tasks = $this->taskService->getTasksByPriority($priority);
                return response()->json([
                    'data' => TaskResource::collection($tasks),
                    'message' => 'Tasks retrieved successfully'
                ]);
            }

            $tasks = $this->taskService->getPaginatedTasksForUser(null, $perPage);

            return response()->json([
                'data' => TaskResource::collection($tasks->items()),
                'meta' => [
                    'current_page' => $tasks->currentPage(),
                    'last_page' => $tasks->lastPage(),
                    'per_page' => $tasks->perPage(),
                    'total' => $tasks->total(),
                ],
                'message' => 'Tasks retrieved successfully'
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to retrieve tasks', ['error' => $e->getMessage()]);
            
            return response()->json([
                'message' => 'Failed to retrieve tasks',
                'error' => 'Unable to fetch tasks'
            ], 500);
        }
    }

    /**
     * Store a newly created task
     */
    public function store(TaskRequest $request): JsonResponse
    {
        try {
            $task = $this->taskService->createTask($request->validated());

            Log::info('Task created successfully', ['task_id' => $task->id, 'user_id' => auth()->id()]);

            return response()->json([
                'data' => new TaskResource($task),
                'message' => 'Task created successfully'
            ], 201);

        } catch (\Exception $e) {
            Log::error('Failed to create task', ['error' => $e->getMessage()]);
            
            return response()->json([
                'message' => 'Failed to create task',
                'error' => 'Unable to create task'
            ], 500);
        }
    }

    /**
     * Display the specified task
     */
    public function show(int $id): JsonResponse
    {
        try {
            $task = $this->taskService->getTaskById($id);

            if (!$task) {
                return response()->json([
                    'message' => 'Task not found'
                ], 404);
            }

            return response()->json([
                'data' => new TaskResource($task),
                'message' => 'Task retrieved successfully'
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to retrieve task', ['task_id' => $id, 'error' => $e->getMessage()]);
            
            return response()->json([
                'message' => 'Failed to retrieve task',
                'error' => 'Unable to fetch task'
            ], 500);
        }
    }

    /**
     * Update the specified task
     */
    public function update(TaskRequest $request, int $id): JsonResponse
    {
        try {
            $task = $this->taskService->updateTask($id, $request->validated());

            Log::info('Task updated successfully', ['task_id' => $id, 'user_id' => auth()->id()]);

            return response()->json([
                'data' => new TaskResource($task),
                'message' => 'Task updated successfully'
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to update task', ['task_id' => $id, 'error' => $e->getMessage()]);
            
            if (str_contains($e->getMessage(), 'not found')) {
                return response()->json([
                    'message' => 'Task not found'
                ], 404);
            }

            if (str_contains($e->getMessage(), 'Unauthorized')) {
                return response()->json([
                    'message' => 'Unauthorized to update this task'
                ], 403);
            }
            
            return response()->json([
                'message' => 'Failed to update task',
                'error' => 'Unable to update task'
            ], 500);
        }
    }

    /**
     * Remove the specified task
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            $deleted = $this->taskService->deleteTask($id);

            if (!$deleted) {
                return response()->json([
                    'message' => 'Task not found'
                ], 404);
            }

            Log::info('Task deleted successfully', ['task_id' => $id, 'user_id' => auth()->id()]);

            return response()->json([
                'message' => 'Task deleted successfully'
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to delete task', ['task_id' => $id, 'error' => $e->getMessage()]);
            
            if (str_contains($e->getMessage(), 'not found')) {
                return response()->json([
                    'message' => 'Task not found'
                ], 404);
            }

            if (str_contains($e->getMessage(), 'Unauthorized')) {
                return response()->json([
                    'message' => 'Unauthorized to delete this task'
                ], 403);
            }
            
            return response()->json([
                'message' => 'Failed to delete task',
                'error' => 'Unable to delete task'
            ], 500);
        }
    }

    /**
     * Update task order for drag and drop
     */
    public function updateOrder(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'tasks' => 'required|array',
                'tasks.*.id' => 'required|integer|exists:tasks,id',
                'tasks.*.order' => 'required|integer|min:0',
            ]);

            $success = $this->taskService->reorderTasks($request->input('tasks'));

            if (!$success) {
                return response()->json([
                    'message' => 'Failed to reorder tasks'
                ], 500);
            }

            Log::info('Tasks reordered successfully', ['user_id' => auth()->id()]);

            return response()->json([
                'message' => 'Tasks reordered successfully'
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to reorder tasks', ['error' => $e->getMessage()]);
            
            return response()->json([
                'message' => 'Failed to reorder tasks',
                'error' => 'Unable to update task order'
            ], 500);
        }
    }

    /**
     * Mark task as completed
     */
    public function markCompleted(int $id): JsonResponse
    {
        try {
            $task = $this->taskService->markAsCompleted($id);

            Log::info('Task marked as completed', ['task_id' => $id, 'user_id' => auth()->id()]);

            return response()->json([
                'data' => new TaskResource($task),
                'message' => 'Task marked as completed'
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to mark task as completed', ['task_id' => $id, 'error' => $e->getMessage()]);
            
            if (str_contains($e->getMessage(), 'not found')) {
                return response()->json([
                    'message' => 'Task not found'
                ], 404);
            }

            if (str_contains($e->getMessage(), 'Unauthorized')) {
                return response()->json([
                    'message' => 'Unauthorized to update this task'
                ], 403);
            }
            
            return response()->json([
                'message' => 'Failed to mark task as completed',
                'error' => 'Unable to update task status'
            ], 500);
        }
    }

    /**
     * Mark task as pending
     */
    public function markPending(int $id): JsonResponse
    {
        try {
            $task = $this->taskService->markAsPending($id);

            Log::info('Task marked as pending', ['task_id' => $id, 'user_id' => auth()->id()]);

            return response()->json([
                'data' => new TaskResource($task),
                'message' => 'Task marked as pending'
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to mark task as pending', ['task_id' => $id, 'error' => $e->getMessage()]);
            
            if (str_contains($e->getMessage(), 'not found')) {
                return response()->json([
                    'message' => 'Task not found'
                ], 404);
            }

            if (str_contains($e->getMessage(), 'Unauthorized')) {
                return response()->json([
                    'message' => 'Unauthorized to update this task'
                ], 403);
            }
            
            return response()->json([
                'message' => 'Failed to mark task as pending',
                'error' => 'Unable to update task status'
            ], 500);
        }
    }

    /**
     * Get task statistics for the authenticated user
     */
    public function statistics(): JsonResponse
    {
        try {
            $stats = $this->taskService->getTaskStatistics();

            return response()->json([
                'data' => $stats,
                'message' => 'Task statistics retrieved successfully'
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to retrieve task statistics', ['error' => $e->getMessage()]);
            
            return response()->json([
                'message' => 'Failed to retrieve task statistics',
                'error' => 'Unable to fetch statistics'
            ], 500);
        }
    }
}
