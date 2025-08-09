import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import { apiService } from '@/services/api'
import { useToast } from 'vue-toastification'

export const useTasksStore = defineStore('tasks', () => {
  // State
  const tasks = ref([])
  const loading = ref(false)
  const statistics = ref(null)
  const filters = ref({
    status: 'all',
    priority: 'all',
    search: ''
  })

  // Getters
  const filteredTasks = computed(() => {
    let filtered = [...tasks.value]

    // Filter by status
    if (filters.value.status && filters.value.status !== 'all') {
      filtered = filtered.filter(task => task.status === filters.value.status)
    }

    // Filter by priority
    if (filters.value.priority && filters.value.priority !== 'all') {
      filtered = filtered.filter(task => task.priority === filters.value.priority)
    }

    // Filter by search
    if (filters.value.search) {
      const searchTerm = filters.value.search.toLowerCase()
      filtered = filtered.filter(task => 
        task.title.toLowerCase().includes(searchTerm) ||
        task.description.toLowerCase().includes(searchTerm)
      )
    }

    // Sort by order
    return filtered.sort((a, b) => a.order - b.order)
  })

  const pendingTasks = computed(() => 
    tasks.value.filter(task => task.status === 'pending')
  )

  const completedTasks = computed(() => 
    tasks.value.filter(task => task.status === 'completed')
  )

  const highPriorityTasks = computed(() => 
    tasks.value.filter(task => task.priority === 'high')
  )

  const taskCount = computed(() => ({
    total: tasks.value.length,
    pending: pendingTasks.value.length,
    completed: completedTasks.value.length,
    high: highPriorityTasks.value.length
  }))

  // Actions
  const fetchTasks = async (forceRefresh = false) => {
    if (loading.value && !forceRefresh) return

    loading.value = true
    const toast = useToast()

    try {
      const response = await apiService.getTasks(filters.value)
      tasks.value = response.data
    } catch (error) {
      const message = error.response?.data?.message || 'Failed to fetch tasks'
      toast.error(message)
      console.error('Fetch tasks error:', error)
    } finally {
      loading.value = false
    }
  }

  const createTask = async (data) => {
    const toast = useToast()
    loading.value = true

    try {
      const response = await apiService.createTask(data)
      
      // Add to local state
      tasks.value.push(response.data)
      
      toast.success(response.message || 'Task created successfully!')
      
      return response.data
    } catch (error) {
      const message = error.response?.data?.message || 'Failed to create task'
      toast.error(message)
      throw error
    } finally {
      loading.value = false
    }
  }

  const updateTask = async (id, data) => {
    const toast = useToast()
    loading.value = true

    try {
      const response = await apiService.updateTask(id, data)
      
      // Update local state
      const index = tasks.value.findIndex(task => task.id === id)
      if (index !== -1) {
        tasks.value[index] = response.data
      }
      
      toast.success(response.message || 'Task updated successfully!')
      
      return response.data
    } catch (error) {
      const message = error.response?.data?.message || 'Failed to update task'
      toast.error(message)
      throw error
    } finally {
      loading.value = false
    }
  }

  const deleteTask = async (id) => {
    const toast = useToast()
    loading.value = true

    try {
      const response = await apiService.deleteTask(id)
      
      // Remove from local state
      tasks.value = tasks.value.filter(task => task.id !== id)
      
      toast.success(response.message || 'Task deleted successfully!')
    } catch (error) {
      const message = error.response?.data?.message || 'Failed to delete task'
      toast.error(message)
      throw error
    } finally {
      loading.value = false
    }
  }

  const toggleTaskStatus = async (id) => {
    const task = tasks.value.find(t => t.id === id)
    if (!task) return

    const toast = useToast()

    try {
      let response
      if (task.status === 'pending') {
        response = await apiService.completeTask(id)
      } else {
        response = await apiService.markTaskPending(id)
      }
      
      // Update local state
      const index = tasks.value.findIndex(t => t.id === id)
      if (index !== -1) {
        tasks.value[index] = response.data
      }
      
      const statusText = response.data.status === 'completed' ? 'completed' : 'pending'
      toast.success(`Task marked as ${statusText}!`)
      
      return response.data
    } catch (error) {
      const message = error.response?.data?.message || 'Failed to update task status'
      toast.error(message)
      throw error
    }
  }

  const reorderTasks = async (reorderedTasks) => {
    const toast = useToast()

    try {
      // Store the old tasks for potential rollback
      const oldTasks = [...tasks.value]

      // Update local state optimistically
      tasks.value = reorderedTasks.map((task, index) => ({
        ...task,
        order: index + 1
      }))

      // Prepare reorder data for API
      const reorderData = {
        tasks: reorderedTasks.map((task, index) => ({
          id: task.id,
          order: index + 1
        }))
      }

      await apiService.reorderTasks(reorderData)
      
      toast.success('Tasks reordered successfully!')
    } catch (error) {
      // Revert on error
      tasks.value = oldTasks
      
      const message = error.response?.data?.message || 'Failed to reorder tasks'
      toast.error(message)
      throw error
    }
  }

  const fetchStatistics = async () => {
    try {
      const response = await apiService.getTaskStatistics()
      statistics.value = response.data
    } catch (error) {
      console.error('Fetch statistics error:', error)
    }
  }

  const setFilters = (newFilters) => {
    filters.value = { ...filters.value, ...newFilters }
  }

  const clearFilters = () => {
    filters.value = {
      status: 'all',
      priority: 'all',
      search: ''
    }
  }

  const getTaskById = (id) => {
    return tasks.value.find(task => task.id === id)
  }

  const addTask = (task) => {
    tasks.value.push(task)
  }

  const removeTask = (id) => {
    tasks.value = tasks.value.filter(task => task.id !== id)
  }

  const updateTaskInList = (updatedTask) => {
    const index = tasks.value.findIndex(task => task.id === updatedTask.id)
    if (index !== -1) {
      tasks.value[index] = updatedTask
    }
  }

  const clearTasks = () => {
    tasks.value = []
    statistics.value = null
  }

  return {
    // State
    tasks,
    loading,
    statistics,
    filters,
    
    // Getters
    filteredTasks,
    pendingTasks,
    completedTasks,
    highPriorityTasks,
    taskCount,
    
    // Actions
    fetchTasks,
    createTask,
    updateTask,
    deleteTask,
    toggleTaskStatus,
    reorderTasks,
    fetchStatistics,
    setFilters,
    clearFilters,
    getTaskById,
    addTask,
    removeTask,
    updateTaskInList,
    clearTasks
  }
})
