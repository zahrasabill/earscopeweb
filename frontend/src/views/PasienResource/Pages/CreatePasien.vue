<template>
  <div class="container mt-4">
    <div class="card">
      <div class="card-header bg-primary text-white">
        <h4>Tambah Pasien</h4>
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
        <form @submit.prevent="createPasien" v-if="!loading">
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
          <div class="d-flex justify-content-between">
            <button type="button" class="btn btn-secondary" @click="$router.push('/pasien')">
              Kembali
            </button>
            <button type="submit" class="btn btn-primary" :disabled="loading">Simpan</button>
          </div>
        </form>
      </div>
    </div>

    <!-- Modal untuk menampilkan kode akses dan password -->
    <div class="modal fade" id="credentialsModal" tabindex="-1" aria-labelledby="credentialsModalLabel" aria-hidden="true">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header bg-success text-white">
            <h5 class="modal-title" id="credentialsModalLabel">Informasi Akun Pasien</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <div class="alert alert-warning">
              <strong>Penting!</strong> Silahkan catat informasi dibawah ini. Informasi ini hanya ditampilkan sekali.
            </div>
            <div class="mb-3">
              <label class="form-label fw-bold">Kode Akses:</label>
              <div class="input-group">
                <input type="text" class="form-control" readonly :value="generatedCredentials.kodeAkses" />
                <button class="btn btn-outline-secondary" type="button" @click="copyToClipboard('kodeAkses')">
                  <i class="bi bi-clipboard"></i>
                </button>
              </div>
            </div>
            <div class="mb-3">
              <label class="form-label fw-bold">Password:</label>
              <div class="input-group">
                <input type="text" class="form-control" readonly :value="generatedCredentials.password" />
                <button class="btn btn-outline-secondary" type="button" @click="copyToClipboard('password')">
                  <i class="bi bi-clipboard"></i>
                </button>
              </div>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-primary" data-bs-dismiss="modal" @click="redirectToList">OK</button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { Modal } from 'bootstrap';
import axios from 'axios';
import api from '@/api';

export default {
  name: 'CreatePasien',
  data() {
    return {
      pasien: {
        nama: '',
        tanggalLahir: '',
        usia: '',
        phone: '',
        gender: ''
      },
      generatedCredentials: {
        kodeAkses: '',
        password: ''
      },
      modal: null,
      loading: false,
      error: null
    };
  },
  methods: {
    async createPasien() {
      this.loading = true;
      this.error = null;
      
      try {
        // Prepare data for API
        const formattedData = {
          nama: this.pasien.nama,
          tanggal_lahir: this.pasien.tanggalLahir, // API expects tanggal_lahir format
          usia: parseInt(this.pasien.usia),
          phone: this.pasien.phone,
          gender: this.pasien.gender
        };
        
        // Call API to create new patient
        const response = await axios.post(
          'https://api.earscope.adrfstwn.cloud/v1/register/pasien', 
          formattedData,
          {
            headers: {
              'Authorization': `Bearer ${token}`,
              'Content-Type': 'application/json'
            }
          }
        );
        
        // Process response from API
        if (response.data && response.data.data) {
          this.generatedCredentials.kodeAkses = response.data.data.kodeAkses || '';
          this.generatedCredentials.password = response.data.data.password || '';
          
          console.log('Pasien berhasil dibuat:', response.data);
          
          // Show credentials modal
          this.showCredentialsModal();
        } else {
          throw new Error('Format response tidak sesuai');
        }
      } catch (err) {
        console.error('Error saat membuat pasien:', err);
        this.error = err.response?.data?.message || err.message || 'Terjadi kesalahan saat membuat pasien. Silakan coba lagi.';
      } finally {
        this.loading = false;
      }
    },
    
    showCredentialsModal() {
      // Show Bootstrap modal
      this.modal = new Modal(document.getElementById('credentialsModal'));
      this.modal.show();
    },
    
    copyToClipboard(type) {
      // Copy credentials to clipboard
      const text = type === 'kodeAkses' ? this.generatedCredentials.kodeAkses : this.generatedCredentials.password;
      navigator.clipboard.writeText(text)
        .then(() => {
          alert(`${type === 'kodeAkses' ? 'Kode Akses' : 'Password'} berhasil disalin!`);
        })
        .catch(err => {
          console.error('Gagal menyalin teks: ', err);
        });
    },
    
    redirectToList() {
      // Redirect to patient list after modal is closed
      this.$router.push('/pasien');
    }
  }
};
</script>

<style scoped>
.card {
  box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
}
</style>