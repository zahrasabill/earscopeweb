<template>
  <div class="container mt-4">
    <div class="card">
      <div class="card-header bg-primary text-white">
        <h4>Edit Dokter</h4>
      </div>
      <div class="card-body">
        <form @submit.prevent="updateDokter">
          <div class="mb-3">
            <label for="kodeAkses" class="form-label">Kode Akses</label>
            <input
              type="text"
              class="form-control"
              id="kodeAkses"
              v-model="dokter.kodeAkses"
              disabled
            />
          </div>
          <div class="mb-3">
            <label for="nama" class="form-label">Nama</label>
            <input
              type="text"
              class="form-control"
              id="nama"
              v-model="dokter.nama"
              required
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
            <input
              type="tel"
              class="form-control"
              id="phone"
              v-model="dokter.phone"
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
                value="Laki-laki"
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
                value="Perempuan"
                v-model="dokter.gender"
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
                <strong>Perhatian!</strong> Tindakan ini akan mereset password dokter.
              </div>
              <button type="button" class="btn btn-danger" @click="resetPassword">
                Reset Password
              </button>
            </div>
          </div>
          
          <div class="d-flex justify-content-between">
            <button type="button" class="btn btn-secondary" @click="$router.push('/dokter')">
              Kembali
            </button>
            <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
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
              <strong>Penting!</strong> Silahkan catat password baru dokter. Password ini hanya ditampilkan sekali.
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
  name: 'EditDokter',
  data() {
    return {
      dokterId: null,
      dokter: {
        kodeAkses: '',
        nama: '',
        tanggalLahir: '',
        phone: '',
        gender: ''
      },
      showResetPasswordForm: false,
      newPassword: '',
      modal: null
    };
  },
  created() {
    // Ambil ID dari parameter rute
    this.dokterId = this.$route.params.id;
    
    // Muat data dokter berdasarkan ID
    this.loadDokterData();
  },
  methods: {
    async loadDokterData() {
      try {
        // Dapatkan token dari localStorage atau dari state management (Vuex/Pinia)
        const token = localStorage.getItem('accessToken') || this.$store?.state?.auth?.token;
        
        if (!token) {
          throw new Error('Token autentikasi tidak ditemukan. Silakan login terlebih dahulu.');
        }
        
        // Panggil API untuk mengambil data dokter berdasarkan ID
        const response = await axios.get(
          `https://api.earscope.adrfstwn.cloud/v1/dokter/${this.dokterId}`,
          {
            headers: {
              'Authorization': `Bearer ${token}`,
              'Content-Type': 'application/json'
            }
          }
        );
        
        if (response.data && response.data.data) {
          // Perbarui data dokter
          this.dokter = {
            kodeAkses: response.data.data.kodeAkses || '',
            nama: response.data.data.nama || '',
            tanggalLahir: response.data.data.tanggalLahir || '',
            phone: response.data.data.phone || '',
            gender: response.data.data.gender || ''
          };
        } else {
          throw new Error('Format response tidak sesuai');
        }
      } catch (err) {
        console.error('Error saat mengambil data dokter:', err);
        alert('Gagal memuat data dokter. Silakan coba lagi.');
        this.$router.push('/dokter');
      }
    },
    
    async updateDokter() {
      try {
        // Dapatkan token dari localStorage atau dari state management (Vuex/Pinia)
        const token = localStorage.getItem('accessToken') || this.$store?.state?.auth?.token;
        
        if (!token) {
          throw new Error('Token autentikasi tidak ditemukan. Silakan login terlebih dahulu.');
        }
        
        // Format tanggal sesuai kebutuhan API (YYYY-MM-DD)
        const formattedData = {
          nama: this.dokter.nama,
          tanggalLahir: this.formatDate(this.dokter.tanggalLahir),
          phone: this.dokter.phone,
          gender: this.dokter.gender
        };
        
        // Panggil API untuk memperbarui data dokter
        const response = await axios.put(
          `https://api.earscope.adrfstwn.cloud/v1/dokter/${this.dokterId}`,
          formattedData,
          {
            headers: {
              'Authorization': `Bearer ${token}`,
              'Content-Type': 'application/json'
            }
          }
        );
        
        console.log('Dokter diperbarui:', response.data);
        
        // Tampilkan notifikasi sukses
        alert('Data dokter berhasil diperbarui!');
        
        // Kembali ke halaman list dokter
        this.$router.push('/dokter');
      } catch (err) {
        console.error('Error saat memperbarui dokter:', err);
        alert('Gagal memperbarui data dokter. Silakan coba lagi.');
      }
    },
    
    formatDate(dateString) {
      // Pastikan format tanggal sesuai dengan yang diharapkan API
      return dateString; // Asumsi format tanggal dari input sudah YYYY-MM-DD
    },
    
    resetPassword() {
      // Generate password baru
      this.newPassword = this.generatePassword();
      
      // Simulasi reset password
      console.log('Password direset untuk dokter:', this.dokter.kodeAkses);
      console.log('Password baru:', this.newPassword);
      
      // Tampilkan modal dengan password baru
      this.showPasswordResetModal();
      
      // Sembunyikan form reset password
      this.showResetPasswordForm = false;
    },
    
    generatePassword() {
      // Generate password acak dengan panjang 8 karakter
      const chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
      let password = '';
      for (let i = 0; i < 8; i++) {
        password += chars.charAt(Math.floor(Math.random() * chars.length));
      }
      return password;
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