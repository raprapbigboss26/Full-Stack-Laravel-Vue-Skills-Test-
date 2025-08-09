<template>
  <div
    v-if="show"
    class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50"
    @click="handleBackdropClick"
  >
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
      <div class="mt-3 text-center">
        <!-- Icon -->
        <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full mb-4"
             :class="iconClasses">
          <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path v-if="type === 'danger'" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z" />
            <path v-else-if="type === 'warning'" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            <path v-else stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
        </div>

        <!-- Title -->
        <h3 class="text-lg font-medium text-gray-900 mb-2">
          {{ title }}
        </h3>

        <!-- Message -->
        <p class="text-sm text-gray-500 mb-6">
          {{ message }}
        </p>

        <!-- Actions -->
        <div class="flex items-center justify-center space-x-3">
          <button
            @click="$emit('cancel')"
            class="btn-outline"
          >
            {{ cancelText }}
          </button>
          <button
            @click="$emit('confirm')"
            class="btn"
            :class="confirmButtonClasses"
          >
            {{ confirmText }}
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  show: {
    type: Boolean,
    default: false
  },
  title: {
    type: String,
    required: true
  },
  message: {
    type: String,
    required: true
  },
  confirmText: {
    type: String,
    default: 'Confirm'
  },
  cancelText: {
    type: String,
    default: 'Cancel'
  },
  type: {
    type: String,
    default: 'info',
    validator: (value) => ['danger', 'warning', 'info'].includes(value)
  }
})

const emit = defineEmits(['confirm', 'cancel'])

const iconClasses = computed(() => {
  switch (props.type) {
    case 'danger':
      return 'bg-red-100 text-red-600'
    case 'warning':
      return 'bg-yellow-100 text-yellow-600'
    default:
      return 'bg-blue-100 text-blue-600'
  }
})

const confirmButtonClasses = computed(() => {
  switch (props.type) {
    case 'danger':
      return 'btn-danger'
    case 'warning':
      return 'btn-warning'
    default:
      return 'btn-primary'
  }
})

const handleBackdropClick = (event) => {
  if (event.target === event.currentTarget) {
    emit('cancel')
  }
}
</script>
