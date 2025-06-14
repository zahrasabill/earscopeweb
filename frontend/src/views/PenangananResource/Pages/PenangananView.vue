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
                    <i v-else class="fas fa-paper-plane me-2"></i>
                    {{ isSubmitting ? 'Mengirim...' : 'Kirim ke Pasien' }}
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
              <h5 class="modal-title">Penanganan Berhasil Dikirim</h5>
              <button type="button" class="btn-close" @click="closeSuccessModal"></button>
            </div>
            <div class="modal-body text-center py-4">
              <i class="fas fa-check-circle text-success" style="font-size: 64px;"></i>
              <h4 class="mt-3 text-success">Berhasil!</h4>
              <p class="mb-0">Data penanganan telah berhasil dikirim ke pasien <strong>{{ selectedPatientName }}</strong></p>
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
  name: 'PenangananView',
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
    const selectedPatientName = ref('');

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

        await axios.post(api.getEndpoint('penanganan'), payload, {
          headers: { Authorization: `Bearer ${token}` },
        });

        // Get selected patient name for success message
        selectedPatientName.value = selectedPatient.value ? selectedPatient.value.name : 'Pasien';
        
        // Show success modal
        showSuccessModal.value = true;

      } catch (error) {
        console.error('Error submitting penanganan:', error);
        
        if (error.response && error.response.data && error.response.data.errors) {
          // Handle validation errors from backend
          const backendErrors = error.response.data.errors;
          Object.keys(backendErrors).forEach(key => {
            errors.value[key] = backendErrors[key][0]; // Take first error message
          });
        } else {
          alert('Gagal mengirim data penanganan. Silakan coba lagi.');
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
        if (userRole.value !== 'dokter') {
          alert('Akses ditolak. Hanya dokter yang dapat mengakses halaman ini.');
          // Redirect to appropriate page
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
      selectedPatientName,
      userRole,
      countWords,
      selectTelinga,
      validateForm,
      submitForm,
      resetForm,
      closeSuccessModal,
      navigateToList
    };
  }
};
</script>

<style scoped>
.penanganan-container {
  padding: 20px;
  max-width: 1400px;
  margin: 0 auto;
}

/* Header Section */
.header-section .card {
  border-radius: 12px;
  border: none;
}

.header-section .card-body {
  padding: 1.5rem;
}

/* Form Section */
.form-section .card {
  border-radius: 12px;
  border: none;
  overflow: hidden;
}

.card-header {
  background: linear-gradient(135deg, #4285f4, #34a853);
  color: white;
  border-bottom: none;
  padding: 1rem 1.5rem;
}

.card-body {
  padding: 2rem;
}

/* Form Groups */
.form-group {
  margin-bottom: 1.5rem;
}

.form-label {
  font-weight: 600;
  color: #495057;
  margin-bottom: 0.5rem;
  display: flex;
  align-items: center;
}

.form-label.required::after {
  content: '*';
  color: #dc3545;
  margin-left: 4px;
}

.form-label i {
  color: #4285f4;
  width: 20px;
}

/* Form Controls */
.form-control, 
.form-select {
  border-radius: 8px;
  border: 2px solid #e9ecef;
  padding: 0.75rem 1rem;
  font-size: 0.95rem;
  transition: all 0.3s ease;
}

.form-control:focus, 
.form-select:focus {
  border-color: #4285f4;
  box-shadow: 0 0 0 0.2rem rgba(66, 133, 244, 0.25);
  outline: none;
}

.form-control.is-invalid,
.form-select.is-invalid {
  border-color: #dc3545;
}

.invalid-feedback {
  display: block;
  font-size: 0.875rem;
  color: #dc3545;
  margin-top: 0.25rem;
}

/* Telinga Buttons */
.telinga-buttons {
  margin-top: 0.5rem;
}

.telinga-buttons .btn {
  border-radius: 8px;
  padding: 0.75rem 1rem;
  font-weight: 500;
  transition: all 0.3s ease;
  border-width: 2px;
}

.telinga-buttons .btn:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
}

.telinga-buttons .btn.active {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(66, 133, 244, 0.3);
}

/* Buttons */
.btn {
  border-radius: 8px;
  font-weight: 500;
  padding: 0.75rem 1.5rem;
  transition: all 0.3s ease;
}

.btn:hover {
  transform: translateY(-1px);
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.btn-primary {
  background: linear-gradient(135deg, #4285f4, #1a73e8);
  border: none;
}

.btn-primary:hover {
  background: linear-gradient(135deg, #1a73e8, #1557b0);
}

.btn-outline-primary {
  border-color: #4285f4;
  color: #4285f4;
  background: transparent;
}

.btn-outline-primary:hover {
  background-color: #4285f4;
  border-color: #4285f4;
  color: #fff;
}

.btn-outline-primary.active {
  background-color: #4285f4;
  border-color: #4285f4;
  color: #fff;
}

.btn-success {
  background: linear-gradient(135deg, #34a853, #137333);
  border: none;
}

.btn-success:hover {
  background: linear-gradient(135deg, #137333, #0d652d);
}

.btn-outline-secondary {
  border-color: #6c757d;
  color: #6c757d;
}

.btn-outline-secondary:hover {
  background-color: #6c757d;
  border-color: #6c757d;
  color: #fff;
}

.btn-lg {
  padding: 1rem 2rem;
  font-size: 1.1rem;
}

/* Back button in header */
.btn-light {
  background-color: rgba(255, 255, 255, 0.9);
  border-color: rgba(255, 255, 255, 0.3);
  color: #495057;
  font-weight: 500;
}

.btn-light:hover {
  background-color: #fff;
  border-color: #fff;
  color: #495057;
  transform: translateY(-1px);
}

.btn-sm {
  padding: 0.5rem 1rem;
  font-size: 0.875rem;
}

/* Modal styling */
.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: rgba(0, 0, 0, 0.5);
  display: flex;
  justify-content: center;
  align-items: center;
  z-index: 1050;
}

.modal-dialog {
  width: 100%;
  max-width: 500px;
  margin: 1rem;
}

.simple-modal {
  border-radius: 12px;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
  border: none;
  background-color: #fff;
}

.simple-modal .modal-header {
  background-color: #f8f9fa;
  border-bottom: 1px solid #dee2e6;
  padding: 1.5rem;
  border-radius: 12px 12px 0 0;
}

.simple-modal .modal-body {
  padding: 2rem;
}

.simple-modal .modal-footer {
  background-color: #f8f9fa;
  border-top: 1px solid #dee2e6;
  padding: 1.5rem;
  border-radius: 0 0 12px 12px;
}

/* Loading overlay */
.loading-overlay {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: rgba(255, 255, 255, 0.9);
  display: flex;
  justify-content: center;
  align-items: center;
  z-index: 1000;
}

.loading-content {
  text-align: center;
}

/* Badges */
.badge {
  font-size: 0.85rem;
  padding: 0.5rem 1rem;
  border-radius: 20px;
}

.bg-info {
  background-color: #17a2b8 !important;
}

/* Text colors */
.text-primary {
  color: #4285f4 !important;
}

.text-success {
  color: #28a745 !important;
}

.text-muted {
  color: #6c757d !important;
}

.text-danger {
  color: #dc3545 !important;
}

/* Utility classes */
.shadow {
  box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075) !important;
}

.shadow-sm {
  box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075) !important;
}

.fw-bold {
  font-weight: 700 !important;
}

/* Modal transitions */
.fade-enter-active, .fade-leave-active {
  transition: opacity 0.3s ease;
}

.fade-enter-from, .fade-leave-to {
  opacity: 0;
}

/* Animation for success icons */
@keyframes checkmark {
  0% {
    transform: scale(0);
    opacity: 0;
  }
  50% {
    transform: scale(1.2);
    opacity: 1;
  }
  100% {
    transform: scale(1);
    opacity: 1;
  }
}

.fas.fa-check-circle {
  animation: checkmark 0.8s ease-in-out;
}

/* Spinner animation */
@keyframes spin {
  0% { transform: rotate(0deg); }
  100% { transform: rotate(360deg); }
}

.spinner-border {
  animation: spin 1s linear infinite;
}

/* Responsive adjustments */
@media (max-width: 768px) {
  .penanganan-container {
    padding: 15px;
  }
  
  .card-body {
    padding: 1.5rem;
  }
  
  .header-section .card-body {
    padding: 1rem;
  }
  
  .row.align-items-center {
    text-align: center;
  }
  
  .d-flex.justify-content-between {
    flex-direction: column;
    align-items: stretch;
    gap: 1rem;
  }
  
  .btn-light {
    margin-top: 0.5rem;
  }
  
  .telinga-buttons .row {
    gap: 0.5rem;
  }
  
  .telinga-buttons .col-6,
  .telinga-buttons .col-12 {
    padding: 0.25rem;
  }
  
  .modal-dialog {
    margin: 0.5rem;
  }
  
  .simple-modal .modal-body {
    padding: 1.5rem;
  }
  
  .simple-modal .modal-header,
  .simple-modal .modal-footer {
    padding: 1rem;
  }
  
  .btn-lg {
    padding: 0.75rem 1.5rem;
    font-size: 1rem;
  }
  
  .d-flex.gap-2 {
    flex-direction: column;
    gap: 0.5rem !important;
  }
}

@media (max-width: 576px) {
  .penanganan-container {
    padding: 10px;
  }
  
  .card-header {
    padding: 1rem;
  }
  
  .card-body {
    padding: 1rem;
  }
  
  .form-group {
    margin-bottom: 1rem;
  }
  
  .form-control,
  .form-select {
    padding: 0.5rem 0.75rem;
    font-size: 0.9rem;
  }
  
  .btn {
    padding: 0.5rem 1rem;
    font-size: 0.9rem;
  }
  
  .btn-lg {
    padding: 0.75rem 1.25rem;
    font-size: 0.95rem;
  }
  
  .telinga-buttons .btn {
    padding: 0.5rem 0.75rem;
    font-size: 0.85rem;
  }
  
  .card-header h5 {
    font-size: 1.1rem;
  }
  
  .modal-dialog {
    margin: 0.25rem;
  }
  
  .simple-modal .modal-body h4 {
    font-size: 1.25rem;
  }
  
  .simple-modal .modal-body i {
    font-size: 48px !important;
  }
}

/* Loading animation */
.loading-overlay .loading-content {
  animation: fadeInUp 0.5s ease;
}

@keyframes fadeInUp {
  from {
    opacity: 0;
    transform: translateY(30px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

/* Form validation animation */
.form-control.is-invalid,
.form-select.is-invalid {
  animation: shake 0.5s ease-in-out;
}

@keyframes shake {
  0%, 20%, 40%, 60%, 80%, 100% {
    transform: translateX(0);
  }
  10%, 30%, 50%, 70%, 90% {
    transform: translateX(-5px);
  }
}

/* Enhanced modal animations */
.modal-overlay {
  animation: fadeIn 0.3s ease;
}

.modal-dialog {
  animation: slideInDown 0.3s ease;
}

@keyframes fadeIn {
  from { opacity: 0; }
  to { opacity: 1; }
}

@keyframes slideInDown {
  from {
    transform: translateY(-50px);
    opacity: 0;
  }
  to {
    transform: translateY(0);
    opacity: 1;
  }
}

/* Success icon animation */
.text-success.fas.fa-check-circle {
  animation: checkmark 0.8s ease-in-out;
}

@keyframes checkmark {
  0% {
    transform: scale(0);
    opacity: 0;
  }
  50% {
    transform: scale(1.2);
    opacity: 1;
  }
  100% {
    transform: scale(1);
    opacity: 1;
  }
}

/* Improved button states */
.btn:disabled {
  opacity: 0.6;
  cursor: not-allowed;
  transform: none !important;
}

.btn-success:disabled {
  background: linear-gradient(135deg, #6c757d, #5a6268);
}

/* Container max-width adjustments */
@media (min-width: 1200px) {
  .penanganan-container {
    max-width: 1200px;
  }
}

@media (min-width: 1400px) {
  .penanganan-container {
    max-width: 1300px;
  }
}
</style>