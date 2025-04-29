<template>
  <div class="container mt-4">
    <div class="card">
      <div class="card-header bg-warning text-dark">
        <h4>Edit Dokter</h4>
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
        
        <form @submit.prevent="updateDokter" v-else>
          <div class="mb-3">
            <label for="nama" class="form-label">Nama</label>
            <input
              type="text"
              class="form-control"
              id="nama"
              v-model.trim="dokter.nama"
              required
              placeholder="Masukkan nama dokter"
            />
          </div>
          
          <div class="mb-3">
            <label for="tanggalLahir" class="form-label">Tanggal Lahir</label>
            <input
              type="date"
              class="form-control"
              id="tanggalLahir"
              v-model="dokter.tanggalLahir"
              required
            />
          </div>
          
          <div class="mb-3">
            <label for="phone" class="form-label">Nomor Telepon</label>
            <div class="input-group">
              <span class="input-group-text">+62</span>
              <input
                type="tel"
                class="form-control"
                id="phone"
                v-model.trim="dokter.phone"
                required
                placeholder="8xxxxxxxxxx"
                pattern="[0-9]+"
              />
            </div>
            <small class="text-muted">Format: 8xxxxxxxxxx (tanpa kode negara)</small>
          </div>
          
          <div class="mb-3">
            <label class="form-label">Gender</label>
            <div class="d-flex gap-4">
              <div class="form-check">
                <input
                  class="form-check-input"
                  type="radio"
                  name="gender"
                  id="laki"
                  value="laki-laki"
                  v-model="dokter.gender"
                  required
                />
                <label class="form-check-label" for="laki">Laki-laki</label>
              </div>
              <div class="form-check">
                <input
                  class="form-check-input"
                  type="radio"
                  name="gender"
                  id="perempuan"
                  value="perempuan"
                  v-model="dokter.gender"
                />
                <label class="form-check-label" for="perempuan">Perempuan</label>
              </div>
            </div>
          </div>
          
          <div class="mb-3">
            <label class="form-label">Status</label>
            <div class="form-check form-switch">
              <input
                class="form-check-input"
                type="checkbox"
                id="isActive"
                v-model="dokter.isActive"
              />
              <label class="form-check-label" for="isActive">
                {{ dokter.isActive ? 'Aktif' : 'Tidak Aktif' }}
              </label>
            </div>
            <small class="text-muted">Dokter tidak aktif tidak dapat login ke sistem</small>
          </div>
          
          <div class="d-flex justify-content-between mt-4">
            <button type="button" class="btn btn-secondary" @click="$router.push(`/dokter/view/${$route.params.id}`)">
              <i class="bi bi-arrow-left me-1"></i> Kembali
            </button>
            <div>
              <button type="submit" class="btn btn-warning" :disabled="updateLoading">
                <span v-if="updateLoading" class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>
                <i v-else class="bi bi-save me-1"></i> Simpan Perubahan
              </button>
            </div>
          </div>
        </form>
      </div>
    </div>
    
    <!-- Modal Sukses -->
    <div class="modal fade" id="successModal" tabindex="-1" aria-labelledby="successModalLabel" aria-hidden="true">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header bg-success text-white">
            <h5 class="modal-title" id="successModalLabel">Berhasil</h5>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <div class="text-center mb-3">
              <i class="bi bi-check-circle-fill text-success" style="font-size: 3rem;"></i>
            </div>
            <p class="text-center">Data dokter berhasil diperbarui.</p>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-success" data-bs-dismiss="modal" @click="redirectToDetail">
              <i class="bi bi-check-lg me-1"></i> OK
            </button>
          </div>
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
      dokter: {
        nama: '',
        tanggalLahir: '',
        phone: '',
        gender: '',
        isActive: true
      },
      originalData: {},
      loading: true,
      error: null,
      updateLoading: false,
      successModal: null
    };
  },
  created() {
    this.fetchDokter();
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
        
        const response = await api.get(`dokter/${dokterId}`, {
          headers: {
            'Authorization': `Bearer ${token}`
          }
        });
        
        if (response.data && response.data.data) {
          const apiData = response.data.data;
          
          // Mapping API data to form data
          this.dokter = {
            nama: apiData.name || '',
            tanggalLahir: apiData.tanggal_lahir || '',
            phone: apiData.no_telp || '',
            gender: apiData.gender || '',
            isActive: apiData.is_active === undefined ? true : Boolean(apiData.is_active)
          };
          
          // Store original data for comparison
          this.originalData = { ...this.dokter };
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
            this.error = 'Dokter tidak ditemukan';
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
    
    async updateDokter() {
      this.updateLoading = true;
      
      try {
        const token = localStorage.getItem("token") || sessionStorage.getItem("token");
        
        if (!token) {
          throw new Error('Token autentikasi tidak ditemukan');
        }
        
        // Format phone number
        const phoneNumber = this.dokter.phone.startsWith('0') 
          ? this.dokter.phone.substring(1) 
          : this.dokter.phone;
          
        // Prepare API data format
        const formattedData = {
          name: this.dokter.nama,
          tanggal_lahir: this.dokter.tanggalLahir,
          no_telp: phoneNumber,
          gender: this.dokter.gender,
          is_active: this.dokter.isActive
        };
        
        console.log('Data yang dikirim ke API:', formattedData);
        
        const dokterId = this.$route.params.id;
        const response = await api.put(`dokter/${dokterId}`, formattedData, {
          headers: {
            'Authorization': `Bearer ${token}`
          }
        });
        
        if (response.data) {
          console.log('Dokter berhasil diperbarui');
          
          // Notify other components that data has been updated
          localStorage.setItem('dokterDataUpdated', Date.now().toString());
          
          // Show success modal
          this.showSuccessModal();
        } else {
          throw new Error('Format response tidak sesuai');
        }
      } catch (err) {
        console.error('Error saat memperbarui dokter:', err);
        
        let errorMessage = 'Terjadi kesalahan saat memperbarui data dokter';
        
        if (err.response) {
          if (err.response.status === 400) {
            errorMessage = `Format data tidak valid: ${err.response.data.message || ''}`;
          } else if (err.response.status === 401) {
            errorMessage = 'Sesi login Anda telah berakhir. Silakan login kembali.';
            setTimeout(() => {
              this.$router.push('/login');
            }, 2000);
          } else {
            errorMessage = err.response.data.message || errorMessage;
          }
        } else if (err.message) {
          errorMessage = err.message;
        }
        
        alert(errorMessage);
      } finally {
        this.updateLoading = false;
      }
    },
    
    showSuccessModal() {
      if (!this.successModal) {
        const modalElement = document.getElementById('successModal');
        this.successModal = new Modal(modalElement);
      }
      
      this.successModal.show();
    },
    
    redirectToDetail() {
      this.$router.push(`/dokter/view/${this.$route.params.id}`);
    }
  },
  mounted() {
    // Initialize modals
    const modalElement = document.getElementById('successModal');
    if (modalElement) {
      this.successModal = new Modal(modalElement);
    }
  }
};
</script>

<style scoped>
.card {
  box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
  border-radius: 8px;
  border: none;
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

.form-check-input:checked {
  background-color: #0d6efd;
  border-color: #0d6efd;
}

.form-switch .form-check-input {
  width: 2.5em;
  height: 1.25em;
}

.modal-content {
  border-radius: 8px;
  border: none;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

.modal-header {
  border-radius: 8px 8px 0 0;
}
</style>