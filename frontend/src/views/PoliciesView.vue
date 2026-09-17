<script setup>
import { onMounted, reactive } from 'vue'
import { usePoliciesStore } from '../stores/policies'
import { quotePdfUrl } from '../services/quotes'
import { useToast } from '../composables/useToast'
import { useLoading } from '../composables/useLoading'

const store = usePoliciesStore()
const toast = useToast()
const { run } = useLoading()

const filters = reactive({
  estado: '',
  destino: '',
  identificacion: '',
  cliente: '',
  desde: '',
  hasta: '',
})

async function fetchWithFeedback(task) {
  try {
    await run(task)
    if (store.error) toast.error(store.error)
  } catch (error) {
    toast.error(error?.message || 'No se pudieron cargar las cotizaciones.')
  }
}

function apply() {
  fetchWithFeedback(() => store.applyFilters({ ...filters }))
}

function reset() {
  Object.keys(filters).forEach((key) => (filters[key] = ''))
  fetchWithFeedback(() => store.applyFilters({ ...filters }))
}

function goToPage(page) {
  fetchWithFeedback(() => store.goToPage(page))
}

function formatCurrency(value) {
  return `$ ${Number(value ?? 0).toFixed(2)}`
}

function formatDate(value) {
  if (!value) return ''
  const [y, m, d] = value.split('T')[0].split('-')
  return `${d}/${m}/${y}`
}

onMounted(() => {
  fetchWithFeedback(() => store.fetch())
})
</script>

<template>
  <div class="mx-auto max-w-6xl px-4 py-8">
    <header class="mb-6">
      <h1 class="text-2xl font-bold text-gray-900">Cotizaciones y seguros</h1>
      <p class="mt-1 text-sm text-gray-500">Consulta las cotizaciones y seguros registrados.</p>
    </header>

    <div class="mb-6 rounded-lg border border-gray-200 bg-white p-4 shadow-sm">
      <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-3">
        <input
          v-model="filters.cliente"
          type="text"
          placeholder="Cliente"
          class="rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500"
        />
        <input
          v-model="filters.identificacion"
          type="text"
          placeholder="Identificación"
          class="rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500"
        />
        <input
          v-model="filters.destino"
          type="text"
          placeholder="Destino"
          class="rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500"
        />
        <select
          v-model="filters.estado"
          class="rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500"
        >
          <option value="">Todos los estados</option>
          <option value="cotizado">Cotizado</option>
          <option value="contratado">Contratado</option>
        </select>
        <div class="flex items-center gap-2">
          <input
            v-model="filters.desde"
            type="date"
            class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500"
            title="Desde"
          />
          <input
            v-model="filters.hasta"
            type="date"
            class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500"
            title="Hasta"
          />
        </div>
      </div>

      <div class="mt-4 flex gap-2">
        <button
          type="button"
          class="rounded-md bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700"
          @click="apply"
        >
          Buscar
        </button>
        <button
          type="button"
          class="rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50"
          @click="reset"
        >
          Limpiar
        </button>
      </div>
    </div>

    <div v-if="store.error" class="mb-4 rounded-md bg-red-50 p-4 text-sm text-red-700">
      {{ store.error }}
    </div>

    <div class="overflow-x-auto rounded-lg border border-gray-200 bg-white shadow-sm">
      <table class="min-w-full divide-y divide-gray-200 text-sm">
        <thead class="bg-gray-50">
          <tr>
            <th class="px-4 py-3 text-left font-semibold text-gray-600">Cliente</th>
            <th class="px-4 py-3 text-left font-semibold text-gray-600">Identificación</th>
            <th class="px-4 py-3 text-left font-semibold text-gray-600">Destino</th>
            <th class="px-4 py-3 text-left font-semibold text-gray-600">Salida</th>
            <th class="px-4 py-3 text-left font-semibold text-gray-600">Regreso</th>
            <th class="px-4 py-3 text-left font-semibold text-gray-600">Valor</th>
            <th class="px-4 py-3 text-left font-semibold text-gray-600">Estado</th>
            <th class="px-4 py-3 text-left font-semibold text-gray-600">Creado</th>
            <th class="px-4 py-3 text-left font-semibold text-gray-600"></th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
          <tr v-if="store.policies.length === 0">
            <td colspan="9" class="px-4 py-8 text-center text-gray-500">Sin resultados</td>
          </tr>
          <tr v-for="policy in store.policies" v-else :key="policy.id" class="hover:bg-gray-50">
            <td class="px-4 py-3 text-gray-900">
              {{ policy.asegurado?.nombres }} {{ policy.asegurado?.apellidos }}
            </td>
            <td class="px-4 py-3 text-gray-600">{{ policy.asegurado?.numero_identificacion }}</td>
            <td class="px-4 py-3 text-gray-900">{{ policy.pais_destino }}</td>
            <td class="px-4 py-3 text-gray-600">{{ formatDate(policy.fecha_salida) }}</td>
            <td class="px-4 py-3 text-gray-600">{{ formatDate(policy.fecha_regreso) }}</td>
            <td class="px-4 py-3 font-medium text-gray-900">{{ formatCurrency(policy.valor_total) }}</td>
            <td class="px-4 py-3">
              <span
                class="rounded-full px-2 py-1 text-xs font-semibold"
                :class="policy.estado === 'contratado' ? 'bg-green-100 text-green-700' : 'bg-amber-100 text-amber-700'"
              >
                {{ policy.estado === 'contratado' ? 'Contratado' : 'Cotizado' }}
              </span>
            </td>
            <td class="px-4 py-3 text-gray-600">{{ formatDate(policy.created_at) }}</td>
            <td class="px-4 py-3">
              <a
                :href="quotePdfUrl(policy.id)"
                target="_blank"
                class="text-sm font-medium text-blue-600 hover:text-blue-800"
              >
                PDF
              </a>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <div v-if="store.meta.total > 0" class="mt-4 flex items-center justify-between">
      <p class="text-sm text-gray-600">
        Página {{ store.meta.current_page }} de {{ store.meta.last_page }} — {{ store.meta.total }} registros
      </p>
      <div class="flex gap-2">
        <button
          type="button"
          :disabled="store.meta.current_page <= 1"
          class="rounded-md border border-gray-300 bg-white px-3 py-1.5 text-sm font-medium text-gray-700 hover:bg-gray-50 disabled:cursor-not-allowed disabled:opacity-50"
          @click="goToPage(store.meta.current_page - 1)"
        >
          Anterior
        </button>
        <button
          type="button"
          :disabled="store.meta.current_page >= store.meta.last_page"
          class="rounded-md border border-gray-300 bg-white px-3 py-1.5 text-sm font-medium text-gray-700 hover:bg-gray-50 disabled:cursor-not-allowed disabled:opacity-50"
          @click="goToPage(store.meta.current_page + 1)"
        >
          Siguiente
        </button>
      </div>
    </div>
  </div>
</template>
