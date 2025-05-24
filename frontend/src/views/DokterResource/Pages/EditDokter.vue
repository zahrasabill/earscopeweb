<template>
  <div class="container mt-4">
    <div class="card">
      <div class="card-header bg-warning text-dark">
        <h4>Edit Data Dokter</h4>
      </div>
      <div class="card-body">
        <div v-if="loading" class="text-center my-5">
          <div class="spinner-border text-warning" role="status">
            <span class="visually-hidden">Loading...</span>
          </div>
          <p class="mt-2">Memuat data dokter...</p>
        </div>
        
        <div v-else-if="error" class="alert alert-danger">
          <i class="bi bi-exclamation-triangle-fill me-2"></i>
          {{ error }}
        </div>
        
        <form v-else @submit.prevent="updateDokter">
          <div class="row">
            <div class="col-md-6">
              <div class="card mb-4">
                <div class="card-header bg-light">
                  <h5 class="mb-0">Informasi Pribadi</h5>
                </div>
                <div class="card-body">
                  <div class="mb-3">
                    <label for="name" class="form-label fw-bold">Nama Lengkap <span class="text-danger">*</span></label>
                    <input 
                      type="text" 
                      class="form-control" 
                      id="name"
                      v-model="formData.name"
                      :class="{ 'is-invalid': errors.name }"
                      required
                    />
                    <div v-if="errors.name" class="invalid-feedback">
                      {{ errors.name }}
                    </div>
                  </div>
                  
                  <div class="mb-3">
                    <label for="tanggal_lahir" class="form-label fw-bold">Tanggal Lahir <span class="text-danger">*</span></label>
                    <input 
                      type="date" 
                      class="form-control" 
                      id="tanggal_lahir"
                      v-model="formData.tanggal_lahir"
                      :class="{ 'is-invalid': errors.tanggal_lahir }"
                      required
                    />
                    <div v-if="errors.tanggal_lahir" class="invalid-feedback">
                      {{ errors.tanggal_lahir }}
                    </div>
                  </div>
                  
                  <div class="mb-3">
                    <label for="no_telp" class="form-label fw-bold">Nomor Telepon <span class="text-danger">*</span></label>
                    <div class="input-group">
                      <span class="input-group-text">+62</span>
                      <input 
                        type="tel" 
                        class="form-control" 
                        id="no_telp"
                        v-model="formData.no_telp"
                        :class="{ 'is-invalid': errors.no_telp }"
                        placeholder="81234567890"
                        pattern="[0-9]{10,13}"
                        required
                      />
                      <div v-if="errors.no_telp" class="invalid-feedback">
                        {{ errors.no_telp }}
                      </div>
                    </div>
                    <small class="form-text text-muted">Masukkan nomor tanpa awalan +62</small>
                  </div>
                  
                  <div class="mb-3">
                    <label for="nomor_str" class="form-label fw-bold">Nomor STR</label>
                    <input 
                      type="text" 
                      class="form-control" 
                      id="nomor_str"
                      v-model="formData.nomor_str"
                      :class="{ 'is-invalid': errors.nomor_str }"
                      placeholder="Masukkan nomor STR (opsional)"
                    />
                    <div v-if="errors.nomor_str" class="invalid-feedback">
                      {{ errors.nomor_str }}
                    </div>
                  </div>
                  
                  <div class="mb-3">
                    <label for="gender" class="form-label fw-bold">Gender <span class="text-danger">*</span></label>
                    <select 
                      class="form-select" 
                      id="gender"
                      v-model="formData.gender"
                      :class="{ 'is-invalid': errors.gender }"
                      required
                    >
                      <option value="">Pilih Gender</option>
                      <option value="laki-laki">Laki-laki</option>
                      <option value="perempuan">Perempuan</option>
                    </select>
                    <div v-if="errors.gender" class="invalid-feedback">
                      {{ errors.gender }}
                    </div>
                  </div>
                </div>
              </div>
            </div>
            
            <div class="col-md-6">
              <div class="card mb-4">
                <div class="card-header bg-light">
                  <h5 class="mb-0">Informasi Akun</h5>
                </div>
                <div class="card-body">
                  <div class="mb-3">
                    <label for="kode_akses" class="form-label fw-bold">Kode Akses</label>
                    <input 
                      type="text" 
                      class="form-control" 
                      id="kode_akses"
                      v-model="formData.kode_akses"
                      :class="{ 'is-invalid': errors.kode_akses }"
                      placeholder="Akan dibuat otomatis jika kosong"
                    />
                    <div v-if="errors.kode_akses" class="invalid-feedback">
                      {{ errors.kode_akses }}
                    </div>
                    <small class="form-text text-muted">Biarkan kosong untuk membuat kode akses baru secara otomatis</small>
                  </div>
                  
                  <div class="mb-3" v-if="formData.role">
                    <label for="role" class="form-label fw-bold">Role</label>
                    <input 
                      type="text" 
                      class="form-control" 
                      id="role"
                      v-model="formData.role"
                      readonly
                      disabled
                    />
                    <small class="form-text text-muted">Role tidak dapat diubah</small>
                  </div>
                  
                  <div class="mb-3" v-if="originalData.created_at">
                    <label class="form-label fw-bold">Terdaftar Pada:</label>
                    <p class="form-control-plaintext">{{ formatDateTime(originalData.created_at) }}</p>
                  </div>
                </div>
              </div>
              
              <div class="card mb-4">
                <div class="card-header bg-danger text-white">
                  <h5 class="mb-0">Reset Password</h5>
                </div>
                <div class="card-body">
                  <div class="alert alert-warning">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i>
                    <strong>Perhatian:</strong> Reset password akan menghasilkan password baru secara otomatis dan tidak dapat dikembalikan.
                  </div>
                  
                  <button 
                    type="button" 
                    class="btn btn-danger"
                    @click="showResetPasswordModal"
                    :disabled="updateLoading || resetPasswordLoading"
                  >
                    <span v-if="resetPasswordLoading" class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>
                    <i v-else class="bi bi-key-fill me-1"></i>
                    {{ resetPasswordLoading ? 'Mereset...' : 'Reset Password' }}
                  </button>
                </div>
              </div>
            </div>
          </div>
          
          <div class="d-flex justify-content-between mt-4">
            <button type="button" class="btn btn-secondary" @click="$router.back()">
              <i class="bi bi-arrow-left me-1"></i> Kembali
            </button>
            
            <div>
              <button 
                type="button" 
                class="btn btn-outline-warning me-2" 
                @click="resetForm"
                :disabled="updateLoading"
              >
                <i class="bi bi-arrow-clockwise me-1"></i> Reset Form
              </button>
              <button 
                type="submit" 
                class="btn btn-warning"
                :disabled="updateLoading"
              >
                <span v-if="updateLoading" class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>
                <i v-else class="bi bi-check-lg me-1"></i>
                {{ updateLoading ? 'Menyimpan...' : 'Simpan Perubahan' }}
              </button>
            </div>
          </div>
        </form>
      </div>
    </div>
    
    <!-- Modal Konfirmasi Simpan -->
    <div class="modal fade" id="confirmSaveModal" tabindex="-1" aria-labelledby="confirmSaveModalLabel" aria-hidden="true">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header bg-warning text-dark">
            <h5 class="modal-title" id="confirmSaveModalLabel">Konfirmasi Perubahan</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <p>Apakah Anda yakin ingin menyimpan perubahan data dokter <strong>{{ formData.name }}</strong>?</p>
            <div v-if="hasChanges" class="mt-3">
              <h6>Perubahan yang akan disimpan:</h6>
              <ul class="list-unstyled">
                <li v-for="change in getChanges()" :key="change.field" class="mb-1">
                  <span class="fw-bold">{{ change.label }}:</span><br>
                  <small class="text-muted">Dari: {{ change.oldValue || '-' }}</small><br>
                  <small class="text-success">Ke: {{ change.newValue || '-' }}</small>
                </li>
              </ul>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
            <button type="button" class="btn btn-warning" @click="confirmSave" :disabled="updateLoading">
              <span v-if="updateLoading" class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>
              Simpan Perubahan
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal Konfirmasi Reset Password -->
    <div class="modal fade" id="resetPasswordModal" tabindex="-1" aria-labelledby="resetPasswordModalLabel" aria-hidden="true">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header bg-danger text-white">
            <h5 class="modal-title" id="resetPasswordModalLabel">Konfirmasi Reset Password</h5>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <div class="alert alert-danger">
              <i class="bi bi-exclamation-triangle-fill me-2"></i>
              <strong>Peringatan!</strong> Tindakan ini tidak dapat dibatalkan.
            </div>
            <p>Apakah Anda yakin ingin mereset password untuk dokter <strong>{{ formData.name }}</strong>?</p>
            <p>Password baru akan dibuat secara otomatis dan akan ditampilkan setelah proses reset selesai.</p>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
            <button type="button" class="btn btn-danger" @click="confirmResetPassword" :disabled="resetPasswordLoading">
              <span v-if="resetPasswordLoading" class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>
              Ya, Reset Password
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal Hasil Reset Password -->
    <div class="modal fade" id="resetPasswordResultModal" tabindex="-1" aria-labelledby="resetPasswordResultModalLabel" 
         aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header bg-success text-white">
            <h5 class="modal-title" id="resetPasswordResultModalLabel">Password Berhasil Direset</h5>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <div class="alert alert-warning">
              <i class="bi bi-exclamation-triangle-fill me-2"></i>
              <strong>Penting!</strong> Silahkan catat informasi di bawah ini. Informasi ini hanya ditampilkan sekali.
            </div>
            <div class="mb-3">
              <label class="form-label fw-bold">Kode Akses:</label>
              <div class="input-group">
                <input type="text" class="form-control" readonly :value="newCredentials.kodeAkses" ref="newKodeAksesInput" />
                <button class="btn btn-outline-secondary" type="button" @click="copyToClipboard('newKodeAkses')">
                  <i class="bi bi-clipboard"></i>
                </button>
              </div>
            </div>
            <div class="mb-3">
              <label class="form-label fw-bold">Password Baru:</label>
              <div class="input-group">
                <input type="text" class="form-control" readonly :value="newCredentials.password" ref="newPasswordInput" />
                <button class="btn btn-outline-secondary" type="button" @click="copyToClipboard('newPassword')">
                  <i class="bi bi-clipboard"></i>
                </button>
              </div>
            </div>
            <div class="d-grid gap-2 mt-4">
              <button class="btn btn-primary" @click="printNewCredentials">
                <i class="bi bi-printer me-1"></i> Cetak Informasi
              </button>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-success" data-bs-dismiss="modal">
              <i class="bi bi-check-lg me-1"></i> Selesai
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Hidden iframe for printing -->
    <iframe id="printFrame" style="display:none;"></iframe>

    <!-- Hidden div for printing new credentials -->
    <div id="printNewCredentialsArea" class="d-none">
      <div style="padding: 20px; font-family: Arial, sans-serif;">
        <h2 style="text-align: center; margin-bottom: 20px;">Password Reset - Informasi Akun Dokter</h2>
        <div style="border: 1px solid #ccc; padding: 15px; margin-bottom: 20px;">
          <h4>Dokter: {{ formData.name }}</h4>
          <p><strong>No. STR:</strong> {{ formData.nomor_str || '-' }}</p>
          <p><strong>Tanggal Lahir:</strong> {{ formatDateForDisplay(formData.tanggal_lahir) }}</p>
          <p><strong>Nomor Telepon:</strong> +62{{ formData.no_telp }}</p>
          <p><strong>Gender:</strong> {{ formData.gender }}</p>
        </div>
        <div style="border: 2px dashed #dc3545; padding: 15px; background-color: #f8f9fa;">
          <h4 style="color: #dc3545;">Kredensial Login Baru</h4>
          <p><strong>Kode Akses:</strong> {{ newCredentials.kodeAkses }}</p>
          <p><strong>Password Baru:</strong> {{ newCredentials.password }}</p>
          <p style="font-style: italic; color: #dc3545; margin-top: 15px;">
            Penting: Password telah direset pada {{ new Date().toLocaleString('id-ID') }}. Harap simpan dengan aman.
          </p>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { Modal } from 'bootstrap';
import api from '@/api';

export default {
  name: 'EditDokter',
  data() {
    return {
      originalData: {},
      formData: {
        name: '',
        tanggal_lahir: '',
        no_telp: '',
        nomor_str: '',
        gender: '',
        kode_akses: '',
        role: ''
      },
      newCredentials: {
        kodeAkses: '',
        password: ''
      },
      loading: true,
      updateLoading: false,
      resetPasswordLoading: false,
      error: null,
      errors: {},
      confirmModal: null,
      resetPasswordModal: null,
      resetPasswordResultModal: null
    };
  },
  computed: {
    hasChanges() {
      const fields = ['name', 'tanggal_lahir', 'no_telp', 'nomor_str', 'gender', 'kode_akses'];
      return fields.some(field => {
        const originalValue = this.originalData[field] || '';
        const formValue = this.formData[field] || '';
        return originalValue !== formValue;
      });
    }
  },
  watch: {
    '$route.params.id': function(newId) {
      if (newId) {
        this.fetchDokter();
      } else {
        this.error = 'ID dokter tidak ditemukan pada URL';
        this.loading = false;
      }
    }
  },
  created() {
    if (this.$route.params.id) {
      this.fetchDokter();
    } else {
      this.error = 'ID dokter tidak ditemukan pada URL';
      this.loading = false;
    }
  },
  mounted() {
    this.initializeModals();
  },
  beforeUnmount() {
    // Cleanup modals
    if (this.confirmModal) {
      this.confirmModal.dispose();
    }
    if (this.resetPasswordModal) {
      this.resetPasswordModal.dispose();
    }
    if (this.resetPasswordResultModal) {
      this.resetPasswordResultModal.dispose();
    }
  },
  methods: {
    initializeModals() {
      // Initialize Bootstrap modals
      this.confirmModal = new Modal(document.getElementById('confirmSaveModal'));
      this.resetPasswordModal = new Modal(document.getElementById('resetPasswordModal'));
      this.resetPasswordResultModal = new Modal(document.getElementById('resetPasswordResultModal'));
    },

    getAuthToken() {
      const token = localStorage.getItem("token") || sessionStorage.getItem("token");
      if (!token) {
        throw new Error('Token autentikasi tidak ditemukan');
      }
      return token;
    },
    
    async fetchDokter() {
      this.loading = true;
      this.error = null;
      
      try {
        if (!this.$route.params.id) {
          throw new Error('ID dokter tidak ditemukan pada URL');
        }
        
        const dokterId = this.$route.params.id;
        const token = this.getAuthToken();
        
        const response = await api.get(`users/${dokterId}`, {
          headers: {
            'Authorization': `Bearer ${token}`
          }
        });
        
        if (response.data) {
          this.originalData = { ...response.data };
          
          // Format tanggal untuk input date
          const formattedData = { ...response.data };
          if (formattedData.tanggal_lahir) {
            formattedData.tanggal_lahir = this.formatDateForInput(formattedData.tanggal_lahir);
          }
          
          this.formData = {
            name: formattedData.name || '',
            tanggal_lahir: formattedData.tanggal_lahir || '',
            no_telp: formattedData.no_telp || '',
            nomor_str: formattedData.nomor_str || '',
            gender: formattedData.gender || '',
            kode_akses: formattedData.kode_akses || '',
            role: formattedData.role || ''
          };
        } else {
          throw new Error('Format response tidak sesuai');
        }
      } catch (err) {
        console.error('Error saat memuat data dokter:', err);
        
        if (err.response) {
          if (err.response.status === 401) {
            this.error = 'Sesi login Anda telah berakhir. Silakan login kembali.';
            setTimeout(() => {
              this.$router.push('/login');
            }, 2000);
          } else if (err.response.status === 404) {
            this.error = 'Dokter tidak ditemukan. ID dokter mungkin tidak valid.';
          } else {
            this.error = err.response.data?.message || 
                        'Terjadi kesalahan saat memuat data dokter';
          }
        } else {
          this.error = err.message || 'Terjadi kesalahan saat memuat data dokter';
        }
      } finally {
        this.loading = false;
      }
    },
    
    formatDateForInput(dateString) {
      if (!dateString) return '';
      
      try {
        const date = new Date(dateString);
        const year = date.getFullYear();
        const month = String(date.getMonth() + 1).padStart(2, '0');
        const day = String(date.getDate()).padStart(2, '0');
        return `${year}-${month}-${day}`;
      } catch (error) {
        return dateString;
      }
    },
    
    formatDateTime(dateTimeString) {
      if (!dateTimeString) return '-';
      
      try {
        const date = new Date(dateTimeString);
        return date.toLocaleDateString('id-ID', {
          day: 'numeric',
          month: 'long',
          year: 'numeric',
          hour: '2-digit',
          minute: '2-digit'
        });
      } catch (error) {
        return dateTimeString;
      }
    },

    formatDateForDisplay(dateString) {
      if (!dateString) return '';
      
      try {
        const date = new Date(dateString);
        return date.toLocaleDateString('id-ID', {
          day: 'numeric',
          month: 'long',
          year: 'numeric'
        });
      } catch (error) {
        return dateString;
      }
    },
    
    resetForm() {
      // Reset form data ke nilai asli
      const formattedData = { ...this.originalData };
      if (formattedData.tanggal_lahir) {
        formattedData.tanggal_lahir = this.formatDateForInput(formattedData.tanggal_lahir);
      }
      
      this.formData = {
        name: formattedData.name || '',
        tanggal_lahir: formattedData.tanggal_lahir || '',
        no_telp: formattedData.no_telp || '',
        nomor_str: formattedData.nomor_str || '',
        gender: formattedData.gender || '',
        kode_akses: formattedData.kode_akses || '',
        role: formattedData.role || ''
      };
      
      // Clear errors
      this.errors = {};
    },
    
    validateForm() {
      this.errors = {};
      
      // Validasi nama
      if (!this.formData.name || this.formData.name.trim().length < 2) {
        this.errors.name = 'Nama lengkap harus diisi minimal 2 karakter';
      }
      
      // Validasi tanggal lahir
      if (!this.formData.tanggal_lahir) {
        this.errors.tanggal_lahir = 'Tanggal lahir harus diisi';
      } else {
        const birthDate = new Date(this.formData.tanggal_lahir);
        const today = new Date();
        const age = today.getFullYear() - birthDate.getFullYear();
        
        if (age < 18 || age > 100) {
          this.errors.tanggal_lahir = 'Usia harus antara 18-100 tahun';
        }
      }
      
      // Validasi nomor telepon
      if (!this.formData.no_telp) {
        this.errors.no_telp = 'Nomor telepon harus diisi';
      } else if (!/^[0-9]{10,13}$/.test(this.formData.no_telp)) {
        this.errors.no_telp = 'Nomor telepon harus berupa angka 10-13 digit';
      }
      
      // Validasi gender
      if (!this.formData.gender) {
        this.errors.gender = 'Gender harus dipilih';
      }
      
      return Object.keys(this.errors).length === 0;
    },
    
    getChanges() {
      const changes = [];
      const fieldLabels = {
        name: 'Nama Lengkap',
        tanggal_lahir: 'Tanggal Lahir',
        no_telp: 'Nomor Telepon', 
        nomor_str: 'Nomor STR',
        gender: 'Gender',
        kode_akses: 'Kode Akses'
      };
      
      Object.keys(fieldLabels).forEach(field => {
        const originalValue = this.originalData[field] || '';
        const formValue = this.formData[field] || '';
        
        if (originalValue !== formValue) {
          changes.push({
            field,
            label: fieldLabels[field],
            oldValue: field === 'tanggal_lahir' ? this.formatDateForDisplay(originalValue) : originalValue,
            newValue: field === 'tanggal_lahir' ? this.formatDateForDisplay(formValue) : formValue
          });
        }
      });
      
      return changes;
    },
    
    updateDokter() {
      // Validasi form
      if (!this.validateForm()) {
        return;
      }
      
      // Cek apakah ada perubahan
      if (!this.hasChanges) {
        alert('Tidak ada perubahan untuk disimpan');
        return;
      }
      
      // Tampilkan modal konfirmasi
      if (!this.confirmModal) {
        this.initializeModals();
      }
      this.confirmModal.show();
    },
    
    async confirmSave() {
      this.updateLoading = true;
      
      try {
        const token = this.getAuthToken();
        
        if (!this.$route.params.id) {
          throw new Error('ID dokter tidak valid');
        }
        
        // Siapkan data untuk dikirim
        const updateData = {
          name: this.formData.name.trim(),
          tanggal_lahir: this.formData.tanggal_lahir,
          no_telp: this.formData.no_telp,
          nomor_str: this.formData.nomor_str || null,
          gender: this.formData.gender,
          kode_akses: this.formData.kode_akses || null
        };
        
        // Kirim update ke backend
        const response = await api.put(`users/${this.$route.params.id}`, updateData, {
          headers: {
            'Authorization': `Bearer ${token}`,
            'Content-Type': 'application/json'
          }
        });
        
        // Tutup modal
        this.confirmModal.hide();
        
        // Update data asli dengan data baru
        this.originalData = { ...this.originalData, ...updateData };
        
        // Tampilkan notifikasi sukses
        alert('Data dokter berhasil diperbarui');
        
        // Refresh data
        await this.fetchDokter();
        
      } catch (err) {
        console.error('Error saat memperbarui data dokter:', err);
        
        // Tutup modal
        this.confirmModal.hide();
        
        if (err.response) {
          if (err.response.status === 401) {
            alert('Sesi login Anda telah berakhir. Silakan login kembali.');
            this.$router.push('/login');
          } else if (err.response.status === 422) {
            // Validation errors
            const validationErrors = err.response.data.errors || {};
            this.errors = validationErrors;
            alert('Terdapat kesalahan pada data yang diisi. Silakan periksa kembali.');
          } else {
            const errorMessage = err.response.data?.message || 'Terjadi kesalahan saat memperbarui data';
            alert(`Gagal memperbarui data: ${errorMessage}`);
          }
        } else {
          alert(`Gagal memperbarui data: ${err.message}`);
        }
      } finally {
        this.updateLoading = false;
      }
    },

    showResetPasswordModal() {
      if (!this.resetPasswordModal) {
        this.initializeModals();
      }
      this.resetPasswordModal.show();
    },

    async confirmResetPassword() {
      this.resetPasswordLoading = true;

      try {
        const token = this.getAuthToken();
        
        if (!this.$route.params.id) {
          throw new Error('ID dokter tidak valid');
        }

        // Panggil API untuk reset password
        const response = await api.post(`users/${this.$route.params.id}/reset-password`, {}, {
          headers: {
            'Authorization': `Bearer ${token}`,
            'Content-Type': 'application/json'
          }
        });

        // Simpan credentials baru dari response
        if (response.data && response.data.data) {
          this.newCredentials.kodeAkses = response.data.data.kode_akses || '';
          this.newCredentials.password = response.data.data.password || '';

          // Update form data dengan kode akses baru
          this.formData.kode_akses = this.newCredentials.kodeAkses;
          this.originalData.kode_akses = this.newCredentials.kodeAkses;

          // Tutup modal konfirmasi reset
          this.resetPasswordModal.hide();

          // Tampilkan modal hasil reset password
          setTimeout(() => {
            if (!this.resetPasswordResultModal) {
              this.initializeModals();
            }
            this.resetPasswordResultModal.show();
          }, 300);

        } else {
          throw new Error('Response tidak mengandung data credentials baru');
        }

      } catch (err) {
        console.error('Error saat reset password:', err);
        
        // Tutup modal
        this.resetPasswordModal.hide();
        
        if (err.response) {
          if (err.response.status === 401) {
            alert('Sesi login Anda telah berakhir. Silakan login kembali.');
            this.$router.push('/login');
          } else {
            const errorMessage = err.response.data?.message || 'Terjadi kesalahan saat reset password';
            alert(`Gagal reset password: ${errorMessage}`);
          }
        } else {
          alert(`Gagal reset password: ${err.message}`);
        }
      } finally {
        this.resetPasswordLoading = false;
      }
    },

    copyToClipboard(type) {
      let textToCopy = '';
      let inputRef = null;

      if (type === 'newKodeAkses') {
        textToCopy = this.newCredentials.kodeAkses;
        inputRef = this.$refs.newKodeAksesInput;
      } else if (type === 'newPassword') {
        textToCopy = this.newCredentials.password;
        inputRef = this.$refs.newPasswordInput;
      }

      if (textToCopy && inputRef) {
        // Select the text in the input
        inputRef.select();
        inputRef.setSelectionRange(0, 99999); // For mobile devices

        try {
          // Copy to clipboard
          document.execCommand('copy');
          
          // Show temporary feedback
          const button = event.target.closest('button');
          const originalHTML = button.innerHTML;
          button.innerHTML = '<i class="bi bi-check text-success"></i>';
          button.classList.add('btn-success');
          button.classList.remove('btn-outline-secondary');
          
          setTimeout(() => {
            button.innerHTML = originalHTML;
            button.classList.remove('btn-success');
            button.classList.add('btn-outline-secondary');
          }, 1500);
          
        } catch (err) {
          console.error('Gagal menyalin ke clipboard:', err);
          alert('Gagal menyalin ke clipboard. Silakan salin manual.');
        }
      }
    },

    printNewCredentials() {
      try {
        // Get the print content
        const printContent = document.getElementById('printNewCredentialsArea').innerHTML;
        
        // Create a new window for printing
        const printWindow = window.open('', '_blank');
        
        if (printWindow) {
          printWindow.document.write(`
            <!DOCTYPE html>
            <html>
            <head>
              <title>Password Reset - Informasi Akun Dokter</title>
              <style>
                body { 
                  font-family: Arial, sans-serif; 
                  margin: 0; 
                  padding: 20px; 
                }
                h2 { 
                  text-align: center; 
                  margin-bottom: 20px; 
                  color: #333;
                }
                h4 { 
                  margin-bottom: 10px; 
                  color: #333;
                }
                p { 
                  margin: 5px 0; 
                  line-height: 1.4;
                }
                .info-box {
                  border: 1px solid #ccc; 
                  padding: 15px; 
                  margin-bottom: 20px;
                  background-color: #f9f9f9;
                }
                .credentials-box {
                  border: 2px dashed #dc3545; 
                  padding: 15px; 
                  background-color: #f8f9fa;
                }
                .credentials-title {
                  color: #dc3545;
                  margin-bottom: 15px;
                }
                .important-note {
                  font-style: italic; 
                  color: #dc3545; 
                  margin-top: 15px;
                  font-weight: bold;
                }
                @media print {
                  body { margin: 0; }
                  .no-print { display: none; }
                }
              </style>
            </head>
            <body>
              <h2>Password Reset - Informasi Akun Dokter</h2>
              <div class="info-box">
                <h4>Dokter: ${this.formData.name}</h4>
                <p><strong>No. STR:</strong> ${this.formData.nomor_str || '-'}</p>
                <p><strong>Tanggal Lahir:</strong> ${this.formatDateForDisplay(this.formData.tanggal_lahir)}</p>
                <p><strong>Nomor Telepon:</strong> +62${this.formData.no_telp}</p>
                <p><strong>Gender:</strong> ${this.formData.gender}</p>
              </div>
              <div class="credentials-box">
                <h4 class="credentials-title">Kredensial Login Baru</h4>
                <p><strong>Kode Akses:</strong> ${this.newCredentials.kodeAkses}</p>
                <p><strong>Password Baru:</strong> ${this.newCredentials.password}</p>
                <p class="important-note">
                  Penting: Password telah direset pada ${new Date().toLocaleString('id-ID')}. Harap simpan dengan aman.
                </p>
              </div>
            </body>
            </html>
          `);
          
          printWindow.document.close();
          
          // Wait for content to load then print
          setTimeout(() => {
            printWindow.print();
            printWindow.close();
          }, 500);
          
        } else {
          throw new Error('Gagal membuka window untuk print');
        }
        
      } catch (err) {
        console.error('Error saat print:', err);
        alert('Gagal mencetak dokumen. Silakan coba lagi.');
      }
    }
  }
};
</script>

<style scoped>
.card {
  box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
  border: 1px solid rgba(0, 0, 0, 0.125);
}

.card-header {
  border-bottom: 1px solid rgba(0, 0, 0, 0.125);
}

.form-control:focus,
.form-select:focus {
  border-color: #ffc107;
  box-shadow: 0 0 0 0.2rem rgba(255, 193, 7, 0.25);
}

.btn-warning:hover {
  background-color: #e0a800;
  border-color: #d39e00;
}

.btn-outline-warning:hover {
  background-color: #ffc107;
  border-color: #ffc107;
}

.invalid-feedback {
  display: block;
}

.spinner-border-sm {
  width: 1rem;
  height: 1rem;
}

.modal-backdrop {
  background-color: rgba(0, 0, 0, 0.5);
}

.input-group-text {
  background-color: #e9ecef;
  border-color: #ced4da;
}

.form-control-plaintext {
  background-color: transparent;
  border: solid transparent;
  border-width: 1px 0;
}

.alert {
  border: 1px solid transparent;
  border-radius: 0.375rem;
}

.alert-warning {
  background-color: #fff3cd;
  border-color: #ffecb5;
  color: #664d03;
}

.alert-danger {
  background-color: #f8d7da;
  border-color: #f5c2c7;
  color: #842029;
}

.text-muted {
  color: #6c757d !important;
}

.fw-bold {
  font-weight: 700 !important;
}

.d-none {
  display: none !important;
}

@media (max-width: 768px) {
  .col-md-6 {
    margin-bottom: 1rem;
  }
  
  .d-flex.justify-content-between {
    flex-direction: column;
    gap: 1rem;
  }
  
  .d-flex.justify-content-between > div {
    display: flex;
    gap: 0.5rem;
  }
}

/* Print styles */
@media print {
  .no-print {
    display: none !important;
  }
}
</style>