<?php

namespace App\Console\Commands;

use App\Jobs\CleanupOldTasks;
use Illuminate\Console\Command;

class CleanupOldTasksCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tasks:cleanup {--days=30 : Number of days old tasks to delete}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clean up tasks older than specified number of days';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $days = (int) $this->option('days');
        
        $this->info("Starting cleanup of tasks older than {$days} days...");
        
        // Dispatch the cleanup job
        CleanupOldTasks::dispatch($days);
        
        $this->info('Cleanup job has been dispatched successfully.');
        
        return Command::SUCCESS;
    }
}
