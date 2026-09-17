<script setup>
import { computed } from 'vue'
import { useQuoteStore } from '../stores/quote'
import { quotePdfUrl } from '../services/quotes'
import { useToast } from '../composables/useToast'
import { useConfirm } from '../composables/useConfirm'
import { useLoading } from '../composables/useLoading'

const store = useQuoteStore()
const toast = useToast()
const { confirm } = useConfirm()
const { run } = useLoading()

const quote = computed(() => store.quote)

const isContracted = computed(() => quote.value?.estado === 'contratado')

function formatCurrency(value) {
  return `$ ${Number(value ?? 0).toFixed(2)}`
}

function formatDate(value) {
  if (!value) return ''
  const [y, m, d] = value.split('T')[0].split('-')
  return `${d}/${m}/${y}`
}

function downloadPdf() {
  window.open(quotePdfUrl(quote.value.id), '_blank')
}

async function contract() {
  const ok = await confirm({
    title: '¿Contratar el seguro?',
    text: 'Al confirmar se registrará la contratación del seguro para esta cotización.',
    icon: 'question',
    confirmButtonColor: '#16a34a',
    confirmButtonText: 'Sí, contratar',
  })

  if (!ok) return

  try {
    const body = await run(() => store.contract(quote.value.id))
    toast.success(body?.message || 'Seguro contratado correctamente.')
  } catch (error) {
    const first = error?.fieldErrors ? Object.values(error.fieldErrors)[0] : null
    const message = first ? (Array.isArray(first) ? first[0] : first) : error?.message
    toast.error(message || 'No se pudo contratar el seguro.')
  }
}

async function newQuote() {
  const ok = await confirm({
    title: 'Iniciar nueva cotización',
    text: 'Se descartará la cotización actual y se limpiará el formulario.',
    icon: 'info',
    confirmButtonColor: '#2563eb',
    confirmButtonText: 'Sí, empezar de nuevo',
  })

  if (ok) store.resetQuote()
}
</script>

<template>
  <div v-if="quote" class="space-y-6">
    <div class="rounded-lg border border-gray-200 bg-white p-5 shadow-sm">
      <div class="mb-4 flex items-center justify-between">
        <h2 class="text-lg font-semibold text-gray-800">Resultado de la cotización</h2>
        <span
          class="rounded-full px-3 py-1 text-xs font-semibold"
          :class="isContracted ? 'bg-green-100 text-green-700' : 'bg-amber-100 text-amber-700'"
        >
          {{ isContracted ? 'Contratado' : 'Cotizado' }}
        </span>
      </div>

      <dl class="grid grid-cols-1 gap-4 sm:grid-cols-2">
        <div>
          <dt class="text-sm text-gray-500">Asegurado</dt>
          <dd class="text-sm font-medium text-gray-900">
            {{ quote.asegurado?.nombres }} {{ quote.asegurado?.apellidos }}
          </dd>
        </div>
        <div>
          <dt class="text-sm text-gray-500">Destino</dt>
          <dd class="text-sm font-medium text-gray-900">{{ quote.pais_destino }}</dd>
        </div>
        <div>
          <dt class="text-sm text-gray-500">Fechas</dt>
          <dd class="text-sm font-medium text-gray-900">
            {{ formatDate(quote.fecha_salida) }} → {{ formatDate(quote.fecha_regreso) }}
          </dd>
        </div>
        <div>
          <dt class="text-sm text-gray-500">Días de viaje</dt>
          <dd class="text-sm font-medium text-gray-900">{{ quote.dias_viaje }}</dd>
        </div>
      </dl>
    </div>

    <div class="rounded-lg border border-gray-200 bg-white p-5 shadow-sm">
      <h2 class="mb-4 text-lg font-semibold text-gray-800">Detalle de valores</h2>
      <dl class="space-y-3">
        <div class="flex justify-between">
          <dt class="text-sm text-gray-600">Tarifa base (USD 3/día)</dt>
          <dd class="text-sm font-medium text-gray-900">{{ formatCurrency(quote.tarifa_base) }}</dd>
        </div>
        <div class="flex justify-between">
          <dt class="text-sm text-gray-600">Recargo ({{ quote.porcentaje_recargo }}%)</dt>
          <dd class="text-sm font-medium text-gray-900">
            {{ formatCurrency((quote.tarifa_base * quote.porcentaje_recargo) / 100) }}
          </dd>
        </div>
        <div class="flex justify-between border-t border-gray-200 pt-3">
          <dt class="text-base font-semibold text-gray-900">Valor total</dt>
          <dd class="text-base font-bold text-blue-600">{{ formatCurrency(quote.valor_total) }}</dd>
        </div>
      </dl>
    </div>

    <div class="flex flex-col gap-3 sm:flex-row">
      <button
        v-if="!isContracted"
        type="button"
        :disabled="store.contracting"
        class="flex-1 rounded-md bg-green-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-green-700 disabled:cursor-not-allowed disabled:opacity-60"
        @click="contract"
      >
        {{ store.contracting ? 'Contratando...' : 'Contratar seguro' }}
      </button>

      <button
        type="button"
        class="flex-1 rounded-md bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-blue-700"
        @click="downloadPdf"
      >
        Descargar PDF
      </button>

      <button
        type="button"
        class="flex-1 rounded-md border border-gray-300 bg-white px-4 py-2.5 text-sm font-semibold text-gray-700 shadow-sm hover:bg-gray-50"
        @click="newQuote"
      >
        Nueva cotización
      </button>
    </div>
  </div>
</template>
