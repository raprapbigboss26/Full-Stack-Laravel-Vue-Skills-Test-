<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "Users and their tasks:\n";

$users = App\Models\User::with('tasks')->get();

foreach ($users as $user) {
    echo $user->name . ' (' . $user->email . '): ' . $user->tasks->count() . " tasks\n";
    foreach ($user->tasks as $task) {
        echo "  - " . $task->title . " (" . $task->status . ")\n";
    }
    echo "\n";
}

echo "Total users: " . App\Models\User::count() . "\n";
echo "Total tasks: " . App\Models\Task::count() . "\n";
