<script setup>
import { RouterLink } from 'vue-router'
</script>

<template>
  <div class="flex min-h-[70vh] flex-col items-center justify-center px-4 py-16 text-center">
    <p class="text-7xl font-black text-blue-600">404</p>
    <h1 class="mt-4 text-2xl font-bold text-gray-900">Página no encontrada</h1>
    <p class="mt-2 max-w-md text-sm text-gray-500">
      La dirección que estás buscando no existe o fue movida. Verifica la URL o vuelve al inicio.
    </p>
    <RouterLink
      to="/"
      class="mt-8 rounded-md bg-blue-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-blue-700"
    >
      Ir al listado de cotizaciones
    </RouterLink>
  </div>
</template>