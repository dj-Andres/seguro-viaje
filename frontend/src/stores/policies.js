import { defineStore } from 'pinia'
import { listQuotes, contractQuote } from '../services/quotes'

export const usePoliciesStore = defineStore('policies', {
  state: () => ({
    policies: [],
    loading: false,
    error: null,
    filters: {
      estado: '',
      destino: '',
      identificacion: '',
      cliente: '',
      desde: '',
      hasta: '',
    },
    meta: {
      current_page: 1,
      per_page: 10,
      total: 0,
      last_page: 1,
    },
  }),

  actions: {
    async fetch() {
      this.loading = true
      this.error = null

      try {
        const { data } = await listQuotes({
          ...this.filters,
          page: this.meta.current_page,
          per_page: this.meta.per_page,
        })

        this.policies = data.data
        this.meta = data.meta
      } catch (error) {
        this.error = error.message || 'No se pudieron cargar las cotizaciones.'
      } finally {
        this.loading = false
      }
    },

    applyFilters(filters) {
      this.filters = { ...filters }
      this.meta.current_page = 1
      return this.fetch()
    },

    goToPage(page) {
      if (page < 1 || page > this.meta.last_page) return
      this.meta.current_page = page
      return this.fetch()
    },

    async contract(id) {
      const { data: body } = await contractQuote(id)
      await this.fetch()
      return body
    },
  },
})
