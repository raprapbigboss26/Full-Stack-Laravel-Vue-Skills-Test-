<template>
  <div
    v-if="show"
    class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50"
    @click="handleBackdropClick"
  >
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
      <div class="mt-3">
        <!-- Header -->
        <div class="flex items-center justify-between mb-4">
          <h3 class="text-lg font-medium text-gray-900">
            {{ task ? 'Edit Task' : 'Create New Task' }}
          </h3>
          <button
            @click="$emit('close')"
            class="text-gray-400 hover:text-gray-600"
          >
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>

        <!-- Form -->
        <form @submit.prevent="handleSubmit">
          <div class="space-y-4">
            <!-- Title -->
            <div>
              <label for="title" class="form-label">Title</label>
              <input
                id="title"
                v-model="form.title"
                type="text"
                required
                class="form-input"
                :class="{ 'border-red-300': errors.title }"
                placeholder="Enter task title"
              />
              <p v-if="errors.title" class="form-error">
                {{ errors.title[0] }}
              </p>
            </div>

            <!-- Description -->
            <div>
              <label for="description" class="form-label">Description</label>
              <textarea
                id="description"
                v-model="form.description"
                rows="3"
                required
                class="form-input"
                :class="{ 'border-red-300': errors.description }"
                placeholder="Enter task description"
              ></textarea>
              <p v-if="errors.description" class="form-error">
                {{ errors.description[0] }}
              </p>
            </div>

            <!-- Priority -->
            <div>
              <label for="priority" class="form-label">Priority</label>
              <select
                id="priority"
                v-model="form.priority"
                required
                class="form-input"
                :class="{ 'border-red-300': errors.priority }"
              >
                <option value="">Select priority</option>
                <option value="low">Low</option>
                <option value="medium">Medium</option>
                <option value="high">High</option>
              </select>
              <p v-if="errors.priority" class="form-error">
                {{ errors.priority[0] }}
              </p>
            </div>
          </div>

          <!-- Actions -->
          <div class="flex items-center justify-end space-x-3 mt-6">
            <button
              type="button"
              @click="$emit('close')"
              class="btn-outline"
            >
              Cancel
            </button>
            <button
              type="submit"
              :disabled="loading"
              class="btn-primary"
            >
              <span v-if="loading" class="absolute left-0 inset-y-0 flex items-center pl-3">
                <div class="loading-spinner"></div>
              </span>
              {{ loading ? 'Saving...' : (task ? 'Update Task' : 'Create Task') }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, watch } from 'vue'

const props = defineProps({
  show: {
    type: Boolean,
    default: false
  },
  task: {
    type: Object,
    default: null
  }
})

const emit = defineEmits(['close', 'save'])

const loading = ref(false)
const errors = ref({})

const form = reactive({
  title: '',
  description: '',
  priority: ''
})

const resetForm = () => {
  form.title = ''
  form.description = ''
  form.priority = ''
  errors.value = {}
}

const handleBackdropClick = (event) => {
  if (event.target === event.currentTarget) {
    emit('close')
  }
}

const handleSubmit = async () => {
  loading.value = true
  errors.value = {}

  try {
    await emit('save', {
      title: form.title,
      description: form.description,
      priority: form.priority
    })
    resetForm()
  } catch (error) {
    if (error.response?.data?.errors) {
      errors.value = error.response.data.errors
    }
  } finally {
    loading.value = false
  }
}

// Watch for task prop changes to populate form
watch(() => props.task, (newTask) => {
  if (newTask) {
    form.title = newTask.title
    form.description = newTask.description
    form.priority = newTask.priority
  } else {
    resetForm()
  }
}, { immediate: true })

// Reset form when modal is closed
watch(() => props.show, (newShow) => {
  if (!newShow) {
    resetForm()
  }
})
</script>
