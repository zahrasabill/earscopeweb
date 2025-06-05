<template>
  <app-layout activePage="dashboard">
    <!-- Welcome Card -->
    <div class="row mb-4">
      <div class="col-12">
        <div class="card bg-gradient-primary text-white shadow-lg">
          <div class="card-body py-4">
            <div class="text-center">
              <h3 class="mb-3">Selamat Datang!</h3>
              <h4 class="mb-1 text-white-50">{{ currentUser.kode_akses }}</h4>
              <p class="mb-0 text-white-75">{{ getUserRoleDisplay(currentUser.role) }}</p>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Statistics Card for Dokter -->
    <div v-if="currentUser.role === 'dokter'" class="row mb-4">
      <div class="col-md-6 col-lg-4">
        <div class="card bg-light shadow-sm">
          <div class="card-body text-center">
            <div class="d-flex align-items-center justify-content-center mb-2">
              <i class="bi bi-people-fill text-primary me-2" style="font-size: 2rem;"></i>
              <div>
                <h2 class="mb-0 text-primary fw-bold">
                  <span v-if="loadingPasienCount">
                    <div class="spinner-border spinner-border-sm" role="status">
                      <span class="visually-hidden">Loading...</span>
                    </div>
                  </span>
                  <span v-else>{{ totalPasien !== null ? totalPasien : '-' }}</span>
                </h2>
                <p class="mb-0 text-muted small">Total Pasien Terdaftar</p>
              </div>
            </div>
            <small class="text-muted">
              <i class="bi bi-clock me-1"></i>
              Diperbarui: {{ lastUpdated }}
            </small>
          </div>
        </div>
      </div>
    </div>

    <!-- Quick Action Buttons -->
    <div class="row mt-4">
      <div class="col-12">
        <div class="row">
          <!-- Quick Actions untuk Admin -->
          <div v-if="currentUser.role === 'admin'" class="col-md-6 mb-3">
            <router-link to="/dokter" class="btn btn-outline-success btn-lg w-100 d-flex align-items-center justify-content-center">
              <i class="bi bi-person-plus me-2"></i>
              Kelola Dokter
            </router-link>
          </div>

          <!-- Quick Actions untuk Dokter -->
          <div v-if="currentUser.role === 'dokter'" class="col-md-6 mb-3">
            <router-link to="/pasien" class="btn btn-outline-primary btn-lg w-100 d-flex align-items-center justify-content-center">
              <i class="bi bi-people me-2"></i>
              Kelola Pasien
            </router-link>
          </div>

          <!-- Quick Actions untuk Dokter dan Pasien -->
          <div v-if="currentUser.role === 'dokter' || currentUser.role === 'pasien'" class="col-md-6 mb-3">
            <router-link to="/pemeriksaan" class="btn btn-outline-warning btn-lg w-100 d-flex align-items-center justify-content-center">
              <i class="bi bi-clipboard2-pulse me-2"></i>
              {{ currentUser.role === 'pasien' ? 'Lihat Hasil' : 'Hasil Pemeriksaan' }}
            </router-link>
          </div>
        </div>
      </div>
    </div>
  </app-layout>
</template>

<script>
import AppLayout from '@/components/AppLayout.vue';
import { jwtDecode } from "jwt-decode";
import api from '@/api';

export default {
  name: "DashboardView",
  components: {
    AppLayout
  },
  data() {
    return {
      currentUser: {
        kode_akses: "",
        role: "",
      },
      totalPasien: null,
      loadingPasienCount: false,
      lastUpdated: ""
    };
  },
  created() {
    this.getCurrentUser();
    
    // Listen for storage events from other components (like ListPasien.vue)
    window.addEventListener('storage', (event) => {
      if (event.key === 'pasienDataUpdated') {
        this.fetchPasienCount();
      }
    });
  },
  mounted() {
    // Check if data was updated from another component
    const updated = localStorage.getItem('pasienDataUpdated');
    if (updated) {
      localStorage.removeItem('pasienDataUpdated');
    }
    
    // Fetch pasien count after component is mounted and user data is available
    this.$nextTick(() => {
      if (this.currentUser.role === 'dokter') {
        console.log('Fetching pasien count for dokter...');
        this.fetchPasienCount();
      }
    });
  },
  watch: {
    'currentUser.role': {
      handler(newRole) {
        if (newRole === 'dokter') {
          this.fetchPasienCount();
        }
      },
      immediate: true
    }
  },
  methods: {
    getCurrentUser() {
      const token = localStorage.getItem("token") || sessionStorage.getItem("token");

      if (token) {
        try {
          // Decode JWT tanpa verifikasi
          const payload = jwtDecode(token);
          console.log('JWT Payload:', payload); // Debug log

          // Ambil `kode_akses` dan `role` dari payload
          this.currentUser.kode_akses = payload.kode_akses || "Tidak Ada Akses";
          this.currentUser.role = payload.role || "Tidak Ada Role";
          
          console.log('Current User:', this.currentUser); // Debug log
          
          // Fetch pasien count immediately if user is dokter
          if (this.currentUser.role === 'dokter') {
            console.log('User is dokter, fetching pasien count...');
            this.$nextTick(() => {
              this.fetchPasienCount();
            });
          }
        } catch (error) {
          console.error("Gagal decode JWT:", error);
          this.currentUser.kode_akses = "JWT Tidak Valid";
          this.currentUser.role = "unknown";
        }
      } else {
        console.warn("Token tidak ditemukan");
        this.currentUser.kode_akses = "Token Tidak Ditemukan";
        this.currentUser.role = "unknown";
      }
    },

    async fetchPasienCount() {
      console.log('fetchPasienCount called, current role:', this.currentUser.role);
      
      if (this.currentUser.role !== 'dokter') {
        console.log('User is not dokter, skipping fetch');
        return;
      }

      this.loadingPasienCount = true;
      console.log('Starting to fetch pasien count...');
      
      try {
        const token = localStorage.getItem("token") || sessionStorage.getItem("token");
        
        if (!token) {
          throw new Error('Token autentikasi tidak ditemukan. Silakan login terlebih dahulu.');
        }
        
        console.log('Making API call to fetch pasien...');
        const response = await api.get('pasien', {
          headers: {
            'Authorization': `Bearer ${token}`
          }
        });

        console.log('API Response:', response.data);

        // Handle different response formats (same logic as ListPasien.vue)
        let pasienData = [];
        
        if (response.data) {
          if (Array.isArray(response.data)) {
            // Direct array format
            console.log('Using direct array format');
            pasienData = response.data;
          } 
          else if (response.data.data && Array.isArray(response.data.data)) {
            // Nested data array format
            console.log('Using nested data array format');
            pasienData = response.data.data;
          }
          else if (response.data.data && response.data.data.items) {
            // Pagination format
            console.log('Using pagination format');
            pasienData = response.data.data.items || [];
          } 
          else {
            console.error('Unrecognized response format:', response.data);
            throw new Error('Format response tidak dikenali');
          }
          
          this.totalPasien = pasienData.length;
          console.log('Total pasien set to:', this.totalPasien);
          
          this.lastUpdated = new Date().toLocaleString('id-ID', {
            day: '2-digit',
            month: '2-digit',
            year: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
          });
          
          console.log('Total pasien berhasil dimuat:', this.totalPasien);
        } else {
          throw new Error('Tidak ada data dari server');
        }

      } catch (error) {
        console.error("Error fetching patient count:", error);
        console.error("Error details:", error.response?.data);
        this.totalPasien = 0;
        
        // Handle different error scenarios (same as ListPasien.vue)
        if (error.response) {
          if (error.response.status === 401) {
            console.warn("Sesi login telah berakhir");
            // Optionally redirect to login
            // this.$router.push('/login');
          } else if (error.response.status === 403) {
            console.warn("Access denied");
          } else {
            console.error("API Error:", error.response.status, error.response.data);
          }
        }
      } finally {
        this.loadingPasienCount = false;
        console.log('fetchPasienCount completed, totalPasien:', this.totalPasien);
      }
    },

    getUserRoleDisplay(role) {
      const roleMap = {
        'admin': 'Administrator',
        'dokter': 'Dokter',
        'pasien': 'Pasien'
      };
      return roleMap[role] || 'Pengguna';
    }
  }
};
</script>

<style scoped>
.bg-gradient-primary {
  background: linear-gradient(135deg, #004178 0%, #0056a3 100%);
}

.text-white-50 {
  color: rgba(255, 255, 255, 0.8) !important;
}

.text-white-75 {
  color: rgba(255, 255, 255, 0.9) !important;
}

.card {
  border: none;
  transition: transform 0.2s ease-in-out;
}

.card:hover {
  transform: translateY(-2px);
}

.btn-outline-success:hover,
.btn-outline-primary:hover,
.btn-outline-warning:hover {
  transform: translateY(-1px);
  box-shadow: 0 4px 8px rgba(0,0,0,0.1);
  transition: all 0.2s ease-in-out;
}

.spinner-border-sm {
  width: 1rem;
  height: 1rem;
}

@media (max-width: 768px) {
  .card-body h3 {
    font-size: 1.5rem;
  }
  
  .card-body h4 {
    font-size: 1.2rem;
  }
}
</style>