<script setup>
import { RouterLink, RouterView } from 'vue-router'
import LoadingOverlay from './components/LoadingOverlay.vue'
</script>

<template>
  <div class="min-h-screen bg-gray-100">
    <nav class="bg-white shadow-sm">
      <div class="mx-auto flex max-w-6xl items-center justify-between px-4 py-3">
        <span class="text-lg font-bold text-blue-700">Seguro de Viaje</span>
        <div class="flex gap-4">
          <RouterLink to="/" class="text-sm font-medium text-gray-700 hover:text-blue-600">
            Cotizaciones
          </RouterLink>
          <RouterLink to="/cotizar" class="text-sm font-medium text-gray-700 hover:text-blue-600">
            Cotizar
          </RouterLink>
        </div>
      </div>
    </nav>

    <main>
      <RouterView />
    </main>

    <LoadingOverlay />
  </div>
</template>
