<template>
  <div class="min-h-screen bg-gray-50 flex flex-col justify-center py-12 sm:px-6 lg:px-8">
    <div class="sm:mx-auto sm:w-full sm:max-w-md">
      <div class="text-center">
        <h1 class="text-9xl font-bold text-gray-300">404</h1>
        <h2 class="mt-4 text-3xl font-bold text-gray-900">Page not found</h2>
        <p class="mt-2 text-base text-gray-500">
          Sorry, we couldn't find the page you're looking for.
        </p>
        <div class="mt-6">
          <router-link
            to="/dashboard"
            class="btn-primary"
          >
            Go back home
          </router-link>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
// No script needed for this simple component
</script>
