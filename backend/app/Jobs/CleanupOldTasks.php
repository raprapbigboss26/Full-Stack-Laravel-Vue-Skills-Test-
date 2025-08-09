<?php

namespace App\Jobs;

use App\Services\TaskService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class CleanupOldTasks implements ShouldQueue
{
    use Queueable;

    protected int $daysOld;

    /**
     * Create a new job instance.
     */
    public function __construct(int $daysOld = 30)
    {
        $this->daysOld = $daysOld;
    }

    /**
     * Execute the job.
     */
    public function handle(TaskService $taskService): void
    {
        try {
            Log::info('Starting cleanup of old tasks', ['days_old' => $this->daysOld]);
            
            $deletedCount = $taskService->cleanupOldTasks($this->daysOld);
            
            Log::info('Cleanup job completed successfully', [
                'deleted_count' => $deletedCount,
                'days_old' => $this->daysOld
            ]);
            
        } catch (\Exception $e) {
            Log::error('Cleanup job failed', [
                'error' => $e->getMessage(),
                'days_old' => $this->daysOld
            ]);
            
            throw $e; // Re-throw to mark job as failed
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('Cleanup job failed permanently', [
            'error' => $exception->getMessage(),
            'days_old' => $this->daysOld
        ]);
    }
}
