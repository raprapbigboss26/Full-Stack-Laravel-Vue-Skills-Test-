<?php

use App\Http\Controllers\Api\AdminController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\TaskController;
use App\Http\Middleware\CheckAdmin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Public routes (no authentication required)
Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
});

// Protected routes (authentication required)
Route::middleware('auth:sanctum')->group(function () {
    
    // Authentication routes
    Route::prefix('auth')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::get('/user', [AuthController::class, 'user']);
        Route::post('/refresh', [AuthController::class, 'refresh']);
    });

    // Task management routes
    Route::prefix('tasks')->group(function () {
        Route::get('/', [TaskController::class, 'index']);
        Route::post('/', [TaskController::class, 'store']);
        Route::get('/statistics', [TaskController::class, 'statistics']);
        Route::post('/reorder', [TaskController::class, 'updateOrder']);
        Route::get('/{id}', [TaskController::class, 'show']);
        Route::put('/{id}', [TaskController::class, 'update']);
        Route::delete('/{id}', [TaskController::class, 'destroy']);
        Route::patch('/{id}/complete', [TaskController::class, 'markCompleted']);
        Route::patch('/{id}/pending', [TaskController::class, 'markPending']);
    });

    // Admin routes (admin access only)
    Route::middleware(CheckAdmin::class)->prefix('admin')->name('admin.')->group(function () {
        
        // Dashboard
        Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');
        Route::get('/statistics', [AdminController::class, 'statistics'])->name('statistics');
        
        // User management
        Route::get('/users', [AdminController::class, 'users'])->name('users');
        Route::get('/users/{userId}/tasks', [AdminController::class, 'userTasks'])->name('users.tasks');
        Route::patch('/users/{userId}/role', [AdminController::class, 'updateUserRole'])->name('users.role');
        
        // Task management
        Route::get('/tasks', [AdminController::class, 'tasks'])->name('tasks');
        Route::delete('/tasks/{taskId}', [AdminController::class, 'deleteTask'])->name('tasks.delete');
    });
});

// Fallback route for API
Route::fallback(function () {
    return response()->json([
        'message' => 'API endpoint not found',
        'error' => 'The requested API endpoint does not exist'
    ], 404);
});
