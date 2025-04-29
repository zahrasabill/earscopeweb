<template>
  <div class="container mt-4">
    <div class="card">
      <div class="card-header bg-info text-white">
        <h4>Detail Dokter</h4>
      </div>
      <div class="card-body">
        <div v-if="loading" class="text-center my-5">
          <div class="spinner-border text-info" role="status">
            <span class="visually-hidden">Loading...</span>
          </div>
          <p class="mt-2">Memuat data dokter...</p>
        </div>
        
        <div v-else-if="error" class="alert alert-danger">
          <i class="bi bi-exclamation-triangle-fill me-2"></i>
          {{ error }}
        </div>
        
        <div v-else>
          <div class="row">
            <div class="col-md-6">
              <div class="card mb-4">
                <div class="card-header bg-light">
                  <h5 class="mb-0">Informasi Pribadi</h5>
                </div>
                <div class="card-body">
                  <div class="mb-3">
                    <label class="form-label fw-bold">Nama Lengkap:</label>
                    <p>{{ dokter.name }}</p>
                  </div>
                  <div class="mb-3">
                    <label class="form-label fw-bold">Tanggal Lahir:</label>
                    <p>{{ formatDate(dokter.tanggal_lahir) }}</p>
                  </div>
                  <div class="mb-3">
                    <label class="form-label fw-bold">Nomor Telepon:</label>
                    <p>+62{{ dokter.no_telp }}</p>
                  </div>
                  <div class="mb-3">
                    <label class="form-label fw-bold">Gender:</label>
                    <p>
                      <span 
                        :class="[
                          'badge',
                          dokter.gender === 'laki-laki' ? 'bg-primary' : 'bg-info'
                        ]"
                      >
                        {{ capitalizeFirst(dokter.gender) }}
                      </span>
                    </p>
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
                    <label class="form-label fw-bold">Kode Akses:</label>
                    <div class="input-group">
                      <input 
                        type="text" 
                        class="form-control" 
                        :value="dokter.kode_akses || 'Tidak tersedia'" 
                        readonly
                        ref="kodeAksesInput"
                      />
                      <button 
                        class="btn btn-outline-secondary" 
                        type="button" 
                        @click="copyToClipboard('kodeAksesInput')"
                        v-if="dokter.kode_akses"
                      >
                        <i class="bi bi-clipboard"></i>
                      </button>
                    </div>
                  </div>
                  <div class="mb-3" v-if="dokter.role">
                    <label class="form-label fw-bold">Role:</label>
                    <p>{{ capitalizeFirst(dokter.role) }}</p>
                  </div>
                  <div class="mb-3" v-if="dokter.created_at">
                    <label class="form-label fw-bold">Terdaftar Pada:</label>
                    <p>{{ formatDateTime(dokter.created_at) }}</p>
                  </div>
                </div>
              </div>
              
              <div class="card mb-4" v-if="showPassword">
                <div class="card-header bg-warning text-dark">
                  <h5 class="mb-0">Kredensial Login</h5>
                </div>
                <div class="card-body">
                  <div class="alert alert-warning">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i>
                    <strong>Penting!</strong> Kredensial ini hanya ditampilkan sekali. Pastikan Anda mencatat atau menyalin password.
                  </div>
                  <div class="mb-3">
                    <label class="form-label fw-bold">Password:</label>
                    <div class="input-group">
                      <input 
                        type="text" 
                        class="form-control" 
                        :value="password" 
                        readonly
                        ref="passwordInput"
                      />
                      <button 
                        class="btn btn-outline-secondary" 
                        type="button" 
                        @click="copyToClipboard('passwordInput')"
                      >
                        <i class="bi bi-clipboard"></i>
                      </button>
                    </div>
                  </div>
                  <div class="d-grid gap-2 mt-4">
                    <button 
                      class="btn btn-primary" 
                      @click="printCredentials"
                    >
                      <i class="bi bi-printer me-1"></i> Cetak Informasi
                    </button>
                  </div>
                </div>
              </div>
              
              <div class="card mb-4" v-if="!showPassword && dokter.id">
                <div class="card-header bg-danger text-white">
                  <h5 class="mb-0">Reset Password</h5>
                </div>
                <div class="card-body">
                  <p>Jika dokter lupa password, Anda dapat mereset password akun ini.</p>
                  <div class="d-grid gap-2">
                    <button 
                      class="btn btn-danger" 
                      @click="confirmResetPassword"
                      :disabled="resetLoading"
                    >
                      <span v-if="resetLoading" class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>
                      <i v-else class="bi bi-key me-1"></i> Reset Password
                    </button>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
        
        <div class="d-flex justify-content-between mt-4">
          <button type="button" class="btn btn-secondary" @click="$router.push('/dokter')">
            <i class="bi bi-arrow-left me-1"></i> Kembali
          </button>
          <router-link 
            v-if="dokter.id" 
            :to="`/dokter/edit/${dokter.id}`" 
            class="btn btn-warning"
          >
            <i class="bi bi-pencil me-1"></i> Edit
          </router-link>
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
            <p>Apakah Anda yakin ingin mereset password untuk dokter <strong>{{ dokter.name }}</strong>?</p>
            <p class="text-danger"><small>Password lama akan dihapus dan tidak dapat dipulihkan.</small></p>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
            <button type="button" class="btn btn-danger" @click="resetPassword" :disabled="resetLoading">
              <span v-if="resetLoading" class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>
              Reset Password
            </button>
          </div>
        </div>
      </div>
    </div>
    
    <!-- Hidden div for printing -->
    <div id="printArea" class="d-none">
      <div style="padding: 20px; font-family: Arial, sans-serif;">
        <h2 style="text-align: center; margin-bottom: 20px;">Informasi Akun Dokter</h2>
        <div style="border: 1px solid #ccc; padding: 15px; margin-bottom: 20px;">
          <h4>Dokter: {{ dokter.name }}</h4>
          <p><strong>Tanggal Lahir:</strong> {{ formatDate(dokter.tanggal_lahir) }}</p>
          <p><strong>Nomor Telepon:</strong> +62{{ dokter.no_telp }}</p>
          <p><strong>Gender:</strong> {{ capitalizeFirst(dokter.gender) }}</p>
        </div>
        <div style="border: 2px dashed #dc3545; padding: 15px; background-color: #f8f9fa;">
          <h4 style="color: #dc3545;">Kredensial Login</h4>
          <p><strong>Kode Akses:</strong> {{ dokter.kode_akses }}</p>
          <p><strong>Password:</strong> {{ password }}</p>
          <p style="font-style: italic; color: #dc3545; margin-top: 15px;">
            Penting: Informasi ini hanya ditampilkan sekali. Harap simpan dengan aman.
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
  name: 'ViewDokter',
  data() {
    return {
      dokter: {},
      loading: true,
      error: null,
      resetModal: null,
      resetLoading: false,
      showPassword: false,
      password: '',
    };
  },
  created() {
    this.fetchDokter();
    
    // Check if viewing after creation (with password)
    const passingData = this.$route.params.passingData;
    if (passingData && passingData.password) {
      this.showPassword = true;
      this.password = passingData.password;
    }
  },
  methods: {
    async fetchDokter() {
      this.loading = true;
      
      try {
        const dokterId = this.$route.params.id;
        const token = localStorage.getItem("token") || sessionStorage.getItem("token");
        
        if (!token) {
          throw new Error('Token autentikasi tidak ditemukan');
        }
        
        // Menggunakan endpoint dari gambar: /v1/users/{id}
        const response = await api.get(`users/${dokterId}`, {
          headers: {
            'Authorization': `Bearer ${token}`
          }
        });
        
        // Memeriksa response berdasarkan format API yang ditunjukkan dalam gambar
        if (response.data) {
          this.dokter = response.data;
        } else {
          throw new Error('Format response tidak sesuai');
        }
      } catch (err) {
        console.error('Error saat memuat detail dokter:', err);
        
        if (err.response) {
          if (err.response.status === 401) {
            this.error = 'Sesi login Anda telah berakhir. Silakan login kembali.';
            setTimeout(() => {
              this.$router.push('/login');
            }, 2000);
          } else if (err.response.status === 404) {
            this.error = 'Dokter tidak ditemukan. ID dokter mungkin tidak valid.';
          } else {
            this.error = err.response.data.message || 'Terjadi kesalahan saat memuat data dokter';
          }
        } else {
          this.error = err.message || 'Terjadi kesalahan saat memuat data dokter';
        }
      } finally {
        this.loading = false;
      }
    },
    
    formatDate(dateString) {
      if (!dateString) return '-';
      
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
    
    capitalizeFirst(string) {
      if (!string) return '';
      return string.charAt(0).toUpperCase() + string.slice(1);
    },
    
    copyToClipboard(refName) {
      try {
        const input = this.$refs[refName];
        input.select();
        document.execCommand('copy');
        
        // Show visual feedback
        const button = input.nextElementSibling;
        const originalHTML = button.innerHTML;
        button.innerHTML = '<i class="bi bi-check-lg"></i>';
        button.classList.add('btn-success');
        button.classList.remove('btn-outline-secondary');
        
        // Kembalikan tampilan button setelah 1.5 detik
        setTimeout(() => {
          button.innerHTML = originalHTML;
          button.classList.remove('btn-success');
          button.classList.add('btn-outline-secondary');
        }, 1500);
      } catch (err) {
        console.error('Error saat menyalin ke clipboard:', err);
      }
    },
    
    printCredentials() {
      const printContent = document.getElementById('printArea').innerHTML;
      const originalContent = document.body.innerHTML;
      
      document.body.innerHTML = printContent;
      window.print();
      document.body.innerHTML = originalContent;
      
      // Re-attach event listeners after printing
      this.$nextTick(() => {
        this.initializeModals();
      });
    },
    
    initializeModals() {
      // Initialize reset password modal
      const modalElement = document.getElementById('resetPasswordModal');
      if (modalElement) {
        this.resetModal = new Modal(modalElement);
      }
    },
    
    confirmResetPassword() {
      // Initialize modal if needed
      if (!this.resetModal) {
        this.initializeModals();
      }
      
      this.resetModal.show();
    },
    
    async resetPassword() {
      this.resetLoading = true;
      
      try {
        const token = localStorage.getItem("token") || sessionStorage.getItem("token");
        
        if (!token) {
          throw new Error('Token autentikasi tidak ditemukan');
        }
        
        // Sesuaikan endpoint reset password dengan format API baru
        const response = await api.post(`reset-password/users/${this.dokter.id}`, {}, {
          headers: {
            'Authorization': `Bearer ${token}`
          }
        });
        
        if (response.data && response.data.password) {
          // Simpan password baru
          this.password = response.data.password;
          this.showPassword = true;
          
          // Tutup modal
          this.resetModal.hide();
        } else {
          throw new Error('Format response tidak sesuai');
        }
      } catch (err) {
        console.error('Error saat mereset password:', err);
        alert(`Gagal mereset password: ${err.response?.data?.message || err.message}`);
      } finally {
        this.resetLoading = false;
      }
    }
  },
  mounted() {
    this.initializeModals();
  }
};
</script>

<style scoped>
.card {
  box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
  border-radius: 8px;
  border: none;
  margin-bottom: 16px;
}

.card-header {
  border-radius: 8px 8px 0 0;
}

.form-control:focus, .input-group-text {
  border-color: #86b7fe;
  box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
}

.btn {
  border-radius: 5px;
  padding: 8px 16px;
}

.badge {
  font-weight: 500;
  padding: 0.5em 0.75em;
}

.modal-content {
  border-radius: 8px;
  border: none;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

.modal-header {
  border-radius: 8px 8px 0 0;
}

@media print {
  body * {
    visibility: hidden;
  }
  #printArea, #printArea * {
    visibility: visible;
  }
  #printArea {
    position: absolute;
    left: 0;
    top: 0;
    width: 100%;
  }
}
</style>