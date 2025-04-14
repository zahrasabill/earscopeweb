<template>
  <div class="container mt-4">
    <div class="card">
      <div class="card-header bg-primary text-white">
        <h4>Detail Pasien</h4>
      </div>
      <div class="card-body" v-if="loading">
        <div class="d-flex justify-content-center align-items-center p-5">
          <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">Loading...</span>
          </div>
        </div>
      </div>
      <div class="card-body" v-else>
        <div class="row mb-4">
          <div class="col-md-4 text-center mb-3 mb-md-0">
            <div class="avatar-container bg-light p-3 rounded-circle mx-auto mb-2" style="width: 150px; height: 150px;">
              <i class="bi bi-person-circle" style="font-size: 120px;"></i>
            </div>
            <h5 class="mt-2">{{ pasien.nama }}</h5>
            <span class="badge bg-success">{{ pasien.kodeAkses }}</span>
          </div>
          <div class="col-md-8">
            <div class="table-responsive">
              <table class="table">
                <tbody>
                  <tr>
                    <th width="30%" class="bg-light">Nama Lengkap</th>
                    <td>{{ pasien.nama }}</td>
                  </tr>
                  <tr>
                    <th class="bg-light">Kode Akses</th>
                    <td>{{ pasien.kodeAkses }}</td>
                  </tr>
                  <tr>
                    <th class="bg-light">Tanggal Lahir</th>
                    <td>{{ formatDate(pasien.tanggalLahir) }}</td>
                  </tr>
                  <tr>
                    <th class="bg-light">Usia</th>
                    <td>{{ pasien.usia }} tahun</td>
                  </tr>
                  <tr>
                    <th class="bg-light">Gender</th>
                    <td>{{ pasien.gender === 'L' ? 'Laki-laki' : 'Perempuan' }}</td>
                  </tr>
                  <tr>
                    <th class="bg-light">Nomor Telepon</th>
                    <td>{{ pasien.phone }}</td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>
        
        <div class="d-flex justify-content-between">
          <button type="button" class="btn btn-secondary" @click="$router.push('/pasien')">
            <i class="bi bi-arrow-left"></i> Kembali
          </button>
          <div>
            <router-link :to="`/pasien/edit/${pasienId}`" class="btn btn-warning me-2">
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
            <p>Apakah Anda yakin ingin menghapus pasien <strong>{{ pasien.nama }}</strong>?</p>
            <p class="text-danger"><strong>Perhatian:</strong> Tindakan ini tidak dapat dibatalkan.</p>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
            <button type="button" class="btn btn-danger" @click="deletePasien">Hapus</button>
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
  name: 'ViewPasien',
  data() {
    return {
      pasienId: null,
      pasien: {},
      loading: true,
      deleteModal: null
    };
  },
  created() {
    // Ambil ID dari parameter rute
    this.pasienId = this.$route.params.id;
    
    // Muat data pasien berdasarkan ID
    this.loadPasienData();
  },
  mounted() {
    this.deleteModal = new Modal(document.getElementById('deleteModal'));
  },
  methods: {
    async loadPasienData() {
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
          this.pasien = response.data.data;
          this.loading = false;
        } else {
          throw new Error('Format response tidak sesuai');
        }
      } catch (err) {
        console.error('Error saat mengambil data pasien:', err);
        alert('Gagal memuat data pasien. Silakan coba lagi.');
        this.$router.push('/pasien');
      }
    },
    
    formatDate(dateString) {
      const options = { day: 'numeric', month: 'long', year: 'numeric' };
      return new Date(dateString).toLocaleDateString('id-ID', options);
    },
    
    confirmDelete() {
      this.deleteModal.show();
    },
    
    async deletePasien() {
      try {
        // Dapatkan token dari localStorage atau dari state management (Vuex/Pinia)
        const token = localStorage.getItem('accessToken') || this.$store?.state?.auth?.token;
        
        if (!token) {
          throw new Error('Token autentikasi tidak ditemukan. Silakan login terlebih dahulu.');
        }
        
        // Panggil API untuk menghapus pasien
        await axios.delete(
          `https://api.earscope.adrfstwn.cloud/v1/pasien/${this.pasienId}`,
          {
            headers: {
              'Authorization': `Bearer ${token}`,
              'Content-Type': 'application/json'
            }
          }
        );
        
        console.log('Menghapus pasien:', this.pasien.kodeAkses);
        
        // Tutup modal
        this.deleteModal.hide();
        
        // Tampilkan notifikasi
        alert('Pasien berhasil dihapus!');
        
        // Redirect ke halaman list pasien
        this.$router.push('/pasien');
      } catch (err) {
        console.error('Error saat menghapus pasien:', err);
        alert('Gagal menghapus pasien. Silakan coba lagi.');
        
        // Tutup modal
        this.deleteModal.hide();
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