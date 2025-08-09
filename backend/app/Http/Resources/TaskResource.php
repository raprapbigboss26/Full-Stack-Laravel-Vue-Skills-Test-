<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TaskResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'status' => $this->status,
            'priority' => $this->priority,
            'order' => $this->order,
            'user_id' => $this->user_id,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            
            // Include user information when requested (for admin views)
            'user' => new UserResource($this->whenLoaded('user')),
            
            // Additional computed fields
            'is_completed' => $this->isCompleted(),
            'priority_color' => $this->getPriorityColor(),
            'status_label' => $this->getStatusLabel(),
        ];
    }

    /**
     * Get priority color for frontend styling
     */
    private function getPriorityColor(): string
    {
        return match($this->priority) {
            'low' => 'green',
            'medium' => 'yellow',
            'high' => 'red',
            default => 'gray',
        };
    }

    /**
     * Get human-readable status label
     */
    private function getStatusLabel(): string
    {
        return match($this->status) {
            'pending' => 'Pending',
            'completed' => 'Completed',
            default => 'Unknown',
        };
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
