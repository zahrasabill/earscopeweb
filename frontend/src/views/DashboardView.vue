<template>
  <app-layout activePage="dashboard">
    <!-- Welcome Card -->
    <div class="row mb-4">
      <div class="col-12">
        <div class="card bg-gradient-primary text-white shadow-lg">
          <div class="card-body py-4">
            <div class="text-center">
              <h3 class="mb-3">Selamat Datang!</h3>
              <h4 class="mb-1 text-white-50">{{ currentUser.name }}</h4>
              <p class="mb-0 text-white-75">{{ getUserRoleDisplay(currentUser.role) }}</p>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Time Filter for Statistics -->
    <div v-if="currentUser.role === 'admin' || currentUser.role === 'dokter'" class="row mb-4">
      <div class="col-12">
        <div class="card bg-light shadow-sm">
          <div class="card-body">
            <div class="row align-items-center">
              <div class="col-md-6">
                <h6 class="mb-0 text-muted">
                  <i class="bi bi-filter me-2"></i>
                  Filter Periode Statistik
                </h6>
              </div>
              <div class="col-md-6">
                <div class="btn-group w-100" role="group">
                  <button 
                    type="button" 
                    class="btn btn-sm"
                    :class="timeFilter === 'today' ? 'btn-primary' : 'btn-outline-primary'"
                    @click="setTimeFilter('today')"
                  >
                    Hari Ini
                  </button>
                  <button 
                    type="button" 
                    class="btn btn-sm"
                    :class="timeFilter === 'week' ? 'btn-primary' : 'btn-outline-primary'"
                    @click="setTimeFilter('week')"
                  >
                    Minggu Ini
                  </button>
                  <button 
                    type="button" 
                    class="btn btn-sm"
                    :class="timeFilter === 'month' ? 'btn-primary' : 'btn-outline-primary'"
                    @click="setTimeFilter('month')"
                  >
                    Bulan Ini
                  </button>
                  <button 
                    type="button" 
                    class="btn btn-sm"
                    :class="timeFilter === 'all' ? 'btn-primary' : 'btn-outline-primary'"
                    @click="setTimeFilter('all')"
                  >
                    Semua
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Statistics Cards for Admin -->
    <div v-if="currentUser.role === 'admin'" class="row mb-4 justify-content-center">
      <!-- Total Dokter -->
      <div class="col-md-6 col-lg-4 mb-3">
        <div class="card bg-light shadow-sm">
          <div class="card-body text-center">
            <div class="d-flex align-items-center justify-content-center mb-2">
              <i class="bi bi-person-badge text-success me-2" style="font-size: 2rem;"></i>
              <div>
                <h2 class="mb-0 text-success fw-bold">
                  <span v-if="loadingDokterCount">
                    <div class="spinner-border spinner-border-sm" role="status">
                      <span class="visually-hidden">Loading...</span>
                    </div>
                  </span>
                  <span v-else>{{ totalDokter !== null ? totalDokter : '-' }}</span>
                </h2>
                <p class="mb-0 text-muted small">
                  Total Dokter<span v-if="getFilterLabel()"> {{ getFilterLabel() }}</span>
                </p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Last Update Info for Admin -->
    <div v-if="currentUser.role === 'admin'" class="row mb-4">
      <div class="col-12">
        <div class="card bg-light shadow-sm">
          <div class="card-body text-center">
            <small class="text-muted">
              <i class="bi bi-clock me-1"></i>
              Terakhir diperbarui: {{ lastUpdated }}
            </small>
          </div>
        </div>
      </div>
    </div>

    <!-- Statistics Cards for Dokter -->
    <div v-if="currentUser.role === 'dokter'" class="row mb-4 justify-content-center">
      <!-- Total Pasien -->
      <div class="col-md-4 col-lg-3 mb-3">
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
                <p class="mb-0 text-muted small">
                  Total Pasien {{ getFilterLabel() }}
                </p>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Total Penanganan -->
      <div class="col-md-4 col-lg-3 mb-3">
        <div class="card bg-light shadow-sm">
          <div class="card-body text-center">
            <div class="d-flex align-items-center justify-content-center mb-2">
              <i class="bi bi-book-fill text-warning me-2" style="font-size: 2rem;"></i>
              <div>
                <h2 class="mb-0 text-warning fw-bold">
                  <span v-if="loadingPenangananCount">
                    <div class="spinner-border spinner-border-sm" role="status">
                      <span class="visually-hidden">Loading...</span>
                    </div>
                  </span>
                  <span v-else>{{ totalPenanganan !== null ? totalPenanganan : '-' }}</span>
                </h2>
                <p class="mb-0 text-muted small">
                  Total Pemeriksaan {{ getFilterLabel() }}
                </p>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Total Video -->
      <div class="col-md-4 col-lg-3 mb-3">
        <div class="card bg-light shadow-sm">
          <div class="card-body text-center">
            <div class="d-flex align-items-center justify-content-center mb-2">
              <i class="bi bi-play-circle text-info me-2" style="font-size: 2rem;"></i>
              <div>
                <h2 class="mb-0 text-info fw-bold">
                  <span v-if="loadingVideoCount">
                    <div class="spinner-border spinner-border-sm" role="status">
                      <span class="visually-hidden">Loading...</span>
                    </div>
                  </span>
                  <span v-else>{{ totalVideo !== null ? totalVideo : '-' }}</span>
                </h2>
                <p class="mb-0 text-muted small">
                  Total Video {{ getFilterLabel() }}
                </p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>



    <!-- Last Update Info for Dokter -->
    <div v-if="currentUser.role === 'dokter'" class="row mb-4">
      <div class="col-12">
        <div class="card bg-light shadow-sm">
          <div class="card-body text-center">
            <small class="text-muted">
              <i class="bi bi-clock me-1"></i>
              Terakhir diperbarui: {{ lastUpdated }}
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
            <router-link to="/dokter"
              class="btn btn-outline-success btn-lg w-100 d-flex align-items-center justify-content-center">
              <i class="bi bi-person-plus me-2"></i>
              Kelola Dokter
            </router-link>
          </div>

          <!-- Quick Actions untuk Dokter -->
          <div v-if="currentUser.role === 'dokter'" class="col-md-6 mb-3">
            <router-link to="/pasien"
              class="btn btn-outline-primary btn-lg w-100 d-flex align-items-center justify-content-center">
              <i class="bi bi-people me-2"></i>
              Kelola Pasien
            </router-link>
          </div>
          <div v-if="currentUser.role === 'dokter'" class="col-md-6 mb-3">
            <router-link to="/penanganan"
              class="btn btn-outline-success btn-lg w-100 d-flex align-items-center justify-content-center">
              <i class="bi bi-book-fill me-2"></i>
              Penanganan
            </router-link>
          </div>
          <div v-if="currentUser.role === 'dokter'" class="col-md-6 mb-3">
            <router-link to="/riwayat"
              class="btn btn-outline-info btn-lg w-100 d-flex align-items-center justify-content-center">
              <i class="bi bi-clock-history me-2"></i>
              Riwayat Pemeriksaan
            </router-link>
          </div>

          <!-- Quick Actions untuk Dokter dan Pasien -->
          <div v-if="currentUser.role === 'dokter' || currentUser.role === 'pasien'" class="col-md-6 mb-3">
            <router-link to="/pemeriksaan"
              class="btn btn-outline-warning btn-lg w-100 d-flex align-items-center justify-content-center">
              <i class="bi bi-clipboard2-pulse me-2"></i>
              {{ currentUser.role === 'pasien' ? 'Lihat Hasil' : 'Hasil Pemeriksaan' }}
            </router-link>
          </div>

          <!-- Quick Actions untuk Pasien -->
          <div v-if="currentUser.role === 'pasien'" class="col-md-6 mb-3">
            <router-link to="/riwayat"
              class="btn btn-outline-info btn-lg w-100 d-flex align-items-center justify-content-center">
              <i class="bi bi-clock-history me-2"></i>
              Riwayat Pemeriksaan
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
        name: ""
      },
      totalDokter: null,
      totalPasien: null,
      totalPenanganan: null,
      totalVideo: null,

      loadingDokterCount: false,
      loadingPasienCount: false,
      loadingPenangananCount: false,
      loadingVideoCount: false,

      lastUpdated: "",
      timeFilter: 'all' // Default filter
    };
  },
  created() {
    this.getCurrentUser();

    // Listen for storage events from other components
    window.addEventListener('storage', (event) => {
      if (event.key === 'dokterDataUpdated' || event.key === 'pasienDataUpdated' || event.key === 'penangananDataUpdated') {
        this.fetchAllStatistics();
      }
    });
  },
  mounted() {
    // Check if data was updated from another component
    const updated = localStorage.getItem('dokterDataUpdated') || 
                   localStorage.getItem('pasienDataUpdated') || 
                   localStorage.getItem('penangananDataUpdated');
    if (updated) {
      localStorage.removeItem('dokterDataUpdated');
      localStorage.removeItem('pasienDataUpdated');
      localStorage.removeItem('penangananDataUpdated');
    }

    // Fetch all statistics after component is mounted and user data is available
    this.$nextTick(() => {
      if (this.currentUser.role === 'dokter' || this.currentUser.role === 'admin') {
        console.log('Fetching all statistics for', this.currentUser.role);
        this.fetchAllStatistics();
      }
    });
  },
  watch: {
    'currentUser.role': {
      handler(newRole) {
        if (newRole === 'dokter' || newRole === 'admin') {
          this.fetchAllStatistics();
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
          const payload = jwtDecode(token);
          this.currentUser.kode_akses = payload.kode_akses || "Tidak Ada Akses";
          this.currentUser.name = payload.name || "Tidak Ada Nama";
          this.currentUser.role = payload.role || "Tidak Ada Role";

          console.log('Current User:', this.currentUser);

          if (this.currentUser.role === 'dokter' || this.currentUser.role === 'admin') {
            console.log('User is', this.currentUser.role, ', fetching all statistics...');
            this.$nextTick(() => {
              this.fetchAllStatistics();
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

    setTimeFilter(filter) {
      this.timeFilter = filter;
      this.fetchAllStatistics();
    },

    getFilterLabel() {
      const labels = {
        'today': 'Hari Ini',
        'week': 'Minggu Ini',
        'month': 'Bulan Ini',
        'all': ''
      };
      return labels[this.timeFilter] || '';
    },

    getDateRange() {
      const now = new Date();
      const today = new Date(now.getFullYear(), now.getMonth(), now.getDate());
      
      switch (this.timeFilter) {
        case 'today':
          return {
            start: today,
            end: new Date(today.getTime() + 24 * 60 * 60 * 1000)
          };
        case 'week':
          const startOfWeek = new Date(today);
          startOfWeek.setDate(today.getDate() - today.getDay());
          return {
            start: startOfWeek,
            end: new Date(startOfWeek.getTime() + 7 * 24 * 60 * 60 * 1000)
          };
        case 'month':
          const startOfMonth = new Date(today.getFullYear(), today.getMonth(), 1);
          const endOfMonth = new Date(today.getFullYear(), today.getMonth() + 1, 0);
          return {
            start: startOfMonth,
            end: new Date(endOfMonth.getTime() + 24 * 60 * 60 * 1000)
          };
        default:
          return null;
      }
    },

    filterDataByDate(data, dateField = 'created_at') {
      if (this.timeFilter === 'all') {
        return data;
      }

      const dateRange = this.getDateRange();
      if (!dateRange) {
        return data;
      }

      return data.filter(item => {
        const itemDate = new Date(item[dateField]);
        return itemDate >= dateRange.start && itemDate < dateRange.end;
      });
    },

    async fetchAllStatistics() {
      if (this.currentUser.role !== 'dokter' && this.currentUser.role !== 'admin') {
        console.log('User is not dokter or admin, skipping fetch');
        return;
      }

      console.log('Fetching all statistics with filter:', this.timeFilter);
      
      const promises = [];
      
      // For admin, fetch only dokter statistics
      if (this.currentUser.role === 'admin') {
        promises.push(this.fetchDokterCount());
      }
      
      // For dokter, fetch pasien, penanganan, and video statistics
      if (this.currentUser.role === 'dokter') {
        promises.push(this.fetchPasienCount());
        promises.push(this.fetchPenangananCount());
        promises.push(this.fetchVideoCount());
      }

      // Fetch all statistics concurrently
      await Promise.all(promises);

      this.updateLastUpdated();
    },

    async fetchDokterCount() {
      console.log('fetchDokterCount called');
      this.loadingDokterCount = true;

      try {
        const token = localStorage.getItem("token") || sessionStorage.getItem("token");
        if (!token) throw new Error('Token tidak ditemukan');

        const response = await api.get('dokter', {
          headers: { 'Authorization': `Bearer ${token}` }
        });

        let dokterData = this.extractDataFromResponse(response.data);
        
        // Filter data berdasarkan waktu
        const filteredData = this.filterDataByDate(dokterData);
        this.totalDokter = filteredData.length;
        
        console.log('Total dokter:', this.totalDokter);

      } catch (error) {
        console.error("Error fetching dokter count:", error);
        this.totalDokter = 0;
      } finally {
        this.loadingDokterCount = false;
      }
    },

    async fetchPasienCount() {
      console.log('fetchPasienCount called');
      this.loadingPasienCount = true;

      try {
        const token = localStorage.getItem("token") || sessionStorage.getItem("token");
        if (!token) throw new Error('Token tidak ditemukan');

        const response = await api.get('pasien', {
          headers: { 'Authorization': `Bearer ${token}` }
        });

        let pasienData = this.extractDataFromResponse(response.data);
        
        // Filter data berdasarkan waktu
        const filteredData = this.filterDataByDate(pasienData);
        this.totalPasien = filteredData.length;
        
        console.log('Total pasien:', this.totalPasien);

      } catch (error) {
        console.error("Error fetching patient count:", error);
        this.totalPasien = 0;
      } finally {
        this.loadingPasienCount = false;
      }
    },

    async fetchPenangananCount() {
      console.log('fetchPenangananCount called');
      this.loadingPenangananCount = true;

      try {
        const token = localStorage.getItem("token") || sessionStorage.getItem("token");
        if (!token) throw new Error('Token tidak ditemukan');

        const response = await api.get('penanganan', {
          headers: { 'Authorization': `Bearer ${token}` }
        });

        let penangananData = this.extractDataFromResponse(response.data);
        
        // Filter data berdasarkan waktu
        const filteredData = this.filterDataByDate(penangananData);
        this.totalPenanganan = filteredData.length;
        
        console.log('Total penanganan:', this.totalPenanganan);

      } catch (error) {
        console.error("Error fetching penanganan count:", error);
        this.totalPenanganan = 0;
      } finally {
        this.loadingPenangananCount = false;
      }
    },

    async fetchVideoCount() {
      console.log('fetchVideoCount called');
      this.loadingVideoCount = true;

      try {
        const token = localStorage.getItem("token") || sessionStorage.getItem("token");
        if (!token) throw new Error('Token tidak ditemukan');

        const response = await api.get('videos', {
          headers: { 'Authorization': `Bearer ${token}` }
        });

        let videoData = this.extractDataFromResponse(response.data);
        
        // Filter data berdasarkan waktu
        const filteredData = this.filterDataByDate(videoData);
        this.totalVideo = filteredData.length;
        
        console.log('Total video:', this.totalVideo);

      } catch (error) {
        console.error("Error fetching video count:", error);
        this.totalVideo = 0;
      } finally {
        this.loadingVideoCount = false;
      }
    },



    extractDataFromResponse(responseData) {
      if (!responseData) return [];
      
      if (Array.isArray(responseData)) {
        return responseData;
      } else if (responseData.data && Array.isArray(responseData.data)) {
        return responseData.data;
      } else if (responseData.data && responseData.data.items) {
        return responseData.data.items || [];
      }
      
      console.error('Unrecognized response format:', responseData);
      return [];
    },

    updateLastUpdated() {
      this.lastUpdated = new Date().toLocaleString('id-ID', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
      });
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

.bg-gradient-info {
  background: linear-gradient(135deg, #17a2b8 0%, #138496 100%);
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
.btn-outline-warning:hover,
.btn-outline-info:hover {
  transform: translateY(-1px);
  box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
  transition: all 0.2s ease-in-out;
}

.spinner-border-sm {
  width: 1rem;
  height: 1rem;
}

.card-header {
  border-bottom: none;
}

.btn-group .btn {
  border-radius: 0;
}

.btn-group .btn:first-child {
  border-top-left-radius: 0.375rem;
  border-bottom-left-radius: 0.375rem;
}

.btn-group .btn:last-child {
  border-top-right-radius: 0.375rem;
  border-bottom-right-radius: 0.375rem;
}

@media (max-width: 768px) {
  .card-body h3 {
    font-size: 1.5rem;
  }

  .card-body h4 {
    font-size: 1.2rem;
  }
  
  .col-lg-3 {
    margin-bottom: 1rem;
  }

  .btn-group {
    flex-direction: column;
  }

  .btn-group .btn {
    border-radius: 0.375rem !important;
    margin-bottom: 0.25rem;
  }

  .btn-group .btn:last-child {
    margin-bottom: 0;
  }
}
</style>