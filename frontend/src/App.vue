<template>
  <div id="app" class="min-h-screen bg-gray-50">
    <Navbar v-if="authStore.isAuthenticated" />
    
    <main :class="{ 'pt-16': authStore.isAuthenticated }">
      <router-view />
    </main>
    
    <!-- Loading overlay -->
    <div
      v-if="loading"
      class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50"
    >
      <div class="bg-white rounded-lg p-6 flex items-center space-x-3">
        <div class="loading-spinner"></div>
        <span class="text-gray-700">Loading...</span>
      </div>
    </div>
  </div>
</template>

<script setup>
import { onMounted, ref } from 'vue'
import { useAuthStore } from '@/stores/auth'
import { useRouter } from 'vue-router'
import Navbar from '@/components/layout/Navbar.vue'

const authStore = useAuthStore()
const router = useRouter()
const loading = ref(false)

onMounted(async () => {
  // Initialize auth state from localStorage
  const token = localStorage.getItem('auth_token')
  if (token && !authStore.user) {
    loading.value = true
    try {
      // Set token first
      authStore.token = token
      // Then fetch user
      await authStore.fetchUser()
    } catch (error) {
      console.error('Failed to fetch user:', error)
      authStore.clearAuth()
      localStorage.removeItem('auth_token')
      // Only redirect if we're on a protected route
      if (router.currentRoute.value.meta.requiresAuth) {
        router.push('/login')
      }
    } finally {
      loading.value = false
    }
  }
})
</script>

<style scoped>
/* Component-specific styles if needed */
</style>
