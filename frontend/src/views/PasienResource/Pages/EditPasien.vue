<template>
  <div class="container mt-4">
    <div class="card">
      <div class="card-header bg-primary text-white">
        <h4>Edit Pasien</h4>
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
        <form @submit.prevent="updatePasien" v-if="!loading">
          <div class="mb-3">
            <label for="kodeAkses" class="form-label">Kode Akses</label>
            <input
              type="text"
              class="form-control"
              id="kodeAkses"
              v-model="pasien.kodeAkses"
              disabled
            />
          </div>
          <div class="mb-3">
            <label for="nama" class="form-label">Nama Lengkap</label>
            <input
              type="text"
              class="form-control"
              id="nama"
              v-model="pasien.nama"
              required
            />
          </div>
          <div class="mb-3">
            <label for="tanggalLahir" class="form-label">Tanggal Lahir</label>
            <input
              type="date"
              class="form-control"
              id="tanggalLahir"
              v-model="pasien.tanggalLahir"
              required
            />
          </div>
          <div class="mb-3">
            <label for="usia" class="form-label">Usia</label>
            <input
              type="number"
              class="form-control"
              id="usia"
              v-model="pasien.usia"
              min="0"
              required
            />
          </div>
          <div class="mb-3">
            <label for="phone" class="form-label">Nomor Telepon</label>
            <input
              type="tel"
              class="form-control"
              id="phone"
              v-model="pasien.phone"
              required
            />
          </div>
          <div class="mb-3">
            <label class="form-label">Gender</label>
            <div class="form-check">
              <input
                class="form-check-input"
                type="radio"
                name="gender"
                id="laki"
                value="L"
                v-model="pasien.gender"
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
                value="P"
                v-model="pasien.gender"
              />
              <label class="form-check-label" for="perempuan">Perempuan</label>
            </div>
          </div>
          
          <div class="mb-3 border p-3 bg-light">
            <div class="d-flex justify-content-between align-items-center mb-2">
              <h5 class="mb-0">Reset Password</h5>
              <button 
                type="button" 
                class="btn btn-sm btn-warning" 
                @click="showResetPasswordForm = !showResetPasswordForm"
              >
                {{ showResetPasswordForm ? 'Batal' : 'Lupa Password' }}
              </button>
            </div>
            
            <div v-if="showResetPasswordForm">
              <div class="alert alert-warning">
                <strong>Perhatian!</strong> Tindakan ini akan mereset password pasien.
              </div>
              <button type="button" class="btn btn-danger" @click="resetPassword">
                Reset Password
              </button>
            </div>
          </div>
          
          <div class="d-flex justify-content-between">
            <button type="button" class="btn btn-secondary" @click="$router.push('/pasien')">
              Kembali
            </button>
            <button type="submit" class="btn btn-primary" :disabled="loading">Simpan Perubahan</button>
          </div>
        </form>
      </div>
    </div>

    <!-- Modal untuk menampilkan password baru -->
    <div class="modal fade" id="passwordResetModal" tabindex="-1" aria-labelledby="passwordResetModalLabel" aria-hidden="true">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header bg-warning text-dark">
            <h5 class="modal-title" id="passwordResetModalLabel">Password Baru</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <div class="alert alert-info">
              <strong>Penting!</strong> Silahkan catat password baru pasien. Password ini hanya ditampilkan sekali.
            </div>
            <div class="mb-3">
              <label class="form-label fw-bold">Password Baru:</label>
              <div class="input-group">
                <input type="text" class="form-control" readonly :value="newPassword" />
                <button class="btn btn-outline-secondary" type="button" @click="copyToClipboard">
                  <i class="bi bi-clipboard"></i>
                </button>
              </div>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-primary" data-bs-dismiss="modal">OK</button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { Modal } from 'bootstrap';
import axios from 'axios';

export default {
  name: 'EditPasien',
  data() {
    return {
      pasienId: null,
      pasien: {
        kodeAkses: '',
        nama: '',
        tanggalLahir: '',
        usia: '',
        phone: '',
        gender: ''
      },
      showResetPasswordForm: false,
      newPassword: '',
      modal: null,
      loading: false,
      error: null
    };
  },
  created() {
    // Ambil ID dari parameter rute
    this.pasienId = this.$route.params.id;
    
    // Muat data pasien berdasarkan ID
    this.loadPasienData();
  },
  methods: {
    async loadPasienData() {
      this.loading = true;
      this.error = null;
      
      try {
        // Dapatkan token dari localStorage atau dari state management (Vuex/Pinia)
        const token = localStorage.getItem('accessToken') || this.$store?.state?.auth?.token;
        
        if (!token) {
          throw new Error('Token autentikasi tidak ditemukan. Silakan login terlebih dahulu.');
        }
        
        // Panggil API untuk mengambil data pasien berdasarkan ID
        const response = await axios.get(
          `https://api.earscope.adrfstwn.cloud/v1/pasien/${this.pasienId}`,
          {
            headers: {
              'Authorization': `Bearer ${token}`,
              'Content-Type': 'application/json'
            }
          }
        );
        
        if (response.data && response.data.data) {
          // Perbarui data pasien
          this.pasien = {
            kodeAkses: response.data.data.kodeAkses || '',
            nama: response.data.data.nama || '',
            tanggalLahir: response.data.data.tanggalLahir || '',
            usia: response.data.data.usia || '',
            phone: response.data.data.phone || '',
            gender: response.data.data.gender || ''
          };
        } else {
          throw new Error('Format response tidak sesuai');
        }
      } catch (err) {
        console.error('Error saat mengambil data pasien:', err);
        this.error = err.response?.data?.message || err.message || 'Gagal memuat data pasien. Silakan coba lagi.';
      } finally {
        this.loading = false;
      }
    },
    
    async updatePasien() {
      this.loading = true;
      this.error = null;
      
      try {
        // Dapatkan token dari localStorage atau dari state management (Vuex/Pinia)
        const token = localStorage.getItem('accessToken') || this.$store?.state?.auth?.token;
        
        if (!token) {
          throw new Error('Token autentikasi tidak ditemukan. Silakan login terlebih dahulu.');
        }
        
        // Format data sesuai kebutuhan API
        const formattedData = {
          nama: this.pasien.nama,
          tanggal_lahir: this.formatDate(this.pasien.tanggalLahir),
          usia: parseInt(this.pasien.usia),
          phone: this.pasien.phone,
          gender: this.pasien.gender
        };
        
        // Panggil API untuk memperbarui data pasien
        const response = await axios.put(
          `https://api.earscope.adrfstwn.cloud/v1/pasien/${this.pasienId}`,
          formattedData,
          {
            headers: {
              'Authorization': `Bearer ${token}`,
              'Content-Type': 'application/json'
            }
          }
        );
        
        console.log('Pasien diperbarui:', response.data);
        
        // Tampilkan notifikasi sukses
        alert('Data pasien berhasil diperbarui!');
        
        // Kembali ke halaman list pasien
        this.$router.push('/pasien');
      } catch (err) {
        console.error('Error saat memperbarui pasien:', err);
        this.error = err.response?.data?.message || err.message || 'Gagal memperbarui data pasien. Silakan coba lagi.';
      } finally {
        this.loading = false;
      }
    },
    
    formatDate(dateString) {
      // Pastikan format tanggal sesuai dengan yang diharapkan API
      return dateString; // Asumsi format tanggal dari input sudah YYYY-MM-DD
    },
    
    async resetPassword() {
      this.loading = true;
      this.error = null;
      
      try {
        // Dapatkan token dari localStorage atau dari state management (Vuex/Pinia)
        const token = localStorage.getItem('accessToken') || this.$store?.state?.auth?.token;
        
        if (!token) {
          throw new Error('Token autentikasi tidak ditemukan. Silakan login terlebih dahulu.');
        }
        
        // Panggil API untuk reset password
        const response = await axios.post(
          `https://api.earscope.adrfstwn.cloud/v1/pasien/reset-password/${this.pasienId}`,
          {},
          {
            headers: {
              'Authorization': `Bearer ${token}`,
              'Content-Type': 'application/json'
            }
          }
        );
        
        if (response.data && response.data.data && response.data.data.password) {
          // Mendapatkan password baru dari respons API
          this.newPassword = response.data.data.password;
          
          // Tampilkan modal dengan password baru
          this.showPasswordResetModal();
        } else {
          throw new Error('Format response tidak sesuai');
        }
        
        // Sembunyikan form reset password
        this.showResetPasswordForm = false;
      } catch (err) {
        console.error('Error saat mereset password:', err);
        this.error = err.response?.data?.message || err.message || 'Gagal mereset password. Silakan coba lagi.';
      } finally {
        this.loading = false;
      }
    },
    
    showPasswordResetModal() {
      // Tampilkan modal Bootstrap
      this.modal = new Modal(document.getElementById('passwordResetModal'));
      this.modal.show();
    },
    
    copyToClipboard() {
      // Copy password baru ke clipboard
      navigator.clipboard.writeText(this.newPassword)
        .then(() => {
          alert('Password baru berhasil disalin!');
        })
        .catch(err => {
          console.error('Gagal menyalin teks: ', err);
        });
    }
  }
};
</script>

<style scoped>
.card {
  box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
}
</style>