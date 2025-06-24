<template>
  <div class="container-fluid penanganan-container">
    <!-- Edit Section -->
    <div class="edit-section">
      <div class="card shadow">
        <div class="card-header bg-gradient text-black">
          <div class="d-flex justify-content-between align-items-center">
            <h5 class="mb-0">
              <i class="fas fa-edit me-2"></i>Edit Penanganan
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
          <form v-if="formData" @submit.prevent="submitForm">
            <div class="row">
              <!-- Column 1 -->
              <div class="col-lg-6">
                <!-- Informasi Pasien (Read Only) -->
                <div class="form-group mb-4">
                  <label class="form-label">
                    <i class="fas fa-user me-2 text-primary"></i>Nama Pasien
                  </label>
                  <div class="form-control-plaintext bg-light p-2 rounded">
                    {{ formData.patient_name }}
                    <span v-if="formData.patient_kode_akses" class="badge bg-secondary ms-2">
                      {{ formData.patient_kode_akses }}
                    </span>
                  </div>
                </div>

                <!-- Tanggal Penanganan -->
                <div class="form-group mb-4">
                  <label class="form-label required">
                    <i class="fas fa-calendar me-2 text-primary"></i>Tanggal Penanganan
                  </label>
                  <input
                    type="date"
                    v-model="formData.tanggal_penanganan"
                    class="form-control"
                    :class="{ 'is-invalid': errors.tanggal_penanganan }"
                    required
                  />
                  <div v-if="errors.tanggal_penanganan" class="invalid-feedback">
                    {{ errors.tanggal_penanganan }}
                  </div>
                </div>

                <!-- Keluhan -->
                <div class="form-group mb-4">
                  <label class="form-label required">
                    <i class="fas fa-exclamation-triangle me-2 text-warning"></i>Keluhan Pasien
                  </label>
                  <textarea
                    v-model="formData.keluhan"
                    class="form-control"
                    :class="{ 'is-invalid': errors.keluhan }"
                    rows="4"
                    placeholder="Masukkan keluhan pasien..."
                    required
                  ></textarea>
                  <div v-if="errors.keluhan" class="invalid-feedback">
                    {{ errors.keluhan }}
                  </div>
                </div>

                <!-- Riwayat Penyakit -->
                <div class="form-group mb-4">
                  <label class="form-label">
                    <i class="fas fa-history me-2 text-info"></i>Riwayat Penyakit Sebelumnya
                  </label>
                  <textarea
                    v-model="formData.riwayat_penyakit"
                    class="form-control"
                    :class="{ 'is-invalid': errors.riwayat_penyakit }"
                    rows="3"
                    placeholder="Masukkan riwayat penyakit (opsional)..."
                  ></textarea>
                  <div v-if="errors.riwayat_penyakit" class="invalid-feedback">
                    {{ errors.riwayat_penyakit }}
                  </div>
                </div>
              </div>

              <!-- Column 2 -->
              <div class="col-lg-6">
                <!-- Diagnosis Manual -->
                <div class="form-group mb-4">
                  <label class="form-label required">
                    <i class="fas fa-diagnoses me-2 text-success"></i>Diagnosis Manual
                  </label>
                  <textarea
                    v-model="formData.diagnosis_manual"
                    class="form-control"
                    :class="{ 'is-invalid': errors.diagnosis_manual }"
                    rows="4"
                    placeholder="Masukkan diagnosis manual..."
                    required
                  ></textarea>
                  <div v-if="errors.diagnosis_manual" class="invalid-feedback">
                    {{ errors.diagnosis_manual }}
                  </div>
                </div>

                <!-- Telinga yang Terkena -->
                <div class="form-group mb-4">
                  <label class="form-label required">
                    <i class="fas fa-deaf me-2 text-danger"></i>Telinga yang Terkena
                  </label>
                  <select
                    v-model="formData.telinga_terkena"
                    class="form-select"
                    :class="{ 'is-invalid': errors.telinga_terkena }"
                    required
                  >
                    <option value="">Pilih telinga yang terkena</option>
                    <option value="kiri">Telinga Kiri</option>
                    <option value="kanan">Telinga Kanan</option>
                    <option value="keduanya">Kedua Telinga</option>
                  </select>
                  <div v-if="errors.telinga_terkena" class="invalid-feedback">
                    {{ errors.telinga_terkena }}
                  </div>
                </div>

                <!-- Tindakan -->
                <div class="form-group mb-4">
                  <label class="form-label required">
                    <i class="fas fa-hand-holding-medical me-2 text-primary"></i>Tindakan yang Diberikan
                  </label>
                  <textarea
                    v-model="formData.tindakan"
                    class="form-control"
                    :class="{ 'is-invalid': errors.tindakan }"
                    rows="4"
                    placeholder="Masukkan tindakan yang diberikan..."
                    required
                  ></textarea>
                  <div v-if="errors.tindakan" class="invalid-feedback">
                    {{ errors.tindakan }}
                  </div>
                </div>

                <!-- Informasi Tambahan (Read Only) -->
                <div class="form-group mb-4">
                  <label class="form-label">
                    <i class="fas fa-info-circle me-2 text-muted"></i>Informasi Tambahan
                  </label>
                  <div class="form-control-plaintext bg-light p-2 rounded">
                    <small class="text-muted">
                      <div class="mb-1">
                        <strong>Dibuat:</strong> {{ formatDateTime(formData.created_at) }}
                      </div>
                      <div>
                        <strong>Terakhir diperbarui:</strong> {{ formatDateTime(formData.updated_at) }}
                      </div>
                    </small>
                  </div>
                </div>
              </div>
            </div>

            <!-- Action Buttons -->
            <div class="row mt-4">
              <div class="col-12">
                <div class="d-flex justify-content-end gap-2">
                  <button 
                    type="button" 
                    @click="navigateToList" 
                    class="btn btn-outline-secondary"
                    :disabled="isSubmitting"
                  >
                    <i class="fas fa-arrow-left me-1"></i>Kembali
                  </button>
                  <button 
                    type="submit" 
                    class="btn btn-primary"
                    :disabled="isSubmitting"
                  >
                    <span v-if="isSubmitting">
                      <i class="fas fa-spinner fa-spin me-1"></i>Menyimpan...
                    </span>
                    <span v-else>
                      <i class="fas fa-save me-1"></i>Simpan
                    </span>
                  </button>
                </div>
              </div>
            </div>
          </form>
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

    <!-- Success Modal -->
    <transition name="fade">
      <div v-if="showSuccessModal" class="modal-overlay" @click.self="closeSuccessModal">
        <div class="modal-dialog">
          <div class="modal-content simple-modal">
            <div class="modal-header">
              <h5 class="modal-title">Berhasil</h5>
              <button type="button" class="btn-close" @click="closeSuccessModal"></button>
            </div>
            <div class="modal-body text-center py-4">
              <i class="fas fa-check-circle text-success" style="font-size: 64px;"></i>
              <h4 class="mt-3 text-success">Data Berhasil Disimpan</h4>
              <p class="mb-0">Data penanganan telah berhasil diperbarui.</p>
            </div>
            <div class="modal-footer">
              <button class="btn btn-primary" @click="navigateToView">
                <i class="fas fa-eye me-1"></i>Lihat Detail
              </button>
              <button class="btn btn-outline-primary" @click="navigateToList">
                <i class="fas fa-list me-1"></i>Kembali ke Daftar
              </button>
            </div>
          </div>
        </div>
      </div>
    </transition>

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
              <button class="btn btn-primary" @click="closeErrorModal">
                <i class="fas fa-check me-1"></i>OK
              </button>
            </div>
          </div>
        </div>
      </div>
    </transition>
  </div>
</template>

<script>
import { ref, onMounted, computed, reactive } from 'vue';
import { useRouter, useRoute } from 'vue-router';
import AppLayout from '@/components/AppLayout.vue';
import api from '@/api';
import axios from 'axios';
import { jwtDecode } from "jwt-decode";

export default {
  name: 'EditPenanganan',
  components: { AppLayout },
  setup() {
    const router = useRouter();
    const route = useRoute();
    
    // Data
    const formData = ref(null);
    const isLoading = ref(false);
    const isSubmitting = ref(false);
    const showSuccessModal = ref(false);
    const showErrorModal = ref(false);
    const errorMessage = ref('');
    const userRole = ref('pasien');
    
    // Form validation errors
    const errors = reactive({
      tanggal_penanganan: '',
      keluhan: '',
      riwayat_penyakit: '',
      diagnosis_manual: '',
      telinga_terkena: '',
      tindakan: ''
    });

    // Get penanganan ID from route params
    const penangananId = computed(() => route.params.id);

    // Navigation functions
    const navigateToList = () => {
      router.push({ name: 'list-penanganan' });
    };

    const navigateToView = () => {
      router.push({ name: 'view-penanganan', params: { id: penangananId.value } });
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

    // Format datetime function
    const formatDateTime = (dateTimeString) => {
      if (!dateTimeString) return '-';
      const date = new Date(dateTimeString);
      return date.toLocaleDateString('id-ID', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
      });
    };

    // Clear all validation errors
    const clearErrors = () => {
      Object.keys(errors).forEach(key => {
        errors[key] = '';
      });
    };

    // Validate form
    const validateForm = () => {
      clearErrors();
      let isValid = true;

      if (!formData.value.tanggal_penanganan) {
        errors.tanggal_penanganan = 'Tanggal penanganan harus diisi';
        isValid = false;
      }

      if (!formData.value.keluhan || formData.value.keluhan.trim() === '') {
        errors.keluhan = 'Keluhan pasien harus diisi';
        isValid = false;
      }

      if (!formData.value.diagnosis_manual || formData.value.diagnosis_manual.trim() === '') {
        errors.diagnosis_manual = 'Diagnosis manual harus diisi';
        isValid = false;
      }

      if (!formData.value.telinga_terkena) {
        errors.telinga_terkena = 'Telinga yang terkena harus dipilih';
        isValid = false;
      }

      if (!formData.value.tindakan || formData.value.tindakan.trim() === '') {
        errors.tindakan = 'Tindakan yang diberikan harus diisi';
        isValid = false;
      }

      return isValid;
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

        const data = response.data.data;
        
        // Format date for input[type="date"]
        const tanggalPenanganan = data.tanggal_penanganan ? 
          new Date(data.tanggal_penanganan).toISOString().split('T')[0] : '';

        formData.value = {
          ...data,
          tanggal_penanganan: tanggalPenanganan,
          riwayat_penyakit: data.riwayat_penyakit || ''
        };
        
      } catch (error) {
        console.error('Error fetching penanganan data:', error);
        
        if (error.response) {
          if (error.response.status === 404) {
            errorMessage.value = 'Data penanganan tidak ditemukan';
          } else if (error.response.status === 403) {
            errorMessage.value = 'Anda tidak memiliki akses untuk mengedit data ini';
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

    // Submit form
    const submitForm = async () => {
      if (!validateForm()) {
        return;
      }

      try {
        isSubmitting.value = true;
        const token = localStorage.getItem("token") || sessionStorage.getItem("token");

        if (!token) {
          throw new Error('Token tidak ditemukan');
        }

        const updateData = {
          tanggal_penanganan: formData.value.tanggal_penanganan,
          keluhan: formData.value.keluhan.trim(),
          riwayat_penyakit: formData.value.riwayat_penyakit ? formData.value.riwayat_penyakit.trim() : null,
          diagnosis_manual: formData.value.diagnosis_manual.trim(),
          telinga_terkena: formData.value.telinga_terkena,
          tindakan: formData.value.tindakan.trim()
        };

        await axios.put(
          `${api.getEndpoint('penanganan')}/${penangananId.value}`,
          updateData,
          {
            headers: { 
              Authorization: `Bearer ${token}`,
              'Content-Type': 'application/json'
            },
          }
        );

        showSuccessModal.value = true;
        
      } catch (error) {
        console.error('Error updating penanganan:', error);
        
        if (error.response) {
          if (error.response.status === 422) {
            // Handle validation errors from server
            const validationErrors = error.response.data.errors || {};
            Object.keys(validationErrors).forEach(key => {
              if (errors.hasOwnProperty(key)) {
                errors[key] = validationErrors[key][0] || 'Field ini tidak valid';
              }
            });
            errorMessage.value = 'Terdapat kesalahan pada form. Silakan periksa kembali.';
          } else if (error.response.status === 404) {
            errorMessage.value = 'Data penanganan tidak ditemukan';
          } else if (error.response.status === 403) {
            errorMessage.value = 'Anda tidak memiliki akses untuk mengedit data ini';
          } else {
            errorMessage.value = error.response.data?.message || 'Gagal menyimpan data penanganan';
          }
        } else {
          errorMessage.value = 'Gagal menyimpan data. Periksa koneksi internet Anda.';
        }
        
        showErrorModal.value = true;
      } finally {
        isSubmitting.value = false;
      }
    };

    // Close modals
    const closeSuccessModal = () => {
      showSuccessModal.value = false;
    };

    const closeErrorModal = () => {
      showErrorModal.value = false;
    };

    // Check authorization
    const checkAuthorization = () => {
      const role = getUserRoleFromToken();
      if (role !== 'dokter') {
        errorMessage.value = 'Hanya dokter yang dapat mengedit data penanganan';
        showErrorModal.value = true;
        return false;
      }
      return true;
    };

    // Mount lifecycle
    onMounted(async () => {
      // Set user role from JWT
      userRole.value = getUserRoleFromToken();
      
      // Check authorization
      if (!checkAuthorization()) {
        return;
      }
      
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
      formData,
      isLoading,
      isSubmitting,
      showSuccessModal,
      showErrorModal,
      errorMessage,
      errors,
      navigateToList,
      navigateToView,
      formatDateTime,
      submitForm,
      closeSuccessModal,
      closeErrorModal
    };
  }
};
</script>

<style scoped>
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

.form-label.required::after {
  content: ' *';
  color: #dc3545;
}

.form-control,
.form-select {
  border: 1px solid #ced4da;
  border-radius: 0.375rem;
  transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
}

.form-control:focus,
.form-select:focus {
  border-color: #86b7fe;
  outline: 0;
  box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
}

.form-control.is-invalid,
.form-select.is-invalid {
  border-color: #dc3545;
}

.form-control.is-invalid:focus,
.form-select.is-invalid:focus {
  border-color: #dc3545;
  box-shadow: 0 0 0 0.25rem rgba(220, 53, 69, 0.25);
}

.invalid-feedback {
  width: 100%;
  margin-top: 0.25rem;
  font-size: 0.875rem;
  color: #dc3545;
}

.form-control-plaintext {
  font-size: 1rem;
  color: #212529;
  min-height: calc(1.5em + 0.75rem + 2px);
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

.badge {
  font-size: 0.85em;
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
</style>