<template>
  <div class="container mt-4">
    <div class="card">
      <div class="card-header bg-info text-white d-flex justify-content-between align-items-center">
        <h4>Detail Pasien</h4>
        <div>
          <router-link :to="`/pasien/edit/${pasienId}`" class="btn btn-warning text-white me-2">
            <i class="bi bi-pencil"></i> Edit
          </router-link>
          <button class="btn btn-danger" @click="confirmDelete">
            <i class="bi bi-trash"></i> Hapus
          </button>
        </div>
      </div>
      <div class="card-body">
        <div v-if="loading" class="text-center my-5">
          <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">Loading...</span>
          </div>
          <p class="mt-2">Memuat data...</p>
        </div>

        <div v-if="error" class="alert alert-danger">
          {{ error }}
          <button class="btn btn-sm btn-outline-danger ms-2" @click="fetchPasienDetail">Coba Lagi</button>
        </div>

        <div v-if="!loading && !error && pasien">
          <div class="row">
            <div class="col-md-6">
              <div class="mb-4">
                <h5 class="border-bottom pb-2">Informasi Pasien</h5>
                <table class="table table-bordered">
                  <tbody>
                    <tr>
                      <th width="40%">Nama Lengkap</th>
                      <td>{{ pasien.name }}</td>
                    </tr>
                    <tr>
                      <th>Tanggal Lahir</th>
                      <td>{{ formatDate(pasien.tanggal_lahir) }}</td>
                    </tr>
                    <tr>
                      <th>Usia</th>
                      <td>{{ pasien.usia }} tahun</td>
                    </tr>
                    <tr>
                      <th>Gender</th>
                      <td>{{ capitalizeFirstLetter(pasien.gender) }}</td>
                    </tr>
                    <tr>
                      <th>Nomor Telepon</th>
                      <td>{{ pasien.no_telp }}</td>
                    </tr>
                    <tr>
                      <th>Kode Akses</th>
                      <td>{{ pasien.kode_akses }}</td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>
            <div class="col-md-6">
              <div class="mb-4">
                <h5 class="border-bottom pb-2">Riwayat Pemeriksaan</h5>
                <div v-if="loadingRiwayat" class="text-center p-4">
                  <div class="spinner-border spinner-border-sm text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                  </div>
                </div>
                <div v-else-if="riwayatPemeriksaan.length === 0" class="alert alert-info">
                  Pasien belum memiliki riwayat pemeriksaan
                </div>
                <div v-else class="list-group">
                  <a 
                    href="#" 
                    class="list-group-item list-group-item-action"
                    v-for="riwayat in riwayatPemeriksaan"
                    :key="riwayat.id"
                    @click.prevent="viewPemeriksaan(riwayat.id)"
                  >
                    <div class="d-flex w-100 justify-content-between">
                      <h6 class="mb-1">{{ formatDate(riwayat.tanggal) }}</h6>
                      <small>{{ formatTime(riwayat.created_at) }}</small>
                    </div>
                    <p class="mb-1">{{ riwayat.diagnosa || 'Tidak ada diagnosa' }}</p>
                    <small>Dokter: {{ riwayat.dokter?.name || 'Tidak tercatat' }}</small>
                  </a>
                </div>
              </div>
            </div>
          </div>
        </div>

        <div class="mt-4">
          <button class="btn btn-secondary" @click="$router.push('/pasien')">
            <i class="bi bi-arrow-left"></i> Kembali
          </button>
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
            <p>Anda yakin ingin menghapus pasien <strong>{{ pasien?.name }}</strong>?</p>
            <p class="text-danger"><small>Tindakan ini tidak dapat dibatalkan.</small></p>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
            <button type="button" class="btn btn-danger" @click="deletePasien" :disabled="deleteLoading">
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
  name: 'ViewPasien',
  data() {
    return {
      pasienId: null,
      pasien: null,
      loading: false,
      error: null,
      riwayatPemeriksaan: [],
      loadingRiwayat: false,
      deleteModal: null,
      deleteLoading: false
    };
  },
  created() {
    this.pasienId = this.$route.params.id;
    this.fetchPasienDetail();
    this.fetchRiwayatPemeriksaan();
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
          this.pasien = response.data.data;
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
    
    async fetchRiwayatPemeriksaan() {
      this.loadingRiwayat = true;
      
      try {
        const token = sessionStorage.getItem('token') || localStorage.getItem('token');
        
        if (!token) {
          throw new Error('Sesi login telah berakhir. Silakan login kembali.');
        }

        const response = await api.get(`pemeriksaan/pasien/${this.pasienId}`, {
          headers: {
            'Authorization': `Bearer ${token}`
          }
        });

        if (response.data && response.data.data) {
          this.riwayatPemeriksaan = response.data.data;
        } else {
          this.riwayatPemeriksaan = [];
        }
      } catch (err) {
        console.error('Error fetching riwayat pemeriksaan:', err);
        this.riwayatPemeriksaan = [];
      } finally {
        this.loadingRiwayat = false;
      }
    },
    
    formatDate(dateString) {
      if (!dateString) return '-';
      const date = new Date(dateString);
      return new Intl.DateTimeFormat('id-ID', { 
        day: '2-digit', 
        month: '2-digit', 
        year: 'numeric' 
      }).format(date);
    },
    
    formatTime(datetimeString) {
      if (!datetimeString) return '-';
      const date = new Date(datetimeString);
      return new Intl.DateTimeFormat('id-ID', { 
        hour: '2-digit', 
        minute: '2-digit'
      }).format(date);
    },
    
    capitalizeFirstLetter(string) {
      if (!string) return '-';
      return string.charAt(0).toUpperCase() + string.slice(1);
    },
    
    viewPemeriksaan(pemeriksaanId) {
      this.$router.push({
        path: '/detail-pemeriksaan',
        query: { id: pemeriksaanId }
      });
    },
    
    confirmDelete() {
      this.deleteModal = new Modal(document.getElementById('deleteModal'));
      this.deleteModal.show();
    },
    
    async deletePasien() {
      this.deleteLoading = true;
      
      try {
        const token = sessionStorage.getItem('token') || localStorage.getItem('token');
        
        if (!token) {
          throw new Error('Sesi login telah berakhir. Silakan login kembali.');
        }
        
        await api.delete(`pasien/${this.pasienId}`, {
          headers: {
            'Authorization': `Bearer ${token}`
          }
        });
        
        // Close modal and redirect to list
        this.deleteModal.hide();
        
        // Show success message and redirect
        alert('Pasien berhasil dihapus');
        this.$router.push('/pasien');
      } catch (err) {
        console.error('Error deleting pasien:', err);
        alert(err.response?.data?.message || err.message || 'Gagal menghapus pasien');
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