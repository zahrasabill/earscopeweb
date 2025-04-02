<template>
  <div class="d-flex">
    <!-- Sidebar -->
    <div class="sidebar bg-primary p-3 text-white">
      <div class="sidebar-header mb-4">
        <div class="text-center">
          <img src="@/assets/logooido.png" alt="Hospital Logo" class="sidebar-logo" />
        </div>
      </div>

      <ul class="nav flex-column">
        <!-- Dashboard (Semua Role Bisa Akses) -->
        <li class="nav-item mb-2">
          <router-link to="/dashboard" class="nav-link text-white d-flex align-items-center"
            :class="{ active: activePage === 'dashboard' }">
            <i class="bi bi-house me-3"></i>
            <span>Dashboard</span>
          </router-link>
        </li>

        <!-- Admin hanya melihat menu Dokter -->
        <li v-if="currentUser.role === 'admin'" class="nav-item mb-2">
          <router-link to="/dokter" class="nav-link text-white d-flex align-items-center"
            :class="{ active: activePage === 'dokter' }">
            <i class="bi bi-person-badge me-3"></i>
            <span>Dokter</span>
          </router-link>
        </li>

        <!-- Dokter hanya melihat menu Pasien -->
        <li v-if="currentUser.role === 'dokter'" class="nav-item mb-2">
          <router-link to="/pasien" class="nav-link text-white d-flex align-items-center"
            :class="{ active: activePage === 'pasien' }">
            <i class="bi bi-people me-3"></i>
            <span>Pasien</span>
          </router-link>
        </li>

        <!-- Dokter & Pasien bisa melihat menu Hasil Pemeriksaan -->
        <li v-if="currentUser.role === 'dokter' || currentUser.role === 'pasien'" class="nav-item mb-2">
          <router-link to="/pemeriksaan" class="nav-link text-white d-flex align-items-center"
            :class="{ active: activePage === 'pemeriksaan' }">
            <i class="bi bi-clipboard2-pulse me-3"></i>
            <span>Hasil Pemeriksaan</span>
          </router-link>
        </li>
      </ul>

      <div class="mt-auto pt-4">
        <hr class="border-light" />
        <ul class="nav flex-column">
          <li class="nav-item mb-2">
            <router-link to="/pengaturan" class="nav-link text-white d-flex align-items-center">
              <i class="bi bi-gear me-3"></i>
              <span>Pengaturan</span>
            </router-link>
          </li>
          <li class="nav-item">
            <a href="#" @click.prevent="logout" class="nav-link text-white d-flex align-items-center">
              <i class="bi bi-box-arrow-right me-3"></i>
              <span>Logout</span>
            </a>
          </li>
        </ul>
      </div>
    </div>

    <!-- Main Content -->
    <div class="content-area">
      <div class="container-fluid p-4">
        <!-- Page Header -->
        <div class="page-header mb-4 d-flex justify-content-between align-items-center">
          <h2>{{ pageTitle }}</h2>
          <div>
            <div class="dropdown">
              <button class="btn btn-light dropdown-toggle d-flex align-items-center" type="button" id="dropdownMenuButton"
                data-bs-toggle="dropdown" aria-expanded="false">
                <div class="user-info text-start">
                  <span class="kode-akses">{{ currentUser.kode_akses }}</span>
                  <span class="role">{{ currentUser.role }}</span>
                </div>
              </button>
              <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="dropdownMenuButton">
                <li>
                  <a class="dropdown-item" href="#"><i class="bi bi-person me-2"></i>Profil</a>
                </li>
                <li>
                  <a class="dropdown-item" href="#"><i class="bi bi-gear me-2"></i>Pengaturan Akun</a>
                </li>
                <li>
                  <hr class="dropdown-divider" />
                </li>
                <li>
                  <a class="dropdown-item" href="#" @click.prevent="logout"><i
                      class="bi bi-box-arrow-right me-2"></i>Logout</a>
                </li>
              </ul>
            </div>
          </div>
        </div>

        <!-- Main Content Goes Here -->
        <slot></slot>
      </div>
    </div>
  </div>
</template>

<script>
import { jwtDecode } from "jwt-decode"; // Import jwt-decode

export default {
  name: "AppLayout",
  props: {
    activePage: {
      type: String,
      default: "dashboard",
    },
  },
  data() {
    return {
      currentUser: {
        kode_akses: "",
        role: "",
      },
      pageTitle: "Dashboard",
    };
  },
  created() {
    this.getCurrentUser();
  },
  watch: {
    activePage: {
      immediate: true,
      handler(newVal) {
        this.updatePageTitle(newVal);
      },
    },
  },
  methods: {
    getCurrentUser() {
      const token = localStorage.getItem("token") || sessionStorage.getItem("token");

      if (token) {
        try {
          // Decode JWT tanpa verifikasi
          const payload = jwtDecode(token);

          // Ambil `kode_akses` dari payload
          this.currentUser.kode_akses = payload.kode_akses || "Tidak Ada Akses";
          this.currentUser.role = payload.role || "Tidak Ada Role";
        } catch (error) {
          console.error("Gagal decode JWT:", error);
          this.currentUser.kode_akses = "JWT Tidak Valid";
        }
      } else {
        this.currentUser.kode_akses = "Token Tidak Ditemukan";
      }
    },

    updatePageTitle(page) {
      const titles = {
        dashboard: "Dashboard",
        dokter: "Kelola Dokter",
        pasien: "Kelola Pasien",
        pemeriksaan: "Hasil Pemeriksaan",
        pengaturan: "Pengaturan",
      };
      this.pageTitle = titles[page] || "Dashboard";
    },

    logout() {
      console.log("Logging out...");
      localStorage.removeItem("token");
      sessionStorage.removeItem("token");
      this.$router.push("/login");
    },
  },
};
</script>

<style scoped>
.kode-akses {
  display: block;
  font-size: 1rem;
  font-weight: bold;
  color: #333;
}

.role {
  display: block;
  font-size: 0.85rem;
  color: #777;
  margin-top: -3px;
}
.sidebar {
  width: 250px;
  height: 100vh;
  position: fixed;
  left: 0;
  top: 0;
  overflow-y: auto;
  background-color: #004178 !important;
  display: flex;
  flex-direction: column;
}

.content-area {
  margin-left: 250px;
  width: calc(100% - 250px);
  min-height: 100vh;
  background-color: #f8f9fa;
}

.nav-link {
  border-radius: 5px;
  padding: 10px 15px;
  transition: all 0.3s;
}

.nav-link:hover,
.nav-link.active {
  background-color: rgba(255, 255, 255, 0.2);
}

.sidebar-header {
  border-bottom: 1px solid rgba(255, 255, 255, 0.2);
  padding-bottom: 20px;
  margin-top: 20px;
  display: flex;
  justify-content: center;
}

.page-header {
  border-bottom: 1px solid #dee2e6;
  padding-bottom: 10px;
}

/* Adjusted style for the logo */
.sidebar-logo {
  width: 150px;
  height: auto;
}

@media (max-width: 768px) {
  .sidebar {
    width: 70px;
    padding: 10px;
  }

  .sidebar span {
    display: none;
  }

  .content-area {
    margin-left: 70px;
    width: calc(100% - 70px);
  }

  /* Adjusted logo size for mobile view */
  .sidebar-logo {
    width: 40px;
    height: 40px;
  }

  .nav-link {
    justify-content: center !important;
    padding: 10px;
  }

  .nav-link i {
    margin-right: 0 !important;
    font-size: 1.2rem;
  }
}
</style>