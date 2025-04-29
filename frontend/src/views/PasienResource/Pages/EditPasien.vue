<template>
  <div class="container mt-4">
    <div class="card">
      <div class="card-header bg-warning text-white">
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
        <form @submit.prevent="updatePasien" v-if="!loading && pasien">
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
                value="laki-laki"
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
                value="perempuan"
                v-model="pasien.gender"
              />
              <label class="form-check-label" for="perempuan">Perempuan</label>
            </div>
          </div>
          <div class="mb-3">
            <label for="kodeAkses" class="form-label">Kode Akses</label>
            <input
              type="text"
              class="form-control"
              id="kodeAkses"
              v-model="pasien.kodeAkses"
              readonly
              disabled
            />
            <small class="text-muted">Kode akses tidak dapat diubah</small>
          </div>
          <div class="mb-3">
            <label for="resetPassword" class="form-label">Reset Password</label>
            <div class="form-check">
              <input class="form-check-input" type="checkbox" id="resetPassword" v-model="resetPassword">
              <label class="form-check-label" for="resetPassword">
                Reset password pasien
              </label>
            </div>
            <small class="text-muted">Password baru akan ditampilkan setelah pasien disimpan</small>
          </div>
          <div class="d-flex justify-content-between">
            <button type="button" class="btn btn-secondary" @click="$router.push(`/pasien/view/${pasienId}`)">
              Kembali
            </button>
            <button type="submit" class="btn btn-primary" :disabled="updateLoading">
              <span v-if="updateLoading" class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>
              Simpan
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- Modal untuk menampilkan password baru -->
    <div class="modal fade" id="passwordModal" tabindex="-1" aria-labelledby="passwordModalLabel" aria-hidden="true">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header bg-success text-white">
            <h5 class="modal-title" id="passwordModalLabel">Password Baru</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <div class="alert alert-warning">
              <strong>Penting!</strong> Silahkan catat password baru ini. Password ini hanya ditampilkan sekali.
            </div>
            <div class="mb-3">
              <label class="form-label fw-bold">Password:</label>
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
import api from '@/api';

export default {
  name: 'EditPasien',
  data() {
    return {
      pasienId: null,
      pasien: {
        nama: '',
        tanggalLahir: '',
        usia: '',
        phone: '',
        gender: '',
        kodeAkses: ''
      },
      loading: false,
      updateLoading: false,
      error: null,
      resetPassword: false,
      newPassword: '',
      passwordModal: null
    };
  },
  created() {
    this.pasienId = this.$route.params.id;
    this.fetchPasienDetail();
  },
  methods: {
    async fetchPasienDetail() {
      this.loading = true;
      this.error = null;

      try {
        const token = sessionStorage.getItem('token') || localStorage.getItem('token');
        
        if (!token) {
          throw new Error('Sesi login telah berakhir. Silakan login kembali.');
        }

        const response = await api.get(`pasien/${this.pasienId}`, {
          headers: {
            'Authorization': `Bearer ${token}`
          }
        });

        if (response.data && response.data.data) {
          const pasienData = response.data.data;
          this.pasien = {
            nama: pasienData.name,
            tanggalLahir: pasienData.tanggal_lahir,
            usia: pasienData.usia,
            phone: pasienData.no_telp,
            gender: pasienData.gender,
            kodeAkses: pasienData.kode_akses
          };
        } else {
          throw new Error('Format response tidak sesuai');
        }
      } catch (err) {
        console.error('Error fetching pasien detail:', err);
        this.error = err.response?.data?.message || err.message || 'Gagal memuat data pasien';
      } finally {
        this.loading = false;
      }
    },
    
    async updatePasien() {
      this.updateLoading = true;
      this.error = null;
      
      try {
        const token = sessionStorage.getItem('token') || localStorage.getItem('token');
        
        if (!token) {
          throw new Error('Sesi login telah berakhir. Silakan login kembali.');
        }
        
        // Prepare data for API
        const formattedData = {
          name: this.pasien.nama,
          tanggal_lahir: this.pasien.tanggalLahir,
          usia: parseInt(this.pasien.usia),
          no_telp: this.pasien.phone,
          gender: this.pasien.gender,
          reset_password: this.resetPassword
        };
        
        // Call API to update patient
        const response = await api.put(`pasien/${this.pasienId}`, formattedData, {
          headers: {
            'Authorization': `Bearer ${token}`,
            'Content-Type': 'application/json'
          }
        });
        
        // If password was reset, show the new password
        if (this.resetPassword && response.data && response.data.data && response.data.data.new_password) {
          this.newPassword = response.data.data.new_password;
          this.showPasswordModal();
        } else {
          // Show success message and redirect
          alert('Data pasien berhasil diperbarui');
          this.$router.push(`/pasien/view/${this.pasienId}`);
        }
      } catch (err) {
        console.error('Error updating pasien:', err);
        this.error = err.response?.data?.message || err.message || 'Terjadi kesalahan saat memperbarui data pasien. Silakan coba lagi.';
      } finally {
        this.updateLoading = false;
      }
    },
    
    showPasswordModal() {
      // Show Bootstrap modal
      this.passwordModal = new Modal(document.getElementById('passwordModal'));
      this.passwordModal.show();
      
      // Add event listener for when modal is hidden
      const modalEl = document.getElementById('passwordModal');
      modalEl.addEventListener('hidden.bs.modal', () => {
        this.$router.push(`/pasien/view/${this.pasienId}`);
      });
    },
    
    copyToClipboard() {
      navigator.clipboard.writeText(this.newPassword)
        .then(() => {
          alert('Password berhasil disalin!');
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