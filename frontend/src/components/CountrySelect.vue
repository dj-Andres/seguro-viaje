<script setup>
import { computed, ref } from 'vue'

const props = defineProps({
  modelValue: { type: String, default: '' },
  options: { type: Array, default: () => [] },
  placeholder: { type: String, default: 'Selecciona un país' },
})

const emit = defineEmits(['update:modelValue'])

const search = ref('')
const open = ref(false)

const selected = computed(() =>
  props.options.find((country) => country.code === props.modelValue),
)

const filtered = computed(() => {
  const query = search.value.trim().toLowerCase()
  if (!query) return props.options
  return props.options.filter(
    (country) =>
      country.name.toLowerCase().includes(query) ||
      country.code.toLowerCase().includes(query),
  )
})

function selectCountry(country) {
  emit('update:modelValue', country.code)
  open.value = false
  search.value = ''
}

function clear() {
  emit('update:modelValue', '')
  search.value = ''
}
</script>

<template>
  <div class="relative">
    <button
      type="button"
      class="flex w-full items-center justify-between rounded-md border border-gray-300 bg-white px-3 py-2 text-left text-sm shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
      @click="open = !open"
    >
      <span v-if="selected" class="flex items-center gap-2">
        <img
          v-if="selected.flag"
          :src="selected.flag"
          :alt="selected.name"
          class="h-4 w-6 object-cover"
        />
        <span class="truncate">{{ selected.name }}</span>
      </span>
      <span v-else class="text-gray-400">{{ placeholder }}</span>
      <svg class="h-4 w-4 text-gray-400" viewBox="0 0 20 20" fill="currentColor">
        <path
          fill-rule="evenodd"
          d="M5.23 7.21a.75.75 0 011.06.02L10 11.06l3.71-3.83a.75.75 0 111.08 1.04l-4.25 4.39a.75.75 0 01-1.08 0L5.21 8.27a.75.75 0 01.02-1.06z"
          clip-rule="evenodd"
        />
      </svg>
    </button>

    <div
      v-if="open"
      class="absolute z-20 mt-1 w-full overflow-hidden rounded-md border border-gray-200 bg-white shadow-lg"
    >
      <div class="border-b border-gray-200 p-2">
        <input
          v-model="search"
          type="text"
          placeholder="Buscar país..."
          class="w-full rounded-md border border-gray-300 px-2 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
          @click.stop
        />
      </div>
      <ul class="max-h-56 overflow-auto">
        <li v-if="filtered.length === 0" class="px-3 py-2 text-sm text-gray-400">
          Sin resultados
        </li>
        <li
          v-for="country in filtered"
          :key="country.code"
          class="flex cursor-pointer items-center gap-2 px-3 py-2 text-sm hover:bg-blue-50"
          @mousedown.prevent="selectCountry(country)"
        >
          <img
            v-if="country.flag"
            :src="country.flag"
            :alt="country.name"
            class="h-4 w-6 object-cover"
          />
          <span>{{ country.name }}</span>
          <span class="ml-auto text-xs text-gray-400">{{ country.code }}</span>
        </li>
      </ul>
    </div>

    <button
      v-if="modelValue"
      type="button"
      class="absolute right-8 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600"
      @click="clear"
    >
      <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
        <path
          d="M6.28 5.22a.75.75 0 00-1.06 1.06L8.94 10l-3.72 3.72a.75.75 0 101.06 1.06L10 11.06l3.72 3.72a.75.75 0 101.06-1.06L11.06 10l3.72-3.72a.75.75 0 00-1.06-1.06L10 8.94 6.28 5.22z"
        />
      </svg>
    </button>
  </div>
</template>
