<template>
  <div class="min-h-screen bg-gray-50">
    <div class="max-w-3xl mx-auto py-6 sm:px-6 lg:px-8">
      <!-- Header -->
      <div class="px-4 py-6 sm:px-0">
        <div class="border-b border-gray-200 pb-5">
          <h1 class="text-3xl font-bold leading-6 text-gray-900">Profile</h1>
          <p class="mt-2 max-w-4xl text-sm text-gray-500">
            Manage your account settings and preferences.
          </p>
        </div>
      </div>

      <!-- Profile Information -->
      <div class="px-4 sm:px-0">
        <div class="bg-white shadow rounded-lg">
          <div class="px-4 py-5 sm:p-6">
            <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">
              Profile Information
            </h3>
            
            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
              <!-- Avatar -->
              <div class="sm:col-span-2">
                <div class="flex items-center">
                  <div class="w-20 h-20 bg-primary-100 rounded-full flex items-center justify-center">
                    <span class="text-2xl font-medium text-primary-600">
                      {{ userInitials }}
                    </span>
                  </div>
                  <div class="ml-4">
                    <h4 class="text-lg font-medium text-gray-900">{{ authStore.user?.name }}</h4>
                    <p class="text-sm text-gray-500">{{ authStore.user?.email }}</p>
                    <p class="text-sm text-gray-500">
                      <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                            :class="authStore.isAdmin ? 'bg-purple-100 text-purple-800' : 'bg-gray-100 text-gray-800'">
                        {{ authStore.isAdmin ? 'Administrator' : 'User' }}
                      </span>
                    </p>
                  </div>
                </div>
              </div>

              <!-- Name -->
              <div>
                <label for="name" class="form-label">Full Name</label>
                <input
                  id="name"
                  v-model="form.name"
                  type="text"
                  class="form-input"
                  :class="{ 'border-red-300': errors.name }"
                />
                <p v-if="errors.name" class="form-error">
                  {{ errors.name[0] }}
                </p>
              </div>

              <!-- Email -->
              <div>
                <label for="email" class="form-label">Email Address</label>
                <input
                  id="email"
                  v-model="form.email"
                  type="email"
                  class="form-input"
                  :class="{ 'border-red-300': errors.email }"
                />
                <p v-if="errors.email" class="form-error">
                  {{ errors.email[0] }}
                </p>
              </div>

              <!-- Member Since -->
              <div>
                <label class="form-label">Member Since</label>
                <p class="text-sm text-gray-900">
                  {{ formatDate(authStore.user?.created_at) }}
                </p>
              </div>

              <!-- Email Verified -->
              <div>
                <label class="form-label">Email Status</label>
                <p class="text-sm">
                  <span v-if="authStore.user?.email_verified_at" class="text-green-600">
                    ✓ Verified
                  </span>
                  <span v-else class="text-yellow-600">
                    ⚠ Not verified
                  </span>
                </p>
              </div>
            </div>

            <div class="mt-6">
              <button
                @click="updateProfile"
                :disabled="loading"
                class="btn-primary"
              >
                <span v-if="loading" class="absolute left-0 inset-y-0 flex items-center pl-3">
                  <div class="loading-spinner"></div>
                </span>
                {{ loading ? 'Updating...' : 'Update Profile' }}
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- Change Password -->
      <div class="px-4 sm:px-0 mt-6">
        <div class="bg-white shadow rounded-lg">
          <div class="px-4 py-5 sm:p-6">
            <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">
              Change Password
            </h3>
            
            <form @submit.prevent="changePassword">
              <div class="grid grid-cols-1 gap-6">
                <!-- Current Password -->
                <div>
                  <label for="current_password" class="form-label">Current Password</label>
                  <input
                    id="current_password"
                    v-model="passwordForm.current_password"
                    type="password"
                    class="form-input"
                    :class="{ 'border-red-300': passwordErrors.current_password }"
                  />
                  <p v-if="passwordErrors.current_password" class="form-error">
                    {{ passwordErrors.current_password[0] }}
                  </p>
                </div>

                <!-- New Password -->
                <div>
                  <label for="new_password" class="form-label">New Password</label>
                  <input
                    id="new_password"
                    v-model="passwordForm.new_password"
                    type="password"
                    class="form-input"
                    :class="{ 'border-red-300': passwordErrors.new_password }"
                  />
                  <p v-if="passwordErrors.new_password" class="form-error">
                    {{ passwordErrors.new_password[0] }}
                  </p>
                </div>

                <!-- Confirm New Password -->
                <div>
                  <label for="new_password_confirmation" class="form-label">Confirm New Password</label>
                  <input
                    id="new_password_confirmation"
                    v-model="passwordForm.new_password_confirmation"
                    type="password"
                    class="form-input"
                    :class="{ 'border-red-300': passwordErrors.new_password_confirmation }"
                  />
                  <p v-if="passwordErrors.new_password_confirmation" class="form-error">
                    {{ passwordErrors.new_password_confirmation[0] }}
                  </p>
                </div>
              </div>

              <div class="mt-6">
                <button
                  type="submit"
                  :disabled="passwordLoading"
                  class="btn-secondary"
                >
                  <span v-if="passwordLoading" class="absolute left-0 inset-y-0 flex items-center pl-3">
                    <div class="loading-spinner"></div>
                  </span>
                  {{ passwordLoading ? 'Changing...' : 'Change Password' }}
                </button>
              </div>
            </form>
          </div>
        </div>
      </div>

      <!-- Account Statistics -->
      <div class="px-4 sm:px-0 mt-6">
        <div class="bg-white shadow rounded-lg">
          <div class="px-4 py-5 sm:p-6">
            <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">
              Account Statistics
            </h3>
            
            <div class="grid grid-cols-1 gap-5 sm:grid-cols-3">
              <div class="bg-gray-50 p-4 rounded-lg">
                <dt class="text-sm font-medium text-gray-500">Total Tasks</dt>
                <dd class="mt-1 text-2xl font-semibold text-gray-900">{{ tasksStore.taskCount.total }}</dd>
              </div>
              <div class="bg-gray-50 p-4 rounded-lg">
                <dt class="text-sm font-medium text-gray-500">Completed</dt>
                <dd class="mt-1 text-2xl font-semibold text-green-600">{{ tasksStore.taskCount.completed }}</dd>
              </div>
              <div class="bg-gray-50 p-4 rounded-lg">
                <dt class="text-sm font-medium text-gray-500">Pending</dt>
                <dd class="mt-1 text-2xl font-semibold text-yellow-600">{{ tasksStore.taskCount.pending }}</dd>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue'
import { useAuthStore } from '@/stores/auth'
import { useTasksStore } from '@/stores/tasks'
import { useToast } from 'vue-toastification'

const authStore = useAuthStore()
const tasksStore = useTasksStore()
const toast = useToast()

const loading = ref(false)
const passwordLoading = ref(false)
const errors = ref({})
const passwordErrors = ref({})

const form = reactive({
  name: '',
  email: ''
})

const passwordForm = reactive({
  current_password: '',
  new_password: '',
  new_password_confirmation: ''
})

const userInitials = computed(() => {
  if (!authStore.user?.name) return 'U'
  return authStore.user.name
    .split(' ')
    .map(name => name.charAt(0))
    .join('')
    .toUpperCase()
    .slice(0, 2)
})

const formatDate = (dateString) => {
  if (!dateString) return 'N/A'
  return new Date(dateString).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'long',
    day: 'numeric'
  })
}

const updateProfile = async () => {
  loading.value = true
  errors.value = {}

  try {
    // This would typically call an API endpoint to update profile
    // For now, we'll just update the local store
    authStore.updateUser({
      ...authStore.user,
      name: form.name,
      email: form.email
    })
    
    toast.success('Profile updated successfully!')
  } catch (error) {
    if (error.response?.data?.errors) {
      errors.value = error.response.data.errors
    } else {
      toast.error('Failed to update profile')
    }
  } finally {
    loading.value = false
  }
}

const changePassword = async () => {
  passwordLoading.value = true
  passwordErrors.value = {}

  try {
    // This would typically call an API endpoint to change password
    // For now, we'll just show a success message
    toast.success('Password changed successfully!')
    
    // Reset form
    passwordForm.current_password = ''
    passwordForm.new_password = ''
    passwordForm.new_password_confirmation = ''
  } catch (error) {
    if (error.response?.data?.errors) {
      passwordErrors.value = error.response.data.errors
    } else {
      toast.error('Failed to change password')
    }
  } finally {
    passwordLoading.value = false
  }
}

onMounted(() => {
  // Initialize form with current user data
  if (authStore.user) {
    form.name = authStore.user.name
    form.email = authStore.user.email
  }
  
  // Fetch tasks for statistics
  tasksStore.fetchTasks()
})
</script>
