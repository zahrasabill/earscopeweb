<template>
  <div class="container mt-4">
    <div class="card">
      <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
        <h4 class="mb-0">Daftar Pasien</h4>
        <router-link to="/pasien/create" class="btn btn-sm btn-light">
          <i class="bi bi-plus-lg me-1"></i> Tambah Pasien
        </router-link>
      </div>
      <div class="card-body">
        <div v-if="loading" class="text-center my-5">
          <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">Loading...</span>
          </div>
          <p class="mt-2">Memuat data pasien...</p>
        </div>
        
        <div v-else-if="error" class="alert alert-danger">
          <i class="bi bi-exclamation-triangle-fill me-2"></i>
          {{ error }}
        </div>
        
        <div v-else-if="pasienList.length === 0" class="text-center my-5">
          <i class="bi bi-inbox-fill text-muted" style="font-size: 3rem;"></i>
          <p class="mt-3 text-muted">Belum ada data pasien tersimpan</p>
          <router-link to="/pasien/create" class="btn btn-primary mt-2">
            <i class="bi bi-plus-lg me-1"></i> Tambah Pasien Baru
          </router-link>
        </div>
        
        <div v-else>
          <!-- Search and filter section -->
          <div class="row mb-3">
            <div class="col-md-6">
              <div class="input-group">
                <span class="input-group-text"><i class="bi bi-search"></i></span>
                <input
                  type="text"
                  class="form-control"
                  placeholder="Cari nama pasien..."
                  v-model="searchQuery"
                  @input="handleSearch"
                />
                <button class="btn btn-outline-secondary" type="button" @click="resetFilter">
                  <i class="bi bi-x-lg"></i>
                </button>
              </div>
            </div>
            <div class="col-md-6 text-md-end mt-2 mt-md-0">
              <select class="form-select w-auto d-inline-block ms-md-auto" v-model="genderFilter" @change="handleFilter">
                <option value="">Semua Gender</option>
                <option value="laki-laki">Laki-laki</option>
                <option value="perempuan">Perempuan</option>
              </select>
            </div>
          </div>
          
          <!-- Table section -->
          <div class="table-responsive">
            <table class="table table-striped table-hover align-middle">
              <thead class="table-light">
                <tr>
                  <th width="5%">No</th>
                  <th width="20%">Nama</th>
                  <th width="15%">Tanggal Lahir</th>
                  <th width="10%">Umur</th>
                  <th width="15%">Nomor Telepon</th>
                  <th width="10%">Gender</th>
                  <th width="25%" class="text-center">Aksi</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="(pasien, index) in paginatedPasiens" :key="pasien.id">
                  <td>{{ startIndex + index + 1 }}</td>
                  <td>{{ pasien.name }}</td>
                  <td>{{ formatDate(pasien.tanggal_lahir) }}</td>
                  <td>{{ pasien.usia }} tahun</td>
                  <td>+62{{ pasien.no_telp }}</td>
                  <td>
                    <span 
                      :class="[
                        'badge', 
                        pasien.gender === 'laki-laki' ? 'bg-primary' : 'bg-info'
                      ]"
                    >
                      {{ capitalizeFirst(pasien.gender) }}
                    </span>
                  </td>
                  <td>
                    <div class="d-flex justify-content-center gap-2">
                      <router-link :to="`/pasien/${pasien.id}`" class="btn btn-sm btn-info text-white">
                        <i class="bi bi-eye"></i>
                      </router-link>
                      <router-link :to="`/pasien/${pasien.id}/edit`" class="btn btn-sm btn-warning text-white">
                        <i class="bi bi-pencil"></i>
                      </router-link>
                      <button 
                        @click="confirmDelete(pasien)" 
                        class="btn btn-sm btn-danger"
                      >
                        <i class="bi bi-trash"></i>
                      </button>
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
          
          <!-- Pagination -->
          <div class="d-flex justify-content-between align-items-center mt-3">
            <div>
              <span class="text-muted">Menampilkan {{ Math.min(totalItems, startIndex + 1) }}-{{ Math.min(totalItems, startIndex + itemsPerPage) }} dari {{ totalItems }} pasien</span>
            </div>
            <nav aria-label="Page navigation">
              <ul class="pagination mb-0">
                <li class="page-item" :class="{ disabled: currentPage === 1 }">
                  <a class="page-link" href="#" @click.prevent="goToPage(currentPage - 1)">
                    <i class="bi bi-chevron-left"></i>
                  </a>
                </li>
                <li 
                  v-for="page in totalPages" 
                  :key="page" 
                  class="page-item"
                  :class="{ active: page === currentPage }"
                >
                  <a class="page-link" href="#" @click.prevent="goToPage(page)">{{ page }}</a>
                </li>
                <li class="page-item" :class="{ disabled: currentPage === totalPages }">
                  <a class="page-link" href="#" @click.prevent="goToPage(currentPage + 1)">
                    <i class="bi bi-chevron-right"></i>
                  </a>
                </li>
              </ul>
            </nav>
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
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <p>Apakah Anda yakin ingin menghapus pasien <strong>{{ selectedPasien?.name }}</strong>?</p>
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
import { debounce } from 'lodash';

export default {
  name: 'ListPasien',
  data() {
    return {
      pasienList: [],
      filteredPasiens: [],
      searchQuery: '',
      genderFilter: '',
      currentPage: 1,
      itemsPerPage: 10,
      totalItems: 0,
      totalPages: 0,
      loading: true,
      error: null,
      selectedPasien: null,
      deleteModal: null,
      deleteLoading: false,
      debouncedSearch: null
    };
  },
  computed: {
    startIndex() {
      return (this.currentPage - 1) * this.itemsPerPage;
    },
    paginatedPasiens() {
      return this.filteredPasiens.slice(this.startIndex, this.startIndex + this.itemsPerPage);
    }
  },
  created() {
    // Initialize debounced search
    this.debouncedSearch = debounce(() => {
      this.applyFilters();
    }, 300);
    
    this.fetchPasienData();
    
    // Listen for updates from other components
    window.addEventListener('storage', (event) => {
      if (event.key === 'pasienDataUpdated') {
        this.fetchPasienData();
      }
    });
  },
  mounted() {
    // Check if data was updated from another component
    const updated = localStorage.getItem('pasienDataUpdated');
    if (updated) {
      localStorage.removeItem('pasienDataUpdated');
      this.fetchPasienData();
    }
  },
  methods: {
async fetchPasienData() {
  this.loading = true;
  this.error = null;
  
  try {
    const token = localStorage.getItem("token") || sessionStorage.getItem("token");
    
    if (!token) {
      throw new Error('Token autentikasi tidak ditemukan. Silakan login terlebih dahulu.');
    }
    
    // Build query parameters
    const params = {
      page: this.currentPage,
      limit: this.itemsPerPage
    };
    
    // Add search term if exists
    if (this.searchQuery.trim()) {
      params.search = this.searchQuery.trim();
    }
    
    const response = await api.get('pasien', {
      headers: {
        'Authorization': `Bearer ${token}`
      },
      params: params
    });
    
    // Periksa apakah response memiliki data
    if (response.data) {
      // Periksa format respons dan tangani kedua kemungkinan format
      if (Array.isArray(response.data)) {
        // Jika response.data langsung array, berarti format adalah API lama
        console.log('Menggunakan format respons array');
        this.pasienList = response.data;
        this.filteredPasiens = [...this.pasienList];
        this.totalItems = response.data.length;
        this.totalPages = Math.ceil(this.totalItems / this.itemsPerPage);
      } 
      else if (response.data.data && Array.isArray(response.data.data)) {
        // Format respons adalah { data: [...] }
        console.log('Menggunakan format respons data array');
        this.pasienList = response.data.data;
        this.filteredPasiens = [...this.pasienList];
        this.totalItems = response.data.data.length;
        this.totalPages = Math.ceil(this.totalItems / this.itemsPerPage);
      }
      else if (response.data.data && response.data.data.items) {
        // Format respons adalah { data: { items: [...], total: X, pages: Y } }
        console.log('Menggunakan format respons pagination');
        this.pasienList = response.data.data.items || [];
        this.filteredPasiens = [...this.pasienList];
        this.totalItems = response.data.data.total || 0;
        this.totalPages = response.data.data.pages || 1;
      } 
      else {
        throw new Error('Format response tidak dikenali');
      }
      
      console.log('Data pasien berhasil dimuat:', this.pasienList.length);
    } else {
      throw new Error('Tidak ada data dari server');
    }
  } catch (err) {
    console.error('Error saat memuat data pasien:', err);
    
    if (err.response) {
      if (err.response.status === 401) {
        this.error = 'Sesi login Anda telah berakhir. Silakan login kembali.';
        setTimeout(() => {
          this.$router.push('/login');
        }, 2000);
      } else {
        this.error = `Error: ${err.response.data?.message || 'Terjadi kesalahan saat memuat data pasien'}`;
      }
    } else {
      this.error = err.message || 'Terjadi kesalahan saat memuat data pasien. Silakan coba lagi.';
    }
  } finally {
    this.loading = false;
  }
},
    
    formatDate(dateString) {
      if (!dateString) return '-';
      
      try {
        const date = new Date(dateString);
        return date.toLocaleDateString('id-ID', {
          day: 'numeric',
          month: 'long',
          year: 'numeric'
        });
      } catch (error) {
        return dateString;
      }
    },
    
    capitalizeFirst(string) {
      if (!string) return '';
      return string.charAt(0).toUpperCase() + string.slice(1);
    },
    
    handleSearch() {
      this.debouncedSearch();
    },
    
    handleFilter() {
      this.applyFilters();
      this.currentPage = 1;
    },
    
    applyFilters() {
      let filtered = [...this.pasienList];
      
      // Apply search filter
      if (this.searchQuery) {
        const query = this.searchQuery.toLowerCase();
        filtered = filtered.filter(pasien => 
          pasien.name.toLowerCase().includes(query)
        );
      }
      
      // Apply gender filter
      if (this.genderFilter) {
        filtered = filtered.filter(pasien => 
          pasien.gender === this.genderFilter
        );
      }
      
      this.filteredPasiens = filtered;
      this.currentPage = 1;
      
      // Recalculate total pages based on filtered results
      this.totalPages = Math.ceil(this.filteredPasiens.length / this.itemsPerPage);
    },
    
    resetFilter() {
      this.searchQuery = '';
      this.genderFilter = '';
      this.filteredPasiens = [...this.pasienList];
      this.currentPage = 1;
      this.totalPages = Math.ceil(this.filteredPasiens.length / this.itemsPerPage);
    },
    
    goToPage(page) {
      if (page < 1 || page > this.totalPages) return;
      this.currentPage = page;
    },
    
    confirmDelete(pasien) {
      this.selectedPasien = pasien;
      
      // Initialize modal if needed
      if (!this.deleteModal) {
        const modalElement = document.getElementById('deleteModal');
        this.deleteModal = new Modal(modalElement);
      }
      
      this.deleteModal.show();
    },
    
    async deletePasien() {
      if (!this.selectedPasien) return;
      
      this.deleteLoading = true;
      
      try {
        const token = localStorage.getItem("token") || sessionStorage.getItem("token");
        
        if (!token) {
          throw new Error('Token autentikasi tidak ditemukan');
        }
        
        await api.delete(`pasien/${this.selectedPasien.id}`, {
          headers: {
            'Authorization': `Bearer ${token}`
          }
        });
        
        // Remove from local array
        this.pasienList = this.pasienList.filter(p => p.id !== this.selectedPasien.id);
        this.applyFilters();
        
        // Update UI
        this.deleteModal.hide();
        
        // Show success message (optional)
        this.$toast?.success('Pasien berhasil dihapus', {
          position: 'top-right',
          duration: 3000
        });
      } catch (err) {
        console.error('Error saat menghapus pasien:', err);
        this.$toast?.error(
          err.response?.data?.message || 'Gagal menghapus pasien', 
          { position: 'top-right', duration: 5000 }
        );
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
  border-radius: 8px;
  border: none;
}

.card-header {
  border-radius: 8px 8px 0 0;
}

.table {
  margin-bottom: 0;
}

.table th {
  font-weight: 600;
}

.badge {
  font-weight: 500;
  padding: 0.5em 0.75em;
}

.btn-sm {
  padding: 0.25rem 0.5rem;
}

.pagination .page-link {
  color: #0d6efd;
}

.pagination .page-item.active .page-link {
  background-color: #0d6efd;
  border-color: #0d6efd;
  color: white;
}

.modal-content {
  border-radius: 8px;
  overflow: hidden;
}
</style>