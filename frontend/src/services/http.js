import axios from 'axios'
import API_URL from '../config'

const http = axios.create({
  baseURL: API_URL,
  headers: {
    Accept: 'application/json',
    'Content-Type': 'application/json',
  },
})

http.interceptors.response.use(
  (response) => response,
  (error) => {
    const data = error.response?.data

    const normalized = {
      ...error,
      message: data?.message || 'Ha ocurrido un error. Inténtalo de nuevo.',
      fieldErrors: data?.errors || null,
      status: error.response?.status,
    }

    return Promise.reject(normalized)
  },
)

export default http
