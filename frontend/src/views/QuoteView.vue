<script setup>
import { onMounted } from 'vue'
import { useQuoteStore } from '../stores/quote'
import { useToast } from '../composables/useToast'
import { useLoading } from '../composables/useLoading'
import QuoteForm from '../components/QuoteForm.vue'
import QuoteResult from '../components/QuoteResult.vue'

const store = useQuoteStore()
const toast = useToast()
const { run } = useLoading()

onMounted(async () => {
  try {
    await run(() => store.loadCountries())
  } catch (error) {
    toast.error(error?.message || 'No se pudieron cargar los países.')
  }
})
</script>

<template>
  <div class="mx-auto max-w-3xl px-4 py-8">
    <header class="mb-8 text-center">
      <h1 class="text-2xl font-bold text-gray-900">Cotiza tu seguro de viaje</h1>
      <p class="mt-1 text-sm text-gray-500">
        Ingresa tus datos y los detalles de tu viaje para obtener una cotización.
      </p>
    </header>

    <div v-if="store.countriesError" class="mb-4 rounded-md bg-red-50 p-4 text-sm text-red-700">
      {{ store.countriesError }}
    </div>

    <div v-if="!store.quote" class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm">
      <QuoteForm />
    </div>

    <QuoteResult v-else />
  </div>
</template>
