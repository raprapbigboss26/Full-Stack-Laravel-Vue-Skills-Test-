<template>
  <div class="min-h-screen bg-gray-50">
    <div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
      <!-- Header -->
      <div class="px-4 py-6 sm:px-0">
        <div class="border-b border-gray-200 pb-5">
          <h1 class="text-3xl font-bold leading-6 text-gray-900">Tasks Management</h1>
          <p class="mt-2 max-w-4xl text-sm text-gray-500">
            Manage all system tasks across all users.
          </p>
        </div>
      </div>

      <!-- Filters -->
      <div class="px-4 sm:px-0 mb-6">
        <div class="bg-white p-4 rounded-lg shadow">
          <div class="grid grid-cols-1 gap-4 sm:grid-cols-4">
            <!-- Search -->
            <div>
              <label for="search" class="form-label">Search</label>
              <input
                id="search"
                v-model="filters.search"
                type="text"
                class="form-input"
                placeholder="Search tasks..."
                @input="debouncedSearch"
              />
            </div>

            <!-- Status Filter -->
            <div>
              <label for="status" class="form-label">Status</label>
              <select
                id="status"
                v-model="filters.status"
                class="form-input"
                @change="applyFilters"
              >
                <option value="">All Status</option>
                <option value="pending">Pending</option>
                <option value="completed">Completed</option>
              </select>
            </div>

            <!-- Priority Filter -->
            <div>
              <label for="priority" class="form-label">Priority</label>
              <select
                id="priority"
                v-model="filters.priority"
                class="form-input"
                @change="applyFilters"
              >
                <option value="">All Priorities</option>
                <option value="high">High</option>
                <option value="medium">Medium</option>
                <option value="low">Low</option>
              </select>
            </div>

            <!-- User Filter -->
            <div>
              <label for="user" class="form-label">User</label>
              <select
                id="user"
                v-model="filters.user_id"
                class="form-input"
                @change="applyFilters"
              >
                <option value="">All Users</option>
                <option v-for="user in users" :key="user.id" :value="user.id">
                  {{ user.name }}
                </option>
              </select>
            </div>
          </div>
        </div>
      </div>

      <!-- Tasks List -->
      <div class="px-4 sm:px-0">
        <div class="bg-white shadow rounded-lg">
          <div class="px-4 py-5 sm:p-6">
            <div v-if="loading" class="flex justify-center py-8">
              <div class="loading-spinner"></div>
            </div>

            <div v-else-if="tasks.length === 0" class="text-center py-8">
              <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
              </svg>
              <h3 class="mt-2 text-sm font-medium text-gray-900">No tasks found</h3>
              <p class="mt-1 text-sm text-gray-500">
                {{ hasFilters ? 'Try adjusting your filters.' : 'No tasks have been created yet.' }}
              </p>
            </div>

            <div v-else class="overflow-x-auto">
              <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                  <tr>
                    <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-2/5">
                      Task
                    </th>
                    <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-1/5">
                      User
                    </th>
                    <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-1/12">
                      Priority
                    </th>
                    <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-1/12">
                      Status
                    </th>
                    <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-1/12">
                      Created
                    </th>
                    <th class="relative px-3 py-3 w-1/6">
                      <span class="sr-only">Actions</span>
                    </th>
                  </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                  <tr v-for="task in tasks" :key="task.id" class="hover:bg-gray-50">
                    <td class="px-3 py-4 w-2/5">
                      <div class="flex items-center">
                        <button
                          @click="toggleTaskStatus(task)"
                          class="flex-shrink-0 mr-3"
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
                        <div class="min-w-0 flex-1 cursor-pointer" @click="viewTaskDetails(task)">
                          <div
                            class="text-sm font-medium text-gray-900 truncate max-w-xs hover:text-primary-600"
                            :class="{ 'line-through text-gray-500': task.status === 'completed' }"
                            :title="task.title"
                          >
                            {{ task.title }}
                          </div>
                          <div class="text-sm text-gray-500 mt-1 truncate max-w-xs" :title="task.description">
                            {{ task.description }}
                          </div>
                        </div>
                      </div>
                    </td>
                    <td class="px-3 py-4 whitespace-nowrap w-1/5">
                      <div class="flex items-center">
                        <div class="w-8 h-8 bg-primary-100 rounded-full flex items-center justify-center flex-shrink-0">
                          <span class="text-xs font-medium text-primary-600">
                            {{ getUserInitials(task.user?.name) }}
                          </span>
                        </div>
                        <div class="ml-3 min-w-0 flex-1">
                          <div class="text-sm font-medium text-gray-900 truncate">{{ task.user?.name }}</div>
                          <div class="text-sm text-gray-500 truncate">{{ task.user?.email }}</div>
                        </div>
                      </div>
                    </td>
                    <td class="px-3 py-4 whitespace-nowrap w-1/12">
                      <span class="badge text-xs" :class="`priority-${task.priority}`">
                        {{ task.priority }}
                      </span>
                    </td>
                    <td class="px-3 py-4 whitespace-nowrap w-1/12">
                      <span class="badge text-xs" :class="`status-${task.status}`">
                        {{ task.status }}
                      </span>
                    </td>
                    <td class="px-3 py-4 whitespace-nowrap text-sm text-gray-500 w-1/12">
                      {{ formatDate(task.created_at) }}
                    </td>
                    <td class="px-3 py-4 whitespace-nowrap text-right text-sm font-medium w-1/6">
                      <div class="flex justify-end space-x-2">
                        <button
                          @click="editTask(task)"
                          class="text-primary-600 hover:text-primary-900 text-xs px-2 py-1 rounded hover:bg-primary-50"
                        >
                          Edit
                        </button>
                        <button
                          @click="deleteTask(task)"
                          class="text-red-600 hover:text-red-900 text-xs px-2 py-1 rounded hover:bg-red-50"
                        >
                          Delete
                        </button>
                      </div>
                    </td>
                  </tr>
                </tbody>
              </table>

              <!-- Pagination -->
              <div v-if="pagination.total > pagination.per_page" class="bg-white px-4 py-3 flex items-center justify-between border-t border-gray-200 sm:px-6">
                <div class="flex-1 flex justify-between sm:hidden">
                  <button
                    @click="changePage(pagination.current_page - 1)"
                    :disabled="pagination.current_page <= 1"
                    class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 disabled:opacity-50"
                  >
                    Previous
                  </button>
                  <button
                    @click="changePage(pagination.current_page + 1)"
                    :disabled="pagination.current_page >= pagination.last_page"
                    class="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 disabled:opacity-50"
                  >
                    Next
                  </button>
                </div>
                <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
                  <div>
                    <p class="text-sm text-gray-700">
                      Showing
                      <span class="font-medium">{{ pagination.from }}</span>
                      to
                      <span class="font-medium">{{ pagination.to }}</span>
                      of
                      <span class="font-medium">{{ pagination.total }}</span>
                      results
                    </p>
                  </div>
                  <div>
                    <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px">
                      <button
                        @click="changePage(pagination.current_page - 1)"
                        :disabled="pagination.current_page <= 1"
                        class="relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50 disabled:opacity-50"
                      >
                        Previous
                      </button>
                      <button
                        @click="changePage(pagination.current_page + 1)"
                        :disabled="pagination.current_page >= pagination.last_page"
                        class="relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50 disabled:opacity-50"
                      >
                        Next
                      </button>
                    </nav>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Edit Task Modal -->
    <TaskModal
      :show="showEditModal"
      :task="editingTask"
      @close="closeEditModal"
      @save="handleTaskSave"
    />

    <!-- Delete Confirmation Modal -->
    <ConfirmModal
      :show="showDeleteModal"
      title="Delete Task"
      message="Are you sure you want to delete this task? This action cannot be undone."
      confirm-text="Delete"
      type="danger"
      @confirm="confirmDelete"
      @cancel="showDeleteModal = false"
    />

    <!-- Task Details Modal -->
    <div
      v-if="showDetailsModal"
      class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50"
      @click="closeDetailsModal"
    >
      <div class="relative top-20 mx-auto p-5 border w-3/4 max-w-2xl shadow-lg rounded-md bg-white" @click.stop>
        <div class="mt-3">
          <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-medium text-gray-900">
              Task Details
            </h3>
            <button
              @click="closeDetailsModal"
              class="text-gray-400 hover:text-gray-600"
            >
              <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
              </svg>
            </button>
          </div>

          <div v-if="selectedTask" class="space-y-4">
            <!-- Task Title -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Title</label>
              <div class="p-3 bg-gray-50 rounded-md">
                <p class="text-sm text-gray-900" :class="{ 'line-through': selectedTask.status === 'completed' }">
                  {{ selectedTask.title }}
                </p>
              </div>
            </div>

            <!-- Task Description -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
              <div class="p-3 bg-gray-50 rounded-md">
                <p class="text-sm text-gray-900 whitespace-pre-wrap">
                  {{ selectedTask.description || 'No description provided' }}
                </p>
              </div>
            </div>

            <!-- Task Details Grid -->
            <div class="grid grid-cols-2 gap-4">
              <!-- Priority -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Priority</label>
                <div class="p-3 bg-gray-50 rounded-md">
                  <span class="badge" :class="`priority-${selectedTask.priority}`">
                    {{ selectedTask.priority }}
                  </span>
                </div>
              </div>

              <!-- Status -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                <div class="p-3 bg-gray-50 rounded-md">
                  <span class="badge" :class="`status-${selectedTask.status}`">
                    {{ selectedTask.status }}
                  </span>
                </div>
              </div>

              <!-- Created Date -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Created</label>
                <div class="p-3 bg-gray-50 rounded-md">
                  <p class="text-sm text-gray-900">
                    {{ formatDate(selectedTask.created_at) }}
                  </p>
                </div>
              </div>

              <!-- Updated Date -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Last Updated</label>
                <div class="p-3 bg-gray-50 rounded-md">
                  <p class="text-sm text-gray-900">
                    {{ formatDate(selectedTask.updated_at) }}
                  </p>
                </div>
              </div>
            </div>

            <!-- User Information -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Assigned User</label>
              <div class="p-3 bg-gray-50 rounded-md">
                <div class="flex items-center">
                  <div class="w-10 h-10 bg-primary-100 rounded-full flex items-center justify-center">
                    <span class="text-sm font-medium text-primary-600">
                      {{ getUserInitials(selectedTask.user?.name) }}
                    </span>
                  </div>
                  <div class="ml-3">
                    <div class="text-sm font-medium text-gray-900">{{ selectedTask.user?.name || 'Unknown User' }}</div>
                    <div class="text-sm text-gray-500">{{ selectedTask.user?.email || 'No email' }}</div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Action Buttons -->
            <div class="flex justify-end space-x-3 pt-4 border-t">
              <button
                @click="editTaskFromModal(selectedTask)"
                class="px-4 py-2 bg-primary-600 text-white text-sm font-medium rounded-md hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-primary-500"
              >
                Edit Task
              </button>
              <button
                @click="deleteTaskFromModal(selectedTask)"
                class="px-4 py-2 bg-red-600 text-white text-sm font-medium rounded-md hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500"
              >
                Delete Task
              </button>
              <button
                @click="closeDetailsModal"
                class="px-4 py-2 bg-gray-300 text-gray-700 text-sm font-medium rounded-md hover:bg-gray-400 focus:outline-none focus:ring-2 focus:ring-gray-500"
              >
                Close
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted, onUnmounted } from 'vue'
import { apiService } from '@/services/api'
import { useToast } from 'vue-toastification'
import TaskModal from '@/components/ui/TaskModal.vue'
import ConfirmModal from '@/components/ui/ConfirmModal.vue'
import { socketService } from '@/services/socket'

const toast = useToast()

const loading = ref(false)
const tasks = ref([])
const users = ref([])
const showEditModal = ref(false)
const showDeleteModal = ref(false)
const showDetailsModal = ref(false)
const editingTask = ref(null)
const deletingTask = ref(null)
const selectedTask = ref(null)

const filters = reactive({
  search: '',
  status: '',
  priority: '',
  user_id: ''
})

const pagination = ref({
  current_page: 1,
  last_page: 1,
  per_page: 15,
  total: 0,
  from: 0,
  to: 0
})

const hasFilters = computed(() => {
  return filters.search || filters.status || filters.priority || filters.user_id
})

let searchTimeout = null
const debouncedSearch = () => {
  clearTimeout(searchTimeout)
  searchTimeout = setTimeout(() => {
    applyFilters()
  }, 300)
}

const fetchTasks = async (page = 1) => {
  loading.value = true
  try {
    const params = {
      page,
      ...filters
    }
    const response = await apiService.getAdminTasks(params)
    
    // Handle the response structure
    if (response.data && Array.isArray(response.data.data)) {
      tasks.value = response.data.data
      pagination.value = {
        current_page: response.data.current_page,
        last_page: response.data.last_page,
        per_page: response.data.per_page,
        total: response.data.total,
        from: response.data.from,
        to: response.data.to
      }
    } else if (response.data && Array.isArray(response.data)) {
      // Fallback for direct array response
      tasks.value = response.data
      pagination.value = {
        current_page: 1,
        last_page: 1,
        per_page: response.data.length,
        total: response.data.length,
        from: 1,
        to: response.data.length
      }
    } else {
      // Empty response
      tasks.value = []
      pagination.value = {
        current_page: 1,
        last_page: 1,
        per_page: 15,
        total: 0,
        from: 0,
        to: 0
      }
    }
  } catch (error) {
    console.error('Failed to fetch tasks:', error)
    toast.error('Failed to fetch tasks')
    // Set empty state on error
    tasks.value = []
    pagination.value = {
      current_page: 1,
      last_page: 1,
      per_page: 15,
      total: 0,
      from: 0,
      to: 0
    }
  } finally {
    loading.value = false
  }
}

const handleSocketTaskStatusUpdated = (updatedTask) => {
  console.log('Admin view - Socket event received: task-status-updated', updatedTask)
  const index = tasks.value.findIndex(t => t.id === updatedTask.id)
  if (index !== -1) {
    // Only update if status is different (prevent unnecessary updates)
    if (tasks.value[index].status !== updatedTask.status) {
      // Merge with existing task to preserve user data
      const existingTask = tasks.value[index]
      const mergedTask = {
        ...existingTask,
        ...updatedTask,
        user: updatedTask.user || existingTask.user // Preserve user data
      }
      tasks.value.splice(index, 1, mergedTask)
    }
  } else {
    fetchTasks(pagination.value.current_page)
  }
}

const handleSocketTaskUpdated = (updatedTask) => {
  console.log('Admin view - Socket event received: task-updated', updatedTask)
  const index = tasks.value.findIndex(t => t.id === updatedTask.id)
  if (index !== -1) {
    // Merge with existing task to preserve user data
    const existingTask = tasks.value[index]
    const mergedTask = {
      ...existingTask,
      ...updatedTask,
      user: updatedTask.user || existingTask.user // Preserve user data
    }
    tasks.value.splice(index, 1, mergedTask)
  } else {
    fetchTasks(pagination.value.current_page)
  }
}

const handleSocketTaskCreated = (newTask) => {
  console.log('Admin view - Socket event received: task-created', newTask)
  // Only add if task doesn't already exist (prevent duplicates)
  const existingTask = tasks.value.find(t => t.id === newTask.id)
  if (!existingTask) {
    fetchTasks(pagination.value.current_page)
  }
}

const handleSocketTaskDeleted = (deletedTask) => {
  console.log('Admin view - Socket event received: task-deleted', deletedTask)
  // Only remove if task exists
  const existingTask = tasks.value.find(t => t.id === deletedTask.id)
  if (existingTask) {
    tasks.value = tasks.value.filter(t => t.id !== deletedTask.id)
  }
}

const handleSocketTaskUpdatedData = (updatedTask) => {
  console.log('Admin view - Socket event received: task-updated-data', updatedTask)
  const index = tasks.value.findIndex(t => t.id === updatedTask.id)
  if (index !== -1) {
    // Merge with existing task to preserve user data
    const existingTask = tasks.value[index]
    const mergedTask = {
      ...existingTask,
      ...updatedTask,
      user: updatedTask.user || existingTask.user // Preserve user data
    }
    tasks.value.splice(index, 1, mergedTask)
  } else {
    fetchTasks(pagination.value.current_page)
  }
}

const fetchUsers = async () => {
  try {
    const response = await apiService.getUsers()
    users.value = response.data
  } catch (error) {
    console.error('Failed to fetch users:', error)
  }
}

const applyFilters = () => {
  fetchTasks(1)
}

const changePage = (page) => {
  if (page >= 1 && page <= pagination.value.last_page) {
    fetchTasks(page)
  }
}

const toggleTaskStatus = async (task) => {
  try {
    const response = await apiService.toggleTaskStatus(task.id)
    task.status = response.data.status
    toast.success(`Task marked as ${task.status}`)
    
    // Emit socket event for real-time updates to user views
    socketService.emitTaskStatusUpdate({
      id: response.data.id,
      status: response.data.status,
      title: response.data.title,
      description: response.data.description,
      priority: response.data.priority,
      user: response.data.user,
      created_at: response.data.created_at,
      updated_at: response.data.updated_at
    })
  } catch (error) {
    console.error('Failed to toggle task status:', error)
    toast.error('Failed to update task status')
  }
}

const editTask = (task) => {
  editingTask.value = { ...task }
  showEditModal.value = true
  showDetailsModal.value = false
}

const closeEditModal = () => {
  showEditModal.value = false
  editingTask.value = null
}

const handleTaskSave = async (taskData) => {
  try {
    const response = await apiService.updateTask(editingTask.value.id, taskData)
    toast.success('Task updated successfully')
    closeEditModal()
    fetchTasks(pagination.value.current_page)
    
    // Emit socket event for real-time updates to user views
    if (response.data) {
      socketService.emitTaskUpdated(response.data)
    }
  } catch (error) {
    console.error('Failed to update task:', error)
    toast.error('Failed to update task')
  }
}

const deleteTask = (task) => {
  deletingTask.value = task
  showDeleteModal.value = true
  showDetailsModal.value = false
}

const confirmDelete = async () => {
  if (deletingTask.value) {
    try {
      await apiService.deleteTask(deletingTask.value.id)
      toast.success('Task deleted successfully')
      
      // Emit socket event for real-time updates to user views with complete task info
      socketService.emitTaskDeleted({
        id: deletingTask.value.id,
        title: deletingTask.value.title,
        status: deletingTask.value.status,
        priority: deletingTask.value.priority,
        user_id: deletingTask.value.user_id,
        user: deletingTask.value.user
      })
      
      showDeleteModal.value = false
      deletingTask.value = null
      fetchTasks(pagination.value.current_page)
    } catch (error) {
      console.error('Failed to delete task:', error)
      toast.error('Failed to delete task')
    }
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

const viewTaskDetails = (task) => {
  selectedTask.value = { ...task }
  showDetailsModal.value = true
}

const closeDetailsModal = () => {
  showDetailsModal.value = false
  selectedTask.value = null
}

const editTaskFromModal = (task) => {
  closeDetailsModal()
  editTask(task)
}

const deleteTaskFromModal = (task) => {
  closeDetailsModal()
  deleteTask(task)
}

const formatDate = (dateString) => {
  if (!dateString) return 'N/A'
  return new Date(dateString).toLocaleDateString()
}

onMounted(() => {
  fetchTasks()
  fetchUsers()

  // Connect socket and join admin room
  socketService.connect()
  socketService.joinAdminRoom()

  // Listen for socket events from users
  socketService.onTaskStatusUpdated(handleSocketTaskStatusUpdated)
  socketService.onTaskUpdated(handleSocketTaskUpdated)
  socketService.onTaskCreated(handleSocketTaskCreated)
  socketService.onTaskDeleted(handleSocketTaskDeleted)
  socketService.onTaskUpdatedData(handleSocketTaskUpdatedData)
})

onUnmounted(() => {
  // Remove socket listeners
  socketService.removeAllListeners()
})
</script>
