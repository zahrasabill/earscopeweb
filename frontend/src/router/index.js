import { createRouter, createWebHistory } from "vue-router";
import HomeView from "../views/HomeView.vue";
import LoginView from "../views/LoginView.vue";
import DashboardView from "@/views/DashboardView.vue";
import RiwayatPemeriksaanView from "@/views/RiwayatPemeriksaanView.vue";
import Penanganan from "@/views/PenangananView.vue";

import ForbiddenView from "@/views/errors/ForbiddenView.vue";
import NotFoundView from "@/views/errors/NotFoundView.vue";
import ServerErrorView from "@/views/errors/ServerErrorView.vue";

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
import PemeriksaanResource from "@/views/PemeriksaanResource.vue";
import PemeriksaanView from "@/views/PemeriksaanResource/Pages/PemeriksaanView.vue";
import PenangananView from "@/views/PenangananView.vue";

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
      meta: { requiresAuth: true },
    },
  {
    path: '/dokter',
    component: DokterResource,
    meta: { requiresAuth: true },
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
    meta: { requiresAuth: true },
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
   {
      path: "/pemeriksaan",
      component: PemeriksaanResource,
      meta: { requiresAuth: true },
      children: [
        {
          path: '',
          name: 'pemeriksaan',
          component: PemeriksaanView
        },
      ],
    },
  {
    path: "/penanganan",
    name: "penanganan",
    component: PenangananView,
    meta: { requiresAuth: true },
  },
  {
    path: "/riwayat",
    name: "riwayat",
    component: RiwayatPemeriksaanView,
    meta: { requiresAuth: true },
  },

  {
    path: "/forbidden",
    name: "forbidden",
    component: ForbiddenView,
  },
  {
    path: "/server-error",
    name: "server-error",
    component: ServerErrorView,
  },
  {
    path: "/:pathMatch(.*)*",
    name: "notfound",
    component: NotFoundView,
  },
  ],
});

router.beforeEach((to, from, next) => {
  const isAuthenticated = sessionStorage.getItem("token") || localStorage.getItem("token"); // Cek token di sessionStorage & localStorage

  if (to.meta.requiresAuth && !isAuthenticated) {
    next("/login");
  } else {
    next();
  }
});

export default router;
