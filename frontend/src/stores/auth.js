import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import { apiService } from '@/services/api'
import { useToast } from 'vue-toastification'

export const useAuthStore = defineStore('auth', () => {
  // State
  const user = ref(null)
  const token = ref(localStorage.getItem('auth_token'))
  const loading = ref(false)

  // Getters
  const isAuthenticated = computed(() => !!token.value && !!user.value)
  const isAdmin = computed(() => user.value?.is_admin ?? false)

  // Actions
  const login = async (credentials) => {
    const toast = useToast()
    loading.value = true
    
    try {
      const response = await apiService.login(credentials)
      
      user.value = response.user
      token.value = response.token
      
      // Store token in localStorage and set API header
      localStorage.setItem('auth_token', response.token)
      apiService.setAuthToken(response.token)
      
      toast.success(response.message || 'Login successful!')
      
      return response
    } catch (error) {
      const message = error.response?.data?.message || 'Login failed'
      toast.error(message)
      throw error
    } finally {
      loading.value = false
    }
  }

  const register = async (credentials) => {
    const toast = useToast()
    loading.value = true
    
    try {
      const response = await apiService.register(credentials)
      
      user.value = response.user
      token.value = response.token
      
      apiService.setAuthToken(response.token)
      
      toast.success(response.message || 'Registration successful!')
      
      return response
    } catch (error) {
      const message = error.response?.data?.message || 'Registration failed'
      toast.error(message)
      throw error
    } finally {
      loading.value = false
    }
  }

  const logout = async () => {
    const toast = useToast()
    loading.value = true
    
    try {
      await apiService.logout()
      
      user.value = null
      token.value = null
      
      apiService.removeAuthToken()
      
      toast.success('Logged out successfully!')
    } catch (error) {
      // Even if logout fails on server, clear local state
      user.value = null
      token.value = null
      apiService.removeAuthToken()
      
      console.error('Logout error:', error)
    } finally {
      loading.value = false
    }
  }

  const fetchUser = async () => {
    const storedToken = localStorage.getItem('auth_token')
    if (!storedToken) {
      throw new Error('No token available')
    }
    
    // Set token if not already set
    if (!token.value) {
      token.value = storedToken
      apiService.setAuthToken(storedToken)
    }
    
    loading.value = true
    
    try {
      const response = await apiService.getUser()
      user.value = response.user
      return response
    } catch (error) {
      // If fetching user fails, clear auth state
      user.value = null
      token.value = null
      apiService.removeAuthToken()
      localStorage.removeItem('auth_token')
      throw error
    } finally {
      loading.value = false
    }
  }

  const refreshToken = async () => {
    if (!token.value) {
      throw new Error('No token to refresh')
    }
    
    try {
      const response = await apiService.refreshToken()
      token.value = response.token
      apiService.setAuthToken(response.token)
      return response
    } catch (error) {
      // If refresh fails, clear auth state
      user.value = null
      token.value = null
      apiService.removeAuthToken()
      throw error
    }
  }

  const updateUser = (updatedUser) => {
    user.value = updatedUser
  }

  const clearAuth = () => {
    user.value = null
    token.value = null
    apiService.removeAuthToken()
  }

  // Initialize auth state from localStorage
  const initializeAuth = async () => {
    const storedToken = localStorage.getItem('auth_token')
    if (storedToken && !user.value) {
      token.value = storedToken
      try {
        await fetchUser()
      } catch (error) {
        clearAuth()
      }
    }
  }

  return {
    // State
    user,
    token,
    loading,
    
    // Getters
    isAuthenticated,
    isAdmin,
    
    // Actions
    login,
    register,
    logout,
    fetchUser,
    refreshToken,
    updateUser,
    clearAuth,
    initializeAuth
  }
})
