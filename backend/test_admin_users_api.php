<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "Testing Admin Users API Response:\n\n";

// Simulate the AdminController users method
$users = App\Models\User::with(['tasks' => function ($query) {
    $query->select('id', 'user_id', 'status', 'priority', 'created_at');
}])->get();

echo "Raw data from database:\n";
foreach ($users as $user) {
    echo "User: " . $user->name . "\n";
    echo "Tasks loaded: " . ($user->relationLoaded('tasks') ? 'Yes' : 'No') . "\n";
    echo "Tasks count: " . $user->tasks->count() . "\n";
    echo "Completed: " . $user->tasks->where('status', 'completed')->count() . "\n";
    echo "Pending: " . $user->tasks->where('status', 'pending')->count() . "\n";
    echo "\n";
}

// Test UserResource with admin route simulation
echo "Testing UserResource with admin route:\n";

// Create a mock request that simulates admin route
$request = new \Illuminate\Http\Request();
$request->setRouteResolver(function () {
    $route = new \Illuminate\Routing\Route(['GET'], '/api/admin/users', []);
    $route->name('admin.users');
    return $route;
});

foreach ($users as $user) {
    $resource = new App\Http\Resources\UserResource($user);
    $resourceArray = $resource->toArray($request);
    
    echo "User: " . $user->name . "\n";
    echo "Route is admin: " . ($request->routeIs('admin.*') ? 'Yes' : 'No') . "\n";
    echo "Has include_stats: " . ($request->has('include_stats') ? 'Yes' : 'No') . "\n";
    echo "tasks_count: " . ($resourceArray['tasks_count'] ?? 'NOT SET') . "\n";
    echo "completed_tasks_count: " . ($resourceArray['completed_tasks_count'] ?? 'NOT SET') . "\n";
    echo "pending_tasks_count: " . ($resourceArray['pending_tasks_count'] ?? 'NOT SET') . "\n";
    echo "\n";
}
