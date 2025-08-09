<?php

namespace Database\Seeders;

use App\Models\Task;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create admin user
        $admin = User::factory()->create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => Hash::make('password'),
            'is_admin' => true,
        ]);

        // Create regular test user
        $testUser = User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => Hash::make('password'),
            'is_admin' => false,
        ]);

        // Create additional regular users
        $users = User::factory(5)->create([
            'is_admin' => false,
        ]);

        // Create tasks for test user
        Task::factory(10)->create([
            'user_id' => $testUser->id,
        ]);

        // Create tasks for other users
        foreach ($users as $user) {
            Task::factory(rand(3, 8))->create([
                'user_id' => $user->id,
            ]);
        }

        // Create some completed tasks
        Task::factory(5)->completed()->create([
            'user_id' => $testUser->id,
        ]);

        // Create some high priority tasks
        Task::factory(3)->highPriority()->create([
            'user_id' => $testUser->id,
        ]);

        $this->command->info('Database seeded successfully!');
        $this->command->info('Admin credentials: admin@example.com / password');
        $this->command->info('Test user credentials: test@example.com / password');
    }
}
