import { defineStore } from 'pinia'
import { getAllCountries } from '../services/countries'
import { createQuote, contractQuote } from '../services/quotes'

export const useQuoteStore = defineStore('quote', {
  state: () => ({
    countries: [],
    countriesLoading: false,
    countriesError: null,
    quote: null,
    submitting: false,
    contracting: false,
    error: null,
  }),

  actions: {
    async loadCountries() {
      if (this.countries.length) return

      this.countriesLoading = true
      this.countriesError = null

      try {
        this.countries = await getAllCountries()
      } catch (error) {
        this.countriesError = error.message || 'No se pudieron cargar los países. Inténtalo de nuevo.'
      } finally {
        this.countriesLoading = false
      }
    },

    async createQuote(payload) {
      this.submitting = true
      this.error = null

      try {
        const { data: body } = await createQuote(payload)
        this.quote = body.data
        return body
      } catch (error) {
        this.error = error.message || 'No se pudo generar la cotización.'
        throw error
      } finally {
        this.submitting = false
      }
    },

    async contract(id) {
      this.contracting = true
      this.error = null

      try {
        const { data: body } = await contractQuote(id)
        this.quote = body.data
        return body
      } catch (error) {
        this.error = error.message || 'No se pudo contratar el seguro.'
        throw error
      } finally {
        this.contracting = false
      }
    },

    resetQuote() {
      this.quote = null
      this.error = null
    },
  },
})
