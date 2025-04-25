<template>
  <div class="container mt-4">
    <div class="card">
      <div class="card-header bg-primary text-white">
        <h4>Tambah Dokter</h4>
      </div>
      <div class="card-body">
        <div v-if="loading" class="text-center my-3">
          <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">Loading...</span>
          </div>
        </div>
        <div v-if="error" class="alert alert-danger">
          {{ error }}
        </div>
        <form @submit.prevent="createDokter" v-if="!loading">
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
          <div class="d-flex justify-content-between mt-4">
            <button type="button" class="btn btn-secondary" @click="$router.push('/dokter')">
              <i class="bi bi-arrow-left me-1"></i> Kembali
            </button>
            <button type="submit" class="btn btn-primary" :disabled="loading">
              <i class="bi bi-save me-1"></i> Simpan
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- Modal untuk menampilkan kode akses dan password -->
    <div class="modal fade" id="credentialsModal" tabindex="-1" aria-labelledby="credentialsModalLabel" aria-hidden="true">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header bg-success text-white">
            <h5 class="modal-title" id="credentialsModalLabel">Informasi Akun Dokter</h5>
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
                <input 
                  type="text" 
                  class="form-control" 
                  readonly 
                  :value="generatedCredentials.kodeAkses"
                  ref="kodeAksesInput" 
                />
                <button class="btn btn-outline-secondary" type="button" @click="copyToClipboard('kodeAkses')">
                  <i class="bi bi-clipboard"></i>
                </button>
              </div>
            </div>
            <div class="mb-3">
              <label class="form-label fw-bold">Password:</label>
              <div class="input-group">
                <input 
                  type="text" 
                  class="form-control" 
                  readonly 
                  :value="generatedCredentials.password"
                  ref="passwordInput"
                />
                <button class="btn btn-outline-secondary" type="button" @click="copyToClipboard('password')">
                  <i class="bi bi-clipboard"></i>
                </button>
              </div>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-primary" data-bs-dismiss="modal" @click="redirectToList">
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
  name: 'CreateDokter',
  data() {
    return {
      dokter: {
        nama: '',
        tanggalLahir: '',
        phone: '',
        gender: ''
      },
      generatedCredentials: {
        kodeAkses: '',
        password: ''
      },
      modal: null,
      loading: false,
      error: null,
    };
  },
  methods: {
    async createDokter() {
      this.loading = true;
      this.error = null;

      try {
        const token = localStorage.getItem("token") || sessionStorage.getItem("token");

        if (!token) {
          throw new Error('Token autentikasi tidak ditemukan. Silakan login terlebih dahulu.');
        }

        // Format phone number with prefix
        const phoneNumber = this.dokter.phone.startsWith('0') 
          ? this.dokter.phone.substring(1) 
          : this.dokter.phone;

        // Pastikan format data sesuai yang diharapkan API
        const formattedData = {
          name: this.dokter.nama,
          tanggal_lahir: this.formatDate(this.dokter.tanggalLahir),
          no_telp: phoneNumber,
          gender: this.dokter.gender
        };

        console.log('Data yang dikirim ke API:', formattedData);

        const response = await api.post(`register/dokter`, formattedData, {
          headers: {
            'Authorization': `Bearer ${token}`
          }
        });
        
        // Simpan credentials dari response API
        if (response.data && response.data.data) {
          this.generatedCredentials.kodeAkses = response.data.data.kodeAkses || '';
          this.generatedCredentials.password = response.data.data.password || '';
          
          console.log('Dokter berhasil dibuat');
          localStorage.setItem('dokterDataUpdated', Date.now().toString());
          
          // Tampilkan modal dengan credentials
          this.showCredentialsModal();
        } else {
          throw new Error('Format response tidak sesuai');
        }
      } catch (err) {
        console.error('Error saat membuat dokter:', err);
        
        // Tampilkan detail error yang lebih spesifik
        if (err.response) {
          console.error('Response error data:', err.response.data);
          
          // Pesan error yang lebih spesifik berdasarkan response
          if (err.response.status === 400) {
            this.error = `Bad Request: ${err.response.data.message || 'Format data tidak valid'}`;
          } else if (err.response.status === 401) {
            this.error = 'Tidak memiliki akses. Silakan login kembali untuk mendapatkan token yang valid.';
            // Redirect ke halaman login setelah 2 detik
            setTimeout(() => {
              this.$router.push('/login');
            }, 2000);
          } else {
            this.error = err.response.data.message || 'Terjadi kesalahan saat membuat dokter.';
          }
        } else if (err.request) {
          this.error = 'Tidak ada respons dari server. Periksa koneksi Anda.';
        } else {
          this.error = err.message || 'Terjadi kesalahan saat membuat dokter. Silakan coba lagi.';
        }
      } finally {
        this.loading = false;
      }
    },

    formatDate(dateString) {
      if (!dateString) return '';
      
      // Validasi string dengan format YYYY-MM-DD
      const regex = /^\d{4}-\d{2}-\d{2}$/;
      if (!regex.test(dateString)) {
        throw new Error('Format tanggal tidak valid. Harus dalam format YYYY-MM-DD.');
      }

      return dateString;
    },

    showCredentialsModal() {
      // Inisialisasi atau perbarui modal
      if (!this.modal) {
        const modalElement = document.getElementById('credentialsModal');
        this.modal = new Modal(modalElement);
      }
      this.modal.show();
    },

    copyToClipboard(type) {
      try {
        const input = type === 'kodeAkses' ? this.$refs.kodeAksesInput : this.$refs.passwordInput;
        input.select();
        document.execCommand('copy');
        
        // Tampilkan indikator bahwa teks telah disalin
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

    redirectToList() {
      this.$router.push('/dokter');
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

.btn-primary {
  background-color: #0d6efd;
  border-color: #0d6efd;
}

.btn-primary:hover {
  background-color: #0b5ed7;
  border-color: #0a58ca;
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