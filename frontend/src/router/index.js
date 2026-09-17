import { createRouter, createWebHistory } from 'vue-router'
import QuoteView from '../views/QuoteView.vue'
import PoliciesView from '../views/PoliciesView.vue'

const router = createRouter({
  history: createWebHistory(),
  routes: [
    { path: '/', name: 'quote', component: QuoteView },
    { path: '/consultas', name: 'policies', component: PoliciesView },
  ],
})

export default router
