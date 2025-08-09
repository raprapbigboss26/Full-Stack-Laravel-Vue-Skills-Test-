<template>
  <div class="min-h-screen flex items-center justify-center relative overflow-hidden py-12 px-4 sm:px-6 lg:px-8">
    <!-- Animated Background -->
    <div class="absolute inset-0 bg-gradient-to-br from-emerald-50 via-teal-50 to-cyan-50">
      <!-- Floating Shapes -->
      <div class="absolute top-10 left-10 w-72 h-72 bg-gradient-to-r from-emerald-300 to-teal-300 rounded-full mix-blend-multiply filter blur-xl opacity-70 animate-blob"></div>
      <div class="absolute top-10 right-10 w-72 h-72 bg-gradient-to-r from-cyan-300 to-blue-300 rounded-full mix-blend-multiply filter blur-xl opacity-70 animate-blob animation-delay-2000"></div>
      <div class="absolute -bottom-8 left-20 w-72 h-72 bg-gradient-to-r from-teal-300 to-emerald-300 rounded-full mix-blend-multiply filter blur-xl opacity-70 animate-blob animation-delay-4000"></div>
      
      <!-- Grid Pattern -->
      <div class="absolute inset-0 opacity-20">
        <svg class="absolute inset-0 h-full w-full" fill="none" viewBox="0 0 400 400" xmlns="http://www.w3.org/2000/svg">
          <defs>
            <pattern id="grid-register" width="40" height="40" patternUnits="userSpaceOnUse">
              <path d="M 40 0 L 0 0 0 40" fill="none" stroke="rgb(20 184 166)" stroke-width="1"/>
            </pattern>
          </defs>
          <rect width="100%" height="100%" fill="url(#grid-register)" />
        </svg>
      </div>
    </div>
    <div class="max-w-md w-full space-y-8 relative z-10">
      <!-- Glass Card Effect -->
      <div class="bg-white/80 backdrop-blur-lg rounded-3xl shadow-2xl border border-white/20 p-8">
        <!-- Logo and Header -->
        <div class="text-center">
          <div class="flex justify-center">
            <div class="w-16 h-16 bg-gradient-to-br from-slate-600 to-slate-700 rounded-2xl flex items-center justify-center shadow-lg">
              <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
              </svg>
            </div>
          </div>
          <h2 class="mt-6 text-3xl font-bold text-gray-900">
            Create your account
          </h2>
          <p class="mt-2 text-sm text-gray-600">
            Join TaskManager and start organizing your work
          </p>
        </div>

        <!-- Registration Form -->
        <div class="mt-8">
          <form class="space-y-5" @submit.prevent="handleSubmit">
            <!-- Name Field -->
            <div>
              <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
                Full Name
              </label>
              <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                  <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                  </svg>
                </div>
                <input
                  id="name"
                  v-model="form.name"
                  name="name"
                  type="text"
                  autocomplete="name"
                  required
                  class="block w-full pl-10 pr-3 py-3 border border-gray-300 rounded-xl shadow-sm placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-slate-500 focus:border-transparent transition-all duration-200"
                  placeholder="Enter your full name"
                  :class="{ 'border-red-300 focus:ring-red-500': errors.name }"
                />
              </div>
              <p v-if="errors.name" class="mt-2 text-sm text-red-600">
                {{ errors.name[0] }}
              </p>
            </div>

            <!-- Email Field -->
            <div>
              <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
                Email address
              </label>
              <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                  <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207" />
                  </svg>
                </div>
                <input
                  id="email"
                  v-model="form.email"
                  name="email"
                  type="email"
                  autocomplete="email"
                  required
                  class="block w-full pl-10 pr-3 py-3 border border-gray-300 rounded-xl shadow-sm placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-slate-500 focus:border-transparent transition-all duration-200"
                  placeholder="Enter your email"
                  :class="{ 'border-red-300 focus:ring-red-500': errors.email }"
                />
              </div>
              <p v-if="errors.email" class="mt-2 text-sm text-red-600">
                {{ errors.email[0] }}
              </p>
            </div>

            <!-- Password Field -->
            <div>
              <label for="password" class="block text-sm font-medium text-gray-700 mb-2">
                Password
              </label>
              <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                  <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                  </svg>
                </div>
                <input
                  id="password"
                  v-model="form.password"
                  name="password"
                  type="password"
                  autocomplete="new-password"
                  required
                  class="block w-full pl-10 pr-3 py-3 border border-gray-300 rounded-xl shadow-sm placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-slate-500 focus:border-transparent transition-all duration-200"
                  placeholder="Enter your password"
                  :class="{ 'border-red-300 focus:ring-red-500': errors.password }"
                />
              </div>
              <p v-if="errors.password" class="mt-2 text-sm text-red-600">
                {{ errors.password[0] }}
              </p>
            </div>

            <!-- Confirm Password Field -->
            <div>
              <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-2">
                Confirm Password
              </label>
              <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                  <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                  </svg>
                </div>
                <input
                  id="password_confirmation"
                  v-model="form.password_confirmation"
                  name="password_confirmation"
                  type="password"
                  autocomplete="new-password"
                  required
                  class="block w-full pl-10 pr-3 py-3 border border-gray-300 rounded-xl shadow-sm placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-slate-500 focus:border-transparent transition-all duration-200"
                  placeholder="Confirm your password"
                  :class="{ 'border-red-300 focus:ring-red-500': errors.password_confirmation }"
                />
              </div>
              <p v-if="errors.password_confirmation" class="mt-2 text-sm text-red-600">
                {{ errors.password_confirmation[0] }}
              </p>
            </div>

            <!-- Terms and Conditions -->
            <div class="flex items-start">
              <input
                id="terms"
                v-model="form.terms"
                name="terms"
                type="checkbox"
                required
                class="h-4 w-4 text-slate-600 focus:ring-slate-500 border-gray-300 rounded mt-1"
              />
              <label for="terms" class="ml-3 block text-sm text-gray-700">
                I agree to the
                <a href="#" class="font-medium text-slate-600 hover:text-slate-500 transition-colors">Terms of Service</a>
                and
                <a href="#" class="font-medium text-slate-600 hover:text-slate-500 transition-colors">Privacy Policy</a>
              </label>
            </div>

            <!-- Create Account Button -->
            <div>
              <button
                type="submit"
                :disabled="loading || !form.terms"
                class="group relative w-full flex justify-center py-3 px-4 border border-transparent text-sm font-medium rounded-xl text-white bg-gradient-to-r from-slate-600 to-slate-700 hover:from-slate-700 hover:to-slate-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-slate-500 disabled:opacity-50 disabled:cursor-not-allowed transform transition-all duration-200 hover:scale-[1.02] shadow-lg hover:shadow-xl"
              >
                <span v-if="loading" class="absolute left-0 inset-y-0 flex items-center pl-3">
                  <div class="animate-spin rounded-full h-5 w-5 border-b-2 border-white"></div>
                </span>
                <span class="flex items-center">
                  <svg v-if="!loading" class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
                  </svg>
                  {{ loading ? 'Creating account...' : 'Create account' }}
                </span>
              </button>
            </div>

            <!-- Sign In Link -->
            <div class="text-center">
              <p class="text-sm text-gray-600">
                Already have an account?
                <router-link to="/login" class="font-medium text-slate-600 hover:text-slate-500 transition-colors">
                  Sign in here
                </router-link>
              </p>
            </div>
          </form>

          <!-- Password Requirements -->
          <div class="mt-6 p-4 bg-gradient-to-r from-gray-50 to-slate-50 rounded-2xl border border-gray-200">
            <div class="flex items-center mb-3">
              <div class="w-8 h-8 bg-slate-100 rounded-lg flex items-center justify-center mr-3">
                <svg class="w-4 h-4 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
              </div>
              <h3 class="text-sm font-semibold text-gray-800">Password Requirements</h3>
            </div>
            <div class="space-y-2 text-xs text-gray-700">
              <div class="flex items-center p-2 bg-white rounded-lg">
                <svg class="w-4 h-4 mr-2" :class="passwordValidation.length ? 'text-green-500' : 'text-gray-400'" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                </svg>
                <span :class="passwordValidation.length ? 'text-green-700' : 'text-gray-600'">
                  At least 8 characters long
                </span>
              </div>
              <div class="flex items-center p-2 bg-white rounded-lg">
                <svg class="w-4 h-4 mr-2" :class="passwordValidation.match ? 'text-green-500' : 'text-gray-400'" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                </svg>
                <span :class="passwordValidation.match ? 'text-green-700' : 'text-gray-600'">
                  Passwords match
                </span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, computed } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

const router = useRouter()
const authStore = useAuthStore()

const loading = ref(false)
const errors = ref({})

const form = reactive({
  name: '',
  email: '',
  password: '',
  password_confirmation: '',
  terms: false
})

const passwordValidation = computed(() => ({
  length: form.password.length >= 8,
  match: form.password === form.password_confirmation && form.password.length > 0
}))

const handleSubmit = async () => {
  loading.value = true
  errors.value = {}

  try {
    await authStore.register({
      name: form.name,
      email: form.email,
      password: form.password,
      password_confirmation: form.password_confirmation
    })
    
    router.push('/dashboard')
  } catch (error) {
    if (error.response?.data?.errors) {
      errors.value = error.response.data.errors
    }
  } finally {
    loading.value = false
  }
}
</script>

<style scoped>
@keyframes blob {
  0% {
    transform: translate(0px, 0px) scale(1);
  }
  33% {
    transform: translate(30px, -50px) scale(1.1);
  }
  66% {
    transform: translate(-20px, 20px) scale(0.9);
  }
  100% {
    transform: translate(0px, 0px) scale(1);
  }
}

.animate-blob {
  animation: blob 7s infinite;
}

.animation-delay-2000 {
  animation-delay: 2s;
}

.animation-delay-4000 {
  animation-delay: 4s;
}
</style>
