import { createRouter, createWebHistory } from "vue-router";
import HomeView from "../views/HomeView.vue";
import LoginView from "../views/LoginView.vue";
import DashboardView from "@/views/DashboardView.vue";
import DokterView from "@/views/DokterView.vue";
import PasienView from "@/views/PasienView.vue";
import PemeriksaanView from "@/views/PemeriksaanView.vue";
import DetailPemeriksaanView from "@/views/DetailPemeriksaanView.vue";

import ForbiddenView from "@/views/errors/ForbiddenView.vue";
import NotFoundView from "@/views/errors/NotFoundView.vue";
import ServerErrorView from "@/views/errors/ServerErrorView.vue";

const routes = [
  {
    path: "/",
    name: "home",
    component: HomeView,
  },
  {
    path: "/login",
    name: "login",
    component: LoginView,
  },
  {
    path: "/dashboard",
    name: "dashboard",
    component: DashboardView,
    meta: { requiresAuth: true },
  },
  {
    path: "/dokter",
    name: "dokter",
    component: DokterView,
    meta: { requiresAuth: true },
  },
  {
    path: "/pasien",
    name: "pasien",
    component: PasienView,
    meta: { requiresAuth: true },
  },
  {
    path: "/pemeriksaan",
    name: "pemeriksaan",
    component: PemeriksaanView,
    meta: { requiresAuth: true },
  },
  {
    path: "/detail-pemeriksaan",
    name: "detail-pemeriksaan",
    component: DetailPemeriksaanView,
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
];

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes,
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
