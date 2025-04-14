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
              pattern="\d{4}-\d{2}-\d{2}"
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
              <label class="form-check-label" for="laki">laki-laki</label>
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
              <label class="form-check-label" for="perempuan">perempuan</label>
            </div>
          </div>
          <div class="d-flex justify-content-between">
            <button type="button" class="btn btn-secondary" @click="$router.push('/dokter')">
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
            <h5 class="modal-title" id="credentialsModalLabel">Informasi Akun Dokter</h5>
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
    // Perubahan pada method createDokter()
async createDokter() {
  this.loading = true;
  this.error = null;

  try {
    const token = localStorage.getItem("token") || sessionStorage.getItem("token");

    if (!token) {
      throw new Error('Token autentikasi tidak ditemukan. Silakan login terlebih dahulu.');
    }

    // Pastikan format data sesuai yang diharapkan API
    const formattedData = {
      nama: this.dokter.nama,
      tanggalLahir: this.formatDate(this.dokter.tanggalLahir),
      no_telp: this.dokter.phone,
      gender: this.dokter.gender
    };

    // Tambahkan logging untuk debugging
    console.log('Data yang dikirim ke API:', formattedData);

    const response = await api.post(
      'register/dokter',
      formattedData,
      {
        headers: {
          'Authorization': `Bearer ${token}`,
          'Content-Type': 'application/json' // Tambahkan header content-type
        }
      }
    );
    
    // Simpan credentials dari response API
    if (response.data && response.data.data) {
      this.generatedCredentials.kodeAkses = response.data.data.kodeAkses || '';
      this.generatedCredentials.password = response.data.data.password || '';
      
      console.log('Dokter berhasil dibuat:', response.data);
      
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
      console.error('Response error status:', err.response.status);
      console.error('Response error headers:', err.response.headers);
      
      // Pesan error yang lebih spesifik berdasarkan response
      if (err.response.status === 400) {
        this.error = `Bad Request: ${err.response.data.message || 'Format data tidak valid'}`;
      } else if (err.response.status === 401) {
        this.error = 'Tidak memiliki akses. Silakan login kembali untuk mendapatkan token yang valid.';
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

// Pastikan formatDate berfungsi dengan benar
formatDate(dateString) {
  if (!dateString) return '';
  
   // Validasi string dengan format YYYY-MM-DD
   const regex = /^\d{4}-\d{2}-\d{2}$/;
  if (!regex.test(dateString)) {
    throw new Error('Format tanggal tidak valid. Harus dalam format YYYY-MM-DD.');
  }

  return dateString;
},
  },
};

</script>

<style scoped>
.card {
  box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
}
</style>