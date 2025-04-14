<template>
  <div class="container mt-4">
    <div class="card">
      <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
        <h4>Daftar Dokter</h4>
        <router-link to="/dokter/create" class="btn btn-light">
          <i class="bi bi-plus-circle me-1"></i> Tambah Dokter
        </router-link>
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
        
        <div v-if="!loading">
          <div class="mb-3">
            <div class="input-group">
              <input 
                type="text" 
                class="form-control" 
                placeholder="Cari dokter..." 
                v-model="searchQuery"
                @input="filterDokter"
              />
              <button class="btn btn-outline-secondary" type="button">
                <i class="bi bi-search"></i>
              </button>
            </div>
          </div>
          
          <div class="table-responsive">
            <table class="table table-striped table-hover">
              <thead class="table-light">
                <tr>
                  <th scope="col">#</th>
                  <th scope="col">Kode Akses</th>
                  <th scope="col">Nama</th>
                  <th scope="col">Tanggal Lahir</th>
                  <th scope="col">Gender</th>
                  <th scope="col">Phone</th>
                  <th scope="col">Aksi</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="(dokter, index) in paginatedDokter" :key="dokter.id">
                  <th scope="row">{{ (currentPage - 1) * itemsPerPage + index + 1 }}</th>
                  <td>{{ dokter.kodeAkses }}</td>
                  <td>{{ dokter.nama }}</td>
                  <td>{{ formatDate(dokter.tanggalLahir) }}</td>
                  <td>{{ dokter.gender }}</td>
                  <td>{{ dokter.phone }}</td>
                  <td>
                    <div class="btn-group" role="group">
                      <router-link :to="`/dokter/view/${dokter.id}`" class="btn btn-sm btn-info text-white">
                        <i class="bi bi-eye"></i>
                      </router-link>
                      <router-link :to="`/dokter/edit/${dokter.id}`" class="btn btn-sm btn-warning text-white">
                        <i class="bi bi-pencil"></i>
                      </router-link>
                      <button @click="confirmDelete(dokter)" class="btn btn-sm btn-danger">
                        <i class="bi bi-trash"></i>
                      </button>
                    </div>
                  </td>
                </tr>
                <tr v-if="filteredDokter.length === 0">
                  <td colspan="7" class="text-center py-3">
                    <div class="alert alert-info mb-0">
                      Tidak ada data dokter yang ditemukan.
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
          
          <!-- Pagination -->
          <nav aria-label="Page navigation" v-if="filteredDokter.length > 0">
            <ul class="pagination justify-content-center">
              <li class="page-item" :class="{ disabled: currentPage === 1 }">
                <a class="page-link" href="#" @click.prevent="changePage(currentPage - 1)">Previous</a>
              </li>
              <li 
                v-for="page in totalPages" 
                :key="page" 
                class="page-item"
                :class="{ active: page === currentPage }"
              >
                <a class="page-link" href="#" @click.prevent="changePage(page)">{{ page }}</a>
              </li>
              <li class="page-item" :class="{ disabled: currentPage === totalPages }">
                <a class="page-link" href="#" @click.prevent="changePage(currentPage + 1)">Next</a>
              </li>
            </ul>
          </nav>
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
          <div class="modal-body" v-if="selectedDokter">
            <p>Apakah Anda yakin ingin menghapus dokter <strong>{{ selectedDokter.nama }}</strong>?</p>
            <p class="text-danger"><strong>Perhatian:</strong> Tindakan ini tidak dapat dibatalkan.</p>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
            <button type="button" class="btn btn-danger" @click="deleteDokter" :disabled="deleting">
              <span v-if="deleting" class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>
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
  name: 'ListDokter',
  data() {
    return {
      dokterList: [],
      filteredDokter: [],
      searchQuery: '',
      currentPage: 1,
      itemsPerPage: 10,
      selectedDokter: null,
      deleteModal: null,
      loading: false,
      deleting: false,
      error: null
    };
  },
  computed: {
    totalPages() {
      return Math.ceil(this.filteredDokter.length / this.itemsPerPage);
    },
    paginatedDokter() {
      const start = (this.currentPage - 1) * this.itemsPerPage;
      const end = start + this.itemsPerPage;
      return this.filteredDokter.slice(start, end);
    }
  },
  created() {
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
        
        // Panggil API untuk mengambil daftar dokter menggunakan api.js
        const response = await api.get(
          'dokter',
          {
            headers: {
              'Authorization': `Bearer ${token}`,
              'Content-Type': 'application/json'
            }
          }
        );
        
        if (response.data && response.data.data) {
          this.dokterList = response.data.data;
          this.filteredDokter = [...this.dokterList];
        } else {
          throw new Error('Format response tidak sesuai');
        }
      } catch (err) {
        console.error('Error saat mengambil daftar dokter:', err);
        
        if (err.response?.status === 401) {
          this.error = 'Tidak memiliki akses. Silakan login kembali untuk mendapatkan token yang valid.';
        } else {
          this.error = err.response?.data?.message || err.message || 'Gagal memuat daftar dokter. Silakan coba lagi.';
        }
      } finally {
        this.loading = false;
      }
    },
    
    formatDate(dateString) {
      if (!dateString) return '-';
      const options = { day: 'numeric', month: 'long', year: 'numeric' };
      return new Date(dateString).toLocaleDateString('id-ID', options);
    },
    
    filterDokter() {
      if (!this.searchQuery.trim()) {
        this.filteredDokter = [...this.dokterList];
      } else {
        const query = this.searchQuery.toLowerCase();
        this.filteredDokter = this.dokterList.filter(dokter => 
          dokter.nama?.toLowerCase().includes(query) ||
          dokter.kodeAkses?.toLowerCase().includes(query) ||
          dokter.phone?.includes(query)
        );
      }
      
      // Reset ke halaman pertama setelah pencarian
      this.currentPage = 1;
    },
    
    changePage(page) {
      if (page >= 1 && page <= this.totalPages) {
        this.currentPage = page;
      }
    },
    
    confirmDelete(dokter) {
      this.selectedDokter = dokter;
      this.deleteModal.show();
    },
    
    async deleteDokter() {
      if (!this.selectedDokter) return;
      
      this.deleting = true;
      
      try {
        // Dapatkan token dari localStorage atau sessionStorage
        const token = localStorage.getItem("token") || sessionStorage.getItem("token");
        
        if (!token) {
          throw new Error('Token autentikasi tidak ditemukan. Silakan login terlebih dahulu.');
        }
        
        // Panggil API untuk menghapus dokter menggunakan api.js
        await api.delete(
          `dokter/${this.selectedDokter.id}`,
          {
            headers: {
              'Authorization': `Bearer ${token}`,
              'Content-Type': 'application/json'
            }
          }
        );
        
        // Tutup modal
        this.deleteModal.hide();
        
        // Perbarui daftar dokter setelah menghapus
        this.dokterList = this.dokterList.filter(dokter => dokter.id !== this.selectedDokter.id);
        this.filterDokter(); // Perbarui filteredDokter juga
        
        // Tampilkan notifikasi sukses
        this.$nextTick(() => {
          alert('Dokter berhasil dihapus!');
        });
        
      } catch (err) {
        console.error('Error saat menghapus dokter:', err);
        
        // Tampilkan pesan error
        alert(err.response?.data?.message || err.message || 'Gagal menghapus dokter. Silakan coba lagi.');
      } finally {
        // Reset state
        this.deleting = false;
        this.selectedDokter = null;
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
  vertical-align: middle;
}

.btn-group .btn {
  margin-right: 3px;
}
</style>