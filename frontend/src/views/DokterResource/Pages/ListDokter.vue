<template>
  <div class="container mt-4">
    <div class="card">
      <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
        <h4>Daftar Dokter</h4>
        <button class="btn btn-light" @click="$router.push('/dokter/create')">
          <i class="bi bi-plus-lg me-1"></i> Tambah Dokter
        </button>
      </div>
      <div class="card-body">
        <!-- Loading indicator -->
        <div v-if="loading" class="text-center my-3">
          <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">Loading...</span>
          </div>
        </div>

        <!-- Error alert -->
        <div v-if="error" class="alert alert-danger">
          {{ error }}
          <button type="button" class="btn-close float-end" @click="error = null"></button>
        </div>

        <!-- Search and filter -->
        <div class="row mb-3">
          <div class="col-md-6">
            <div class="input-group">
              <span class="input-group-text">
                <i class="bi bi-search"></i>
              </span>
              <input
                type="text"
                class="form-control"
                placeholder="Cari dokter..."
                v-model="search"
                @input="searchDokter"
              />
            </div>
          </div>
          <div class="col-md-6">
            <div class="d-flex justify-content-md-end">
              <select class="form-select w-50" v-model="filter" @change="searchDokter">
                <option value="all">Semua Gender</option>
                <option value="laki-laki">Laki-laki</option>
                <option value="perempuan">Perempuan</option>
              </select>
            </div>
          </div>
        </div>

        <!-- Data table -->
        <div class="table-responsive">
          <table class="table table-striped table-hover">
            <thead>
              <tr>
                <th>No</th>
                <th>Nama</th>
                <th>Kode Akses</th>
                <th>Tanggal Lahir</th>
                <th>Nomor Telepon</th>
                <th>Gender</th>
                <th>Aksi</th>
              </tr>
            </thead>
            <tbody v-if="dokterList.length > 0">
              <tr v-for="(dokter, index) in dokterList" :key="dokter.id">
                <td>{{ startIndex + index + 1 }}</td>
                <td>{{ dokter.nama }}</td>
                <td>
                  <div class="d-flex align-items-center">
                    <span class="me-2">{{ dokter.kodeAkses || '-' }}</span>
                    <button 
                      v-if="dokter.kodeAkses"
                      class="btn btn-sm btn-outline-secondary border-0" 
                      @click="copyKodeAkses(dokter.kodeAkses)"
                      title="Salin kode akses"
                    >
                      <i class="bi bi-clipboard"></i>
                    </button>
                  </div>
                </td>
                <td>{{ formatDate(dokter.tanggalLahir) }}</td>
                <td>+62{{ dokter.phone }}</td>
                <td>
                  <span 
                    :class="dokter.gender === 'laki-laki' ? 'badge bg-primary' : 'badge bg-info'"
                  >
                    {{ capitalizeFirst(dokter.gender) }}
                  </span>
                </td>
                <td>
                  <div class="btn-group">
                    <button class="btn btn-sm btn-info me-1" @click="viewDokter(dokter.id)">
                      <i class="bi bi-eye"></i>
                    </button>
                    <button class="btn btn-sm btn-warning me-1" @click="editDokter(dokter.id)">
                      <i class="bi bi-pencil"></i>
                    </button>
                    <button class="btn btn-sm btn-danger" @click="confirmDelete(dokter.id, dokter.nama)">
                      <i class="bi bi-trash"></i>
                    </button>
                  </div>
                </td>
              </tr>
            </tbody>
            <tbody v-else>
              <tr>
                <td colspan="7" class="text-center py-3">
                  <div class="alert alert-info mb-0">
                    <i class="bi bi-info-circle me-2"></i>
                    Tidak ada data dokter yang tersedia.
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Pagination -->
        <div class="d-flex justify-content-between align-items-center mt-3">
          <div>
            <span class="text-muted">Menampilkan {{ dokterList.length }} dari {{ totalDokter }} data</span>
          </div>
          <nav v-if="totalPages > 1">
            <ul class="pagination mb-0">
              <li class="page-item" :class="{ disabled: currentPage === 1 }">
                <a class="page-link" href="#" @click.prevent="changePage(currentPage - 1)">
                  <i class="bi bi-chevron-left"></i>
                </a>
              </li>
              <li 
                v-for="page in displayedPages" 
                :key="page" 
                class="page-item"
                :class="{ active: currentPage === page }"
              >
                <a class="page-link" href="#" @click.prevent="changePage(page)">{{ page }}</a>
              </li>
              <li class="page-item" :class="{ disabled: currentPage === totalPages }">
                <a class="page-link" href="#" @click.prevent="changePage(currentPage + 1)">
                  <i class="bi bi-chevron-right"></i>
                </a>
              </li>
            </ul>
          </nav>
        </div>
      </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header bg-danger text-white">
            <h5 class="modal-title" id="deleteModalLabel">Konfirmasi Hapus</h5>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <p>Apakah Anda yakin ingin menghapus dokter <strong>{{ selectedDokterName }}</strong>?</p>
            <p class="text-danger"><small>Tindakan ini tidak dapat dibatalkan.</small></p>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
              <i class="bi bi-x-lg me-1"></i> Batal
            </button>
            <button type="button" class="btn btn-danger" @click="deleteDokter" :disabled="deletingDokter">
              <span v-if="deletingDokter" class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>
              <i v-else class="bi bi-trash me-1"></i> Hapus
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Toast notification for copy -->
    <div class="position-fixed bottom-0 end-0 p-3" style="z-index: 11">
      <div 
        id="copyToast" 
        class="toast align-items-center text-white bg-success border-0" 
        role="alert" 
        aria-live="assertive" 
        aria-atomic="true"
      >
        <div class="d-flex">
          <div class="toast-body">
            <i class="bi bi-check-circle me-2"></i> Kode akses berhasil disalin!
          </div>
          <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { Modal, Toast } from 'bootstrap';
import api from '@/api';

export default {
  name: 'ListDokter',
  data() {
    return {
      dokterList: [],
      loading: false,
      error: null,
      search: '',
      filter: 'all',
      currentPage: 1,
      perPage: 10,
      totalDokter: 0,
      totalPages: 0,
      selectedDokterId: null,
      selectedDokterName: '',
      deleteModal: null,
      copyToast: null,
      deletingDokter: false,
      searchTimeout: null
    };
  },
  computed: {
    startIndex() {
      return (this.currentPage - 1) * this.perPage;
    },
    displayedPages() {
      const pages = [];
      const maxDisplayed = 5;
      
      if (this.totalPages <= maxDisplayed) {
        for (let i = 1; i <= this.totalPages; i++) {
          pages.push(i);
        }
      } else {
        // Always show first page
        pages.push(1);
        
        let startPage = Math.max(2, this.currentPage - 1);
        let endPage = Math.min(this.totalPages - 1, this.currentPage + 1);
        
        // Adjust if at beginning or end
        if (this.currentPage <= 2) {
          endPage = 3;
        } else if (this.currentPage >= this.totalPages - 1) {
          startPage = this.totalPages - 2;
        }
        
        // Add ellipsis if needed
        if (startPage > 2) {
          pages.push('...');
        }
        
        // Add middle pages
        for (let i = startPage; i <= endPage; i++) {
          pages.push(i);
        }
        
        // Add ellipsis if needed
        if (endPage < this.totalPages - 1) {
          pages.push('...');
        }
        
        // Always show last page
        pages.push(this.totalPages);
      }
      
      return pages;
    }
  },
  created() {
    this.fetchDokterList();
  },
  mounted() {
    // Initialize bootstrap components
    this.initializeBootstrapComponents();
    
    // Menambahkan event listener untuk route change
    this.$watch(
      () => this.$route.fullPath,
      () => {
        // Jika kembali ke halaman list dokter, refresh data
        if (this.$route.name === 'list-dokter') {
          this.fetchDokterList();
        }
      }
    );
    
    // Menambahkan event listener untuk storage changes (untuk deteksi perubahan antar tab)
    window.addEventListener('storage', this.handleStorageChange);
  },
  beforeUnmount() {
    // Remove event listener when component is destroyed
    window.removeEventListener('storage', this.handleStorageChange);
  },
  methods: {
    initializeBootstrapComponents() {
      // Initialize modal
      const modalElement = document.getElementById('deleteModal');
      if (modalElement) {
        this.deleteModal = new Modal(modalElement);
      }
      
      // Initialize toast
      const toastEl = document.getElementById('copyToast');
      if (toastEl) {
        this.copyToast = new Toast(toastEl, {
          delay: 2000
        });
      }
    },
    
    handleStorageChange(event) {
      // Jika ada perubahan pada dokter data di localStorage
      if (event.key === 'dokterDataUpdated') {
        this.fetchDokterList();
      }
    },
    
    async fetchDokterList() {
      this.loading = true;
      this.error = null;

      try {
        const token = localStorage.getItem("token") || sessionStorage.getItem("token");

        if (!token) {
          throw new Error('Token autentikasi tidak ditemukan. Silakan login terlebih dahulu.');
        }

        const params = {
          page: this.currentPage,
          limit: this.perPage
        };

        if (this.search) {
          params.search = this.search;
        }

        if (this.filter !== 'all') {
          params.gender = this.filter;
        }

        const response = await api.get('dokter', {
          headers: {
            'Authorization': `Bearer ${token}`
          },
          params
        });

        // Standarisasi format response
        this.processApiResponse(response);
      } catch (err) {
        console.error('Error saat mengambil data dokter:', err);
        this.handleApiError(err);
      } finally {
        this.loading = false;
      }
    },

    processApiResponse(response) {
      try {
        // Fungsi untuk standarisasi format response
        if (!response || !response.data) {
          throw new Error('Response tidak valid');
        }
        
        let data = response.data;
        let dokterData = [];
        let totalCount = 0;
        
        // Cek format response dan ekstrak data yang sesuai
        if (data.data && data.data.dokter) {
          // Format 1: { data: { dokter: [...], total: 100 } }
          dokterData = data.data.dokter;
          totalCount = data.data.total || dokterData.length;
        } else if (data.dokter) {
          // Format 2: { dokter: [...], total: 100 }
          dokterData = data.dokter;
          totalCount = data.total || dokterData.length;
        } else if (Array.isArray(data.data)) {
          // Format 3: { data: [...] }
          dokterData = data.data;
          totalCount = data.total || dokterData.length;
        } else if (Array.isArray(data)) {
          // Format 4: [...]
          dokterData = data;
          totalCount = dokterData.length;
        } else {
          throw new Error('Format response tidak dikenal');
        }
        
        // Update state
        this.dokterList = dokterData;
        this.totalDokter = totalCount;
        this.totalPages = Math.ceil(this.totalDokter / this.perPage);
        
      } catch (error) {
        console.error('Error saat memproses response:', error);
        this.error = 'Gagal memproses data dari server: ' + error.message;
      }
    },

    handleApiError(err) {
      let errorMessage = 'Gagal mengambil data dokter.';
      
      if (err.response) {
        // Error dari response API
        if (err.response.data && err.response.data.message) {
          errorMessage = err.response.data.message;
        } else {
          const status = err.response.status;
          if (status === 401) {
            errorMessage = 'Sesi Anda telah berakhir. Silakan login kembali.';
            // Redirect ke login
            setTimeout(() => {
              localStorage.removeItem('token');
              sessionStorage.removeItem('token');
              this.$router.push('/login');
            }, 2000);
          } else if (status === 403) {
            errorMessage = 'Anda tidak memiliki akses untuk melihat data dokter.';
          } else if (status === 404) {
            errorMessage = 'Data dokter tidak ditemukan.';
          } else if (status >= 500) {
            errorMessage = 'Terjadi kesalahan pada server. Silakan coba lagi nanti.';
          }
        }
      } else if (err.request) {
        // Error karena tidak ada response
        errorMessage = 'Tidak dapat terhubung ke server. Periksa koneksi internet Anda.';
      }
      
      this.error = errorMessage;
    },

    searchDokter() {
      // Debounce search to avoid too many requests
      clearTimeout(this.searchTimeout);
      this.searchTimeout = setTimeout(() => {
        this.currentPage = 1; // Reset to first page when searching
        this.fetchDokterList();
      }, 500);
    },

    changePage(page) {
      if (page >= 1 && page <= this.totalPages && page !== this.currentPage) {
        this.currentPage = page;
        this.fetchDokterList();
      }
    },

    viewDokter(id) {
      this.$router.push(`/dokter/${id}`);
    },

    editDokter(id) {
      this.$router.push(`/dokter/edit/${id}`);
    },

    confirmDelete(id, name) {
      this.selectedDokterId = id;
      this.selectedDokterName = name;
      
      if (!this.deleteModal) {
        this.initializeBootstrapComponents();
      }
      
      this.deleteModal.show();
    },

    async deleteDokter() {
      if (!this.selectedDokterId) return;
      
      this.deletingDokter = true;
      
      try {
        const token = localStorage.getItem("token") || sessionStorage.getItem("token");

        if (!token) {
          throw new Error('Token autentikasi tidak ditemukan');
        }

        await api.delete(`dokter/${this.selectedDokterId}`, {
          headers: {
            'Authorization': `Bearer ${token}`
          }
        });
        
        // Hide modal after successful deletion
        this.deleteModal.hide();
        
        // Notify other components/tabs about the change
        localStorage.setItem('dokterDataUpdated', Date.now().toString());
        
        // Show success alert
        this.error = null;
        
        // Refresh data
        this.fetchDokterList();
      } catch (err) {
        console.error('Error saat menghapus dokter:', err);
        this.handleApiError(err);
        
        // Hide modal
        this.deleteModal.hide();
      } finally {
        this.deletingDokter = false;
      }
    },

    copyKodeAkses(kodeAkses) {
      if (!kodeAkses) return;
      
      try {
        navigator.clipboard.writeText(kodeAkses).then(() => {
          // Show toast notification
          if (this.copyToast) {
            this.copyToast.show();
          }
        });
      } catch (err) {
        console.error('Gagal menyalin kode akses:', err);
        
        // Fallback untuk browser yang tidak mendukung Clipboard API
        const textArea = document.createElement('textarea');
        textArea.value = kodeAkses;
        textArea.style.position = 'fixed';  // Agar tidak mengubah layout
        document.body.appendChild(textArea);
        textArea.focus();
        textArea.select();
        
        try {
          const successful = document.execCommand('copy');
          if (successful && this.copyToast) {
            this.copyToast.show();
          }
        } catch (err) {
          console.error('Fallback: Gagal menyalin kode akses', err);
        }
        
        document.body.removeChild(textArea);
      }
    },

    formatDate(dateString) {
      if (!dateString) return '-';
      
      const date = new Date(dateString);
      
      if (isNaN(date.getTime())) {
        return dateString; // Return original if parsing failed
      }
      
      // Format as dd-MM-yyyy
      const day = String(date.getDate()).padStart(2, '0');
      const month = String(date.getMonth() + 1).padStart(2, '0');
      const year = date.getFullYear();
      
      return `${day}-${month}-${year}`;
    },

    capitalizeFirst(string) {
      if (!string) return '';
      return string.charAt(0).toUpperCase() + string.slice(1);
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

.table th {
  font-weight: 600;
  background-color: #f8f9fa;
}

.btn-group .btn {
  padding: 0.25rem 0.5rem;
}

.pagination {
  margin-bottom: 0;
}

.page-item.active .page-link {
  background-color: #0d6efd;
  border-color: #0d6efd;
}

.badge {
  padding: 0.35em 0.65em;
}

.modal-content {
  border-radius: 8px;
  border: none;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

.modal-header {
  border-radius: 8px 8px 0 0;
}

/* Styling for toast notification */
.toast {
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}
</style>