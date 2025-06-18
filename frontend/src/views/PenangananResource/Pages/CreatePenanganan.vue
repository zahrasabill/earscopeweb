<template>
  <div class="container-fluid penanganan-container">
    <!-- Form Section -->
    <div class="form-section">
      <div class="card shadow">
        <div class="card-header bg-gradient text-white">
          <div class="d-flex justify-content-between align-items-center">
            <h5 class="mb-0">
              <i class="fas fa-clipboard-list me-2"></i>Data Penanganan
            </h5>
            <button 
              type="button" 
              @click="navigateToList" 
              class="btn btn-light btn-sm"
              :disabled="isSubmitting"
            >
              <i class="fas fa-arrow-left me-1"></i>Kembali ke Daftar
            </button>
          </div>
        </div>
        <div class="card-body">
          <form @submit.prevent="submitForm">
            <div class="row">
              <!-- Column 1 -->
              <div class="col-lg-6">
                <!-- Pilih Pasien -->
                <div class="form-group mb-3">
                  <label for="patientSelect" class="form-label fw-bold required">
                    <i class="fas fa-user me-1"></i>Pilih Pasien
                  </label>
                  <select 
                    v-model="formData.patientId" 
                    id="patientSelect" 
                    class="form-select"
                    :class="{ 'is-invalid': errors.patientId }"
                    @change="onPatientSelect"
                    required
                  >
                    <option value="" disabled>-- Pilih Pasien --</option>
                    <option v-for="user in users" :key="user.id" :value="user.id">
                      {{ user.name }} {{ user.kode_akses ? `(${user.kode_akses})` : '' }}
                    </option>
                  </select>
                  <div v-if="errors.patientId" class="invalid-feedback">
                    {{ errors.patientId }}
                  </div>
                </div>

                <!-- Patient Information Card -->
                <div v-if="selectedPatientInfo" class="card border-info mb-3">
                  <div class="card-header bg-info text-white">
                    <h6 class="mb-0">
                      <i class="fas fa-info-circle me-2"></i>Informasi Pasien
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
                          <p class="mb-2">{{ selectedPatientInfo.name || '-' }}</p>
                        </div>
                        <div class="patient-info-item">
                          <label class="fw-bold text-muted">No. Rekam Medis:</label>
                          <p class="mb-2">{{ selectedPatientInfo.kode_akses || '-' }}</p>
                        </div>
                        <div class="patient-info-item">
                          <label class="fw-bold text-muted">Tanggal Lahir:</label>
                          <p class="mb-2">{{ formatDate(selectedPatientInfo.tanggal_lahir) || '-' }}</p>
                        </div>
                      </div>
                      <div class="col-md-6">
                        <div class="patient-info-item">
                          <label class="fw-bold text-muted">Usia:</label>
                          <p class="mb-2">{{ calculateAge(selectedPatientInfo.tanggal_lahir) }}</p>
                        </div>
                        <div class="patient-info-item">
                          <label class="fw-bold text-muted">No. Telepon:</label>
                          <p class="mb-2">{{ selectedPatientInfo.no_telp || '-' }}</p>
                        </div>
                        <div class="patient-info-item">
                          <label class="fw-bold text-muted">Jenis Kelamin:</label>
                          <p class="mb-2">
                            <span
                              class="badge"
                              :class="selectedPatientInfo.gender.toLowerCase() === 'laki-laki' ? 'bg-primary' : selectedPatientInfo.gender.toLowerCase() === 'perempuan' ? 'bg-pink' : 'bg-secondary'"
                            >
                              {{ selectedPatientInfo.gender.toLowerCase() === 'laki-laki'
                                ? 'Laki-laki'
                                : selectedPatientInfo.gender.toLowerCase() === 'perempuan'
                                ? 'Perempuan'
                                : '-' }}
                            </span>
                          </p>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>

                <!-- Tanggal Penanganan -->
                <div class="form-group mb-3">
                  <label for="tanggalPenanganan" class="form-label fw-bold required">
                    <i class="fas fa-calendar me-1"></i>Tanggal Penanganan
                  </label>
                  <input 
                    type="date" 
                    v-model="formData.tanggal" 
                    id="tanggalPenanganan" 
                    class="form-control"
                    :class="{ 'is-invalid': errors.tanggal }"
                    required
                  >
                  <div v-if="errors.tanggal" class="invalid-feedback">
                    {{ errors.tanggal }}
                  </div>
                </div>

                <!-- Keluhan -->
                <div class="form-group mb-3">
                  <label for="keluhan" class="form-label fw-bold required">
                    <i class="fas fa-exclamation-triangle me-1"></i>Keluhan Pasien
                  </label>
                  <textarea 
                    v-model="formData.keluhan" 
                    id="keluhan" 
                    class="form-control" 
                    rows="4" 
                    placeholder="Masukkan keluhan pasien..."
                    :class="{ 'is-invalid': errors.keluhan }"
                    @input="countWords('keluhan')"
                    required
                  ></textarea>
                  <small class="form-text text-muted">
                    {{ wordCounts.keluhan }}/200 kata
                  </small>
                  <div v-if="errors.keluhan" class="invalid-feedback">
                    {{ errors.keluhan }}
                  </div>
                </div>

                <!-- Riwayat Penyakit -->
                <div class="form-group mb-3">
                  <label for="riwayatPenyakit" class="form-label fw-bold">
                    <i class="fas fa-history me-1"></i>Riwayat Penyakit Sebelumnya
                  </label>
                  <textarea 
                    v-model="formData.riwayatPenyakit" 
                    id="riwayatPenyakit" 
                    class="form-control" 
                    rows="3" 
                    placeholder="Masukkan riwayat penyakit sebelumnya (opsional)..."
                    :class="{ 'is-invalid': errors.riwayatPenyakit }"
                    @input="countWords('riwayatPenyakit')"
                  ></textarea>
                  <small class="form-text text-muted">
                    {{ wordCounts.riwayatPenyakit }}/150 kata
                  </small>
                  <div v-if="errors.riwayatPenyakit" class="invalid-feedback">
                    {{ errors.riwayatPenyakit }}
                  </div>
                </div>
              </div>

              <!-- Column 2 -->
              <div class="col-lg-6">
                <!-- Diagnosis Manual -->
                <div class="form-group mb-3">
                  <label for="diagnosisManual" class="form-label fw-bold required">
                    <i class="fas fa-diagnoses me-1"></i>Diagnosis Manual
                  </label>
                  <textarea 
                    v-model="formData.diagnosisManual" 
                    id="diagnosisManual" 
                    class="form-control" 
                    rows="4" 
                    placeholder="Masukkan diagnosis manual..."
                    :class="{ 'is-invalid': errors.diagnosisManual }"
                    @input="countWords('diagnosisManual')"
                    required
                  ></textarea>
                  <small class="form-text text-muted">
                    {{ wordCounts.diagnosisManual }}/200 kata
                  </small>
                  <div v-if="errors.diagnosisManual" class="invalid-feedback">
                    {{ errors.diagnosisManual }}
                  </div>
                </div>

                <!-- Telinga yang Terkena -->
                <div class="form-group mb-3">
                  <label class="form-label fw-bold required">
                    <i class="fas fa-deaf me-1"></i>Telinga yang Terkena
                  </label>
                  <div class="telinga-buttons">
                    <div class="row g-2">
                      <div class="col-6">
                        <button 
                          type="button" 
                          @click="selectTelinga('kiri')"
                          class="btn w-100"
                          :class="formData.telinga === 'kiri' ? 'btn-primary active' : 'btn-outline-primary'"
                        >
                          <i class="fas fa-ear-deaf me-2"></i>Telinga Kiri
                        </button>
                      </div>
                      <div class="col-6">
                        <button 
                          type="button" 
                          @click="selectTelinga('kanan')"
                          class="btn w-100"
                          :class="formData.telinga === 'kanan' ? 'btn-primary active' : 'btn-outline-primary'"
                        >
                          <i class="fas fa-ear-deaf me-2"></i>Telinga Kanan
                        </button>
                      </div>
                      <div class="col-12">
                        <button 
                          type="button" 
                          @click="selectTelinga('keduanya')"
                          class="btn w-100"
                          :class="formData.telinga === 'keduanya' ? 'btn-primary active' : 'btn-outline-primary'"
                        >
                          <i class="fas fa-deaf me-2"></i>Keduanya
                        </button>
                      </div>
                    </div>
                  </div>
                  <div v-if="errors.telinga" class="text-danger small mt-1">
                    {{ errors.telinga }}
                  </div>
                </div>

                <!-- Tindakan -->
                <div class="form-group mb-3">
                  <label for="tindakan" class="form-label fw-bold required">
                    <i class="fas fa-hand-holding-medical me-1"></i>Tindakan yang Diberikan
                  </label>
                  <textarea 
                    v-model="formData.tindakan" 
                    id="tindakan" 
                    class="form-control" 
                    rows="4" 
                    placeholder="Masukkan tindakan yang diberikan..."
                    :class="{ 'is-invalid': errors.tindakan }"
                    @input="countWords('tindakan')"
                    required
                  ></textarea>
                  <small class="form-text text-muted">
                    {{ wordCounts.tindakan }}/200 kata
                  </small>
                  <div v-if="errors.tindakan" class="invalid-feedback">
                    {{ errors.tindakan }}
                  </div>
                </div>
              </div>
            </div>

            <!-- Submit Button -->
            <div class="row mt-4">
              <div class="col-12">
                <div class="d-flex justify-content-end gap-2">
                  <button 
                    type="button" 
                    @click="resetForm" 
                    class="btn btn-outline-secondary"
                    :disabled="isSubmitting"
                  >
                    <i class="fas fa-undo me-1"></i>Reset Form
                  </button>
                  <button 
                    type="submit" 
                    class="btn btn-success btn-lg"
                    :disabled="isSubmitting"
                  >
                    <span v-if="isSubmitting" class="spinner-border spinner-border-sm me-2"></span>
                    <i v-else class="fas fa-save me-2"></i>
                    {{ isSubmitting ? 'Menyimpan...' : 'Simpan Data Penanganan' }}
                  </button>
                </div>
              </div>
            </div>
          </form>
        </div>
      </div>
    </div>

    <!-- Success Modal -->
    <transition name="fade">
      <div v-if="showSuccessModal" class="modal-overlay" @click.self="closeSuccessModal">
        <div class="modal-dialog">
          <div class="modal-content simple-modal">
            <div class="modal-header">
              <h5 class="modal-title">Data Penanganan Berhasil Disimpan</h5>
              <button type="button" class="btn-close" @click="closeSuccessModal"></button>
            </div>
            <div class="modal-body text-center py-4">
              <i class="fas fa-check-circle text-success" style="font-size: 64px;"></i>
              <h4 class="mt-3 text-success">Berhasil!</h4>
              <p class="mb-0">Data penanganan untuk pasien <strong>{{ selectedPatientName }}</strong> telah berhasil disimpan</p>
            </div>
            <div class="modal-footer">
              <button class="btn btn-outline-primary me-2" @click="navigateToList">
                <i class="fas fa-list me-1"></i>Lihat Daftar Penanganan
              </button>
              <button class="btn btn-primary" @click="closeSuccessModal">
                <i class="fas fa-plus me-1"></i>Input Penanganan Baru
              </button>
            </div>
          </div>
        </div>
      </div>
    </transition>

    <!-- Access Denied Modal -->
    <transition name="fade">
      <div v-if="showAccessDeniedModal" class="modal-overlay">
        <div class="modal-dialog">
          <div class="modal-content simple-modal">
            <div class="modal-header">
              <h5 class="modal-title">Akses Ditolak</h5>
            </div>
            <div class="modal-body text-center py-4">
              <i class="fas fa-exclamation-triangle text-warning" style="font-size: 64px;"></i>
              <h4 class="mt-3 text-warning">Akses Ditolak</h4>
              <p class="mb-0">Hanya dokter yang dapat mengakses halaman ini</p>
            </div>
            <div class="modal-footer">
              <button class="btn btn-primary" @click="redirectToHome">
                <i class="fas fa-home me-1"></i>Kembali ke Beranda
              </button>
            </div>
          </div>
        </div>
      </div>
    </transition>

    <!-- Loading overlay -->
    <div v-if="isLoading" class="loading-overlay">
      <div class="loading-content">
        <div class="spinner-border text-primary" style="width: 3rem; height: 3rem;"></div>
        <p class="mt-3 fw-bold">Memuat data...</p>
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
  name: 'CreatePenanganan',
  components: { AppLayout },
  setup() {
    const userStore = useUserStore();
    const { users } = storeToRefs(userStore);
    const router = useRouter();
    
    // Form data
    const formData = ref({
      patientId: '',
      tanggal: new Date().toISOString().split('T')[0],
      keluhan: '',
      riwayatPenyakit: '',
      diagnosisManual: '',
      telinga: '',
      tindakan: ''
    });

    // Form validation errors
    const errors = ref({});

    // Word counts for textareas
    const wordCounts = ref({
      keluhan: 0,
      riwayatPenyakit: 0,
      diagnosisManual: 0,
      tindakan: 0
    });

    // Loading and modal states
    const isLoading = ref(false);
    const isSubmitting = ref(false);
    const showSuccessModal = ref(false);
    const showAccessDeniedModal = ref(false);
    const selectedPatientName = ref('');
    const loadingPatientInfo = ref(false);

    // Patient information
    const selectedPatientInfo = ref(null);

    // User role from JWT
    const userRole = ref('pasien');

    // Computed property for selected patient name
    const selectedPatient = computed(() => {
      return users.value.find(user => user.id.toString() === formData.value.patientId.toString());
    });

    // Navigate to list page
    const navigateToList = () => {
      router.push({ name: 'list-penanganan' });
    };

    // Redirect to home page
    const redirectToHome = () => {
      router.push({ name: 'login' }); 
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

    // Check if user is doctor
    const checkDoctorAccess = () => {
      const role = getUserRoleFromToken();
      if (role !== 'dokter') {
        showAccessDeniedModal.value = true;
        return false;
      }
      return true;
    };

    // Fetch patient details
    const fetchPatientDetails = async (patientId) => {
      try {
        loadingPatientInfo.value = true;
        const token = localStorage.getItem("token") || sessionStorage.getItem("token");
        
        const response = await axios.get(api.getEndpoint(`users/${patientId}`), {
          headers: { Authorization: `Bearer ${token}` },
        });

        selectedPatientInfo.value = response.data.data || response.data;
        console.log('Patient details loaded:', selectedPatientInfo.value);
        
      } catch (error) {
        console.error('Error fetching patient details:', error);
        selectedPatientInfo.value = null;
        
        if (error.response && error.response.status === 404) {
          alert('Data pasien tidak ditemukan.');
        } else {
          alert('Gagal memuat informasi pasien.');
        }
      } finally {
        loadingPatientInfo.value = false;
      }
    };

    // Handle patient selection
    const onPatientSelect = async () => {
      if (formData.value.patientId) {
        await fetchPatientDetails(formData.value.patientId);
      } else {
        selectedPatientInfo.value = null;
      }
    };

    // Format date function
    const formatDate = (dateString) => {
      if (!dateString) return '-';
      
      try {
        const date = new Date(dateString);
        return date.toLocaleDateString('id-ID', {
          year: 'numeric',
          month: 'long',
          day: 'numeric'
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

    // Count words in textarea
    const countWords = (field) => {
      const text = formData.value[field].trim();
      wordCounts.value[field] = text ? text.split(/\s+/).length : 0;
    };

    // Select telinga
    const selectTelinga = (telinga) => {
      formData.value.telinga = telinga;
      if (errors.value.telinga) {
        delete errors.value.telinga;
      }
    };

    // Validate form
    const validateForm = () => {
      errors.value = {};

      // Required fields validation
      if (!formData.value.patientId) {
        errors.value.patientId = 'Pilih pasien terlebih dahulu';
      }

      if (!formData.value.tanggal) {
        errors.value.tanggal = 'Tanggal penanganan harus diisi';
      }

      if (!formData.value.keluhan.trim()) {
        errors.value.keluhan = 'Keluhan pasien harus diisi';
      } else if (wordCounts.value.keluhan > 200) {
        errors.value.keluhan = 'Keluhan tidak boleh lebih dari 200 kata';
      }

      if (!formData.value.diagnosisManual.trim()) {
        errors.value.diagnosisManual = 'Diagnosis manual harus diisi';
      } else if (wordCounts.value.diagnosisManual > 200) {
        errors.value.diagnosisManual = 'Diagnosis tidak boleh lebih dari 200 kata';
      }

      if (!formData.value.telinga) {
        errors.value.telinga = 'Pilih telinga yang terkena';
      }

      if (!formData.value.tindakan.trim()) {
        errors.value.tindakan = 'Tindakan harus diisi';
      } else if (wordCounts.value.tindakan > 200) {
        errors.value.tindakan = 'Tindakan tidak boleh lebih dari 200 kata';
      }

      // Optional fields word count validation
      if (wordCounts.value.riwayatPenyakit > 150) {
        errors.value.riwayatPenyakit = 'Riwayat penyakit tidak boleh lebih dari 150 kata';
      }

      return Object.keys(errors.value).length === 0;
    };

    // Submit form
    const submitForm = async () => {
      // Double check doctor access before submitting
      if (!checkDoctorAccess()) {
        return;
      }

      // Count words for all fields before validation
      Object.keys(wordCounts.value).forEach(field => {
        countWords(field);
      });

      if (!validateForm()) {
        return;
      }

      try {
        isSubmitting.value = true;
        const token = localStorage.getItem("token") || sessionStorage.getItem("token");

        const payload = {
          user_id: formData.value.patientId,
          tanggal_penanganan: formData.value.tanggal,
          keluhan: formData.value.keluhan.trim(),
          riwayat_penyakit: formData.value.riwayatPenyakit.trim() || null,
          diagnosis_manual: formData.value.diagnosisManual.trim(),
          telinga_terkena: formData.value.telinga,
          tindakan: formData.value.tindakan.trim()
        };

        const response = await axios.post(api.getEndpoint('penanganan'), payload, {
          headers: { Authorization: `Bearer ${token}` },
        });

        // Get selected patient name for success message
        selectedPatientName.value = selectedPatient.value ? selectedPatient.value.name : 'Pasien';
        
        // Show success modal
        console.log('Data penanganan berhasil disimpan:', response.data);
        showSuccessModal.value = true;

      } catch (error) {
        console.error('Error submitting penanganan:', error);
        
        if (error.response && error.response.data && error.response.data.errors) {
          // Handle validation errors from backend
          const backendErrors = error.response.data.errors;
          Object.keys(backendErrors).forEach(key => {
            errors.value[key] = Array.isArray(backendErrors[key]) 
              ? backendErrors[key][0] 
              : backendErrors[key];
          });
        } else if (error.response && error.response.data && error.response.data.message) {
          alert(`Error: ${error.response.data.message}`);
        } else {
          alert('Gagal menyimpan data penanganan. Silakan coba lagi.');
        }
      } finally {
        isSubmitting.value = false;
      }
    };

    // Reset form
    const resetForm = () => {
      formData.value = {
        patientId: '',
        tanggal: new Date().toISOString().split('T')[0],
        keluhan: '',
        riwayatPenyakit: '',
        diagnosisManual: '',
        telinga: '',
        tindakan: ''
      };
      
      wordCounts.value = {
        keluhan: 0,
        riwayatPenyakit: 0,
        diagnosisManual: 0,
        tindakan: 0,
      };
      
      errors.value = {};
      selectedPatientInfo.value = null;
    };

    // Close success modal and reset form
    const closeSuccessModal = () => {
      showSuccessModal.value = false;
      resetForm();
    };

    onMounted(async () => {
      try {
        isLoading.value = true;
        
        // Set user role from JWT
        userRole.value = getUserRoleFromToken();
        
        // Check if user is doctor
        if (!checkDoctorAccess()) {
          return;
        }
        
        // Load users data
        if (users.value.length === 0) {
          await userStore.fetchUsers();
        }
        
      } catch (error) {
        console.error('Error loading data:', error);
        alert('Gagal memuat data. Silakan refresh halaman.');
      } finally {
        isLoading.value = false;
      }
    });

    return {
      formData,
      errors,
      wordCounts,
      users,
      isLoading,
      isSubmitting,
      showSuccessModal,
      showAccessDeniedModal,
      selectedPatientName,
      selectedPatientInfo,
      loadingPatientInfo,
      userRole,
      countWords,
      selectTelinga,
      validateForm,
      submitForm,
      resetForm,
      closeSuccessModal,
      navigateToList,
      redirectToHome,
      checkDoctorAccess,
      onPatientSelect,
      fetchPatientDetails,
      formatDate,
      calculateAge
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

.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.3s ease;
}

.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}

.telinga-buttons .btn {
  min-height: 45px;
}

.required::after {
  content: '*';
  color: red;
  margin-left: 4px;
}

.form-group {
  margin-bottom: 1rem;
}

.card-header.bg-gradient {
  background: linear-gradient(45deg, #007bff, #0056b3);
}

.penanganan-container {
  padding: 2rem 0;
}
</style>