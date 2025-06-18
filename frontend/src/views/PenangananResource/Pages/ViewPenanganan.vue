<template>
  <div class="container-fluid penanganan-container">
    <!-- View Section -->
    <div class="view-section">
      <div class="card shadow">
        <div class="card-header bg-gradient text-white">
          <div class="d-flex justify-content-between align-items-center">
            <h5 class="mb-0">
              <i class="fas fa-eye me-2"></i>Detail Penanganan
            </h5>
            <button 
              type="button" 
              @click="navigateToList" 
              class="btn btn-light btn-sm"
            >
              <i class="fas fa-arrow-left me-1"></i>Kembali ke Daftar
            </button>
          </div>
        </div>
        <div class="card-body">
          <div v-if="penangananData" class="row">
            <!-- Column 1 -->
            <div class="col-lg-6">
              <!-- Informasi Pasien Card -->
              <div class="card border-info mb-4">
                <div class="card-header bg-info text-white">
                  <h6 class="mb-0">
                    <i class="fas fa-user me-2"></i>Informasi Pasien
                  </h6>
                </div>
                <div class="card-body">
                  <div v-if="loadingPatientInfo" class="text-center py-3">
                    <div class="spinner-border spinner-border-sm text-primary me-2"></div>
                    <span>Memuat informasi pasien...</span>
                  </div>
                  <div v-else class="row">
                    <div class="col-md-6">
                      <div class="patient-info-item">
                        <label class="fw-bold text-muted">Nama Lengkap:</label>
                        <p class="mb-2">{{ patientInfo?.name || penangananData.patient_name || '-' }}</p>
                      </div>
                      <div class="patient-info-item">
                        <label class="fw-bold text-muted">No. Rekam Medis:</label>
                        <p class="mb-2">{{ patientInfo?.kode_akses || penangananData.patient_kode_akses || '-' }}</p>
                      </div>
                      <div class="patient-info-item">
                        <label class="fw-bold text-muted">Tanggal Lahir:</label>
                        <p class="mb-2">{{ formatDate(patientInfo?.tanggal_lahir) || '-' }}</p>
                      </div>
                    </div>
                    <div class="col-md-6">
                      <div class="patient-info-item">
                        <label class="fw-bold text-muted">Usia:</label>
                        <p class="mb-2">{{ calculateAge(patientInfo?.tanggal_lahir) }}</p>
                      </div>
                      <div class="patient-info-item">
                        <label class="fw-bold text-muted">No. Telepon:</label>
                        <p class="mb-2">{{ patientInfo?.no_telp || '-' }}</p>
                      </div>
                      <div class="patient-info-item">
                        <label class="fw-bold text-muted">Jenis Kelamin:</label>
                        <p class="mb-2">
                          <span 
                            v-if="patientInfo?.gender" 
                            class="badge"
                            :class="patientInfo.gender.toLowerCase() === 'laki-laki' ? 'bg-primary' : patientInfo.gender.toLowerCase() === 'perempuan' ? 'bg-pink' : 'bg-secondary'"
                          >
                            {{ patientInfo.gender.toLowerCase() === 'laki-laki' 
                              ? 'Laki-laki' 
                              : patientInfo.gender.toLowerCase() === 'perempuan' 
                              ? 'Perempuan' 
                              : '-' }}
                          </span>
                          <span v-else>-</span>
                        </p>
                      </div>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Tanggal Penanganan -->
              <div class="form-group mb-4">
                <label class="form-label">
                  <i class="fas fa-calendar me-2 text-primary"></i>Tanggal Penanganan
                </label>
                <div class="form-display">
                  {{ formatDate(penangananData.tanggal_penanganan) }}
                </div>
              </div>

              <!-- Keluhan -->
              <div class="form-group mb-4">
                <label class="form-label">
                  <i class="fas fa-exclamation-triangle me-2 text-warning"></i>Keluhan Pasien
                </label>
                <div class="form-display textarea-display">{{ penangananData.keluhan }}</div>
              </div>

              <!-- Riwayat Penyakit -->
              <div class="form-group mb-4">
                <label class="form-label">
                  <i class="fas fa-history me-2 text-info"></i>Riwayat Penyakit Sebelumnya
                </label>
                <div class="form-display textarea-display">{{ penangananData.riwayat_penyakit || 'Tidak ada riwayat penyakit sebelumnya' }}</div>
              </div>
            </div>

            <!-- Column 2 -->
            <div class="col-lg-6">
              <!-- Diagnosis Manual -->
              <div class="form-group mb-4">
                <label class="form-label">
                  <i class="fas fa-diagnoses me-2 text-success"></i>Diagnosis Manual
                </label>
                <div class="form-display textarea-display">{{ penangananData.diagnosis_manual }}</div>
              </div>

              <!-- Telinga yang Terkena -->
              <div class="form-group mb-4">
                <label class="form-label">
                  <i class="fas fa-deaf me-2 text-danger"></i>Telinga yang Terkena
                </label>
                <div class="form-display">
                  {{ getTelingaText(penangananData.telinga_terkena) }}
                </div>
              </div>

              <!-- Tindakan -->
              <div class="form-group mb-4">
                <label class="form-label">
                  <i class="fas fa-hand-holding-medical me-2 text-primary"></i>Tindakan yang Diberikan
                </label>
                <div class="form-display textarea-display">{{ penangananData.tindakan }}</div>
              </div>

              <!-- Informasi Tambahan -->
              <div class="form-group mb-4">
                <label class="form-label">
                  <i class="fas fa-info-circle me-2 text-muted"></i>Informasi Tambahan
                </label>
                <div class="info-section">
                  <div class="info-item">
                    <span class="info-label">Dibuat:</span> 
                    <span class="info-value">{{ formatDateTime(penangananData.created_at) }}</span>
                  </div>
                  <div v-if="penangananData.updated_at !== penangananData.created_at" class="info-item">
                    <span class="info-label">Terakhir diperbarui:</span> 
                    <span class="info-value">{{ formatDateTime(penangananData.updated_at) }}</span>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Action Buttons -->
          <div class="row mt-4" v-if="penangananData">
            <div class="col-12">
              <div class="d-flex justify-content-end gap-2">
                <button 
                  type="button" 
                  @click="navigateToList" 
                  class="btn btn-outline-secondary"
                >
                  <i class="fas fa-arrow-left me-1"></i>Kembali
                </button>
                <button 
                  v-if="canEdit"
                  type="button" 
                  @click="navigateToEdit" 
                  class="btn btn-warning"
                >
                  <i class="fas fa-edit me-1"></i>Edit Data
                </button>
                <button 
                  type="button" 
                  class="btn btn-primary"
                  @click="viewDetailPage"
                >
                  <i class="fas fa-print me-1"></i>Cetak Halaman
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Loading overlay -->
    <div v-if="isLoading" class="loading-overlay">
      <div class="loading-content">
        <div class="spinner-border text-primary" style="width: 3rem; height: 3rem;"></div>
        <p class="mt-3 fw-bold">Memuat data...</p>
      </div>
    </div>

    <!-- Error Modal -->
    <transition name="fade">
      <div v-if="showErrorModal" class="modal-overlay" @click.self="closeErrorModal">
        <div class="modal-dialog">
          <div class="modal-content simple-modal">
            <div class="modal-header">
              <h5 class="modal-title">Error</h5>
              <button type="button" class="btn-close" @click="closeErrorModal"></button>
            </div>
            <div class="modal-body text-center py-4">
              <i class="fas fa-exclamation-triangle text-danger" style="font-size: 64px;"></i>
              <h4 class="mt-3 text-danger">Terjadi Kesalahan</h4>
              <p class="mb-0">{{ errorMessage }}</p>
            </div>
            <div class="modal-footer">
              <button class="btn btn-primary" @click="navigateToList">
                <i class="fas fa-arrow-left me-1"></i>Kembali ke Daftar
              </button>
            </div>
          </div>
        </div>
      </div>
    </transition>
  </div>
</template>

<script>
import { ref, onMounted, computed } from 'vue';
import { useRouter, useRoute } from 'vue-router';
import AppLayout from '@/components/AppLayout.vue';
import api from '@/api';
import axios from 'axios';
import { jwtDecode } from "jwt-decode";

export default {
  name: 'ViewPenanganan',
  components: { AppLayout },
  setup() {
    const router = useRouter();
    const route = useRoute();
    
    // Data
    const penangananData = ref(null);
    const patientInfo = ref(null);
    const isLoading = ref(false);
    const loadingPatientInfo = ref(false);
    const showErrorModal = ref(false);
    const errorMessage = ref('');
    const userRole = ref('pasien');

    // Get penanganan ID from route params
    const penangananId = computed(() => route.params.id);

    // Check if user can edit (only doctors can edit)
    const canEdit = computed(() => userRole.value === 'dokter');

    // Navigation functions
    const navigateToList = () => {
      router.push({ name: 'list-penanganan' });
    };

    const navigateToEdit = () => {
      router.push({ name: 'edit-penanganan', params: { id: penangananId.value } });
    };

    const viewDetailPage = () => {
      // You can implement this to show a more detailed view or print view
      window.print();
    };

    // Function to get user role from JWT
    const getUserRoleFromToken = () => {
      try {
        const token = localStorage.getItem("token") || sessionStorage.getItem("token");
        if (token) {
          const payload = jwtDecode(token);
          return payload.role || 'pasien';
        }
        return 'pasien';
      } catch (error) {
        console.error('Error decoding token:', error);
        return 'pasien';
      }
    };

    // Format date function
    const formatDate = (dateString) => {
      if (!dateString) return '-';
      
      try {
        const date = new Date(dateString);
        return date.toLocaleDateString('id-ID', {
          weekday: 'long',
          year: 'numeric',
          month: 'long',
          day: 'numeric'
        });
      } catch (error) {
        return '-';
      }
    };

    // Format datetime function
    const formatDateTime = (dateTimeString) => {
      if (!dateTimeString) return '-';
      
      try {
        const date = new Date(dateTimeString);
        return date.toLocaleDateString('id-ID', {
          year: 'numeric',
          month: 'long',
          day: 'numeric',
          hour: '2-digit',
          minute: '2-digit'
        });
      } catch (error) {
        return '-';
      }
    };

    // Calculate age function
    const calculateAge = (birthDate) => {
      if (!birthDate) return '-';
      
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
        return '-';
      }
    };

    // Get telinga text
    const getTelingaText = (telinga) => {
      switch (telinga) {
        case 'kiri':
          return 'Telinga Kiri';
        case 'kanan':
          return 'Telinga Kanan';
        case 'keduanya':
          return 'Kedua Telinga';
        default:
          return telinga;
      }
    };

    // Fetch patient details
    const fetchPatientDetails = async (userId) => {
      try {
        loadingPatientInfo.value = true;
        const token = localStorage.getItem("token") || sessionStorage.getItem("token");
        
        if (!token) {
          throw new Error('Token tidak ditemukan');
        }

        const response = await axios.get(`${api.getEndpoint('users')}/${userId}`, {
          headers: { Authorization: `Bearer ${token}` },
        });

        patientInfo.value = response.data.data || response.data;
        console.log('Patient details loaded:', patientInfo.value);
        
      } catch (error) {
        console.error('Error fetching patient details:', error);
        patientInfo.value = null;
        
        // Don't show alert for patient info errors, just log them
        console.warn('Failed to load patient information, using basic data from penanganan');
      } finally {
        loadingPatientInfo.value = false;
      }
    };

    // Fetch penanganan data
    const fetchPenangananData = async () => {
      try {
        isLoading.value = true;
        const token = localStorage.getItem("token") || sessionStorage.getItem("token");

        if (!token) {
          throw new Error('Token tidak ditemukan');
        }

        const response = await axios.get(`${api.getEndpoint('penanganan')}/${penangananId.value}`, {
          headers: { Authorization: `Bearer ${token}` },
        });

        penangananData.value = response.data.data;
        
        // After getting penanganan data, fetch patient details if user_id is available
        if (penangananData.value.user_id) {
          await fetchPatientDetails(penangananData.value.user_id);
        }
        
      } catch (error) {
        console.error('Error fetching penanganan data:', error);
        
        if (error.response) {
          if (error.response.status === 404) {
            errorMessage.value = 'Data penanganan tidak ditemukan';
          } else if (error.response.status === 403) {
            errorMessage.value = 'Anda tidak memiliki akses untuk melihat data ini';
          } else {
            errorMessage.value = error.response.data?.message || 'Gagal memuat data penanganan';
          }
        } else {
          errorMessage.value = 'Gagal memuat data penanganan. Periksa koneksi internet Anda.';
        }
        
        showErrorModal.value = true;
      } finally {
        isLoading.value = false;
      }
    };

    // Close error modal
    const closeErrorModal = () => {
      showErrorModal.value = false;
    };

    // Mount lifecycle
    onMounted(async () => {
      // Set user role from JWT
      userRole.value = getUserRoleFromToken();
      
      // Validate penanganan ID
      if (!penangananId.value || isNaN(penangananId.value)) {
        errorMessage.value = 'ID penanganan tidak valid';
        showErrorModal.value = true;
        return;
      }
      
      // Fetch data
      await fetchPenangananData();
    });

    return {
      penangananData,
      patientInfo,
      isLoading,
      loadingPatientInfo,
      showErrorModal,
      errorMessage,
      canEdit,
      navigateToList,
      navigateToEdit,
      viewDetailPage,
      formatDate,
      formatDateTime,
      calculateAge,
      getTelingaText,
      closeErrorModal
    };
  }
};
</script>

<style scoped>
/* Patient Info Card Styling */
.patient-info-item {
  margin-bottom: 0.75rem;
}

.patient-info-item label {
  font-size: 0.85rem;
  margin-bottom: 0.25rem;
  display: block;
}

.patient-info-item p {
  font-size: 0.95rem;
  color: #333;
  margin-bottom: 0;
}

.bg-pink {
  background-color: #e83e8c !important;
}

/* Existing styles */
.penanganan-container {
  padding: 2rem 0;
}

.card-header.bg-gradient {
  background: linear-gradient(45deg, #007bff, #0056b3);
}

.form-group {
  position: relative;
}

.form-label {
  font-weight: bold;
  font-size: 0.9rem;
  color: #495057;
  margin-bottom: 0.5rem;
  display: block;
}

.form-display {
  background-color: #ffffff;
  border: 1px solid #ced4da;
  border-radius: 0.375rem;
  padding: 0.75rem;
  color: #495057;
  font-size: 1rem;
  min-height: calc(1.5em + 0.75rem + 2px);
  display: flex;
  align-items: center;
}

.textarea-display {
  min-height: 80px;
  align-items: flex-start;
  white-space: pre-wrap;
  line-height: 1.5;
  padding-top: 0.75rem;
}

.info-section {
  background-color: #f8f9fa;
  border: 1px solid #ced4da;
  border-radius: 0.375rem;
  padding: 0.75rem;
}

.info-item {
  margin-bottom: 0.25rem;
  font-size: 0.875rem;
}

.info-item:last-child {
  margin-bottom: 0;
}

.info-label {
  color: #6c757d;
  font-weight: 500;
}

.info-value {
  color: #495057;
}

.badge {
  font-size: 0.85em;
}

.btn {
  font-size: 0.875rem;
  padding: 0.5rem 1rem;
  border-radius: 0.375rem;
  transition: all 0.15s ease-in-out;
}

.btn-light {
  background-color: #ffffff;
  border-color: #ffffff;
  color: #495057;
}

.btn-light:hover {
  background-color: #e2e6ea;
  border-color: #dae0e5;
  color: #495057;
}

.btn-outline-secondary {
  border-color: #6c757d;
  color: #6c757d;
}

.btn-outline-secondary:hover {
  background-color: #6c757d;
  border-color: #6c757d;
  color: #ffffff;
}

.btn-warning {
  background-color: #ffc107;
  border-color: #ffc107;
  color: #212529;
}

.btn-warning:hover {
  background-color: #ffca2c;
  border-color: #ffc720;
  color: #212529;
}

.btn-primary {
  background-color: #007bff;
  border-color: #007bff;
  color: #ffffff;
}

.btn-primary:hover {
  background-color: #0069d9;
  border-color: #0062cc;
  color: #ffffff;
}

.loading-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background-color: rgba(255, 255, 255, 0.8);
  display: flex;
  justify-content: center;
  align-items: center;
  z-index: 1000;
}

.loading-content {
  text-align: center;
}

.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background-color: rgba(0, 0, 0, 0.5);
  display: flex;
  justify-content: center;
  align-items: center;
  z-index: 1050;
}

.modal-dialog {
  max-width: 500px;
  width: 100%;
  margin: 1rem;
}

.modal-content {
  background: white;
  border-radius: 8px;
  box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
}

.modal-header {
  padding: 1rem 1.5rem;
  border-bottom: 1px solid #dee2e6;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.modal-body {
  padding: 1.5rem;
}

.modal-footer {
  padding: 1rem 1.5rem;
  border-top: 1px solid #dee2e6;
  display: flex;
  justify-content: flex-end;
  gap: 0.5rem;
}

.btn-close {
  background: none;
  border: none;
  font-size: 1.5rem;
  cursor: pointer;
  padding: 0;
  width: 1.5rem;
  height: 1.5rem;
  display: flex;
  align-items: center;
  justify-content: center;
}

.btn-close:before {
  content: '×';
}

.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.3s ease;
}

.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}

@media (max-width: 768px) {
  .penanganan-container {
    padding: 1rem 0;
  }
  
  .form-group {
    margin-bottom: 1.5rem;
  }
  
  .d-flex.justify-content-end {
    flex-direction: column;
    gap: 0.5rem;
  }
  
  .d-flex.justify-content-end .btn {
    width: 100%;
  }
  
  .modal-footer {
    flex-direction: column;
  }
  
  .modal-footer .btn {
    width: 100%;
  }
}

/* Print styles */
@media print {
  .btn,
  .loading-overlay,
  .modal-overlay {
    display: none !important;
  }
  
  .card-header {
    background: #007bff !important;
    -webkit-print-color-adjust: exact;
  }
}
</style>