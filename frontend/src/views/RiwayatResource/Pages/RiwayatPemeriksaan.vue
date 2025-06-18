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
        <div class="modal-dialog">
          <div class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title">
                <i class="fas fa-user-plus me-2"></i>Assign Penanganan ke Pasien
              </h5>
              <button type="button" class="btn-close" @click="closeAssignModal"></button>
            </div>
            <div class="modal-body">
              <div v-if="assignSuccess" class="text-center py-3">
                <i class="fas fa-check-circle text-success" style="font-size: 48px;"></i>
                <h5 class="mt-3 text-success">Berhasil!</h5>
                <p>Penanganan telah berhasil di-assign ke pasien</p>
              </div>
              <div v-else-if="selectedRiwayatForAssign">
                <div class="alert alert-info">
                  <strong>Penanganan untuk:</strong> {{ selectedRiwayatForAssign.patient_name }}
                  <br>
                  <strong>Tanggal:</strong> {{ formatDate(selectedRiwayatForAssign.tanggal_penanganan) }}
                </div>
                
                <div class="mb-3">
                  <label class="form-label fw-bold">Pilih Pasien untuk di-assign:</label>
                  <select v-model="selectedAssignUserId" class="form-select">
                    <option value="" disabled selected>-- Pilih Pasien --</option>
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
                  class="btn btn-success"
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
        
        let endpoint = 'penanganan';
        
        // Use the correct route based on user role
        if (userRole.value === 'pasien') {
          endpoint = 'penanganan/pasien'; // Updated route for patient-specific data
        }

        const response = await axios.get(api.getEndpoint(endpoint), {
          headers: { Authorization: `Bearer ${token}` },
        });

        const data = response.data.data || response.data;
        
        // Fetch detailed patient information for each penanganan
        const enrichedData = await Promise.all(data.map(async (item) => {
          try {
            // Fetch patient details
            const patientResponse = await axios.get(api.getEndpoint(`users/${item.user_id}`), {
              headers: { Authorization: `Bearer ${token}` },
            });
            
            const patientData = patientResponse.data.data || patientResponse.data;
            
            return {
              ...item,
              patient_name: patientData.name || 'Nama tidak tersedia',
              patient_tanggal_lahir: patientData.tanggal_lahir || null,
              patient_no_telp: patientData.no_telp || null,
              patient_gender: patientData.gender || null,
              kode_akses: patientData.kode_akses || null
            };
          } catch (error) {
            console.error(`Failed to fetch patient details for user ${item.user_id}:`, error);
            // Return original data if patient fetch fails
            return {
              ...item,
              patient_name: 'Nama tidak tersedia',
              patient_tanggal_lahir: null,
              patient_no_telp: null,
              patient_gender: null,
              kode_akses: null
            };
          }
        }));

        riwayatList.value = enrichedData;
      } catch (error) {
        console.error('Error fetching riwayat pemeriksaan:', error);
        // Show error message to user
        alert('Gagal memuat riwayat pemeriksaan. Silakan coba lagi.');
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
        alert('Silakan pilih pasien terlebih dahulu');
        return;
      }

      try {
        assignLoading.value = true;
        const token = localStorage.getItem("token") || sessionStorage.getItem("token");
        
        // Use the correct assign endpoint: POST penanganan/{id}/assign/{userId}
        const endpoint = api.getEndpoint(`penanganan/${selectedRiwayatForAssign.value.id}/assign/${selectedAssignUserId.value}`);
        console.log('Assign endpoint:', endpoint);

        const response = await axios.post(
          endpoint,
          {}, // Empty body as specified in the route
          {
            headers: { 
              Authorization: `Bearer ${token}`,
              'Content-Type': 'application/json'
            }
          }
        );

        console.log('Assign response:', response);

        if (response.status === 200 || response.status === 201) {
          // Show success message
          assignSuccess.value = true;
          
          // Refresh data to get latest state
          await fetchRiwayatPemeriksaan();

          // Close modal automatically after 2 seconds
          setTimeout(() => {
            if (showAssignModalFlag.value) {
              closeAssignModal();
            }
          }, 2000);
        }
      } catch (error) {
        console.error('Error assigning to patient:', error);
        
        // Show more detailed error message
        let errorMessage = 'Gagal assign penanganan ke pasien.';
        
        if (error.response) {
          if (error.response.status === 404) {
            errorMessage = 'Penanganan atau pasien tidak ditemukan.';
          } else if (error.response.status === 403) {
            errorMessage = 'Anda tidak memiliki izin untuk melakukan tindakan ini.';
          } else if (error.response.data && error.response.data.message) {
            errorMessage = error.response.data.message;
          }
        }
        
        alert(errorMessage + ' Silakan coba lagi.');
      } finally {
        assignLoading.value = false;
      }
    };

    const unassignFromPatient = async (riwayat) => {
      if (!confirm('Apakah Anda yakin ingin unassign penanganan ini dari pasien?')) {
        return;
      }

      try {
        isUpdating.value = true;
        const token = localStorage.getItem("token") || sessionStorage.getItem("token");
        
        // Use the correct update status endpoint: PATCH penanganan/{id}
        const response = await axios.patch(
          api.getEndpoint(`penanganan/${riwayat.id}`),
          {
            assigned_to: null,
            status: 'completed'
          },
          {
            headers: { 
              Authorization: `Bearer ${token}`,
              'Content-Type': 'application/json'
            }
          }
        );

        if (response.status === 200) {
          // Refresh data to get latest state
          await fetchRiwayatPemeriksaan();
          alert('Penanganan berhasil di-unassign dari pasien');
        }
      } catch (error) {
        console.error('Error unassigning from patient:', error);
        
        let errorMessage = 'Gagal unassign penanganan dari pasien.';
        if (error.response && error.response.data && error.response.data.message) {
          errorMessage = error.response.data.message;
        }
        
        alert(errorMessage + ' Silakan coba lagi.');
      } finally {
        isUpdating.value = false;
      }
    };

    const navigateToCreate = () => {
      router.push({ name: 'create-penanganan' });
    };

    // Utility functions
    const formatDate = (dateString) => {
      if (!dateString) return '-';
      const date = new Date(dateString);
      return date.toLocaleDateString('id-ID', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric'
      });
    };

    const formatDateTime = (dateString) => {
      if (!dateString) return '-';
      const date = new Date(dateString);
      return date.toLocaleString('id-ID', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
      });
    };

    const formatPatientAge = (dateString) => {
      if (!dateString) return 'Usia tidak tersedia';
      const age = calculateAge(dateString);
      return `${age} tahun`;
    };

    const calculateAge = (dateString) => {
      if (!dateString) return 0;
      const today = new Date();
      const birthDate = new Date(dateString);
      let age = today.getFullYear() - birthDate.getFullYear();
      const monthDiff = today.getMonth() - birthDate.getMonth();
      
      if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birthDate.getDate())) {
        age--;
      }
      
      return age;
    };

    const formatGender = (gender) => {
      if (!gender) return '-';

      const normalized = gender.toString().toLowerCase();

      if (['perempuan', 'p', 'female', 'f'].includes(normalized)) {
        return 'Perempuan';
      }

      if (['laki-laki', 'l', 'male', 'm'].includes(normalized)) {
        return 'Laki-laki';
      }

      return gender; // Return original if no match
    };

    const formatTelinga = (telinga) => {
      if (!telinga) return '-';
      
      const normalized = telinga.toString().toLowerCase();
      
      if (normalized === 'kiri') return 'Kiri';
      if (normalized === 'kanan') return 'Kanan';
      if (normalized === 'keduanya' || normalized === 'both') return 'Keduanya';
      
      return telinga; // Return original if no match
    };

    const getTelingaClass = (telinga) => {
      if (!telinga) return 'bg-secondary';
      
      const normalized = telinga.toString().toLowerCase();
      
      if (normalized === 'kiri') return 'bg-info';
      if (normalized === 'kanan') return 'bg-warning';
      if (normalized === 'keduanya' || normalized === 'both') return 'bg-danger';
      
      return 'bg-secondary';
    };

    const getStatusClass = (status) => {
      if (!status) return 'bg-secondary';
      
      const normalized = status.toString().toLowerCase();
      
      switch (normalized) {
        case 'completed':
          return 'bg-success';
        case 'assigned':
          return 'bg-warning';
        case 'pending':
          return 'bg-info';
        case 'cancelled':
          return 'bg-danger';
        default:
          return 'bg-secondary';
      }
    };

    const getStatusText = (status) => {
      if (!status) return 'Tidak diketahui';
      
      const normalized = status.toString().toLowerCase();
      
      switch (normalized) {
        case 'completed':
          return 'Selesai';
        case 'assigned':
          return 'Ditugaskan';
        case 'pending':
          return 'Menunggu';
        case 'cancelled':
          return 'Dibatalkan';
        default:
          return status;
      }
    };

    const getAssignedPatientName = (userId) => {
      if (!userId) return '-';
      
      const patient = availablePatients.value.find(p => p.id.toString() === userId.toString());
      return patient ? patient.name : 'Pasien tidak ditemukan';
    };

    const truncateText = (text, maxLength) => {
      if (!text) return '-';
      if (text.length <= maxLength) return text;
      return text.substring(0, maxLength) + '...';
    };

    // Initialize component
    onMounted(async () => {
      // Get user info from token
      const userInfo = getUserInfoFromToken();
      userRole.value = userInfo.role;
      currentUserId.value = userInfo.userId;

      // Load initial data
      await refreshData();
    });

    return {
      // Data
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
      
      // Computed
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
      calculateAge,
      formatGender,
      formatTelinga,
      getTelingaClass,
      getStatusClass,
      getStatusText,
      getAssignedPatientName,
      truncateText,
      getUserInfoFromToken
    };
  }
};
</script>

<style scoped>
.riwayat-container {
  min-height: 100vh;
  background-color: #f8f9fa;
  padding: 20px;
}

.header-section .bg-gradient {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
}

.header-section .text-black {
  color: white !important;
}

.header-actions .btn-light {
  background-color: rgba(255, 255, 255, 0.9);
  border: none;
  color: #333;
}

.header-actions .btn-light:hover {
  background-color: white;
  transform: translateY(-1px);
  box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
}

.filter-section .card {
  border: none;
  box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
}

.stats-section .card {
  border: none;
  box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
  transition: transform 0.2s;
}

.stats-section .card:hover {
  transform: translateY(-2px);
}

.riwayat-card {
  border: none;
  box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
  transition: all 0.3s ease;
  overflow: hidden;
}

.riwayat-card:hover {
  transform: translateY(-5px);
  box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
}

.riwayat-card .card-header {
  background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
  border-bottom: 2px solid #dee2e6;
}

.patient-info h6 {
  color: #2c3e50;
  font-size: 1.1rem;
}

.patient-details {
  margin-top: 8px;
}

.patient-details small {
  font-size: 0.85rem;
  margin-bottom: 2px;
}

.status-badges {
  display: flex;
  flex-wrap: wrap;
  gap: 5px;
}

.badge-telinga {
  font-size: 0.8rem;
  padding: 6px 12px;
  border-radius: 20px;
}

.info-item {
  padding: 8px 0;
  border-bottom: 1px solid #f1f3f4;
}

.info-item:last-child {
  border-bottom: none;
}

.info-item strong {
  color: #2c3e50;
  font-size: 0.9rem;
}

.info-text {
  margin: 0;
  color: #6c757d;
  font-size: 0.9rem;
  line-height: 1.4;
}

.action-buttons .btn {
  transition: all 0.2s;
}

.action-buttons .btn:hover {
  transform: scale(1.05);
}

.pagination .page-link {
  color: #667eea;
  border: 1px solid #dee2e6;
  padding: 8px 12px;
}

.pagination .page-item.active .page-link {
  background-color: #667eea;
  border-color: #667eea;
}

.pagination .page-link:hover {
  color: #5a6fd8;
  background-color: #f8f9fa;
}

/* Modal Styles */
.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background-color: rgba(0, 0, 0, 0.5);
  display: flex;
  justify-content: center;
  align-items: center;
  z-index: 1000;
}

.modal-dialog {
  background: white;
  border-radius: 8px;
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
  width: 90%;
  max-width: 500px;
  max-height: 90vh;
  overflow-y: auto;
}

.modal-header {
  padding: 1.5rem;
  border-bottom: 1px solid #dee2e6;
  display: flex;
  justify-content: between;
  align-items: center;
}

.modal-title {
  margin: 0;
  color: #2c3e50;
}

.btn-close {
  background: none;
  border: none;
  font-size: 1.5rem;
  cursor: pointer;
  color: #6c757d;
}

.btn-close:hover {
  color: #000;
}

.modal-body {
  padding: 1.5rem;
}

.modal-footer {
  padding: 1rem 1.5rem;
  border-top: 1px solid #dee2e6;
  display: flex;
  justify-content: flex-end;
  gap: 10px;
}

/* Loading Overlay */
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
  z-index: 1001;
}

.loading-content {
  text-align: center;
  padding: 20px;
  background: white;
  border-radius: 8px;
  box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
}

/* Transitions */
.fade-enter-active, .fade-leave-active {
  transition: opacity 0.3s;
}

.fade-enter-from, .fade-leave-to {
  opacity: 0;
}

/* Responsive Design */
@media (max-width: 768px) {
  .riwayat-container {
    padding: 10px;
  }
  
  .header-actions {
    flex-direction: column;
    gap: 5px;
  }
  
  .header-actions .btn {
    width: 100%;
  }
  
  .patient-details {
    font-size: 0.8rem;
  }
  
  .info-item {
    padding: 6px 0;
  }
  
  .modal-dialog {
    width: 95%;
    margin: 10px;
  }
  
  .modal-header,
  .modal-body,
  .modal-footer {
    padding: 1rem;
  }
}
</style>