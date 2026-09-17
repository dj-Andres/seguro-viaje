import http from './http'

export async function getAllCountries() {
  const perPage = 100
  let page = 1
  let lastPage = 1
  let all = []

  do {
    const { data } = await http.get('/countries', { params: { page, per_page: perPage } })
    all = all.concat(data.data)
    lastPage = data.meta.last_page
    page += 1
  } while (page <= lastPage)

  return all
}
