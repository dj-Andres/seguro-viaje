import { createRouter, createWebHistory } from 'vue-router'
import QuoteView from '../views/QuoteView.vue'
import PoliciesView from '../views/PoliciesView.vue'
import NotFoundView from '../views/NotFoundView.vue'

const router = createRouter({
  history: createWebHistory(),
  routes: [
    { path: '/', name: 'policies', component: PoliciesView },
    { path: '/cotizar', name: 'quote', component: QuoteView },
    { path: '/:pathMatch(.*)*', name: 'not-found', component: NotFoundView },
  ],
})

export default router
