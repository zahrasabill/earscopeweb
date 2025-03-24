import { createRouter, createWebHistory } from 'vue-router'
import HomeView from '../views/HomeView.vue'
import LoginView from '../views/LoginView.vue'

import DashboardView from '@/views/DashboardView.vue'
import DokterView from '@/views/DokterView.vue'
import PasienView from '@/views/PasienView.vue'
import PemeriksaanView from '@/views/PemeriksaanView.vue'

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes: [
    {
      path: '/',
      name: 'home',
      component: HomeView,
    },
    {
      path: '/login',
      name: 'login',
      component: LoginView,
    },
    {
      path: '/dashboard',
      name: 'dashboard',
      component: DashboardView,
    },
    {
      path: '/dokter',
      name: 'dokter',
      component: DokterView,
    },
    {
      path: '/pasien',
      name: 'pasien',
      component: PasienView,
    },
    {
      path: '/pemeriksaan',
      name: 'pemeriksaan',
      component: PemeriksaanView,
    },
  ],
})

export default router