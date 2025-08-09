<template>
  <div class="min-h-screen bg-gray-50">
    <div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
      <!-- Header -->
      <div class="px-4 py-6 sm:px-0">
        <div class="flex items-center justify-between border-b border-gray-200 pb-5">
          <div>
            <h1 class="text-3xl font-bold leading-6 text-gray-900">Tasks</h1>
            <p class="mt-2 max-w-4xl text-sm text-gray-500">
              Manage your tasks efficiently with drag-and-drop reordering.
            </p>
          </div>
          <button
            @click="showCreateModal = true"
            class="btn-primary"
          >
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
            </svg>
            New Task
          </button>
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
                <option value="all">All Status</option>
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
                <option value="all">All Priorities</option>
                <option value="high">High</option>
                <option value="medium">Medium</option>
                <option value="low">Low</option>
              </select>
            </div>

            <!-- Items Per Page -->
            <div>
              <label for="per-page" class="form-label">Items Per Page</label>
              <select
                id="per-page"
                v-model="pagination.per_page"
                class="form-input"
                @change="changePerPage"
              >
                <option value="6">6</option>
                <option value="10">10</option>
                <option value="20">20</option>
                <option value="30">30</option>
              </select>
            </div>
          </div>
        </div>
      </div>

      <!-- Tasks List -->
      <div class="px-4 sm:px-0">
        <div class="bg-white shadow rounded-lg">
          <div class="px-4 py-5 sm:p-6">
            <div v-if="tasksStore.loading" class="flex justify-center py-8">
              <div class="loading-spinner"></div>
            </div>

            <div v-else-if="tasksStore.filteredTasks.length === 0" class="text-center py-8">
              <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
              </svg>
              <h3 class="mt-2 text-sm font-medium text-gray-900">No tasks found</h3>
              <p class="mt-1 text-sm text-gray-500">
                {{ hasFilters ? 'Try adjusting your filters.' : 'Get started by creating a new task.' }}
              </p>
              <div v-if="!hasFilters" class="mt-6">
                <button
                  @click="showCreateModal = true"
                  class="btn-primary"
                >
                  Create Task
                </button>
              </div>
            </div>

            <div v-else>
              <draggable
                v-model="paginatedTasks"
                item-key="id"
                class="space-y-3"
                :disabled="hasFilters || hasPagination"
                @end="handleDragEnd"
              >
                <template #item="{ element: task }">
                  <div
                    class="task-item"
                    :class="{ 'opacity-75': task.status === 'completed' }"
                  >
                    <div class="flex items-center justify-between">
                      <div class="flex items-center space-x-3 flex-1">
                        <!-- Drag Handle -->
                        <div
                          v-if="!hasFilters && !hasPagination"
                          class="cursor-move text-gray-500 hover:text-gray-700 p-1 rounded hover:bg-gray-100 transition-colors"
                          title="Drag to reorder"
                        >
                          <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M7 2a2 2 0 1 0 0 4 2 2 0 0 0 0-4zM7 8a2 2 0 1 0 0 4 2 2 0 0 0 0-4zM7 14a2 2 0 1 0 0 4 2 2 0 0 0 0-4zM13 2a2 2 0 1 0 0 4 2 2 0 0 0 0-4zM13 8a2 2 0 1 0 0 4 2 2 0 0 0 0-4zM13 14a2 2 0 1 0 0 4 2 2 0 0 0 0-4z"/>
                          </svg>
                        </div>

                        <!-- Checkbox -->
                        <button
                          @click="toggleTaskStatus(task)"
                          class="flex-shrink-0"
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

                        <!-- Task Content -->
                        <div class="flex-1 min-w-0">
                          <h3
                            class="text-sm font-medium text-gray-900"
                            :class="{ 'line-through text-gray-500': task.status === 'completed' }"
                          >
                            {{ task.title }}
                          </h3>
                          <p class="text-sm text-gray-500 mt-1">{{ task.description }}</p>
                          <div class="flex items-center space-x-2 mt-2">
                            <span
                              class="badge"
                              :class="`priority-${task.priority}`"
                            >
                              {{ task.priority }}
                            </span>
                            <span
                              class="badge"
                              :class="`status-${task.status}`"
                            >
                              {{ task.status }}
                            </span>
                            <span class="text-xs text-gray-400">
                              {{ formatDate(task.created_at) }}
                            </span>
                          </div>
                        </div>
                      </div>

                      <!-- Actions -->
                      <div class="flex items-center space-x-2">
                        <button
                          @click="editTask(task)"
                          class="text-gray-400 hover:text-gray-600"
                        >
                          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                          </svg>
                        </button>
                        <button
                          @click="deleteTask(task)"
                          class="text-red-400 hover:text-red-600"
                        >
                          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                          </svg>
                        </button>
                      </div>
                    </div>
                  </div>
                </template>
              </draggable>

              <!-- Pagination -->
              <div v-if="totalPages > 1" class="mt-6 flex items-center justify-between border-t border-gray-200 pt-4">
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
                    :disabled="pagination.current_page >= totalPages"
                    class="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 disabled:opacity-50"
                  >
                    Next
                  </button>
                </div>
                <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
                  <div>
                    <p class="text-sm text-gray-700">
                      Showing
                      <span class="font-medium">{{ startIndex + 1 }}</span>
                      to
                      <span class="font-medium">{{ Math.min(endIndex, totalTasks) }}</span>
                      of
                      <span class="font-medium">{{ totalTasks }}</span>
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
                        v-for="page in visiblePages"
                        :key="page"
                        @click="changePage(page)"
                        :class="[
                          'relative inline-flex items-center px-4 py-2 border text-sm font-medium',
                          page === pagination.current_page
                            ? 'z-10 bg-primary-50 border-primary-500 text-primary-600'
                            : 'bg-white border-gray-300 text-gray-500 hover:bg-gray-50'
                        ]"
                      >
                        {{ page }}
                      </button>
                      <button
                        @click="changePage(pagination.current_page + 1)"
                        :disabled="pagination.current_page >= totalPages"
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

    <!-- Create/Edit Task Modal -->
    <TaskModal
      :show="showCreateModal || showEditModal"
      :task="editingTask"
      @close="closeModal"
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
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted, watch } from 'vue'
import { useTasksStore } from '@/stores/tasks'
import draggable from 'vuedraggable'
import TaskModal from '@/components/ui/TaskModal.vue'
import ConfirmModal from '@/components/ui/ConfirmModal.vue'
import { socketService } from '@/services/socket'

const tasksStore = useTasksStore()

const showCreateModal = ref(false)
const showEditModal = ref(false)
const showDeleteModal = ref(false)
const editingTask = ref(null)
const deletingTask = ref(null)

const filters = ref({
  search: '',
  status: 'all',
  priority: 'all'
})

const pagination = ref({
  current_page: 1,
  last_page: 1,
  per_page: 6,
  total: 0,
  from: 0,
  to: 0
})

const paginatedTasks = ref([])
const draggableTasks = ref([])

const hasFilters = computed(() => {
  return filters.value.search || 
         filters.value.status !== 'all' || 
         filters.value.priority !== 'all'
})

const hasPagination = computed(() => {
  return totalTasks.value > pagination.value.per_page
})

// Pagination computed properties
const totalTasks = computed(() => {
  return tasksStore.filteredTasks.length
})

const totalPages = computed(() => {
  return Math.ceil(totalTasks.value / pagination.value.per_page)
})

const startIndex = computed(() => {
  return (pagination.value.current_page - 1) * pagination.value.per_page
})

const endIndex = computed(() => {
  return startIndex.value + pagination.value.per_page
})

const paginatedTasksComputed = computed(() => {
  const start = startIndex.value
  const end = endIndex.value
  return tasksStore.filteredTasks.slice(start, end)
})

const visiblePages = computed(() => {
  const current = pagination.value.current_page
  const total = totalPages.value
  const pages = []
  
  // Show up to 5 pages around current page
  const start = Math.max(1, current - 2)
  const end = Math.min(total, current + 2)
  
  for (let i = start; i <= end; i++) {
    pages.push(i)
  }
  
  return pages
})

// Update pagination info
const updatePagination = () => {
  pagination.value.total = totalTasks.value
  pagination.value.last_page = totalPages.value
  pagination.value.from = totalTasks.value > 0 ? startIndex.value + 1 : 0
  pagination.value.to = Math.min(endIndex.value, totalTasks.value)
}

// Update draggable tasks when filtered tasks change
watch(() => tasksStore.filteredTasks, (newTasks) => {
  draggableTasks.value = [...newTasks]
  updatePagination()
}, { immediate: true })

// Update paginated tasks when pagination changes
watch(() => paginatedTasksComputed.value, (newTasks) => {
  paginatedTasks.value = [...newTasks]
}, { immediate: true })

// Watch pagination per_page changes
watch(() => pagination.value.per_page, () => {
  pagination.value.current_page = 1
  updatePagination()
})

let searchTimeout = null
const debouncedSearch = () => {
  clearTimeout(searchTimeout)
  searchTimeout = setTimeout(() => {
    applyFilters()
  }, 300)
}

const applyFilters = () => {
  tasksStore.setFilters(filters.value)
  tasksStore.fetchTasks()
}

const changePage = (page) => {
  if (page >= 1 && page <= totalPages.value) {
    pagination.value.current_page = page
    updatePagination()
  }
}

const changePerPage = () => {
  pagination.value.current_page = 1
  updatePagination()
}

const handleDragEnd = async (event) => {
  if (hasFilters.value) return
  
  try {
    // Update the order based on the new positions
    const reorderedTasks = draggableTasks.value.map((task, index) => ({
      ...task,
      order: index + 1
    }))
    
    await tasksStore.reorderTasks(reorderedTasks)
  } catch (error) {
    console.error('Failed to reorder tasks:', error)
    // Revert the draggable tasks on error
    draggableTasks.value = [...tasksStore.filteredTasks]
  }
}

const toggleTaskStatus = async (task) => {
  try {
    const result = await tasksStore.toggleTaskStatus(task.id)
    
    // Emit socket event for real-time updates with complete user data
    if (result) {
      socketService.emitTaskStatusUpdate({
        id: result.id,
        status: result.status,
        title: result.title,
        description: result.description,
        priority: result.priority,
        user: result.user || task.user, // Ensure user data is included
        user_id: result.user_id || task.user_id,
        created_at: result.created_at,
        updated_at: result.updated_at
      })
    }
  } catch (error) {
    console.error('Failed to toggle task status:', error)
  }
}

const editTask = (task) => {
  editingTask.value = { ...task }
  showEditModal.value = true
}

const deleteTask = (task) => {
  deletingTask.value = task
  showDeleteModal.value = true
}

const confirmDelete = async () => {
  if (deletingTask.value) {
    try {
      await tasksStore.deleteTask(deletingTask.value.id)
      
      // Emit socket event for task deletion with complete task info
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
    } catch (error) {
      console.error('Failed to delete task:', error)
    }
  }
}

const closeModal = () => {
  showCreateModal.value = false
  showEditModal.value = false
  editingTask.value = null
}

const handleTaskSave = async (taskData) => {
  try {
    let result
    if (editingTask.value) {
      result = await tasksStore.updateTask(editingTask.value.id, taskData)
      // Emit socket event for task update
      if (result) {
        socketService.emitTaskUpdated(result)
      }
    } else {
      result = await tasksStore.createTask(taskData)
      // Emit socket event for task creation
      if (result) {
        socketService.emitTaskCreated(result)
      }
    }
    closeModal()
  } catch (error) {
    console.error('Failed to save task:', error)
  }
}

const formatDate = (dateString) => {
  return new Date(dateString).toLocaleDateString()
}

// Watch for route query parameters
watch(() => filters.value, () => {
  applyFilters()
}, { deep: true })

// Socket event handlers for receiving updates from admin
const handleSocketTaskStatusUpdated = (updatedTask) => {
  console.log('User view - Socket event received: task-status-updated', updatedTask)
  // Only update if this is from another user (not our own action)
  const existingTask = tasksStore.getTaskById(updatedTask.id)
  if (existingTask && existingTask.status !== updatedTask.status) {
    tasksStore.updateTaskInList(updatedTask)
    draggableTasks.value = [...tasksStore.filteredTasks]
  }
}

const handleSocketTaskUpdated = (updatedTask) => {
  console.log('User view - Socket event received: task-updated', updatedTask)
  // Only update if task exists and is different
  const existingTask = tasksStore.getTaskById(updatedTask.id)
  if (existingTask) {
    tasksStore.updateTaskInList(updatedTask)
    draggableTasks.value = [...tasksStore.filteredTasks]
  }
}

const handleSocketTaskCreated = (newTask) => {
  console.log('User view - Socket event received: task-created', newTask)
  // Only add if task doesn't already exist (prevent duplicates)
  const existingTask = tasksStore.getTaskById(newTask.id)
  if (!existingTask) {
    tasksStore.addTask(newTask)
    draggableTasks.value = [...tasksStore.filteredTasks]
  }
}

const handleSocketTaskDeleted = (deletedTask) => {
  console.log('User view - Socket event received: task-deleted', deletedTask)
  // Only remove if task exists
  const existingTask = tasksStore.getTaskById(deletedTask.id)
  if (existingTask) {
    tasksStore.removeTask(deletedTask.id)
    draggableTasks.value = [...tasksStore.filteredTasks]
  }
}

const handleSocketTaskUpdatedData = (updatedTask) => {
  console.log('User view - Socket event received: task-updated-data', updatedTask)
  // Only update if task exists
  const existingTask = tasksStore.getTaskById(updatedTask.id)
  if (existingTask) {
    tasksStore.updateTaskInList(updatedTask)
    draggableTasks.value = [...tasksStore.filteredTasks]
  }
}

onMounted(async () => {
  // Connect to socket
  console.log('User view - Connecting to socket...')
  socketService.connect()
  
  // Add a small delay to ensure connection is established
  setTimeout(() => {
    console.log('User view - Setting up socket listeners...')
    // Listen for socket events from admin actions
    socketService.onTaskStatusUpdated(handleSocketTaskStatusUpdated)
    socketService.onTaskUpdated(handleSocketTaskUpdated)
    socketService.onTaskCreated(handleSocketTaskCreated)
    socketService.onTaskDeleted(handleSocketTaskDeleted)
    socketService.onTaskUpdatedData(handleSocketTaskUpdatedData)
    console.log('User view - Socket listeners set up')
  }, 1000)
  
  await tasksStore.fetchTasks()
})

onUnmounted(() => {
  // Clean up socket connection
  socketService.removeAllListeners()
})
</script>
