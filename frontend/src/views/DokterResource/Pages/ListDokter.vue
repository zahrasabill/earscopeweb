<template>
  <div class="container mt-4">
    <div class="card">
      <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
        <h4 class="mb-0">Daftar Dokter</h4>
        <router-link to="/dokter/create" class="btn btn-sm btn-light">
          <i class="bi bi-plus-lg me-1"></i> Tambah Dokter
        </router-link>
      </div>
      <div class="card-body">
        <div v-if="loading" class="text-center my-5">
          <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">Loading...</span>
          </div>
          <p class="mt-2">Memuat data dokter...</p>
        </div>
        
        <div v-else-if="error" class="alert alert-danger">
          <i class="bi bi-exclamation-triangle-fill me-2"></i>
          {{ error }}
        </div>
        
        <div v-else-if="dokters.length === 0" class="text-center my-5">
          <i class="bi bi-inbox-fill text-muted" style="font-size: 3rem;"></i>
          <p class="mt-3 text-muted">Belum ada data dokter tersimpan</p>
          <router-link to="/dokter/create" class="btn btn-primary mt-2">
            <i class="bi bi-plus-lg me-1"></i> Tambah Dokter Baru
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
                  placeholder="Cari nama dokter..."
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
                  <th width="25%">Nama</th>
                  <th width="15%">Tanggal Lahir</th>
                  <th width="15%">Nomor Telepon</th>
                  <th width="10%">Gender</th>
                  <th width="15%">No STR</th>
                  <th width="15%" class="text-center">Aksi</th>
                </tr>
              </thead>
              <tbody>
                <tr 
                  v-for="(dokter, index) in paginatedDokters" 
                  :key="dokter.id"
                  :class="{ 'deleted-row': dokter.deleted_at }"
                >
                  <td>{{ startIndex + index + 1 }}</td>
                  <td>
                    <span v-if="dokter.deleted_at" class="text-muted text-decoration-line-through">
                      {{ dokter.name }} 
                      <small class="badge bg-secondary ms-2">Dihapus</small>
                    </span>
                    <span v-else>{{ dokter.name }}</span>
                  </td>
                  <td>
                    <span :class="{ 'text-muted': dokter.deleted_at }">
                      {{ formatDate(dokter.tanggal_lahir) }}
                    </span>
                  </td>
                  <td>
                    <span :class="{ 'text-muted': dokter.deleted_at }">
                      +62{{ dokter.no_telp }}
                    </span>
                  </td>
                  <td>
                    <span 
                      :class="[
                        'badge', 
                        dokter.deleted_at ? 'bg-secondary' : (dokter.gender === 'laki-laki' ? 'bg-primary' : 'bg-info')
                      ]"
                    >
                      {{ capitalizeFirst(dokter.gender) }}
                    </span>
                  </td>
                  <td>
                    <span :class="{ 'text-muted': dokter.deleted_at }">
                      {{ dokter.no_str || '-' }}
                    </span>
                  </td>
                  <td>
                    <div class="d-flex justify-content-center gap-2">
                      <template v-if="!dokter.deleted_at">
                        <router-link :to="`/dokter/view/${dokter.id}`" class="btn btn-sm btn-info text-white">
                          <i class="bi bi-eye"></i>
                        </router-link>
                        <router-link :to="`/dokter/edit/${dokter.id}`" class="btn btn-sm btn-warning text-white">
                          <i class="bi bi-pencil"></i>
                        </router-link>
                        <button 
                          @click="confirmDelete(dokter)" 
                          class="btn btn-sm btn-danger"
                        >
                          <i class="bi bi-trash"></i>
                        </button>
                      </template>
                      <template v-else>
                        <button 
                          @click="confirmRestore(dokter)" 
                          class="btn btn-sm btn-success"
                          title="Pulihkan dokter"
                        >
                          <i class="bi bi-arrow-clockwise"></i>
                        </button>
                        <button 
                          @click="confirmPermanentDelete(dokter)" 
                          class="btn btn-sm btn-outline-danger"
                          title="Hapus permanen"
                        >
                          <i class="bi bi-trash-fill"></i>
                        </button>
                      </template>
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
          
          <!-- Pagination -->
          <div class="d-flex justify-content-between align-items-center mt-3">
            <div>
              <span class="text-muted">Menampilkan {{ Math.min(dokters.length, startIndex + 1) }}-{{ Math.min(dokters.length, startIndex + itemsPerPage) }} dari {{ dokters.length }} dokter</span>
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
            <p>Apakah Anda yakin ingin menghapus dokter <strong>{{ selectedDokter?.name }}</strong>?</p>
            <p class="text-danger"><small>Data dokter akan dipindahkan ke arsip dan dapat dipulihkan kembali.</small></p>
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

    <!-- Modal Konfirmasi Pulihkan -->
    <div class="modal fade" id="restoreModal" tabindex="-1" aria-labelledby="restoreModalLabel" aria-hidden="true">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header bg-success text-white">
            <h5 class="modal-title" id="restoreModalLabel">Konfirmasi Pulihkan</h5>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <p>Apakah Anda yakin ingin memulihkan dokter <strong>{{ selectedDokter?.name }}</strong>?</p>
            <p class="text-success"><small>Data dokter akan dikembalikan ke daftar aktif.</small></p>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
            <button type="button" class="btn btn-success" @click="restoreDokter" :disabled="restoreLoading">
              <span v-if="restoreLoading" class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>
              Pulihkan
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal Konfirmasi Hapus Permanen -->
    <div class="modal fade" id="permanentDeleteModal" tabindex="-1" aria-labelledby="permanentDeleteModalLabel" aria-hidden="true">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header bg-danger text-white">
            <h5 class="modal-title" id="permanentDeleteModalLabel">Konfirmasi Hapus Permanen</h5>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <p>Apakah Anda yakin ingin menghapus permanen dokter <strong>{{ selectedDokter?.name }}</strong>?</p>
            <p class="text-danger"><strong>PERINGATAN: Tindakan ini tidak dapat dibatalkan dan data akan hilang selamanya!</strong></p>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
            <button type="button" class="btn btn-danger" @click="permanentDeleteDokter" :disabled="permanentDeleteLoading">
              <span v-if="permanentDeleteLoading" class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>
              Hapus Permanen
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
      dokters: [],
      filteredDokters: [],
      searchQuery: '',
      genderFilter: '',
      loading: true,
      error: null,
      currentPage: 1,
      itemsPerPage: 10,
      selectedDokter: null,
      deleteModal: null,
      restoreModal: null,
      permanentDeleteModal: null,
      deleteLoading: false,
      restoreLoading: false,
      permanentDeleteLoading: false
    };
  },
  computed: {
    startIndex() {
      return (this.currentPage - 1) * this.itemsPerPage;
    },
    paginatedDokters() {
      return this.filteredDokters.slice(this.startIndex, this.startIndex + this.itemsPerPage);
    },
    totalPages() {
      return Math.ceil(this.filteredDokters.length / this.itemsPerPage);
    }
  },
  created() {
    this.fetchDokters();
    
    // Listen for updates from other components
    window.addEventListener('storage', (event) => {
      if (event.key === 'dokterDataUpdated') {
        this.fetchDokters();
      }
    });
  },
  mounted() {
    // Check if data was updated from another component
    const updated = localStorage.getItem('dokterDataUpdated');
    if (updated) {
      localStorage.removeItem('dokterDataUpdated');
      this.fetchDokters();
    }
  },
  methods: {
    async fetchDokters() {
      this.loading = true;
      this.error = null;
      
      try {
        const token = localStorage.getItem("token") || sessionStorage.getItem("token");
        
        if (!token) {
          throw new Error('Token autentikasi tidak ditemukan. Silakan login terlebih dahulu.');
        }
        
        // Fetch both active and deleted doctors
        const response = await api.get('dokter?include_deleted=true', {
          headers: {
            'Authorization': `Bearer ${token}`
          }
        });
        
        if (Array.isArray(response.data)) {
          this.dokters = response.data;
          this.filteredDokters = [...this.dokters];
          console.log('Data dokter berhasil dimuat:', this.dokters.length);
        } else {
          throw new Error('Format response tidak sesuai');
        }
      } catch (err) {
        console.error('Error saat memuat data dokter:', err);
        
        if (err.response) {
          if (err.response.status === 401) {
            this.error = 'Sesi login Anda telah berakhir. Silakan login kembali.';
            setTimeout(() => {
              this.$router.push('/login');
            }, 2000);
          } else {
            this.error = `Error: ${err.response.data.message || 'Terjadi kesalahan saat memuat data dokter'}`;
          }
        } else {
          this.error = err.message || 'Terjadi kesalahan saat memuat data dokter. Silakan coba lagi.';
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
      this.applyFilters();
      this.currentPage = 1;
    },
    
    handleFilter() {
      this.applyFilters();
      this.currentPage = 1;
    },
    
    applyFilters() {
      let filtered = [...this.dokters];
      
      // Apply search filter
      if (this.searchQuery) {
        const query = this.searchQuery.toLowerCase();
        filtered = filtered.filter(dokter => 
          dokter.name.toLowerCase().includes(query)
        );
      }
      
      // Apply gender filter
      if (this.genderFilter) {
        filtered = filtered.filter(dokter => 
          dokter.gender === this.genderFilter
        );
      }
      
      this.filteredDokters = filtered;
    },
    
    resetFilter() {
      this.searchQuery = '';
      this.genderFilter = '';
      this.filteredDokters = [...this.dokters];
      this.currentPage = 1;
    },
    
    goToPage(page) {
      if (page < 1 || page > this.totalPages) return;
      this.currentPage = page;
    },
    
    confirmDelete(dokter) {
      this.selectedDokter = dokter;
      
      if (!this.deleteModal) {
        const modalElement = document.getElementById('deleteModal');
        this.deleteModal = new Modal(modalElement);
      }
      
      this.deleteModal.show();
    },
    
    confirmRestore(dokter) {
      this.selectedDokter = dokter;
      
      if (!this.restoreModal) {
        const modalElement = document.getElementById('restoreModal');
        this.restoreModal = new Modal(modalElement);
      }
      
      this.restoreModal.show();
    },
    
    confirmPermanentDelete(dokter) {
      this.selectedDokter = dokter;
      
      if (!this.permanentDeleteModal) {
        const modalElement = document.getElementById('permanentDeleteModal');
        this.permanentDeleteModal = new Modal(modalElement);
      }
      
      this.permanentDeleteModal.show();
    },
    
    async deleteDokter() {
      if (!this.selectedDokter) return;
      this.deleteLoading = true;
      try {
        const token = localStorage.getItem("token") || sessionStorage.getItem("token");
        if (!token) {
          throw new Error('Token autentikasi tidak ditemukan');
        }
        
        // Soft delete
        await api.delete(`users/${this.selectedDokter.id}`, {
          headers: {
            'Authorization': `Bearer ${token}`
          }
        });
        
        this.deleteModal.hide();
        
        // Update the local data to reflect the soft delete
        const dokterIndex = this.dokters.findIndex(d => d.id === this.selectedDokter.id);
        if (dokterIndex !== -1) {
          this.dokters[dokterIndex].deleted_at = new Date().toISOString();
          this.applyFilters();
        }
        
        console.log('Dokter berhasil dihapus (soft delete)');
      } catch (err) {
        console.error('Error saat menghapus dokter:', err);
        alert(`Gagal menghapus dokter: ${err.response?.data?.message || err.message}`);
      } finally {
        this.deleteLoading = false;
      }
    },
    
    async restoreDokter() {
      if (!this.selectedDokter) return;
      this.restoreLoading = true;
      try {
        const token = localStorage.getItem("token") || sessionStorage.getItem("token");
        if (!token) {
          throw new Error('Token autentikasi tidak ditemukan');
        }
        
        // Restore the deleted doctor
        await api.post(`users/${this.selectedDokter.id}/restore`, {}, {
          headers: {
            'Authorization': `Bearer ${token}`
          }
        });
        
        this.restoreModal.hide();
        
        // Update the local data to reflect the restore
        const dokterIndex = this.dokters.findIndex(d => d.id === this.selectedDokter.id);
        if (dokterIndex !== -1) {
          this.dokters[dokterIndex].deleted_at = null;
          this.applyFilters();
        }
        
        console.log('Dokter berhasil dipulihkan');
      } catch (err) {
        console.error('Error saat memulihkan dokter:', err);
        alert(`Gagal memulihkan dokter: ${err.response?.data?.message || err.message}`);
      } finally {
        this.restoreLoading = false;
      }
    },
    
    async permanentDeleteDokter() {
      if (!this.selectedDokter) return;
      this.permanentDeleteLoading = true;
      try {
        const token = localStorage.getItem("token") || sessionStorage.getItem("token");
        if (!token) {
          throw new Error('Token autentikasi tidak ditemukan');
        }
        
        // Permanent delete
        await api.delete(`users/${this.selectedDokter.id}?force=true`, {
          headers: {
            'Authorization': `Bearer ${token}`
          }
        });
        
        this.permanentDeleteModal.hide();
        
        // Remove from local data
        this.dokters = this.dokters.filter(d => d.id !== this.selectedDokter.id);
        this.applyFilters();
        
        console.log('Dokter berhasil dihapus permanen');
      } catch (err) {
        console.error('Error saat menghapus permanen dokter:', err);
        alert(`Gagal menghapus permanen dokter: ${err.response?.data?.message || err.message}`);
      } finally {
        this.permanentDeleteLoading = false;
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

.deleted-row {
  background-color: #f8f9fa;
  opacity: 0.7;
}

.text-decoration-line-through {
  text-decoration: line-through;
}
</style>