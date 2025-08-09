<template>
  <div class="min-h-screen bg-gray-50">
    <div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
      <!-- Header -->
      <div class="px-4 py-6 sm:px-0">
        <div class="border-b border-gray-200 pb-5">
          <h1 class="text-3xl font-bold leading-6 text-gray-900">Dashboard</h1>
          <p class="mt-2 max-w-4xl text-sm text-gray-500">
            Welcome back, {{ authStore.user?.name }}! Here's an overview of your tasks.
          </p>
        </div>
      </div>

      <!-- Stats -->
      <div class="px-4 sm:px-0">
        <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4">
          <!-- Total Tasks -->
          <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="p-5">
              <div class="flex items-center">
                <div class="flex-shrink-0">
                  <div class="w-8 h-8 bg-blue-500 rounded-md flex items-center justify-center">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                    </svg>
                  </div>
                </div>
                <div class="ml-5 w-0 flex-1">
                  <dl>
                    <dt class="text-sm font-medium text-gray-500 truncate">Total Tasks</dt>
                    <dd class="text-lg font-medium text-gray-900">{{ tasksStore.taskCount.total }}</dd>
                  </dl>
                </div>
              </div>
            </div>
          </div>

          <!-- Pending Tasks -->
          <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="p-5">
              <div class="flex items-center">
                <div class="flex-shrink-0">
                  <div class="w-8 h-8 bg-yellow-500 rounded-md flex items-center justify-center">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                  </div>
                </div>
                <div class="ml-5 w-0 flex-1">
                  <dl>
                    <dt class="text-sm font-medium text-gray-500 truncate">Pending</dt>
                    <dd class="text-lg font-medium text-gray-900">{{ tasksStore.taskCount.pending }}</dd>
                  </dl>
                </div>
              </div>
            </div>
          </div>

          <!-- Completed Tasks -->
          <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="p-5">
              <div class="flex items-center">
                <div class="flex-shrink-0">
                  <div class="w-8 h-8 bg-green-500 rounded-md flex items-center justify-center">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                  </div>
                </div>
                <div class="ml-5 w-0 flex-1">
                  <dl>
                    <dt class="text-sm font-medium text-gray-500 truncate">Completed</dt>
                    <dd class="text-lg font-medium text-gray-900">{{ tasksStore.taskCount.completed }}</dd>
                  </dl>
                </div>
              </div>
            </div>
          </div>

          <!-- High Priority -->
          <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="p-5">
              <div class="flex items-center">
                <div class="flex-shrink-0">
                  <div class="w-8 h-8 bg-red-500 rounded-md flex items-center justify-center">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z" />
                    </svg>
                  </div>
                </div>
                <div class="ml-5 w-0 flex-1">
                  <dl>
                    <dt class="text-sm font-medium text-gray-500 truncate">High Priority</dt>
                    <dd class="text-lg font-medium text-gray-900">{{ tasksStore.taskCount.high }}</dd>
                  </dl>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Recent Tasks -->
      <div class="mt-8 px-4 sm:px-0">
        <div class="bg-white shadow rounded-lg">
          <div class="px-4 py-5 sm:p-6">
            <div class="flex items-center justify-between mb-4">
              <h3 class="text-lg leading-6 font-medium text-gray-900">Recent Tasks</h3>
              <router-link
                to="/tasks"
                class="text-sm font-medium text-primary-600 hover:text-primary-500"
              >
                View all tasks
              </router-link>
            </div>

            <div v-if="tasksStore.loading" class="flex justify-center py-8">
              <div class="loading-spinner"></div>
            </div>

            <div v-else-if="recentTasks.length === 0" class="text-center py-8">
              <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
              </svg>
              <h3 class="mt-2 text-sm font-medium text-gray-900">No tasks</h3>
              <p class="mt-1 text-sm text-gray-500">Get started by creating a new task.</p>
              <div class="mt-6">
                <router-link
                  to="/tasks"
                  class="btn-primary"
                >
                  Create Task
                </router-link>
              </div>
            </div>

            <div v-else class="space-y-3">
              <div
                v-for="task in recentTasks"
                :key="task.id"
                class="p-4 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors duration-200"
              >
                <!-- Mobile and Desktop Layout -->
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                  <!-- Left section: Checkbox and Task Info -->
                  <div class="flex items-start space-x-3 min-w-0 flex-1">
                    <button
                      @click="toggleTaskStatus(task)"
                      class="flex-shrink-0 mt-0.5"
                    >
                      <div
                        class="w-5 h-5 rounded border-2 flex items-center justify-center transition-colors duration-200"
                        :class="task.status === 'completed' 
                          ? 'bg-green-500 border-green-500' 
                          : 'border-gray-300 hover:border-green-400'"
                      >
                        <svg
                          v-if="task.status === 'completed'"
                          class="w-3 h-3 text-white"
                          fill="none"
                          stroke="currentColor"
                          viewBox="0 0 24 24"
                        >
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                      </div>
                    </button>
                    
                    <div class="min-w-0 flex-1">
                      <p
                        class="text-sm font-medium text-gray-900 break-words"
                        :class="{ 'line-through text-gray-500': task.status === 'completed' }"
                      >
                        {{ task.title }}
                      </p>
                      <p class="text-sm text-gray-500 mt-1 break-words line-clamp-2">
                        {{ task.description }}
                      </p>
                    </div>
                  </div>

                  <!-- Right section: Badges -->
                  <div class="flex items-center gap-2 flex-shrink-0 sm:ml-4">
                    <span
                      class="badge whitespace-nowrap"
                      :class="`priority-${task.priority}`"
                    >
                      {{ task.priority }}
                    </span>
                    <span
                      class="badge whitespace-nowrap"
                      :class="`status-${task.status}`"
                    >
                      {{ task.status }}
                    </span>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Quick Actions -->
      <div class="mt-8 px-4 sm:px-0">
        <div class="bg-white shadow rounded-lg">
          <div class="px-4 py-5 sm:p-6">
            <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">Quick Actions</h3>
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
              <router-link
                to="/tasks"
                class="relative group bg-white p-6 focus-within:ring-2 focus-within:ring-inset focus-within:ring-primary-500 border border-gray-200 rounded-lg hover:border-gray-300 transition-colors duration-200"
              >
                <div>
                  <span class="rounded-lg inline-flex p-3 bg-primary-50 text-primary-600 ring-4 ring-white">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                    </svg>
                  </span>
                </div>
                <div class="mt-8">
                  <h3 class="text-lg font-medium">
                    <span class="absolute inset-0" aria-hidden="true"></span>
                    Create New Task
                  </h3>
                  <p class="mt-2 text-sm text-gray-500">
                    Add a new task to your list and start being productive.
                  </p>
                </div>
              </router-link>

              <router-link
                to="/tasks?status=pending"
                class="relative group bg-white p-6 focus-within:ring-2 focus-within:ring-inset focus-within:ring-primary-500 border border-gray-200 rounded-lg hover:border-gray-300 transition-colors duration-200"
              >
                <div>
                  <span class="rounded-lg inline-flex p-3 bg-yellow-50 text-yellow-600 ring-4 ring-white">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                  </span>
                </div>
                <div class="mt-8">
                  <h3 class="text-lg font-medium">
                    <span class="absolute inset-0" aria-hidden="true"></span>
                    View Pending Tasks
                  </h3>
                  <p class="mt-2 text-sm text-gray-500">
                    See all your pending tasks that need attention.
                  </p>
                </div>
              </router-link>

              <router-link
                v-if="authStore.isAdmin"
                to="/admin"
                class="relative group bg-white p-6 focus-within:ring-2 focus-within:ring-inset focus-within:ring-primary-500 border border-gray-200 rounded-lg hover:border-gray-300 transition-colors duration-200"
              >
                <div>
                  <span class="rounded-lg inline-flex p-3 bg-purple-50 text-purple-600 ring-4 ring-white">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                    </svg>
                  </span>
                </div>
                <div class="mt-8">
                  <h3 class="text-lg font-medium">
                    <span class="absolute inset-0" aria-hidden="true"></span>
                    Admin Dashboard
                  </h3>
                  <p class="mt-2 text-sm text-gray-500">
                    Manage users and view system-wide statistics.
                  </p>
                </div>
              </router-link>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed, onMounted, onUnmounted } from 'vue'
import { useAuthStore } from '@/stores/auth'
import { useTasksStore } from '@/stores/tasks'
import { socketService } from '@/services/socket'

const authStore = useAuthStore()
const tasksStore = useTasksStore()

const recentTasks = computed(() => {
  return tasksStore.tasks.slice(0, 5)
})

const toggleTaskStatus = async (task) => {
  try {
    const result = await tasksStore.toggleTaskStatus(task.id)
    
    // Emit socket event for real-time updates
    if (result) {
      socketService.emitTaskStatusUpdate({
        id: result.id,
        status: result.status,
        title: result.title,
        description: result.description,
        priority: result.priority,
        user: result.user || task.user,
        user_id: result.user_id || task.user_id,
        created_at: result.created_at,
        updated_at: result.updated_at
      })
    }
  } catch (error) {
    console.error('Failed to toggle task status:', error)
  }
}

// Socket event handlers for receiving updates from admin
const handleSocketTaskStatusUpdated = (updatedTask) => {
  console.log('Dashboard - Socket event received: task-status-updated', updatedTask)
  const existingTask = tasksStore.getTaskById(updatedTask.id)
  if (existingTask && existingTask.status !== updatedTask.status) {
    tasksStore.updateTaskInList(updatedTask)
  }
}

const handleSocketTaskUpdated = (updatedTask) => {
  console.log('Dashboard - Socket event received: task-updated', updatedTask)
  const existingTask = tasksStore.getTaskById(updatedTask.id)
  if (existingTask) {
    tasksStore.updateTaskInList(updatedTask)
  }
}

const handleSocketTaskCreated = (newTask) => {
  console.log('Dashboard - Socket event received: task-created', newTask)
  const existingTask = tasksStore.getTaskById(newTask.id)
  if (!existingTask) {
    tasksStore.addTask(newTask)
  }
}

const handleSocketTaskDeleted = (deletedTask) => {
  console.log('Dashboard - Socket event received: task-deleted', deletedTask)
  const existingTask = tasksStore.getTaskById(deletedTask.id)
  if (existingTask) {
    tasksStore.removeTask(deletedTask.id)
  }
}

const handleSocketTaskUpdatedData = (updatedTask) => {
  console.log('Dashboard - Socket event received: task-updated-data', updatedTask)
  const existingTask = tasksStore.getTaskById(updatedTask.id)
  if (existingTask) {
    tasksStore.updateTaskInList(updatedTask)
  }
}

onMounted(async () => {
  await tasksStore.fetchTasks()
  
  // Connect to socket
  console.log('Dashboard - Connecting to socket...')
  socketService.connect()
  
  // Add a small delay to ensure connection is established
  setTimeout(() => {
    console.log('Dashboard - Setting up socket listeners...')
    // Listen for socket events from admin actions
    socketService.onTaskStatusUpdated(handleSocketTaskStatusUpdated)
    socketService.onTaskUpdated(handleSocketTaskUpdated)
    socketService.onTaskCreated(handleSocketTaskCreated)
    socketService.onTaskDeleted(handleSocketTaskDeleted)
    socketService.onTaskUpdatedData(handleSocketTaskUpdatedData)
    console.log('Dashboard - Socket listeners set up')
  }, 1000)
})

onUnmounted(() => {
  // Clean up socket connection
  socketService.removeAllListeners()
})
</script>
