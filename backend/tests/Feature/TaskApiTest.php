<?php

namespace Tests\Feature;

use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class TaskApiTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    /**
     * Test user can get their tasks
     */
    public function test_user_can_get_tasks(): void
    {
        Sanctum::actingAs($this->user);

        Task::factory()->count(3)->create(['user_id' => $this->user->id]);

        $response = $this->getJson('/api/tasks');

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'data' => [
                        '*' => [
                            'id',
                            'title',
                            'description',
                            'status',
                            'priority',
                            'order',
                            'created_at',
                            'updated_at'
                        ]
                    ],
                    'meta',
                    'message'
                ]);
    }

    /**
     * Test user can create a task
     */
    public function test_user_can_create_task(): void
    {
        Sanctum::actingAs($this->user);

        $taskData = [
            'title' => 'New Task',
            'description' => 'Task description',
            'priority' => Task::PRIORITY_HIGH,
        ];

        $response = $this->postJson('/api/tasks', $taskData);

        $response->assertStatus(201)
                ->assertJsonStructure([
                    'data' => [
                        'id',
                        'title',
                        'description',
                        'status',
                        'priority',
                        'order'
                    ],
                    'message'
                ]);

        $this->assertDatabaseHas('tasks', [
            'title' => 'New Task',
            'description' => 'Task description',
            'priority' => Task::PRIORITY_HIGH,
            'user_id' => $this->user->id,
        ]);
    }

    /**
     * Test user can update their task
     */
    public function test_user_can_update_task(): void
    {
        Sanctum::actingAs($this->user);

        $task = Task::factory()->create(['user_id' => $this->user->id]);

        $updateData = [
            'title' => 'Updated Task',
            'description' => 'Updated description',
            'priority' => Task::PRIORITY_LOW,
        ];

        $response = $this->putJson("/api/tasks/{$task->id}", $updateData);

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'data' => [
                        'id',
                        'title',
                        'description',
                        'status',
                        'priority'
                    ],
                    'message'
                ]);

        $this->assertDatabaseHas('tasks', [
            'id' => $task->id,
            'title' => 'Updated Task',
            'description' => 'Updated description',
            'priority' => Task::PRIORITY_LOW,
        ]);
    }

    /**
     * Test user can delete their task
     */
    public function test_user_can_delete_task(): void
    {
        Sanctum::actingAs($this->user);

        $task = Task::factory()->create(['user_id' => $this->user->id]);

        $response = $this->deleteJson("/api/tasks/{$task->id}");

        $response->assertStatus(200)
                ->assertJson(['message' => 'Task deleted successfully']);

        $this->assertDatabaseMissing('tasks', ['id' => $task->id]);
    }

    /**
     * Test user can mark task as completed
     */
    public function test_user_can_mark_task_completed(): void
    {
        Sanctum::actingAs($this->user);

        $task = Task::factory()->create([
            'user_id' => $this->user->id,
            'status' => Task::STATUS_PENDING
        ]);

        $response = $this->patchJson("/api/tasks/{$task->id}/complete");

        $response->assertStatus(200)
                ->assertJsonPath('data.status', Task::STATUS_COMPLETED);

        $this->assertDatabaseHas('tasks', [
            'id' => $task->id,
            'status' => Task::STATUS_COMPLETED,
        ]);
    }

    /**
     * Test user can reorder tasks
     */
    public function test_user_can_reorder_tasks(): void
    {
        Sanctum::actingAs($this->user);

        $task1 = Task::factory()->create(['user_id' => $this->user->id, 'order' => 1]);
        $task2 = Task::factory()->create(['user_id' => $this->user->id, 'order' => 2]);

        $reorderData = [
            'tasks' => [
                ['id' => $task1->id, 'order' => 2],
                ['id' => $task2->id, 'order' => 1],
            ]
        ];

        $response = $this->postJson('/api/tasks/reorder', $reorderData);

        $response->assertStatus(200)
                ->assertJson(['message' => 'Tasks reordered successfully']);

        $this->assertDatabaseHas('tasks', ['id' => $task1->id, 'order' => 2]);
        $this->assertDatabaseHas('tasks', ['id' => $task2->id, 'order' => 1]);
    }

    /**
     * Test user cannot access other user's tasks
     */
    public function test_user_cannot_access_other_users_tasks(): void
    {
        Sanctum::actingAs($this->user);

        $otherUser = User::factory()->create();
        $otherTask = Task::factory()->create(['user_id' => $otherUser->id]);

        $response = $this->getJson("/api/tasks/{$otherTask->id}");

        $response->assertStatus(404);
    }

    /**
     * Test unauthenticated user cannot access tasks
     */
    public function test_unauthenticated_user_cannot_access_tasks(): void
    {
        $response = $this->getJson('/api/tasks');

        $response->assertStatus(401);
    }

    /**
     * Test task creation validation
     */
    public function test_task_creation_validation(): void
    {
        Sanctum::actingAs($this->user);

        $response = $this->postJson('/api/tasks', []);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['title']);
    }

    /**
     * Test task statistics endpoint
     */
    public function test_user_can_get_task_statistics(): void
    {
        Sanctum::actingAs($this->user);

        Task::factory()->create(['user_id' => $this->user->id, 'status' => Task::STATUS_PENDING]);
        Task::factory()->create(['user_id' => $this->user->id, 'status' => Task::STATUS_COMPLETED]);

        $response = $this->getJson('/api/tasks/statistics');

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'data' => [
                        'total',
                        'completed',
                        'pending',
                        'completion_rate',
                        'by_priority'
                    ],
                    'message'
                ]);
    }
}
