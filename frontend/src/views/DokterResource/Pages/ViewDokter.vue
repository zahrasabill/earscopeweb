<template>
  <div class="container mt-4">
    <div class="card">
      <div class="card-header bg-primary text-white">
        <h4>Detail Dokter</h4>
      </div>
      <div class="card-body" v-if="loading">
        <div class="d-flex justify-content-center align-items-center p-5">
          <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">Loading...</span>
          </div>
        </div>
      </div>
      <div class="card-body" v-else>
        <div v-if="error" class="alert alert-danger">
          {{ error }}
        </div>
        <div class="row mb-4" v-if="!error">
          <div class="col-md-4 text-center mb-3 mb-md-0">
            <div class="avatar-container bg-light p-3 rounded-circle mx-auto mb-2" style="width: 150px; height: 150px;">
              <i class="bi bi-person-circle" style="font-size: 120px;"></i>
            </div>
            <h5 class="mt-2">{{ dokter.nama }}</h5>
            <span class="badge bg-success">{{ dokter.kodeAkses }}</span>
          </div>
          <div class="col-md-8">
            <div class="table-responsive">
              <table class="table">
                <tbody>
                  <tr>
                    <th width="30%" class="bg-light">Nama Lengkap</th>
                    <td>{{ dokter.nama }}</td>
                  </tr>
                  <tr>
                    <th class="bg-light">Kode Akses</th>
                    <td>{{ dokter.kodeAkses }}</td>
                  </tr>
                  <tr>
                    <th class="bg-light">Tanggal Lahir</th>
                    <td>{{ formatDate(dokter.tanggalLahir) }}</td>
                  </tr>
                  <tr>
                    <th class="bg-light">Gender</th>
                    <td>{{ dokter.gender }}</td>
                  </tr>
                  <tr>
                    <th class="bg-light">Nomor Telepon</th>
                    <td>{{ dokter.phone }}</td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>
        
        <div class="d-flex justify-content-between">
          <button type="button" class="btn btn-secondary" @click="$router.push('/dokter')">
            <i class="bi bi-arrow-left"></i> Kembali
          </button>
          <div v-if="!error">
            <router-link :to="`/dokter/edit/${dokterId}`" class="btn btn-warning me-2">
              <i class="bi bi-pencil"></i> Edit
            </router-link>
            <button type="button" class="btn btn-danger" @click="confirmDelete">
              <i class="bi bi-trash"></i> Hapus
            </button>
          </div>
        </div>
      </div>
    </div>
  
    <!-- Modal Konfirmasi Hapus -->
    <div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header bg-danger text-white">
            <h5 class="modal-title" id="deleteModalLabel">Konfirmasi Hapus</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <p>Apakah Anda yakin ingin menghapus dokter <strong>{{ dokter.nama }}</strong>?</p>
            <p class="text-danger"><strong>Perhatian:</strong> Tindakan ini tidak dapat dibatalkan.</p>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
            <button type="button" class="btn btn-danger" @click="deleteDokter" :disabled="deleteLoading">
              <span v-if="deleteLoading" class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>
              Hapus
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
  name: 'ViewDokter',
  data() {
    return {
      dokterId: null,
      dokter: {},
      loading: true,
      deleteLoading: false,
      error: null,
      deleteModal: null
    };
  },
  created() {
    // Ambil ID dari parameter rute
    this.dokterId = this.$route.params.id;
    
    // Muat data dokter berdasarkan ID
    this.loadDokterData();
  },
  mounted() {
    this.deleteModal = new Modal(document.getElementById('deleteModal'));
  },
  methods: {
    async loadDokterData() {
      this.loading = true;
      this.error = null;
      
      try {
        // Dapatkan token dari localStorage atau sessionStorage
        const token = localStorage.getItem("token") || sessionStorage.getItem("token");
        
        if (!token) {
          throw new Error('Token autentikasi tidak ditemukan. Silakan login terlebih dahulu.');
        }
        
        // Panggil API untuk mengambil data dokter berdasarkan ID
        const response = await api.get(
          `dokter/${this.dokterId}`,
          {
            headers: {
              'Authorization': `Bearer ${token}`,
              'Content-Type': 'application/json'
            }
          }
        );
        
        if (response.data && response.data.data) {
          // Perbarui data dokter
          this.dokter = response.data.data;
        } else {
          throw new Error('Format response tidak sesuai');
        }
      } catch (err) {
        console.error('Error saat mengambil data dokter:', err);
        
        if (err.response?.status === 401) {
          this.error = 'Tidak memiliki akses. Silakan login kembali untuk mendapatkan token yang valid.';
        } else if (err.response?.status === 404) {
          this.error = 'Data dokter tidak ditemukan.';
        } else {
          this.error = err.response?.data?.message || err.message || 'Gagal memuat data dokter. Silakan coba lagi.';
        }
      } finally {
        this.loading = false;
      }
    },
    
    formatDate(dateString) {
      if (!dateString) return '';
      const options = { day: 'numeric', month: 'long', year: 'numeric' };
      return new Date(dateString).toLocaleDateString('id-ID', options);
    },
    
    confirmDelete() {
      this.deleteModal.show();
    },
    
    async deleteDokter() {
      this.deleteLoading = true;
      
      try {
        // Dapatkan token dari localStorage atau sessionStorage
        const token = localStorage.getItem("token") || sessionStorage.getItem("token");
        
        if (!token) {
          throw new Error('Token autentikasi tidak ditemukan. Silakan login terlebih dahulu.');
        }
        
        // Panggil API untuk menghapus dokter
        await api.delete(
          `dokter/${this.dokterId}`,
          {
            headers: {
              'Authorization': `Bearer ${token}`
            }
          }
        );
        
        console.log('Berhasil menghapus dokter:', this.dokter.kodeAkses);
        
        // Tutup modal
        this.deleteModal.hide();
        
        // Tampilkan notifikasi
        alert('Dokter berhasil dihapus!');
        
        // Redirect ke halaman list dokter
        this.$router.push('/dokter');
      } catch (err) {
        console.error('Error saat menghapus dokter:', err);
        
        // Tampilkan pesan error
        let errorMessage = 'Gagal menghapus dokter. Silakan coba lagi.';
        if (err.response?.data?.message) {
          errorMessage = err.response.data.message;
        } else if (err.message) {
          errorMessage = err.message;
        }
        
        alert(errorMessage);
      } finally {
        this.deleteLoading = false;
      }
    }
  }
};
</script>

<style scoped>
.card {
  box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
}

.table th, .table td {
  padding: 0.75rem;
}

.avatar-container {
  display: flex;
  align-items: center;
  justify-content: center;
}
</style>