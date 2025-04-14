import { createRouter, createWebHistory } from 'vue-router'
import HomeView from '../views/HomeView.vue'
import LoginView from '../views/LoginView.vue'

import DashboardView from '@/views/DashboardView.vue'
import PasienView from '@/views/PasienView.vue'

import DokterResource from '@/views/DokterResource.vue'
import ListDokter from '@/views/DokterResource/Pages/ListDokter.vue'
import CreateDokter from '@/views/DokterResource/Pages/CreateDokter.vue'
import EditDokter from '@/views/DokterResource/Pages/EditDokter.vue'
import ViewDokter from '@/views/DokterResource/Pages/ViewDokter.vue'
import PasienResource from '@/views/PasienResource.vue'
import ListPasien from '@/views/PasienResource/Pages/ListPasien.vue'
import CreatePasien from '@/views/PasienResource/Pages/CreatePasien.vue'
import EditPasien from '@/views/PasienResource/Pages/EditPasien.vue'
import ViewPasien from '@/views/PasienResource/Pages/ViewPasien.vue'

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
    component: DokterResource,
    children: [
      {
        path: '',
        name: 'list-dokter',
        component: ListDokter
      },
      {
        path: 'create',
        name: 'create-dokter',
        component: CreateDokter
      },
      {
        path: 'edit/:id',
        name: 'edit-dokter',
        component: EditDokter
      },
      {
        path: 'view/:id',
        name: 'view-dokter',
        component: ViewDokter
      },
    ],
  },
  {
    path: '/pasien',
    component: PasienResource,
    children: [
      {
        path: '',
        name: 'list-pasien',
        component: ListPasien
      },
      {
        path: 'create',
        name: 'create-pasien',
        component: CreatePasien
      },
      {
        path: 'edit/:id',
        name: 'edit-pasien',
        component: EditPasien
      },
      {
        path: 'view/:id',
        name: 'view-pasien',
        component: ViewPasien
      },
    ],
  },
  ],
});

export default router