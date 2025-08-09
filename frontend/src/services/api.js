import axios from 'axios'

class ApiService {
  constructor() {
    this.api = axios.create({
      baseURL: 'http://127.0.0.1:8000/api',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
      },
      withCredentials: false,
    })

    // Request interceptor to add auth token
    this.api.interceptors.request.use(
      (config) => {
        const token = localStorage.getItem('auth_token')
        if (token) {
          config.headers.Authorization = `Bearer ${token}`
        }
        return config
      },
      (error) => {
        return Promise.reject(error)
      }
    )

    // Response interceptor to handle errors
    this.api.interceptors.response.use(
      (response) => response,
      (error) => {
        if (error.response?.status === 401) {
          // Only redirect to login if we're not already on the login page
          if (!window.location.pathname.includes('/login')) {
            localStorage.removeItem('auth_token')
            window.location.href = '/login'
          }
        }
        return Promise.reject(error)
      }
    )
  }

  // Auth endpoints
  async login(credentials) {
    const response = await this.api.post('/auth/login', credentials)
    return response.data
  }

  async register(credentials) {
    const response = await this.api.post('/auth/register', credentials)
    return response.data
  }

  async logout() {
    const response = await this.api.post('/auth/logout')
    return response.data
  }

  async getUser() {
    const response = await this.api.get('/auth/user')
    return response.data
  }

  async refreshToken() {
    const response = await this.api.post('/auth/refresh')
    return response.data
  }

  // Task endpoints
  async getTasks(filters = {}) {
    const params = new URLSearchParams()
    
    if (filters.status && filters.status !== 'all') {
      params.append('status', filters.status)
    }
    
    if (filters.priority && filters.priority !== 'all') {
      params.append('priority', filters.priority)
    }
    
    if (filters.search) {
      params.append('search', filters.search)
    }

    const response = await this.api.get(`/tasks?${params.toString()}`)
    return response.data
  }

  async getTask(id) {
    const response = await this.api.get(`/tasks/${id}`)
    return response.data
  }

  async createTask(data) {
    const response = await this.api.post('/tasks', data)
    return response.data
  }

  async updateTask(id, data) {
    const response = await this.api.put(`/tasks/${id}`, data)
    return response.data
  }

  async deleteTask(id) {
    const response = await this.api.delete(`/tasks/${id}`)
    return response.data
  }

  async completeTask(id) {
    const response = await this.api.patch(`/tasks/${id}/complete`)
    return response.data
  }

  async markTaskPending(id) {
    const response = await this.api.patch(`/tasks/${id}/pending`)
    return response.data
  }

  async reorderTasks(data) {
    const response = await this.api.post('/tasks/reorder', data)
    return response.data
  }

  async getTaskStatistics() {
    const response = await this.api.get('/tasks/statistics')
    return response.data
  }

  // Admin endpoints
  async getAdminDashboard() {
    const response = await this.api.get('/admin/dashboard')
    return response.data
  }

  async getAllUsers() {
    const response = await this.api.get('/admin/users')
    return response.data
  }

  async getUserTasks(userId) {
    const response = await this.api.get(`/admin/users/${userId}/tasks`)
    return response.data
  }

  async updateUserRole(userId, isAdmin) {
    const response = await this.api.patch(`/admin/users/${userId}/role`, { is_admin: isAdmin })
    return response.data
  }

  async getAllTasks() {
    const response = await this.api.get('/admin/tasks')
    return response.data
  }

  async getAdminTasks(params = {}) {
    const queryParams = new URLSearchParams()
    
    if (params.page) {
      queryParams.append('page', params.page)
    }
    
    if (params.search) {
      queryParams.append('search', params.search)
    }
    
    if (params.status && params.status !== '') {
      queryParams.append('status', params.status)
    }
    
    if (params.priority && params.priority !== '') {
      queryParams.append('priority', params.priority)
    }
    
    if (params.user_id && params.user_id !== '') {
      queryParams.append('user_id', params.user_id)
    }

    const response = await this.api.get(`/admin/tasks?${queryParams.toString()}`)
    return response.data
  }

  async toggleTaskStatus(taskId) {
    // Get current task to determine new status
    const task = await this.getTask(taskId)
    const newStatus = task.data.status === 'completed' ? 'pending' : 'completed'
    
    if (newStatus === 'completed') {
      return await this.completeTask(taskId)
    } else {
      return await this.markTaskPending(taskId)
    }
  }

  async deleteAnyTask(id) {
    const response = await this.api.delete(`/admin/tasks/${id}`)
    return response.data
  }

  async getAdminStatistics() {
    const response = await this.api.get('/admin/statistics')
    return response.data
  }

  // Alias methods for compatibility
  async getUsers() {
    return this.getAllUsers()
  }

  async toggleUserAdmin(userId, isAdmin) {
    const response = await this.api.patch(`/admin/users/${userId}/role`, { is_admin: isAdmin })
    return response.data
  }

  // Utility methods
  setAuthToken(token) {
    localStorage.setItem('auth_token', token)
  }

  removeAuthToken() {
    localStorage.removeItem('auth_token')
  }

  getAuthToken() {
    return localStorage.getItem('auth_token')
  }
}

export const apiService = new ApiService()
export default apiService
