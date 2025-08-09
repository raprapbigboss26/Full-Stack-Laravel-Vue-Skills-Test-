<?php

namespace Tests\Unit;

use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TaskTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test task creation
     */
    public function test_task_can_be_created(): void
    {
        $user = User::factory()->create();
        
        $task = Task::create([
            'title' => 'Test Task',
            'description' => 'This is a test task',
            'status' => Task::STATUS_PENDING,
            'priority' => Task::PRIORITY_MEDIUM,
            'order' => 1,
            'user_id' => $user->id,
        ]);

        $this->assertInstanceOf(Task::class, $task);
        $this->assertEquals('Test Task', $task->title);
        $this->assertEquals('This is a test task', $task->description);
        $this->assertEquals(Task::STATUS_PENDING, $task->status);
        $this->assertEquals(Task::PRIORITY_MEDIUM, $task->priority);
        $this->assertEquals(1, $task->order);
        $this->assertEquals($user->id, $task->user_id);
    }

    /**
     * Test task belongs to user relationship
     */
    public function test_task_belongs_to_user(): void
    {
        $user = User::factory()->create();
        $task = Task::factory()->create(['user_id' => $user->id]);

        $this->assertInstanceOf(User::class, $task->user);
        $this->assertEquals($user->id, $task->user->id);
    }

    /**
     * Test task status scopes
     */
    public function test_task_status_scopes(): void
    {
        $user = User::factory()->create();
        
        Task::factory()->create([
            'user_id' => $user->id,
            'status' => Task::STATUS_PENDING
        ]);
        
        Task::factory()->create([
            'user_id' => $user->id,
            'status' => Task::STATUS_COMPLETED
        ]);

        $this->assertEquals(1, Task::pending()->count());
        $this->assertEquals(1, Task::completed()->count());
    }

    /**
     * Test task priority scopes
     */
    public function test_task_priority_scopes(): void
    {
        $user = User::factory()->create();
        
        Task::factory()->create([
            'user_id' => $user->id,
            'priority' => Task::PRIORITY_HIGH
        ]);
        
        Task::factory()->create([
            'user_id' => $user->id,
            'priority' => Task::PRIORITY_MEDIUM
        ]);
        
        Task::factory()->create([
            'user_id' => $user->id,
            'priority' => Task::PRIORITY_LOW
        ]);

        $this->assertEquals(1, Task::highPriority()->count());
        $this->assertEquals(1, Task::mediumPriority()->count());
        $this->assertEquals(1, Task::lowPriority()->count());
    }

    /**
     * Test task validation constants
     */
    public function test_task_constants(): void
    {
        $this->assertEquals('pending', Task::STATUS_PENDING);
        $this->assertEquals('completed', Task::STATUS_COMPLETED);
        $this->assertEquals('high', Task::PRIORITY_HIGH);
        $this->assertEquals('medium', Task::PRIORITY_MEDIUM);
        $this->assertEquals('low', Task::PRIORITY_LOW);
    }

    /**
     * Test task fillable attributes
     */
    public function test_task_fillable_attributes(): void
    {
        $task = new Task();
        $fillable = $task->getFillable();

        $expectedFillable = [
            'title',
            'description',
            'status',
            'priority',
            'order',
            'user_id'
        ];

        $this->assertEquals($expectedFillable, $fillable);
    }

    /**
     * Test task casts
     */
    public function test_task_casts(): void
    {
        $user = User::factory()->create();
        $task = Task::factory()->create(['user_id' => $user->id]);

        $this->assertIsInt($task->order);
        $this->assertIsInt($task->user_id);
    }
}
