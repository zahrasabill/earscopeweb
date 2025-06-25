<template>
  <div class="container-fluid riwayat-container">
    <!-- Header Section -->
    <div class="header-section mb-4">
      <div class="card shadow-sm">
        <div class="card-header bg-gradient text-black">
          <div class="d-flex justify-content-between align-items-center">
            <h4 class="mb-0">
              <i class="fas fa-file-medical me-2"></i>
              {{ userRole === 'dokter' ? 'Riwayat Pemeriksaan Pasien' : 'Riwayat Pemeriksaan Saya' }}
            </h4>
            <div class="header-actions">
              <button 
                v-if="userRole === 'dokter'" 
                @click="navigateToCreate" 
                class="btn btn-light btn-sm me-2"
              >
                <i class="fas fa-plus me-1"></i>Tambah Penanganan
              </button>
              <button @click="refreshData" class="btn btn-light btn-sm" :disabled="isLoading">
                <i class="fas fa-sync-alt me-1" :class="{ 'fa-spin': isLoading }"></i>Refresh
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Filter Section for Doctor -->
    <div v-if="userRole === 'dokter'" class="filter-section mb-4">
      <div class="card">
        <div class="card-body">
          <div class="row align-items-end">
            <div class="col-md-6">
              <label class="form-label fw-bold">Filter Pasien</label>
              <select v-model="selectedPatientFilter" class="form-select" @change="applyFilters">
                <option value="">Semua Pasien</option>
                <option v-for="user in availablePatients" :key="user.id" :value="user.id">
                  {{ user.name }} {{ user.kode_akses ? `(${user.kode_akses})` : '' }}
                </option>
              </select>
            </div>
            <div class="col-md-2">
              <button @click="clearFilters" class="btn btn-outline-secondary w-100">
                <i class="fas fa-times me-1"></i>Clear
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Statistics Cards for Doctor -->
    <div v-if="userRole === 'dokter'" class="stats-section mb-4">
      <div class="row">
        <div class="col-md-6">
          <div class="card border-primary">
            <div class="card-body text-center">
              <i class="fas fa-file-medical text-primary mb-2" style="font-size: 2rem;"></i>
              <h5 class="card-title">Total Pemeriksaan</h5>
              <h3 class="text-primary">{{ totalPemeriksaan }}</h3>
            </div>
          </div>
        </div>
        <div class="col-md-6">
          <div class="card border-warning">
            <div class="card-body text-center">
              <i class="fas fa-paper-plane text-warning mb-2" style="font-size: 2rem;"></i>
              <h5 class="card-title">Assigned</h5>
              <h3 class="text-warning">{{ pemeriksaanAssigned }}</h3>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Riwayat Pemeriksaan Cards -->
    <div class="riwayat-section">
      <!-- Loading State -->
      <div v-if="isLoading" class="text-center py-5">
        <div class="spinner-border text-primary me-2"></div>
        <span>Memuat riwayat pemeriksaan...</span>
      </div>

      <!-- Empty State -->
      <div v-else-if="filteredRiwayat.length === 0" class="text-center py-5">
        <i class="fas fa-file-medical text-muted mb-3" style="font-size: 4rem;"></i>
        <h5 class="text-muted">Belum ada riwayat pemeriksaan</h5>
        <p class="text-muted">
          {{ userRole === 'dokter' 
            ? 'Mulai dengan menambahkan penanganan baru untuk pasien'
            : 'Belum ada pemeriksaan yang diterima dari dokter'
          }}
        </p>
        <button 
          v-if="userRole === 'dokter'" 
          @click="navigateToCreate" 
          class="btn btn-primary"
        >
          <i class="fas fa-plus me-1"></i>Tambah Penanganan Baru
        </button>
      </div>

      <!-- Riwayat Cards -->
      <div v-else class="row">
        <div v-for="riwayat in paginatedRiwayat" :key="riwayat.id" class="col-lg-6 col-xl-4 mb-4">
          <div class="card h-100 shadow-sm riwayat-card">
            <!-- Card Header -->
            <div class="card-header">
              <div class="patient-info">
                <h6 class="mb-1 fw-bold">{{ riwayat.patient_name }}</h6>
                <div class="patient-details">
                  <small class="text-muted d-block">
                    <i class="fas fa-id-card me-1"></i>{{ riwayat.kode_akses || 'No. RM tidak tersedia' }}
                  </small>
                  <small class="text-muted d-block">
                    <i class="fas fa-birthday-cake me-1"></i>{{ formatPatientAge(riwayat.patient_tanggal_lahir) }}
                  </small>
                  <small class="text-muted d-block">
                    <i class="fas fa-venus-mars me-1"></i>{{ formatGender(riwayat.patient_gender) }}
                  </small>
                  <small class="text-muted d-block" v-if="riwayat.patient_no_telp">
                    <i class="fas fa-phone me-1"></i>{{ riwayat.patient_no_telp }}
                  </small>
                </div>
              </div>
              <div class="status-badges mt-2">
                <span 
                  class="badge me-1"
                  :class="getStatusClass(riwayat.status)"
                >
                  {{ getStatusText(riwayat.status) }}
                </span>
                <span class="badge bg-secondary">
                  {{ formatDate(riwayat.tanggal_penanganan) }}
                </span>
              </div>
            </div>

            <!-- Card Body -->
            <div class="card-body">
              <!-- Telinga Terkena -->
              <div class="info-item mb-3">
                <div class="d-flex align-items-center mb-2">
                  <i class="fas fa-deaf text-primary me-2"></i>
                  <strong>Telinga Terkena:</strong>
                </div>
                <span class="badge badge-telinga" :class="getTelingaClass(riwayat.telinga_terkena)">
                  {{ formatTelinga(riwayat.telinga_terkena) }}
                </span>
              </div>

              <!-- Keluhan -->
              <div class="info-item mb-3">
                <div class="d-flex align-items-center mb-2">
                  <i class="fas fa-exclamation-triangle text-warning me-2"></i>
                  <strong>Keluhan:</strong>
                </div>
                <p class="info-text">{{ truncateText(riwayat.keluhan, 100) }}</p>
              </div>

              <!-- Diagnosis -->
              <div class="info-item mb-3">
                <div class="d-flex align-items-center mb-2">
                  <i class="fas fa-diagnoses text-info me-2"></i>
                  <strong>Diagnosis:</strong>
                </div>
                <p class="info-text">{{ truncateText(riwayat.diagnosis_manual, 100) }}</p>
              </div>

              <!-- Tindakan -->
              <div class="info-item mb-3">
                <div class="d-flex align-items-center mb-2">
                  <i class="fas fa-hand-holding-medical text-success me-2"></i>
                  <strong>Tindakan:</strong>
                </div>
                <p class="info-text">{{ truncateText(riwayat.tindakan, 100) }}</p>
              </div>

              <!-- Assigned To (for doctors) -->
              <div v-if="userRole === 'dokter' && riwayat.assigned_to" class="info-item mb-3">
                <div class="d-flex align-items-center mb-2">
                  <i class="fas fa-user-check text-success me-2"></i>
                  <strong>Assigned To:</strong>
                </div>
                <span class="badge bg-success">{{ getAssignedPatientName(riwayat.assigned_to) }}</span>
              </div>

              <!-- Riwayat Penyakit (if exists) -->
              <div v-if="riwayat.riwayat_penyakit" class="info-item mb-3">
                <div class="d-flex align-items-center mb-2">
                  <i class="fas fa-history text-secondary me-2"></i>
                  <strong>Riwayat Penyakit:</strong>
                </div>
                <p class="info-text">{{ truncateText(riwayat.riwayat_penyakit, 80) }}</p>
              </div>

              <!-- Assigned Info for Patient -->
              <div v-if="userRole === 'pasien' && riwayat.assigned_at" class="info-item mb-3">
                <div class="d-flex align-items-center mb-2">
                  <i class="fas fa-clock text-info me-2"></i>
                  <strong>Diterima:</strong>
                </div>
                <small class="text-info">
                  {{ formatDateTime(riwayat.assigned_at) }}
                </small>
              </div>
            </div>

            <!-- Card Footer -->
            <div class="card-footer bg-light">
              <div class="d-flex justify-content-between align-items-center">
                <small class="text-muted">
                  <i class="fas fa-clock me-1"></i>
                  {{ formatDateTime(riwayat.created_at) }}
                </small>
                <div class="action-buttons">
                  <div v-if="userRole === 'dokter'" class="btn-group" role="group">
                    <button 
                      v-if="riwayat.status !== 'assigned'" 
                      @click="showAssignModal(riwayat)" 
                      class="btn btn-sm btn-outline-success"
                      :disabled="isUpdating"
                      title="Assign ke Pasien"
                    >
                      <i class="fas fa-user-plus"></i>
                    </button>
                    <button 
                      v-if="riwayat.status === 'assigned'" 
                      @click="unassignFromPatient(riwayat)" 
                      class="btn btn-sm btn-outline-warning"
                      :disabled="isUpdating"
                      title="Unassign dari Pasien"
                    >
                      <i class="fas fa-user-minus"></i>
                    </button>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Pagination -->
      <div v-if="totalPages > 1" class="d-flex justify-content-center mt-4">
        <nav>
          <ul class="pagination">
            <li class="page-item" :class="{ disabled: currentPage === 1 }">
              <button class="page-link" @click="changePage(currentPage - 1)" :disabled="currentPage === 1">
                <i class="fas fa-chevron-left"></i>
              </button>
            </li>
            <li 
              v-for="page in visiblePages" 
              :key="page" 
              class="page-item" 
              :class="{ active: page === currentPage }"
            >
              <button class="page-link" @click="changePage(page)">{{ page }}</button>
            </li>
            <li class="page-item" :class="{ disabled: currentPage === totalPages }">
              <button class="page-link" @click="changePage(currentPage + 1)" :disabled="currentPage === totalPages">
                <i class="fas fa-chevron-right"></i>
              </button>
            </li>
          </ul>
        </nav>
      </div>
    </div>

    <!-- Assign Modal -->
    <transition name="fade">
      <div v-if="showAssignModalFlag" class="modal-overlay" @click.self="closeAssignModal">
        <div class="modal-dialog modal-assign">
          <div class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title">
                <i class="fas fa-user-plus me-2"></i>Assign Penanganan ke Pasien
              </h5>
              <button type="button" class="btn-close" @click="closeAssignModal">
                <i class="fas fa-times"></i>
              </button>
            </div>
            <div class="modal-body">
              <div v-if="assignSuccess" class="text-center py-3">
                <i class="fas fa-check-circle text-success" style="font-size: 48px;"></i>
                <h5 class="mt-3 text-success">Berhasil!</h5>
                <p>Penanganan telah berhasil di-assign ke pasien</p>
              </div>
              <div v-else-if="selectedRiwayatForAssign">
                <div class="assign-info mb-3">
                  <div class="info-row">
                    <strong>Penanganan untuk:</strong> {{ selectedRiwayatForAssign.patient_name }}
                  </div>
                  <div class="info-row">
                    <strong>Tanggal:</strong> {{ formatDate(selectedRiwayatForAssign.tanggal_penanganan) }}
                  </div>
                </div>
                
                <div class="mb-3">
                  <label class="form-label fw-bold">Pilih Pasien untuk di-assign:</label>
                  <select v-model="selectedAssignUserId" class="form-select">
                    <option value="" disabled>Pilih pasien...</option>
                    <option 
                      v-for="user in availablePatients" 
                      :key="user.id" 
                      :value="user.id"
                    >
                      {{ user.name }} {{ user.kode_akses ? `(${user.kode_akses})` : '' }}
                    </option>
                  </select>
                </div>
              </div>
            </div>
            <div class="modal-footer">
              <button v-if="assignSuccess" class="btn btn-primary w-100" @click="closeAssignModal">
                Selesai
              </button>
              <template v-else>
                <button class="btn btn-secondary" @click="closeAssignModal">Batal</button>
                <button 
                  class="btn btn-success btn-assign"
                  :disabled="!selectedAssignUserId || assignLoading"
                  @click="assignToPatient"
                >
                  <span v-if="assignLoading" class="spinner-border spinner-border-sm me-2"></span>
                  <i v-else class="fas fa-paper-plane me-1"></i>
                  {{ assignLoading ? 'Mengassign...' : 'Assign' }}
                </button>
              </template>
            </div>
          </div>
        </div>
      </div>
    </transition>

    <!-- Loading Overlay -->
    <div v-if="isUpdating" class="loading-overlay">
      <div class="loading-content">
        <div class="spinner-border text-primary"></div>
        <p class="mt-3">Memproses...</p>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, onMounted, computed } from 'vue';
import { useRouter } from 'vue-router';
import AppLayout from '@/components/AppLayout.vue';
import { useUserStore } from '@/stores/userStore';
import { storeToRefs } from 'pinia';
import api from '@/api';
import axios from 'axios';
import { jwtDecode } from "jwt-decode";

export default {
  name: 'RiwayatPemeriksaan',
  components: { AppLayout },
  setup() {
    const userStore = useUserStore();
    const { users } = storeToRefs(userStore);
    const router = useRouter();

    // Reactive data
    const riwayatList = ref([]);
    const isLoading = ref(false);
    const isUpdating = ref(false);
    const userRole = ref('pasien');
    const currentUserId = ref(null);
    const availablePatients = ref([]);

    // Filters
    const selectedPatientFilter = ref('');

    // Pagination
    const currentPage = ref(1);
    const itemsPerPage = ref(6);

    // Modal states
    const showAssignModalFlag = ref(false);
    const selectedRiwayatForAssign = ref(null);
    const selectedAssignUserId = ref('');
    const assignLoading = ref(false);
    const assignSuccess = ref(false);

    // Get user role and ID from JWT
    const getUserInfoFromToken = () => {
      try {
        const token = localStorage.getItem("token") || sessionStorage.getItem("token");
        if (token) {
          const payload = jwtDecode(token);
          return {
            role: payload.role || 'pasien',
            userId: payload.sub || payload.user_id || payload.id
          };
        }
        return { role: 'pasien', userId: null };
      } catch (error) {
        console.error('Error decoding token:', error);
        return { role: 'pasien', userId: null };
      }
    };

    // Initialize user info
    const initializeUserInfo = () => {
      const userInfo = getUserInfoFromToken();
      userRole.value = userInfo.role;
      currentUserId.value = userInfo.userId;
      console.log('User info initialized:', { role: userRole.value, userId: currentUserId.value });
    };

    // Computed properties
    const filteredRiwayat = computed(() => {
      let filtered = [...riwayatList.value];

      // Filter by patient (for doctors)
      if (userRole.value === 'dokter' && selectedPatientFilter.value) {
        filtered = filtered.filter(item => item.user_id.toString() === selectedPatientFilter.value.toString());
      }

      // Sort by date (newest first)
      return filtered.sort((a, b) => new Date(b.tanggal_penanganan) - new Date(a.tanggal_penanganan));
    });

    const totalPages = computed(() => Math.ceil(filteredRiwayat.value.length / itemsPerPage.value));

    const paginatedRiwayat = computed(() => {
      const start = (currentPage.value - 1) * itemsPerPage.value;
      const end = start + itemsPerPage.value;
      return filteredRiwayat.value.slice(start, end);
    });

    const visiblePages = computed(() => {
      const pages = [];
      const total = totalPages.value;
      const current = currentPage.value;
      
      if (total <= 7) {
        for (let i = 1; i <= total; i++) {
          pages.push(i);
        }
      } else {
        if (current <= 4) {
          for (let i = 1; i <= 5; i++) pages.push(i);
          pages.push('...');
          pages.push(total);
        } else if (current >= total - 3) {
          pages.push(1);
          pages.push('...');
          for (let i = total - 4; i <= total; i++) pages.push(i);
        } else {
          pages.push(1);
          pages.push('...');
          for (let i = current - 1; i <= current + 1; i++) pages.push(i);
          pages.push('...');
          pages.push(total);
        }
      }
      
      return pages;
    });

    // Statistics for doctors
    const totalPemeriksaan = computed(() => riwayatList.value.length);
    const pemeriksaanAssigned = computed(() => {
      return riwayatList.value.filter(item => item.status === 'assigned').length;
    });

    // Methods
    const fetchRiwayatPemeriksaan = async () => {
  try {
    isLoading.value = true;
    const token = localStorage.getItem("token") || sessionStorage.getItem("token");

    if (!token) {
      console.error("No token found");
      return;
    }

    let enrichedData = [];

    // Ambil semua penanganan
    const response = await axios.get(api.getEndpoint("penanganan"), {
      headers: { Authorization: `Bearer ${token}` },
    });

    const penangananData = response.data.data || response.data;
    console.log("Raw penanganan data:", penangananData);

    // Enrich data dengan informasi user untuk SEMUA role (dokter dan pasien)
    enrichedData = await Promise.all(
      penangananData.map(async (item) => {
        try {
          // Fetch data user berdasarkan user_id dari penanganan
          const userResponse = await axios.get(api.getEndpoint(`users/${item.user_id}`), {
            headers: { Authorization: `Bearer ${token}` },
          });

          const user = userResponse.data.data || userResponse.data;
          console.log(`User data for ID ${item.user_id}:`, user);

          return {
            ...item,
            patient_name: user.name || "Nama tidak tersedia",
            patient_tanggal_lahir: user.tanggal_lahir || null,
            patient_no_telp: user.no_telp || null,
            patient_gender: user.gender || null,
            kode_akses: user.kode_akses || null,
          };
        } catch (error) {
          console.error(`Failed to fetch user details for user ${item.user_id}:`, error);
          
          // Jika gagal fetch user, kembalikan dengan data default
          return {
            ...item,
            patient_name: "Nama tidak tersedia",
            patient_tanggal_lahir: null,
            patient_no_telp: null,
            patient_gender: null,
            kode_akses: null,
          };
        }
      })
    );

    riwayatList.value = enrichedData;
    console.log("Final riwayat data:", enrichedData);
  } catch (error) {
    console.error("Error fetching riwayat pemeriksaan:", error);
    alert("Gagal memuat riwayat pemeriksaan. Silakan coba lagi.");
  } finally {
    isLoading.value = false;
  }
};

    const fetchAvailablePatients = async () => {
      if (userRole.value === 'dokter') {
        try {
          const token = localStorage.getItem("token") || sessionStorage.getItem("token");
          
          // Fetch all patients using the correct endpoint
          const response = await axios.get(api.getEndpoint('pasien'), {
            headers: { Authorization: `Bearer ${token}` },
          });

          const patientsData = response.data.data || response.data;
          availablePatients.value = Array.isArray(patientsData) ? patientsData : [];
          
          console.log('Available patients:', availablePatients.value);
        } catch (error) {
          console.error('Error fetching patients:', error);
          availablePatients.value = [];
        }
      }
    };

    const fetchUsers = async () => {
      if (userRole.value === 'dokter') {
        try {
          // Load users data only once if not already loaded
          if (users.value.length === 0) {
            await userStore.fetchUsers();
          }
        } catch (error) {
          console.error('Error fetching users:', error);
        }
      }
    };

    const refreshData = async () => {
      await Promise.all([
        fetchRiwayatPemeriksaan(),
        fetchUsers(),
        fetchAvailablePatients()
      ]);
    };

    const applyFilters = () => {
      currentPage.value = 1; // Reset to first page when filters change
    };

    const clearFilters = () => {
      selectedPatientFilter.value = '';
      currentPage.value = 1;
    };

    const changePage = (page) => {
      if (page >= 1 && page <= totalPages.value) {
        currentPage.value = page;
      }
    };

    const showAssignModal = (riwayat) => {
      selectedRiwayatForAssign.value = riwayat;
      selectedAssignUserId.value = '';
      assignSuccess.value = false;
      showAssignModalFlag.value = true;
    };

    const closeAssignModal = () => {
      showAssignModalFlag.value = false;
      setTimeout(() => {
        selectedRiwayatForAssign.value = null;
        selectedAssignUserId.value = '';
        assignSuccess.value = false;
      }, 300);
    };

    const assignToPatient = async () => {
      if (!selectedAssignUserId.value || !selectedRiwayatForAssign.value) {
        alert('Pilih pasien terlebih dahulu');
        return;
      }

      try {
        assignLoading.value = true;
        const token = localStorage.getItem("token") || sessionStorage.getItem("token");
        const response = await axios.post(
          api.getEndpoint(`penanganan/${selectedRiwayatForAssign.value.id}/assign/${selectedAssignUserId.value}`),
          {}, // Empty body karena userId sudah di URL
          {
            headers: { Authorization: `Bearer ${token}` }
          }
        );

        if (response.data.success || response.status === 200) {
          assignSuccess.value = true;
          
          // Update the local data
          const index = riwayatList.value.findIndex(item => item.id === selectedRiwayatForAssign.value.id);
          if (index !== -1) {
            riwayatList.value[index] = {
              ...riwayatList.value[index],
              status: 'assigned',
              assigned_to: selectedAssignUserId.value,
              assigned_at: new Date().toISOString()
            };
          }

          // Auto close after 2 seconds
          setTimeout(() => {
            closeAssignModal();
          }, 2000);
        } else {
          throw new Error('Failed to assign penanganan');
        }
      } catch (error) {
        console.error('Error assigning penanganan:', error);
        alert('Gagal assign penanganan. Silakan coba lagi.');
      } finally {
        assignLoading.value = false;
      }
    };

    const unassignFromPatient = async (riwayat) => {
      if (!confirm('Yakin ingin unassign penanganan ini dari pasien?')) {
        return;
      }

      try {
        isUpdating.value = true;
        const token = localStorage.getItem("token") || sessionStorage.getItem("token");
        const response = await axios.patch(
          api.getEndpoint(`penanganan/${riwayat.id}`),
          {
            // Data yang dikirim untuk unassign
            assigned_to: null,
            status: 'created', // Kembalikan status ke created
            assigned_at: null
          },
          {
            headers: { 
              Authorization: `Bearer ${token}`,
              'Content-Type': 'application/json'
            }
          }
        );

        if (response.data.success || response.status === 200) {
          // Update the local data
          const index = riwayatList.value.findIndex(item => item.id === riwayat.id);
          if (index !== -1) {
            riwayatList.value[index] = {
              ...riwayatList.value[index],
              status: 'created',
              assigned_to: null,
              assigned_at: null
            };
          }

          alert('Penanganan berhasil di-unassign dari pasien');
        } else {
          throw new Error('Failed to unassign penanganan');
        }
      } catch (error) {
        console.error('Error unassigning penanganan:', error);
        
        // Tampilkan pesan error yang lebih spesifik jika ada
        const errorMessage = error.response?.data?.message || 'Gagal unassign penanganan. Silakan coba lagi.';
        alert(errorMessage);
      } finally {
        isUpdating.value = false;
      }
    };

    const navigateToCreate = () => {
      router.push('/create-penanganan');
    };

    // Utility functions
    const formatDate = (dateString) => {
      if (!dateString) return 'Tanggal tidak tersedia';
      try {
        const date = new Date(dateString);
        return date.toLocaleDateString('id-ID', {
          year: 'numeric',
          month: 'long',
          day: 'numeric'
        });
      } catch (error) {
        return 'Tanggal tidak valid';
      }
    };

    const formatDateTime = (dateString) => {
      if (!dateString) return 'Tanggal tidak tersedia';
      try {
        const date = new Date(dateString);
        return date.toLocaleString('id-ID', {
          year: 'numeric',
          month: 'short',
          day: 'numeric',
          hour: '2-digit',
          minute: '2-digit'
        });
      } catch (error) {
        return 'Tanggal tidak valid';
      }
    };

    const formatPatientAge = (birthDate) => {
      if (!birthDate) return 'Umur tidak tersedia';
      try {
        const today = new Date();
        const birth = new Date(birthDate);
        let age = today.getFullYear() - birth.getFullYear();
        const monthDiff = today.getMonth() - birth.getMonth();
        
        if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birth.getDate())) {
          age--;
        }
        
        return `${age} tahun`;
      } catch (error) {
        return 'Umur tidak valid';
      }
    };

    const formatGender = (gender) => {
      if (!gender) return 'Tidak diketahui';
      return gender.toLowerCase() === 'laki-laki' || gender.toLowerCase() === 'l' ? 'Laki-laki' : 
             gender.toLowerCase() === 'perempuan' || gender.toLowerCase() === 'p' ? 'Perempuan' : 
             gender;
    };

    const formatTelinga = (telinga) => {
      if (!telinga) return 'Tidak diketahui';
      switch (telinga.toLowerCase()) {
        case 'kiri': return 'Telinga Kiri';
        case 'kanan': return 'Telinga Kanan';
        case 'keduanya': return 'Kedua Telinga';
        default: return telinga;
      }
    };

    const getTelingaClass = (telinga) => {
      if (!telinga) return 'bg-secondary';
      switch (telinga.toLowerCase()) {
        case 'kiri': return 'bg-info';
        case 'kanan': return 'bg-warning';
        case 'keduanya': return 'bg-danger';
        default: return 'bg-secondary';
      }
    };

    const getStatusClass = (status) => {
      switch (status) {
        case 'assigned': return 'bg-success';
        case 'completed': return 'bg-primary';
        case 'cancelled': return 'bg-danger';
        case 'created': return 'bg-warning';
        default: return 'bg-secondary';
      }
    };

    const getStatusText = (status) => {
      switch (status) {
        case 'assigned': return 'Assigned';
        case 'completed': return 'Selesai';
        case 'cancelled': return 'Dibatalkan';
        case 'created': return 'Dibuat';
        default: return status || 'Unknown';
      }
    };

    const getAssignedPatientName = (userId) => {
      const patient = availablePatients.value.find(p => p.id.toString() === userId.toString());
      return patient ? patient.name : 'Patient tidak ditemukan';
    };

    const truncateText = (text, maxLength = 100) => {
      if (!text) return 'Tidak ada data';
      if (text.length <= maxLength) return text;
      return text.substring(0, maxLength) + '...';
    };

    // Lifecycle hooks
    onMounted(async () => {
      initializeUserInfo();
      await refreshData();
    });

    // Return all reactive data and methods for template
    return {
      // Reactive data
      riwayatList,
      isLoading,
      isUpdating,
      userRole,
      currentUserId,
      availablePatients,
      
      // Filters
      selectedPatientFilter,
      
      // Pagination
      currentPage,
      itemsPerPage,
      
      // Modal states
      showAssignModalFlag,
      selectedRiwayatForAssign,
      selectedAssignUserId,
      assignLoading,
      assignSuccess,
      
      // Computed properties
      filteredRiwayat,
      totalPages,
      paginatedRiwayat,
      visiblePages,
      totalPemeriksaan,
      pemeriksaanAssigned,
      
      // Methods
      fetchRiwayatPemeriksaan,
      fetchAvailablePatients,
      fetchUsers,
      refreshData,
      applyFilters,
      clearFilters,
      changePage,
      showAssignModal,
      closeAssignModal,
      assignToPatient,
      unassignFromPatient,
      navigateToCreate,
      
      // Utility functions
      formatDate,
      formatDateTime,
      formatPatientAge,
      formatGender,
      formatTelinga,
      getTelingaClass,
      getStatusClass,
      getStatusText,
      getAssignedPatientName,
      truncateText
    };
  }
};
</script>

<style scoped>
.riwayat-container {
  padding: 20px;
  background-color: #f8f9fa;
  min-height: 100vh;
}

.header-section .card-header {
  background: linear-gradient(135deg, #b0b5cc 0%, #bbb4c2 100%);
  color: white;
  border-radius: 10px 10px 0 0;
}

.header-actions .btn {
  border-radius: 20px;
  font-weight: 500;
}

.filter-section .card,
.stats-section .card {
  border-radius: 15px;
  box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}

.riwayat-card {
  border-radius: 15px;
  border: none;
  transition: all 0.3s ease;
  overflow: hidden;
}

.riwayat-card:hover {
  transform: translateY(-5px);
  box-shadow: 0 8px 25px rgba(0,0,0,0.1);
}

.riwayat-card .card-header {
  background: white;
  color: #495057;
  border-bottom: 1px solid #e9ecef;
  border-radius: 15px 15px 0 0;
}

.riwayat-card .card-header.patient-header {
  background: #d3d4d5;
  color: #495057;
  border-bottom: 1px solid #e9ecef;
}

.patient-info h6 {
  margin-bottom: 8px;
  font-size: 1.1rem;
  color: #495057;
  font-weight: 600;
}

.patient-details small {
  font-size: 0.85rem;
  margin-bottom: 2px;
  color: #6c757d;
}

/* Patient card header styling */
.patient-card-header {
  background: #f8f9fa !important;
  color: #495057 !important;
  border-bottom: 1px solid #e9ecef !important;
}

.patient-card-header h6 {
  color: #495057 !important;
  margin-bottom: 5px;
}

.patient-card-header small {
  color: #6c757d !important;
}

.patient-card-header .badge {
  background-color: #28a745 !important;
  color: white !important;
}

.status-badges {
  display: flex;
  flex-wrap: wrap;
  gap: 5px;
}

.status-badges .badge {
  font-size: 0.75rem;
  padding: 4px 8px;
  border-radius: 12px;
}

.info-item {
  border-left: 3px solid #e9ecef;
  padding-left: 12px;
  margin-bottom: 15px;
}

.info-text {
  margin: 0;
  color: #6c757d;
  font-size: 0.9rem;
  line-height: 1.4;
}

.badge-telinga {
  font-size: 0.8rem;
  padding: 6px 12px;
  border-radius: 15px;
}

.action-buttons .btn {
  border-radius: 20px;
  font-size: 0.8rem;
  padding: 5px 10px;
}

.pagination .page-link {
  border-radius: 50%;
  margin: 0 2px;
  width: 40px;
  height: 40px;
  display: flex;
  align-items: center;
  justify-content: center;
  border: 1px solid #dee2e6;
  color: #6c757d;
}

.pagination .page-item.active .page-link {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  border-color: #667eea;
}

.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background-color: rgba(0, 0, 0, 0.6);
  display: flex;
  justify-content: center;
  align-items: center;
  z-index: 1050;
}

.modal-assign {
  max-width: 500px;
  width: 90%;
}

.modal-content {
  border-radius: 15px;
  border: none;
  box-shadow: 0 10px 30px rgba(0,0,0,0.3);
  background: white;
}

.modal-header {
  background: linear-gradient(135deg, #ffffff 0%, #ffffff 100%);
  color: black;
  border-radius: 15px 15px 0 0;
  border-bottom: none;
  padding: 20px;
}

.modal-body {
  padding: 25px;
  background: white;
}

.btn-close {
  background: none;
  border: none;
  color: white;
  font-size: 1.2rem;
  opacity: 0.8;
}

.btn-close:hover {
  opacity: 1;
}

/* Patient info section - White background */
.assign-info {
  background-color: white;
  padding: 20px;
  border-radius: 12px;
  border: 2px solid #e9ecef;
  margin-bottom: 20px;
  box-shadow: 0 2px 8px rgba(0,0,0,0.05);
}

.assign-info h6 {
  color: #495057;
  margin-bottom: 12px;
  font-weight: 600;
  font-size: 1.1rem;
}

.info-row {
  margin-bottom: 10px;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.info-row:last-child {
  margin-bottom: 0;
}

.info-row strong {
  color: #343a40;
  font-weight: 600;
}

.info-row span {
  color: #6c757d;
}

/* Patient selection section - Clear visibility */
.patient-selection {
  margin-top: 20px;
}

.patient-selection label {
  font-weight: 600;
  color: #495057;
  margin-bottom: 12px;
  display: block;
  font-size: 1rem;
}

.patient-selection select {
  width: 100%;
  padding: 12px 16px;
  border: 2px solid #dee2e6;
  border-radius: 10px;
  font-size: 1rem;
  background-color: #fff;
  color: #495057;
  appearance: none;
  background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%236b7280' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='m6 8 4 4 4-4'/%3e%3c/svg%3e");
  background-position: right 12px center;
  background-repeat: no-repeat;
  background-size: 16px;
  transition: all 0.2s ease;
}

.patient-selection select:focus {
  outline: none;
  border-color: #667eea;
  box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
}

.patient-selection select:hover {
  border-color: #adb5bd;
}

/* Diagnosis section */
.diagnosis-section {
  margin-top: 20px;
  padding: 15px;
  background-color: #f8f9fa;
  border-radius: 10px;
  border-left: 4px solid #667eea;
}

.diagnosis-section h6 {
  color: #495057;
  margin-bottom: 8px;
  font-weight: 600;
}

.diagnosis-text {
  color: #6c757d;
  font-size: 0.95rem;
  margin: 0;
}

/* Modal footer */
.modal-footer {
  padding: 20px 25px;
  border-top: 1px solid #e9ecef;
  background: white;
  border-radius: 0 0 15px 15px;
}

.btn-assign {
  background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
  border: none;
  border-radius: 25px;
  font-weight: 600;
  padding: 10px 25px;
  font-size: 1rem;
  transition: all 0.3s ease;
}

.btn-assign:hover {
  background: linear-gradient(135deg, #218838 0%, #1ca085 100%);
  transform: translateY(-1px);
  box-shadow: 0 4px 12px rgba(40, 167, 69, 0.3);
}

.btn-secondary {
  border-radius: 25px;
  font-weight: 600;
  padding: 10px 25px;
  font-size: 1rem;
  border: 2px solid #6c757d;
  background: white;
  color: #6c757d;
  transition: all 0.3s ease;
}

.btn-secondary:hover {
  background: #6c757d;
  color: white;
  transform: translateY(-1px);
}

.loading-overlay {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background-color: rgba(255, 255, 255, 0.8);
  display: flex;
  justify-content: center;
  align-items: center;
  z-index: 1060;
}

.loading-content {
  text-align: center;
  background: white;
  padding: 30px;
  border-radius: 15px;
  box-shadow: 0 5px 15px rgba(0,0,0,0.1);
}

/* Transitions */
.fade-enter-active, .fade-leave-active {
  transition: opacity 0.3s ease;
}

.fade-enter-from, .fade-leave-to {
  opacity: 0;
}

/* Responsive adjustments */
@media (max-width: 768px) {
  .riwayat-container {
    padding: 15px;
  }
  
  .header-actions {
    flex-direction: column;
    gap: 8px;
  }
  
  .patient-info h6 {
    font-size: 1rem;
  }
  
  .pagination .page-link {
    width: 35px;
    height: 35px;
  }
  
  .modal-assign {
    width: 95%;
    margin: 10px;
  }
  
  .modal-body {
    padding: 20px;
  }
  
  .modal-footer {
    padding: 15px 20px;
  }
  
  .info-row {
    flex-direction: column;
    align-items: flex-start;
    gap: 4px;
  }
}

@media (max-width: 576px) {
  .stats-section .row > .col-md-6 {
    margin-bottom: 15px;
  }
  
  .filter-section .row {
    flex-direction: column;
  }
  
  .filter-section .col-md-2 {
    margin-top: 10px;
  }
  
  .modal-footer .btn {
    width: 100%;
    margin-bottom: 10px;
  }
  
  .modal-footer .btn:last-child {
    margin-bottom: 0;
  }
}
</style>