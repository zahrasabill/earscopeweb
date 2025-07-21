import { createRouter, createWebHistory } from "vue-router";
import HomeView from "../views/HomeView.vue";
import LoginView from "../views/LoginView.vue";
import DashboardView from "@/views/DashboardView.vue";

import ProfileResource from "@/views/ProfileResource.vue";
import ViewProfile from "@/views/ProfileResource/Pages/ViewProfile.vue";
import EditProfile from "@/views/ProfileResource/Pages/EditProfile.vue";

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
import PenangananResource from "@/views/PenangananResource.vue";
import ListPenanganan from "@/views/PenangananResource/Pages/ListPenanganan.vue";
import CreatePenanganan from "@/views/PenangananResource/Pages/CreatePenanganan.vue";
import EditPenanganan from "@/views/PenangananResource/Pages/EditPenanganan.vue";
import ViewPenanganan from "@/views/PenangananResource/Pages/ViewPenanganan.vue";
import RiwayatResource from "@/views/RiwayatResource.vue";
import RiwayatPemeriksaan from "@/views/RiwayatResource/Pages/RiwayatPemeriksaan.vue";


const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes: [
    // {
    //   path: '/',
    //   name: 'home',
    //   component: HomeView,
    // },
        {
      path: '/',
      name: 'home',
      component: LoginView,
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
  path: '/profile',
  component: ProfileResource,
  meta: { requiresAuth: true },
  children: [
    {
      path: '',
      name: 'profile',
      component: ViewProfile
    },
    {
      path: 'edit',
      name: 'edit-profile',
      component: EditProfile
    }
  ]
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
    path: '/penanganan',
    component: PenangananResource,
    meta: { requiresAuth: true },
    children: [
      {
        path: '',
        name: 'list-penanganan',
        component: ListPenanganan
      },
      {
        path: 'create',
        name: 'create-penanganan',
        component: CreatePenanganan
      },
      {
        path: 'edit/:id',
        name: 'edit-penanganan',
        component: EditPenanganan
      },
      {
        path: 'view/:id',
        name: 'view-penanganan',
        component: ViewPenanganan
      },
    ],
    },
  {
  path: "/riwayat",
  component: RiwayatResource,
  meta: { requiresAuth: true },
  children: [
    // {
    //   path: 'pasien/:id',
    //   name: 'riwayat-pasien',
    //   component: RiwayatPemeriksaan
    // },
    {
      path: '',
      name: 'riwayat',
      component: RiwayatPemeriksaan
    },
  ],
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
