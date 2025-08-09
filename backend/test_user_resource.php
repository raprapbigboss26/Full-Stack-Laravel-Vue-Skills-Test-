<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "Testing UserResource output:\n\n";

// Create a mock request that simulates admin route
$request = new \Illuminate\Http\Request();
$request->setRouteResolver(function () {
    $route = new \Illuminate\Routing\Route(['GET'], '/api/admin/users', []);
    $route->name('admin.users');
    return $route;
});

// Get users with tasks loaded
$users = App\Models\User::with('tasks')->get();

foreach ($users as $user) {
    echo "User: " . $user->name . "\n";
    echo "Tasks loaded: " . ($user->relationLoaded('tasks') ? 'Yes' : 'No') . "\n";
    echo "Tasks count: " . $user->tasks->count() . "\n";
    
    // Test UserResource
    $resource = new App\Http\Resources\UserResource($user);
    $resourceArray = $resource->toArray($request);
    
    echo "Resource output:\n";
    echo "- tasks_count: " . ($resourceArray['tasks_count'] ?? 'NOT SET') . "\n";
    echo "- completed_tasks_count: " . ($resourceArray['completed_tasks_count'] ?? 'NOT SET') . "\n";
    echo "- pending_tasks_count: " . ($resourceArray['pending_tasks_count'] ?? 'NOT SET') . "\n";
    echo "- task_statistics: " . (isset($resourceArray['task_statistics']) ? json_encode($resourceArray['task_statistics']) : 'NOT SET') . "\n";
    echo "\n";
}
