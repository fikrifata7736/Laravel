<?php

namespace App\Console\Commands;

use App\Services\TaskService;
use Illuminate\Console\Command;

class CheckOverdueTasks extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tasks:check-overdue';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check for overdue tasks and log them';

    /**
     * Execute the console command.
     */
    public function handle(TaskService $taskService)
    {
        $count = $taskService->checkOverdueTasks();

        $this->info("Found {$count} overdue tasks and logged them.");

        return Command::SUCCESS;
    }
}
