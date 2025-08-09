<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $data = [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'is_admin' => $this->is_admin,
            'email_verified_at' => $this->email_verified_at,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            
            // Include tasks when requested
            'tasks' => TaskResource::collection($this->whenLoaded('tasks')),
        ];

        // Add task statistics for admin routes or when explicitly requested
        $isAdminRoute = $request->routeIs('admin.*') || 
                       str_contains($request->path(), 'admin') || 
                       $request->has('include_stats');
        
        if ($isAdminRoute) {
            // Use loaded relationship if available, otherwise query
            if ($this->relationLoaded('tasks')) {
                $tasks = $this->tasks;
                $totalTasks = $tasks->count();
                $completedTasks = $tasks->where('status', 'completed')->count();
                $pendingTasks = $tasks->where('status', 'pending')->count();
            } else {
                $totalTasks = $this->tasks()->count();
                $completedTasks = $this->tasks()->where('status', 'completed')->count();
                $pendingTasks = $this->tasks()->where('status', 'pending')->count();
            }

            $data['tasks_count'] = $totalTasks;
            $data['completed_tasks_count'] = $completedTasks;
            $data['pending_tasks_count'] = $pendingTasks;
            
            // Also include nested format for compatibility
            $data['task_statistics'] = [
                'total_tasks' => $totalTasks,
                'completed_tasks' => $completedTasks,
                'pending_tasks' => $pendingTasks,
            ];
        }

        return $data;
    }

    /**
     * Get additional data that should be returned with the resource array.
     *
     * @return array<string, mixed>
     */
    public function with(Request $request): array
    {
        return [
            'meta' => [
                'version' => '1.0',
                'timestamp' => now()->toISOString(),
            ],
        ];
    }
}
