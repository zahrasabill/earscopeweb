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
        <div class="col-md-4">
          <div class="card border-primary">
            <div class="card-body text-center">
              <i class="fas fa-file-medical text-primary mb-2" style="font-size: 2rem;"></i>
              <h5 class="card-title">Total Pemeriksaan</h5>
              <h3 class="text-primary">{{ totalPemeriksaan }}</h3>
            </div>
          </div>
        </div>
        <div class="col-md-4">
          <div class="card border-warning">
            <div class="card-body text-center">
              <i class="fas fa-paper-plane text-warning mb-2" style="font-size: 2rem;"></i>
              <h5 class="card-title">Assigned</h5>
              <h3 class="text-warning">{{ pemeriksaanAssigned }}</h3>
            </div>
          </div>
        </div>
        <div class="col-md-4">
          <div class="card border-success">
            <div class="card-body text-center">
              <i class="fas fa-share text-success mb-2" style="font-size: 2rem;"></i>
              <h5 class="card-title">Terkirim</h5>
              <h3 class="text-success">{{ pemeriksaanTerkirim }}</h3>
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
                <span v-if="riwayat.is_sent_to_patient" class="badge bg-success me-1">
                  <i class="fas fa-check-circle me-1"></i>Terkirim
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

              <!-- Info Pengiriman -->
              <div v-if="riwayat.is_sent_to_patient" class="info-item mb-3">
                <div class="d-flex align-items-center mb-2">
                  <i class="fas fa-paper-plane text-success me-2"></i>
                  <strong>Dikirim:</strong>
                </div>
                <small class="text-success">
                  <i class="fas fa-clock me-1"></i>{{ formatDateTime(riwayat.sent_at) }}
                </small>
                <div v-if="riwayat.catatan_pengiriman" class="mt-1">
                  <small class="text-muted">{{ riwayat.catatan_pengiriman }}</small>
                </div>
                <div v-if="riwayat.pdf_url" class="mt-2">
                  <a :href="riwayat.pdf_url" target="_blank" class="btn btn-sm btn-outline-primary">
                    <i class="fas fa-file-pdf me-1"></i>Lihat PDF
                  </a>
                </div>
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
                    <!-- Button Kirim ke Pasien dihapus -->
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

    <!-- Kirim ke Pasien Modal -->
    <transition name="fade">
      <div v-if="showKirimModalFlag" class="modal-overlay" @click.self="closeKirimModal">
        <div class="modal-dialog">
          <div class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title">
                <i class="fas fa-share me-2"></i>Kirim Hasil Penanganan ke Pasien
              </h5>
              <button type="button" class="btn-close" @click="closeKirimModal"></button>
            </div>
            <div class="modal-body">
              <div v-if="kirimSuccess" class="text-center py-3">
                <i class="fas fa-check-circle text-success" style="font-size: 48px;"></i>
                <h5 class="mt-3 text-success">Berhasil Terkirim!</h5>
                <p>Hasil penanganan telah berhasil dikirim ke pasien</p>
                <div v-if="kirimResult && kirimResult.pdf_url" class="mt-3">
                  <a :href="kirimResult.pdf_url" target="_blank" class="btn btn-outline-primary">
                    <i class="fas fa-file-pdf me-1"></i>Lihat PDF yang Dikirim
                  </a>
                </div>
              </div>
              <div v-else-if="selectedRiwayatForKirim">
                <div class="alert alert-info">
                  <strong>Penanganan untuk:</strong> {{ selectedRiwayatForKirim.patient_name }}
                  <br>
                  <strong>Tanggal:</strong> {{ formatDate(selectedRiwayatForKirim.tanggal_penanganan) }}
                  <br>
                  <strong>Diagnosis:</strong> {{ truncateText(selectedRiwayatForKirim.diagnosis_manual, 50) }}
                </div>
                
                <div class="mb-3">
                  <label class="form-label fw-bold">Catatan Pengiriman (Opsional):</label>
                  <textarea 
                    v-model="catatanPengiriman" 
                    class="form-control" 
                    rows="3"
                    placeholder="Tambahkan catatan khusus untuk pasien..."
                  ></textarea>
                  <small class="form-text text-muted">
                    Catatan ini akan disertakan dalam hasil yang dikirim ke pasien
                  </small>
                </div>

                <div class="alert alert-warning">
                  <i class="fas fa-info-circle me-2"></i>
                  <strong>Informasi:</strong> Sistem akan membuat PDF otomatis dan mengirimkannya ke pasien.
                </div>
              </div>
            </div>
            <div class="modal-footer">
              <button v-if="kirimSuccess" class="btn btn-primary w-100" @click="closeKirimModal">
                Selesai
              </button>
              <template v-else>
                <button class="btn btn-secondary" @click="closeKirimModal">Batal</button>
                <button 
                  class="btn btn-primary"
                  :disabled="kirimLoading"
                  @click="kirimKePasien"
                >
                  <span v-if="kirimLoading" class="spinner-border spinner-border-sm me-2"></span>
                  <i v-else class="fas fa-share me-1"></i>
                  {{ kirimLoading ? 'Mengirim...' : 'Kirim ke Pasien' }}
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

    // Kirim ke Pasien Modal states
    const showKirimModalFlag = ref(false);
    const selectedRiwayatForKirim = ref(null);
    const catatanPengiriman = ref('');
    const kirimLoading = ref(false);
    const kirimSuccess = ref(false);
    const kirimResult = ref(null);

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
    const pemeriksaanTerkirim = computed(() => {
      return riwayatList.value.filter(item => item.is_sent_to_patient === true).length;
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

    // New methods for "Kirim ke Pasien" functionality
    const showKirimModal = (riwayat) => {
      selectedRiwayatForKirim.value = riwayat;
      catatanPengiriman.value = '';
      kirimSuccess.value = false;
      kirimResult.value = null;
      showKirimModalFlag.value = true;
    };

    const closeKirimModal = () => {
      showKirimModalFlag.value = false;
      setTimeout(() => {
        selectedRiwayatForKirim.value = null;
        catatanPengiriman.value = '';
        kirimSuccess.value = false;
        kirimResult.value = null;
      }, 300);
    };

    const kirimKePasien = async () => {
      if (!selectedRiwayatForKirim.value) {
        alert('Data penanganan tidak ditemukan');
        return;
      }

      try {
        kirimLoading.value = true;
        const token = localStorage.getItem("token") || sessionStorage.getItem("token");
        
        // Use the PUT endpoint for kirim ke pasien
        const endpoint = api.getEndpoint(`penanganan/${selectedRiwayatForKirim.value.id}/kirim`);
        console.log('Kirim endpoint:', endpoint);

        const requestData = {
          catatan_pengiriman: catatanPengiriman.value || null
        };

        const response = await axios.put(
          endpoint,
          requestData,
          {
            headers: { 
              Authorization: `Bearer ${token}`,
              'Content-Type': 'application/json'
            }
          }
        );

        console.log('Kirim response:', response);

        if (response.status === 200) {
          // Show success message
          kirimSuccess.value = true;
          kirimResult.value = response.data.data;
          
          // Refresh data to get latest state
          await fetchRiwayatPemeriksaan();

          // Close modal automatically after 3 seconds
          setTimeout(() => {
            if (showKirimModalFlag.value) {
              closeKirimModal();
            }
          }, 3000);
        }
      } catch (error) {
        console.error('Error sending to patient:', error);
        
        // Show more detailed error message
        let errorMessage = 'Gagal mengirim hasil penanganan ke pasien.';
        
        if (error.response) {
          if (error.response.status === 404) {
            errorMessage = 'Penanganan tidak ditemukan.';
          } else if (error.response.status === 403) {
            errorMessage = 'Anda tidak memiliki izin untuk melakukan tindakan ini.';
          } else if (error.response.status === 422) {
            errorMessage = 'Data tidak valid. Silakan periksa kembali.';
          } else if (error.response.data && error.response.data.message) {
            errorMessage = error.response.data.message;
          }
        }
        
        alert(errorMessage + ' Silakan coba lagi.');
      } finally {
        kirimLoading.value = false;
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
      
      // Kirim ke Pasien Modal states
      showKirimModalFlag,
      selectedRiwayatForKirim,
      catatanPengiriman,
      kirimLoading,
      kirimSuccess,
      kirimResult,
      
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
      
      // New methods for Kirim ke Pasien
      showKirimModal,
      closeKirimModal,
      kirimKePasien,
      
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
<style>
/* Main Container */
.riwayat-container {
  padding: 20px 15px;
  background-color: #f8f9fa;
  min-height: 100vh;
}

/* Header Section */
.header-section .card {
  border: none;
  border-radius: 12px;
  box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
}

.header-section .card-header {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  border-radius: 12px 12px 0 0 !important;
  border: none;
  padding: 20px 25px;
}

.header-section .card-header h4 {
  color: #222 !important;
  font-weight: 600;
  margin: 0;
}

.header-actions .btn {
  border-radius: 8px;
  font-weight: 500;
  padding: 8px 16px;
  transition: all 0.3s ease;
}

.header-actions .btn:hover {
  transform: translateY(-1px);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

/* Filter Section */
.filter-section .card {
  border: none;
  border-radius: 12px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
}

.filter-section .form-label {
  color: #495057;
  font-size: 14px;
  margin-bottom: 8px;
}

.filter-section .form-select {
  border-radius: 8px;
  border: 1px solid #e0e6ed;
  padding: 12px 16px;
  font-size: 14px;
  transition: all 0.3s ease;
}

.filter-section .form-select:focus {
  border-color: #667eea;
  box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
}

/* Statistics Cards */
.stats-section .card {
  border-radius: 12px;
  transition: all 0.3s ease;
  overflow: hidden;
}

.stats-section .card:hover {
  transform: translateY(-2px);
  box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
}

.stats-section .card-body {
  padding: 25px 20px;
}

.stats-section .card-title {
  font-weight: 500;
  font-size: 16px;
  margin-bottom: 10px;
  color: #6c757d;
}

.stats-section h3 {
  font-weight: 700;
  font-size: 2.5rem;
  margin: 0;
}

/* Riwayat Cards */
.riwayat-card {
  border: none;
  border-radius: 12px;
  box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
  transition: all 0.3s ease;
  overflow: hidden;
}

.riwayat-card:hover {
  transform: translateY(-3px);
  box-shadow: 0 8px 30px rgba(0, 0, 0, 0.12);
}

.riwayat-card .card-header {
  background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
  border-bottom: 1px solid #e0e6ed;
  padding: 20px;
}

.patient-info h6 {
  color: #2c3e50;
  font-weight: 600;
  font-size: 18px;
  margin-bottom: 8px;
}

.patient-details {
  margin-top: 8px;
}

.patient-details small {
  font-size: 13px;
  color: #6c757d;
  line-height: 1.6;
}

.patient-details i {
  width: 16px;
  text-align: center;
  color: #95a5a6;
}

/* Status Badges */
.status-badges {
  display: flex;
  flex-wrap: wrap;
  gap: 6px;
  margin-top: 12px;
}

.badge {
  font-size: 11px;
  font-weight: 500;
  padding: 6px 12px;
  border-radius: 20px;
  letter-spacing: 0.5px;
}

.badge-telinga {
  font-size: 12px;
  font-weight: 500;
  padding: 8px 16px;
  border-radius: 20px;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

/* Card Body */
.riwayat-card .card-body {
  padding: 20px;
}

.info-item {
  margin-bottom: 16px;
}

.info-item strong {
  color: #2c3e50;
  font-size: 14px;
  font-weight: 600;
}

.info-item i {
  width: 18px;
  text-align: center;
}

.info-text {
  color: #495057;
  font-size: 14px;
  line-height: 1.6;
  margin: 8px 0 0 0;
  word-wrap: break-word;
}

/* Card Footer */
.riwayat-card .card-footer {
  background-color: #f8f9fa;
  border-top: 1px solid #e0e6ed;
  padding: 15px 20px;
}

.action-buttons .btn {
  border-radius: 6px;
  padding: 6px 12px;
  font-size: 12px;
  font-weight: 500;
  transition: all 0.3s ease;
}

.action-buttons .btn:hover {
  transform: scale(1.05);
}

.action-buttons .btn i {
  font-size: 11px;
}

/* Pagination */
.pagination {
  margin: 0;
}

.pagination .page-link {
  border: none;
  color: #667eea;
  font-weight: 500;
  padding: 12px 16px;
  margin: 0 4px;
  border-radius: 8px;
  transition: all 0.3s ease;
}

.pagination .page-link:hover {
  background-color: #667eea;
  color: white;
  transform: translateY(-1px);
}

.pagination .page-item.active .page-link {
  background-color: #667eea;
  border-color: #667eea;
  color: white;
  box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
}

.pagination .page-item.disabled .page-link {
  color: #adb5bd;
  background-color: transparent;
}

/* Modal Styles */
.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background-color: rgba(0, 0, 0, 0.5);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 9999;
  padding: 20px;
}

.modal-dialog {
  background: white;
  border-radius: 12px;
  box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
  max-width: 500px;
  width: 100%;
  max-height: 90vh;
  overflow-y: auto;
  animation: modalSlideIn 0.3s ease-out;
}

/* Tambahkan style untuk modal-assign agar lebih besar dan nyata */
.modal-dialog.modal-assign {
  max-width: 600px;
  min-width: 350px;
  width: 100%;
  box-shadow: 0 12px 40px rgba(0,0,0,0.18);
  border-radius: 16px;
  padding: 0 10px;
}

@media (max-width: 768px) {
  .modal-dialog.modal-assign {
    max-width: 98vw;
    min-width: unset;
    padding: 0;
  }
}

@keyframes modalSlideIn {
  from {
    opacity: 0;
    transform: translateY(-20px) scale(0.95);
  }
  to {
    opacity: 1;
    transform: translateY(0) scale(1);
  }
}

.modal-content {
  border: none;
  border-radius: 12px;
}

.modal-header {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
  border-radius: 12px 12px 0 0;
  padding: 20px 25px;
  border-bottom: none;
}

.modal-title {
  font-weight: 600;
  font-size: 18px;
  margin: 0;
}

.btn-close {
  background: none;
  border: none;
  color: white;
  font-size: 24px;
  opacity: 0.8;
  cursor: pointer;
  padding: 0;
  width: 30px;
  height: 30px;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 50%;
  transition: all 0.3s ease;
}

.btn-close:hover {
  opacity: 1;
  background-color: rgba(255, 255, 255, 0.1);
}

.btn-close::before {
  content: "×";
  font-size: 24px;
  line-height: 1;
}

.modal-body {
  padding: 25px;
}

.modal-footer {
  padding: 20px 25px;
  border-top: 1px solid #e0e6ed;
  background-color: #f8f9fa;
  border-radius: 0 0 12px 12px;
}

/* Form Styles in Modal */
.modal-body .form-label {
  font-weight: 600;
  color: #2c3e50;
  margin-bottom: 8px;
}

.modal-body .form-select,
.modal-body .form-control {
  border-radius: 8px;
  border: 1px solid #e0e6ed;
  padding: 12px 16px;
  transition: all 0.3s ease;
}

.modal-body .form-select:focus,
.modal-body .form-control:focus {
  border-color: #667eea;
  box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
}

/* Alert Styles */
.alert {
  border-radius: 8px;
  border: none;
  padding: 16px 20px;
  margin-bottom: 20px;
}

.alert-info {
  background-color: #e7f3ff;
  color: #0c5460;
}

.alert-warning {
  background-color: #fff3cd;
  color: #856404;
}

/* Loading States */
.loading-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background-color: rgba(255, 255, 255, 0.9);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 9998;
}

.loading-content {
  text-align: center;
  padding: 30px;
  background: white;
  border-radius: 12px;
  box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
}

.loading-content .spinner-border {
  width: 3rem;
  height: 3rem;
}

.loading-content p {
  margin-top: 15px;
  color: #6c757d;
  font-weight: 500;
}

/* Spinner Styles */
.spinner-border-sm {
  width: 1rem;
  height: 1rem;
}

.fa-spin {
  animation: fa-spin 2s infinite linear;
}

@keyframes fa-spin {
  0% {
    transform: rotate(0deg);
  }
  100% {
    transform: rotate(360deg);
  }
}

/* Button Styles */
.btn {
  border-radius: 8px;
  font-weight: 500;
  padding: 10px 20px;
  transition: all 0.3s ease;
  border: none;
}

.btn:hover {
  transform: translateY(-1px);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

.btn-primary {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
}

.btn-success {
  background: linear-gradient(135deg, #56ab2f 0%, #a8e6cf 100%);
  color: white;
}

.btn-warning {
  background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
  color: white;
}

.btn-outline-primary {
  border: 2px solid #667eea;
  color: #667eea;
  background: transparent;
}

.btn-outline-primary:hover {
  background: #667eea;
  color: white;
}

.btn-outline-success {
  border: 2px solid #28a745;
  color: #28a745;
  background: transparent;
}

.btn-outline-success:hover {
  background: #28a745;
  color: white;
}

.btn-outline-warning {
  border: 2px solid #ffc107;
  color: #ffc107;
  background: transparent;
}

.btn-outline-warning:hover {
  background: #ffc107;
  color: #212529;
}

.btn-outline-secondary {
  border: 2px solid #6c757d;
  color: #6c757d;
  background: transparent;
}

.btn-outline-secondary:hover {
  background: #6c757d;
  color: white;
}

.btn:disabled {
  opacity: 0.6;
  cursor: not-allowed;
  transform: none !important;
  box-shadow: none !important;
}

/* Empty State */
.text-center i[style*="font-size: 4rem"] {
  color: #dee2e6;
  margin-bottom: 20px;
}

/* Responsive Design */
@media (max-width: 768px) {
  .riwayat-container {
    padding: 15px 10px;
  }
  
  .header-section .card-header {
    padding: 15px 20px;
  }
  
  .header-section h4 {
    font-size: 18px;
  }
  
  .header-actions {
    flex-direction: column;
    gap: 8px;
    align-items: stretch;
  }
  
  .stats-section .card-body {
    padding: 20px 15px;
  }
  
  .stats-section h3 {
    font-size: 2rem;
  }
  
  .riwayat-card .card-header,
  .riwayat-card .card-body,
  .riwayat-card .card-footer {
    padding: 15px;
  }
  
  .patient-info h6 {
    font-size: 16px;
  }
  
  .modal-dialog {
    margin: 10px;
    max-width: calc(100% - 20px);
  }
  
  .modal-header,
  .modal-body,
  .modal-footer {
    padding: 20px;
  }
  
  .action-buttons .btn-group {
    flex-direction: column;
    width: 100%;
  }
  
  .action-buttons .btn {
    margin-bottom: 5px;
  }
}

@media (max-width: 576px) {
  .filter-section .col-md-6,
  .filter-section .col-md-2 {
    margin-bottom: 15px;
  }
  
  .stats-section .col-md-4 {
    margin-bottom: 15px;
  }
  
  .pagination .page-link {
    padding: 8px 12px;
    font-size: 14px;
  }
  
  .patient-details {
    display: flex;
    flex-direction: column;
    gap: 4px;
  }
  
  .status-badges {
    justify-content: flex-start;
  }
  
  .badge {
    font-size: 10px;
    padding: 4px 8px;
  }
}

/* Transition Animations */
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.3s;
}

.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}

/* Focus States for Accessibility */
.btn:focus,
.form-control:focus,
.form-select:focus {
  outline: none;
  box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
}

/* Custom Scrollbar */
.modal-dialog::-webkit-scrollbar {
  width: 8px;
}

.modal-dialog::-webkit-scrollbar-track {
  background: #f1f1f1;
  border-radius: 4px;
}

.modal-dialog::-webkit-scrollbar-thumb {
  background: #c1c1c1;
  border-radius: 4px;
}

.modal-dialog::-webkit-scrollbar-thumb:hover {
  background: #a8a8a8;
}

/* Success Animation */
@keyframes successPulse {
  0% {
    transform: scale(1);
  }
  50% {
    transform: scale(1.05);
  }
  100% {
    transform: scale(1);
  }
}

.text-success i[style*="font-size: 48px"] {
  animation: successPulse 0.6s ease-in-out;
}
</style>