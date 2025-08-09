<template>
  <div class="min-h-screen bg-gray-50">
    <div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
      <!-- Header -->
      <div class="px-4 py-6 sm:px-0">
        <div class="border-b border-gray-200 pb-5">
          <h1 class="text-3xl font-bold leading-6 text-gray-900">Users Management</h1>
          <p class="mt-2 max-w-4xl text-sm text-gray-500">
            Manage all system users and their permissions.
          </p>
        </div>
      </div>

      <!-- Users List -->
      <div class="px-4 sm:px-0">
        <div class="bg-white shadow rounded-lg">
          <div class="px-4 py-5 sm:p-6">
            <div v-if="loading" class="flex justify-center py-8">
              <div class="loading-spinner"></div>
            </div>

            <div v-else-if="users.length === 0" class="text-center py-8">
              <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z" />
              </svg>
              <h3 class="mt-2 text-sm font-medium text-gray-900">No users found</h3>
              <p class="mt-1 text-sm text-gray-500">No users are currently registered in the system.</p>
            </div>

            <div v-else class="overflow-hidden">
              <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                  <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                      User
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                      Role
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                      Tasks
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                      Joined
                    </th>
                    <th class="relative px-6 py-3">
                      <span class="sr-only">Actions</span>
                    </th>
                  </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                  <tr v-for="user in users" :key="user.id" class="hover:bg-gray-50">
                    <td class="px-6 py-4 whitespace-nowrap">
                      <div class="flex items-center">
                        <div class="w-10 h-10 bg-primary-100 rounded-full flex items-center justify-center">
                          <span class="text-sm font-medium text-primary-600">
                            {{ getUserInitials(user.name) }}
                          </span>
                        </div>
                        <div class="ml-4">
                          <div class="text-sm font-medium text-gray-900">{{ user.name }}</div>
                          <div class="text-sm text-gray-500">{{ user.email }}</div>
                        </div>
                      </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                      <span
                        class="inline-flex px-2 py-1 text-xs font-semibold rounded-full"
                        :class="user.is_admin ? 'bg-purple-100 text-purple-800' : 'bg-gray-100 text-gray-800'"
                      >
                        {{ user.is_admin ? 'Administrator' : 'User' }}
                      </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                      <div class="flex space-x-4">
                        <span>Total: {{ user.tasks_count || 0 }}</span>
                        <span class="text-green-600">Completed: {{ user.completed_tasks_count || 0 }}</span>
                        <span class="text-yellow-600">Pending: {{ user.pending_tasks_count || 0 }}</span>
                      </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                      {{ formatDate(user.created_at) }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                      <button
                        @click="viewUserTasks(user)"
                        class="text-primary-600 hover:text-primary-900 mr-3"
                      >
                        View Tasks
                      </button>
                      <button
                        v-if="!user.is_admin"
                        @click="toggleAdminRole(user)"
                        class="text-purple-600 hover:text-purple-900"
                      >
                        Make Admin
                      </button>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- User Tasks Modal -->
    <div
      v-if="showTasksModal"
      class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50"
      @click="closeTasksModal"
    >
      <div class="relative top-20 mx-auto p-5 border w-3/4 max-w-4xl shadow-lg rounded-md bg-white">
        <div class="mt-3">
          <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-medium text-gray-900">
              Tasks for {{ selectedUser?.name }}
            </h3>
            <button
              @click="closeTasksModal"
              class="text-gray-400 hover:text-gray-600"
            >
              <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
              </svg>
            </button>
          </div>

          <div v-if="userTasks.length === 0" class="text-center py-8">
            <p class="text-gray-500">This user has no tasks.</p>
          </div>

          <div v-else class="space-y-3 max-h-96 overflow-y-auto">
            <div
              v-for="task in userTasks"
              :key="task.id"
              class="p-4 border border-gray-200 rounded-lg"
            >
              <div class="flex items-center justify-between">
                <div>
                  <h4 class="text-sm font-medium text-gray-900">{{ task.title }}</h4>
                  <p class="text-sm text-gray-500 mt-1">{{ task.description }}</p>
                  <div class="flex items-center space-x-2 mt-2">
                    <span class="badge" :class="`priority-${task.priority}`">
                      {{ task.priority }}
                    </span>
                    <span class="badge" :class="`status-${task.status}`">
                      {{ task.status }}
                    </span>
                  </div>
                </div>
                <div class="text-sm text-gray-400">
                  {{ formatDate(task.created_at) }}
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue'
import { apiService } from '@/services/api'
import { useToast } from 'vue-toastification'
import { socketService } from '@/services/socket'

const toast = useToast()

const loading = ref(false)
const users = ref([])
const showTasksModal = ref(false)
const selectedUser = ref(null)
const userTasks = ref([])

const fetchUsers = async () => {
  loading.value = true
  try {
    const response = await apiService.getUsers()
    users.value = response.data
  } catch (error) {
    console.error('Failed to fetch users:', error)
    toast.error('Failed to fetch users')
  } finally {
    loading.value = false
  }
}

const updateUserTaskCounts = (userId, statusChange) => {
  const user = users.value.find(u => u.id === userId)
  if (!user) return

  // Update task counts based on status change
  if (statusChange.from === 'pending' && statusChange.to === 'completed') {
    user.pending_tasks_count = Math.max(0, (user.pending_tasks_count || 0) - 1)
    user.completed_tasks_count = (user.completed_tasks_count || 0) + 1
  } else if (statusChange.from === 'completed' && statusChange.to === 'pending') {
    user.completed_tasks_count = Math.max(0, (user.completed_tasks_count || 0) - 1)
    user.pending_tasks_count = (user.pending_tasks_count || 0) + 1
  }
}

const viewUserTasks = async (user) => {
  selectedUser.value = user
  showTasksModal.value = true
  
  try {
    const response = await apiService.getUserTasks(user.id)
    userTasks.value = response.data.tasks || []
  } catch (error) {
    console.error('Failed to fetch user tasks:', error)
    toast.error('Failed to fetch user tasks')
    userTasks.value = []
  }
}

const closeTasksModal = () => {
  showTasksModal.value = false
  selectedUser.value = null
  userTasks.value = []
}

const toggleAdminRole = async (user) => {
  try {
    const newAdminStatus = !user.is_admin
    await apiService.toggleUserAdmin(user.id, newAdminStatus)
    user.is_admin = newAdminStatus
    toast.success(`User ${newAdminStatus ? 'promoted to' : 'demoted from'} admin`)
  } catch (error) {
    console.error('Failed to toggle admin role:', error)
    toast.error('Failed to update user role')
  }
}

const getUserInitials = (name) => {
  if (!name) return 'U'
  return name
    .split(' ')
    .map(n => n.charAt(0))
    .join('')
    .toUpperCase()
    .slice(0, 2)
}

const formatDate = (dateString) => {
  if (!dateString) return 'N/A'
  return new Date(dateString).toLocaleDateString()
}

// Socket event handlers for real-time user statistics updates
const handleSocketTaskStatusUpdated = (updatedTask) => {
  console.log('Users view - Socket event received: task-status-updated', updatedTask)
  
  // Find the user and update their task counts instantly without reload
  const user = users.value.find(u => u.id === updatedTask.user_id)
  if (user) {
    // Since we don't know the previous status, we'll make an educated guess
    // If task is now completed, assume it was pending before
    if (updatedTask.status === 'completed') {
      user.pending_tasks_count = Math.max(0, (user.pending_tasks_count || 0) - 1)
      user.completed_tasks_count = (user.completed_tasks_count || 0) + 1
    } else if (updatedTask.status === 'pending') {
      user.completed_tasks_count = Math.max(0, (user.completed_tasks_count || 0) - 1)
      user.pending_tasks_count = (user.pending_tasks_count || 0) + 1
    }
  }
}

const handleSocketTaskCreated = (newTask) => {
  console.log('Users view - Socket event received: task-created', newTask)
  
  // Find the user and increment their total and status-specific task counts instantly
  const user = users.value.find(u => u.id === newTask.user_id)
  if (user) {
    user.tasks_count = (user.tasks_count || 0) + 1
    if (newTask.status === 'pending') {
      user.pending_tasks_count = (user.pending_tasks_count || 0) + 1
    } else if (newTask.status === 'completed') {
      user.completed_tasks_count = (user.completed_tasks_count || 0) + 1
    }
  }
}

const handleSocketTaskDeleted = (deletedTask) => {
  console.log('Users view - Socket event received: task-deleted', deletedTask)
  
  // Find the user and decrement their task counts instantly with correct status
  const user = users.value.find(u => u.id === deletedTask.user_id)
  if (user) {
    user.tasks_count = Math.max(0, (user.tasks_count || 0) - 1)
    
    // Now we have the exact status of the deleted task, so we can decrement correctly
    if (deletedTask.status === 'pending') {
      user.pending_tasks_count = Math.max(0, (user.pending_tasks_count || 0) - 1)
    } else if (deletedTask.status === 'completed') {
      user.completed_tasks_count = Math.max(0, (user.completed_tasks_count || 0) - 1)
    }
  }
}

const handleSocketTaskUpdated = (updatedTask) => {
  console.log('Users view - Socket event received: task-updated', updatedTask)
  // Task data updated, but counts shouldn't change unless status changed
  // This is handled by handleSocketTaskStatusUpdated
}

const handleSocketTaskUpdatedData = (updatedTask) => {
  console.log('Users view - Socket event received: task-updated-data', updatedTask)
  // Task data updated, no count changes needed for data-only updates
}

onMounted(() => {
  fetchUsers()
  
  // Connect to socket
  console.log('Users view - Connecting to socket...')
  socketService.connect()
  socketService.joinAdminRoom()
  
  // Add a small delay to ensure connection is established
  setTimeout(() => {
    console.log('Users view - Setting up socket listeners...')
    // Listen for socket events to update user statistics
    socketService.onTaskStatusUpdated(handleSocketTaskStatusUpdated)
    socketService.onTaskCreated(handleSocketTaskCreated)
    socketService.onTaskDeleted(handleSocketTaskDeleted)
    socketService.onTaskUpdated(handleSocketTaskUpdated)
    socketService.onTaskUpdatedData(handleSocketTaskUpdatedData)
    console.log('Users view - Socket listeners set up')
  }, 1000)
})

onUnmounted(() => {
  // Clean up socket connection
  socketService.removeAllListeners()
})
</script>
