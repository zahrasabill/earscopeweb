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
                class="btn btn-light btn-sm me-2 custom-header-btn"
              >
                <i class="fas fa-plus me-1"></i>Tambah Penanganan
              </button>
              <button 
                @click="refreshData" 
                class="btn btn-light btn-sm custom-header-btn" 
                :disabled="isLoading"
              >
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
      <label class="form-label fw-bold mb-2">Cari Pasien</label>
      <input
        type="text"
        v-model="patientSearchQuery"
        class="form-control"
        placeholder="Masukkan nama pasien..."
        @input="applyFilters"
        @keyup.enter="applyFilters"
      />
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
        <h5 class="text-muted">
          {{ patientSearchQuery ? 'Tidak ada hasil pencarian' : 'Belum ada riwayat pemeriksaan' }}
        </h5>
        <p class="text-muted">
          {{ patientSearchQuery 
            ? `Tidak ditemukan pemeriksaan untuk "${patientSearchQuery}"`
            : userRole === 'dokter' 
              ? 'Mulai dengan menambahkan penanganan baru untuk pasien'
              : 'Belum ada pemeriksaan yang diterima dari dokter'
          }}
        </p>
        <div class="empty-state-actions">
          <button 
            v-if="patientSearchQuery" 
            @click="clearPatientSearch" 
            class="btn btn-outline-primary me-2"
          >
            <i class="fas fa-times me-1"></i>Hapus Pencarian
          </button>
          <button 
            v-if="userRole === 'dokter'" 
            @click="navigateToCreate" 
            class="btn btn-primary"
          >
            <i class="fas fa-plus me-1"></i>Tambah Penanganan Baru
          </button>
        </div>
      </div>

      <!-- Riwayat Cards -->
      <div v-else class="row">
        <div v-for="riwayat in paginatedRiwayat" :key="riwayat.id" class="col-lg-6 col-xl-4 mb-4">
          <div class="card h-100 shadow-sm riwayat-card">
            <!-- Card Header -->
            <div class="card-header">
              <div class="patient-info">
                <h6 class="mb-1 fw-bold">
                  <span v-html="highlightSearchTerm(riwayat.patient_name, patientSearchQuery)"></span>
                </h6>
                <div class="patient-details">
                  <small class="text-muted d-block">
                    <i class="fas fa-id-card me-1"></i>
                    <span v-html="highlightSearchTerm(riwayat.kode_akses || 'No. RM tidak tersedia', patientSearchQuery)"></span>
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

    // Filters - Changed from dropdown to search
    const patientSearchQuery = ref('');

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

      // Filter by patient search query (for doctors)
      if (userRole.value === 'dokter' && patientSearchQuery.value.trim()) {
        const searchTerm = patientSearchQuery.value.toLowerCase().trim();
        filtered = filtered.filter(item => {
          const patientName = (item.patient_name || '').toLowerCase();
          const kodeAkses = (item.kode_akses || '').toLowerCase();
          
          return patientName.includes(searchTerm) || kodeAkses.includes(searchTerm);
        });
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

        if (userRole.value === "dokter") {
          // Dokter: enrich data pasien via API
          enrichedData = await Promise.all(
            penangananData.map(async (item) => {
              try {
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
        } else {
          // Pasien: langsung pakai data dirinya dari localStorage/session
          const currentUser = JSON.parse(localStorage.getItem("currentUser")) || {};

          enrichedData = penangananData.map((item) => ({
            ...item,
            patient_name: currentUser.name || "Nama tidak tersedia",
            patient_tanggal_lahir: currentUser.tanggal_lahir || null,
            patient_no_telp: currentUser.no_telp || null,
            patient_gender: currentUser.gender || null,
            kode_akses: currentUser.kode_akses || null,
          }));
        }

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
      patientSearchQuery.value = '';
      currentPage.value = 1;
    };

    const clearPatientSearch = () => {
      patientSearchQuery.value = '';
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
      if (!confirm('Apakah Anda yakin ingin unassign penanganan ini dari pasien?')) {
        return;
      }

      try {
        isUpdating.value = true;
        const token = localStorage.getItem("token") || sessionStorage.getItem("token");
        
        const response = await axios.post(
          api.getEndpoint(`penanganan/${riwayat.id}/unassign`),
          {},
          {
            headers: { Authorization: `Bearer ${token}` }
          }
        );

        if (response.data.success || response.status === 200) {
          // Update the local data
          const index = riwayatList.value.findIndex(item => item.id === riwayat.id);
          if (index !== -1) {
            riwayatList.value[index] = {
              ...riwayatList.value[index],
              status: 'active',
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
        alert('Gagal unassign penanganan. Silakan coba lagi.');
      } finally {
        isUpdating.value = false;
      }
    };

    const navigateToCreate = () => {
      router.push('/penanganan/create');
    };

    // Utility methods
    const formatDate = (dateString) => {
      if (!dateString) return 'Tanggal tidak tersedia';
      try {
        const date = new Date(dateString);
        return date.toLocaleDateString('id-ID', {
          day: '2-digit',
          month: 'long',
          year: 'numeric'
        });
      } catch (error) {
        return 'Format tanggal salah';
      }
    };

    const formatDateTime = (dateString) => {
      if (!dateString) return 'Waktu tidak tersedia';
      try {
        const date = new Date(dateString);
        return date.toLocaleString('id-ID', {
          day: '2-digit',
          month: 'long',
          year: 'numeric',
          hour: '2-digit',
          minute: '2-digit'
        });
      } catch (error) {
        return 'Format waktu salah';
      }
    };

    const formatPatientAge = (birthDate) => {
      if (!birthDate) return 'Umur tidak tersedia';
      try {
        const birth = new Date(birthDate);
        const today = new Date();
        let age = today.getFullYear() - birth.getFullYear();
        const monthDiff = today.getMonth() - birth.getMonth();
        
        if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birth.getDate())) {
          age--;
        }
        
        return `${age} tahun`;
      } catch (error) {
        return 'Format tanggal lahir salah';
      }
    };

    const formatGender = (gender) => {
      const genderMap = {
        'L': 'Laki-laki',
        'P': 'Perempuan',
        'laki-laki': 'Laki-laki',
        'perempuan': 'Perempuan',
        'male': 'Laki-laki',
        'female': 'Perempuan'
      };
      return genderMap[gender] || gender || 'Tidak diketahui';
    };

    const formatTelinga = (telinga) => {
      const telingaMap = {
        'kiri': 'Telinga Kiri',
        'kanan': 'Telinga Kanan',
        'keduanya': 'Kedua Telinga',
        'left': 'Telinga Kiri',
        'right': 'Telinga Kanan',
        'both': 'Kedua Telinga'
      };
      return telingaMap[telinga] || telinga || 'Tidak diketahui';
    };

    const getTelingaClass = (telinga) => {
      const classMap = {
        'kiri': 'bg-info',
        'kanan': 'bg-warning',
        'keduanya': 'bg-danger',
        'left': 'bg-info',
        'right': 'bg-warning',
        'both': 'bg-danger'
      };
      return classMap[telinga] || 'bg-secondary';
    };

    const getStatusClass = (status) => {
      const statusMap = {
        'active': 'bg-primary',
        'assigned': 'bg-success',
        'completed': 'bg-secondary',
        'cancelled': 'bg-danger'
      };
      return statusMap[status] || 'bg-secondary';
    };

    const getStatusText = (status) => {
      const statusMap = {
        'active': 'Aktif',
        'assigned': 'Assigned',
        'completed': 'Selesai',
        'cancelled': 'Dibatalkan'
      };
      return statusMap[status] || status || 'Tidak diketahui';
    };

    const getAssignedPatientName = (assignedToId) => {
      const patient = availablePatients.value.find(p => p.id === assignedToId);
      return patient ? patient.name : 'Pasien tidak ditemukan';
    };

    const truncateText = (text, maxLength = 100) => {
      if (!text) return 'Tidak ada data';
      if (text.length <= maxLength) return text;
      return text.substring(0, maxLength) + '...';
    };

    const highlightSearchTerm = (text, searchTerm) => {
      if (!searchTerm || !text) return text;
      
      const regex = new RegExp(`(${searchTerm})`, 'gi');
      return text.replace(regex, '<mark>$1</mark>');
    };

    // Lifecycle hooks
    onMounted(async () => {
      initializeUserInfo();
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
      patientSearchQuery,
      currentPage,
      itemsPerPage,
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
      refreshData,
      applyFilters,
      clearFilters,
      clearPatientSearch,
      changePage,
      showAssignModal,
      closeAssignModal,
      assignToPatient,
      unassignFromPatient,
      navigateToCreate,
      formatDate,
      formatDateTime,
      formatPatientAge,
      formatGender,
      formatTelinga,
      getTelingaClass,
      getStatusClass,
      getStatusText,
      getAssignedPatientName,
      truncateText,
      highlightSearchTerm
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

.header-section .card-header {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  border: none;
  color: white;
}

.custom-header-btn {
  background: #fff !important;
  color: #222 !important;
  border: 1px solid #e0e0e0 !important;
  font-weight: 500;
  box-shadow: none;
  transition: background 0.2s, color 0.2s;
}
.custom-header-btn:hover, .custom-header-btn:focus {
  background: #f5f5f5 !important;
  color: #111 !important;
  border-color: #bbb !important;
}

.header-section h4 {
  font-weight: 600;
  margin: 0;
}

.header-actions .btn {
  border: 2px solid rgba(255, 255, 255, 0.3);
  color: white;
  font-weight: 500;
  transition: all 0.3s ease;
}

.header-actions .btn:hover {
  background-color: rgba(255, 255, 255, 0.2);
  border-color: rgba(255, 255, 255, 0.5);
  transform: translateY(-1px);
}

.filter-section .card {
  border: 1px solid #e0e6ed;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
}

.form-label {
  color: #495057;
  font-size: 0.9rem;
}

.input-group-text {
  background-color: #f8f9fa;
  border-color: #ced4da;
  color: #6c757d;
}

.search-results-info {
  background-color: #e7f3ff;
  padding: 10px;
  border-radius: 6px;
  border-left: 4px solid #007bff;
}

.stats-section .card {
  transition: transform 0.2s ease;
  border: none;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.stats-section .card:hover {
  transform: translateY(-2px);
}

.riwayat-card {
  transition: transform 0.2s ease, box-shadow 0.2s ease;
  border: 1px solid #e0e6ed;
  border-radius: 12px;
  overflow: hidden;
}

.riwayat-card:hover {
  transform: translateY(-3px);
  box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
}

.riwayat-card .card-header {
  background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
  border-bottom: 1px solid #dee2e6;
  padding: 15px;
}

.patient-info h6 {
  color: #495057;
  font-size: 1.1rem;
}

.patient-details {
  margin-top: 8px;
}

.patient-details small {
  margin-bottom: 2px;
  color: #6c757d;
}

.status-badges {
  display: flex;
  flex-wrap: wrap;
  gap: 5px;
}

.badge {
  font-size: 0.75rem;
  padding: 4px 8px;
  border-radius: 6px;
}

.badge-telinga {
  font-size: 0.8rem;
  padding: 6px 12px;
  font-weight: 500;
}

.info-item {
  border-bottom: 1px solid #f0f0f0;
  padding-bottom: 12px;
}

.info-item:last-child {
  border-bottom: none;
  padding-bottom: 0;
}

.info-text {
  margin: 0;
  color: #495057;
  line-height: 1.5;
  font-size: 0.9rem;
}

.card-footer {
  border-top: 1px solid #e9ecef;
  background-color: #f8f9fa;
}

.action-buttons .btn-group .btn {
  border-radius: 4px;
  font-size: 0.8rem;
  padding: 4px 8px;
  margin: 0 2px;
}

.pagination {
  margin: 0;
}

.page-link {
  color: #667eea;
  border-color: #dee2e6;
  transition: all 0.2s ease;
}

.page-link:hover {
  color: #495057;
  background-color: #e9ecef;
  border-color: #dee2e6;
}

.page-item.active .page-link {
  background-color: #667eea;
  border-color: #667eea;
}

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
  z-index: 1050;
}

.modal-dialog {
  max-width: 500px;
  width: 90%;
  margin: 20px;
}

.modal-content {
  border-radius: 12px;
  border: none;
  box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
}

.modal-header {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
  border-bottom: none;
  border-radius: 12px 12px 0 0;
  padding: 20px;
}

.modal-title {
  font-weight: 600;
  margin: 0;
}

.btn-close {
  background: none;
  border: none;
  color: white;
  font-size: 1.2rem;
  cursor: pointer;
  padding: 0;
  width: 30px;
  height: 30px;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 50%;
  transition: background-color 0.2s ease;
}

.btn-close:hover {
  background-color: rgba(255, 255, 255, 0.2);
}

.modal-body {
  padding: 25px;
}

.assign-info {
  background-color: #f8f9fa;
  padding: 15px;
  border-radius: 8px;
  border-left: 4px solid #007bff;
}

.info-row {
  margin-bottom: 8px;
  color: #495057;
}

.info-row:last-child {
  margin-bottom: 0;
}

.btn-assign {
  background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
  border: none;
  color: white;
  font-weight: 500;
  transition: all 0.3s ease;
}

.btn-assign:hover:not(:disabled) {
  transform: translateY(-1px);
  box-shadow: 0 4px 12px rgba(40, 167, 69, 0.3);
}

.btn-assign:disabled {
  opacity: 0.6;
  cursor: not-allowed;
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
  z-index: 2000;
}

.loading-content {
  text-align: center;
  color: #495057;
}

.empty-state-actions {
  margin-top: 20px;
}

.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.3s ease;
}

.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}

mark {
  background-color: #fff3cd;
  color: #856404;
  padding: 1px 3px;
  border-radius: 3px;
}

/* Responsive Design */
@media (max-width: 768px) {
  .riwayat-container {
    padding: 10px;
  }
  
  .filter-section .row {
    flex-direction: column;
  }
  
  .filter-section .col-md-6,
  .filter-section .col-md-2,
  .filter-section .col-md-4 {
    margin-bottom: 15px;
  }
  
  .stats-section .row {
    margin: 0 -5px;
  }
  
  .stats-section .col-md-6 {
    padding: 0 5px;
    margin-bottom: 10px;
  }
  
  .header-actions {
    flex-direction: column;
    gap: 10px;
  }
  
  .header-actions .btn {
    width: 100%;
  }
  
  .modal-dialog {
    margin: 10px;
    width: calc(100% - 20px);
  }
  
  .action-buttons .btn-group {
    flex-direction: column;
  }
  
  .action-buttons .btn-group .btn {
    margin: 2px 0;
  }
}
</style>