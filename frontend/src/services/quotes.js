import http from './http'
import API_URL from '../config'

export function createQuote(payload) {
  return http.post('/quotes', payload)
}

export function listQuotes(params) {
  return http.get('/quotes', { params })
}

export function contractQuote(id) {
  return http.post(`/quotes/${id}/contract`)
}

export function quotePdfUrl(id) {
  return `${API_URL}/quotes/${id}/pdf`
}
