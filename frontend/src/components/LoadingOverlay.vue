<script setup>
import { useLoading } from '../composables/useLoading'

const { active } = useLoading()
</script>

<template>
  <Teleport to="body">
    <Transition name="fade">
      <div
        v-if="active"
        class="fixed inset-0 z-[9999] flex items-center justify-center bg-gray-900/20 backdrop-blur-[2px]"
      >
        <div class="flex items-center gap-3 rounded-lg bg-white px-5 py-3 shadow-xl">
          <span class="h-5 w-5 animate-spin rounded-full border-2 border-blue-600 border-t-transparent"></span>
          <span class="text-sm font-medium text-gray-700">Cargando...</span>
        </div>
      </div>
    </Transition>
  </Teleport>
</template>

<style scoped>
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.15s ease;
}

.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}
</style>