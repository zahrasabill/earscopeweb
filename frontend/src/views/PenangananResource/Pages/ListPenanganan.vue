<template>
  <div class="container-fluid list-penanganan-container">
    <!-- Header Section -->
    <div class="header-section mb-4">
      <div class="card shadow">
        <div class="card-body">
          <div class="row align-items-center">
            <div class="col-md-8">
              <h4 class="mb-0 text-gradient">
                <i class="fas fa-list-alt me-2"></i>Daftar Penanganan Pasien
              </h4>
            </div>
            <div class="col-md-4 text-end">
              <button 
                @click="navigateToForm" 
                class="btn btn-primary btn-lg"
                v-if="userRole === 'dokter'"
              >
                <i class="fas fa-plus me-2"></i>Tambah Penanganan
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Filters Section -->
    <div class="filters-section mb-4">
      <div class="card shadow-sm">
        <div class="card-body">
          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label fw-bold">
                <i class="fas fa-search me-1"></i>Cari Pasien
              </label>
              <input 
                type="text" 
                v-model="searchQuery" 
                class="form-control" 
                placeholder="Masukkan nama pasien..."
                @input="filterData"
              >
            </div>
            <div class="col-md-6">
              <label class="form-label fw-bold">
                <i class="fas fa-calendar me-1"></i>Filter Tanggal
              </label>
              <input 
                type="date" 
                v-model="selectedDate" 
                class="form-control"
                @change="filterData"
              >
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Table Section -->
    <div class="table-section">
      <div class="card shadow">
        <div class="card-header bg-gradient text-white">
          <h5 class="mb-0">
            <i class="fas fa-table me-2"></i>Data Penanganan
            <span class="badge bg-light text-dark ms-2">{{ filteredPenanganan.length }} Data</span>
          </h5>
        </div>
        <div class="card-body p-0">
          <!-- Loading State -->
          <div v-if="isLoading" class="text-center py-5">
            <div class="spinner-border text-primary" style="width: 3rem; height: 3rem;"></div>
            <p class="mt-3 fw-bold">Memuat data...</p>
          </div>

          <!-- Table - Always show header -->
          <div v-else class="table-responsive">
            <table class="table table-hover mb-0">
              <thead class="table-light">
                <tr>
                  <th scope="col" class="fw-bold">
                    <i class="fas fa-hashtag me-1"></i>No
                  </th>
                  <th scope="col" class="fw-bold">
                    <i class="fas fa-user me-1"></i>Nama Pasien
                  </th>
                  <th scope="col" class="fw-bold">
                    <i class="fas fa-calendar me-1"></i>Tanggal Penanganan
                  </th>
                  <th scope="col" class="fw-bold">
                    <i class="fas fa-exclamation-triangle me-1"></i>Keluhan Pasien
                  </th>
                  <th scope="col" class="fw-bold text-center">
                    <i class="fas fa-cogs me-1"></i>Action
                  </th>
                </tr>
              </thead>
              <tbody>
                <!-- Empty State -->
                <tr v-if="filteredPenanganan.length === 0">
                  <td colspan="5" class="text-center py-5">
                    <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                    <h5 class="text-muted">Tidak ada data penanganan</h5>
                  </td>
                </tr>
                
                <!-- Data Rows -->
                <tr v-else v-for="(item, index) in paginatedData" :key="item.id" class="table-row">
                  <td class="fw-bold text-primary">{{ getRowNumber(index) }}</td>
                  <td>
                    <div class="d-flex align-items-center">
                      <div class="avatar-circle me-2">
                        {{ getInitials(item.patient_name) }}
                      </div>
                      <div>
                        <div class="fw-bold">{{ item.patient_name }}</div>
                      </div>
                    </div>
                  </td>
                  <td>
                    <div class="fw-bold">{{ formatDate(item.tanggal_penanganan) }}</div>
                    <small class="text-muted">{{ formatTime(item.created_at) }}</small>
                  </td>
                  <td>
                    <div class="keluhan-preview" :title="item.keluhan">
                      {{ truncateText(item.keluhan, 60) }}
                    </div>
                  </td>
                  <td class="text-center">
                    <div class="btn-group" role="group">
                      <button
                        @click="viewDetail(item)"
                        class="btn btn-sm btn-outline-info"
                        title="Lihat Detail"
                      >
                        <i class="fas fa-eye"></i>
                      </button>
                      <button
                        v-if="userRole === 'dokter'"
                        @click="editPenanganan(item)"
                        class="btn btn-sm btn-outline-warning"
                        title="Edit Data"
                      >
                        <i class="fas fa-edit"></i>
                      </button>
                      <button
                        v-if="userRole === 'dokter'"
                        @click="deletePenanganan(item)"
                        class="btn btn-sm btn-outline-danger"
                        title="Hapus Data"
                        :disabled="isDeleting === item.id"
                      >
                        <span v-if="isDeleting === item.id" class="spinner-border spinner-border-sm"></span>
                        <i v-else class="fas fa-trash"></i>
                      </button>
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

          <!-- Pagination -->
          <div v-if="filteredPenanganan.length > 0" class="card-footer bg-light">
            <div class="row align-items-center">
              <div class="col-md-6">
                <div class="d-flex align-items-center">
                  <label class="form-label me-2 mb-0">Tampilkan:</label>
                  <select v-model="itemsPerPage" @change="updatePagination" class="form-select form-select-sm" style="width: auto;">
                    <option value="10">10</option>
                    <option value="25">25</option>
                    <option value="50">50</option>
                    <option value="100">100</option>
                  </select>
                  <span class="text-muted ms-2">dari {{ filteredPenanganan.length }} data</span>
                </div>
              </div>
              <div class="col-md-6">
                <nav aria-label="Pagination">
                  <ul class="pagination pagination-sm justify-content-end mb-0">
                    <li class="page-item" :class="{ 'disabled': currentPage === 1 }">
                      <button class="page-link" @click="changePage(currentPage - 1)" :disabled="currentPage === 1">
                        <i class="fas fa-chevron-left"></i>
                      </button>
                    </li>
                    <li 
                      v-for="page in visiblePages" 
                      :key="page" 
                      class="page-item" 
                      :class="{ 'active': page === currentPage }"
                    >
                      <button class="page-link" @click="changePage(page)">{{ page }}</button>
                    </li>
                    <li class="page-item" :class="{ 'disabled': currentPage === totalPages }">
                      <button class="page-link" @click="changePage(currentPage + 1)" :disabled="currentPage === totalPages">
                        <i class="fas fa-chevron-right"></i>
                      </button>
                    </li>
                  </ul>
                </nav>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Detail Modal -->
    <transition name="fade">
      <div v-if="showDetailModal" class="modal-overlay" @click.self="closeDetailModal">
        <div class="modal-dialog modal-lg">
          <div class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title">
                <i class="fas fa-info-circle me-2"></i>Detail Penanganan
              </h5>
              <button type="button" class="btn-close" @click="closeDetailModal"></button>
            </div>
            <div class="modal-body" v-if="selectedDetail">
              <div class="row">
                <div class="col-md-6">
                  <div class="detail-group mb-3">
                    <label class="detail-label">Nama Pasien</label>
                    <div class="detail-value">{{ selectedDetail.patient_name }}</div>
                  </div>
                  <div class="detail-group mb-3">
                    <label class="detail-label">Tanggal Penanganan</label>
                    <div class="detail-value">{{ formatDate(selectedDetail.tanggal_penanganan) }}</div>
                  </div>
                  <div class="detail-group mb-3">
                    <label class="detail-label">Telinga Terkena</label>
                    <div class="detail-value">
                      <span class="badge bg-secondary">{{ selectedDetail.telinga_terkena }}</span>
                    </div>
                  </div>
                  <div class="detail-group mb-3">
                    <label class="detail-label">Dibuat Pada</label>
                    <div class="detail-value">{{ formatDateTime(selectedDetail.created_at) }}</div>
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="detail-group mb-3">
                    <label class="detail-label">Status</label>
                    <div class="detail-value">
                      <span 
                        class="badge"
                        :class="selectedDetail.is_assigned ? 'bg-success' : 'bg-warning'"
                      >
                        {{ selectedDetail.is_assigned ? 'Assigned' : 'Unassigned' }}
                      </span>
                    </div>
                  </div>
                  <div class="detail-group mb-3">
                    <label class="detail-label">Terakhir Update</label>
                    <div class="detail-value">{{ formatDateTime(selectedDetail.updated_at) }}</div>
                  </div>
                </div>
              </div>
              
              <hr>
              
              <div class="row">
                <div class="col-12">
                  <div class="detail-group mb-3">
                    <label class="detail-label">Keluhan Pasien</label>
                    <div class="detail-value detail-text">{{ selectedDetail.keluhan }}</div>
                  </div>
                  <div class="detail-group mb-3">
                    <label class="detail-label">Riwayat Penyakit</label>
                    <div class="detail-value detail-text">{{ selectedDetail.riwayat_penyakit || 'Tidak ada riwayat penyakit' }}</div>
                  </div>
                  <div class="detail-group mb-3">
                    <label class="detail-label">Diagnosis Manual</label>
                    <div class="detail-value detail-text">{{ selectedDetail.diagnosis_manual }}</div>
                  </div>
                  <div class="detail-group mb-0">
                    <label class="detail-label">Tindakan</label>
                    <div class="detail-value detail-text">{{ selectedDetail.tindakan }}</div>
                  </div>
                </div>
              </div>
            </div>
            <div class="modal-footer">
              <button class="btn btn-secondary" @click="closeDetailModal">
                <i class="fas fa-times me-1"></i>Tutup
              </button>
            </div>
          </div>
        </div>
      </div>
    </transition>

    <!-- Delete Confirmation Modal -->
    <transition name="fade">
      <div v-if="showDeleteModal" class="modal-overlay" @click.self="closeDeleteModal">
        <div class="modal-dialog">
          <div class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title">
                <i class="fas fa-exclamation-triangle me-2 text-danger"></i>Konfirmasi Hapus
              </h5>
              <button type="button" class="btn-close" @click="closeDeleteModal"></button>
            </div>
            <div class="modal-body">
              <p>Apakah Anda yakin ingin menghapus data penanganan untuk pasien <strong>{{ deleteData?.patient_name }}</strong>?</p>
              <div class="alert alert-warning">
                <i class="fas fa-exclamation-triangle me-2"></i>
                <strong>Peringatan:</strong> Tindakan ini tidak dapat dibatalkan!
              </div>
            </div>
            <div class="modal-footer">
              <button class="btn btn-secondary" @click="closeDeleteModal">
                <i class="fas fa-times me-1"></i>Batal
              </button>
              <button 
                class="btn btn-danger"
                @click="confirmDelete"
                :disabled="isDeleting"
              >
                <span v-if="isDeleting" class="spinner-border spinner-border-sm me-2"></span>
                <i v-else class="fas fa-trash me-1"></i>
                {{ isDeleting ? 'Menghapus...' : 'Ya, Hapus' }}
              </button>
            </div>
          </div>
        </div>
      </div>
    </transition>
  </div>
</template>

<script>
import { ref, onMounted, computed } from 'vue';
import AppLayout from '@/components/AppLayout.vue';
import { useRouter } from 'vue-router';
import api from '@/api';
import axios from 'axios';
import { jwtDecode } from "jwt-decode";

export default {
  name: 'ListPenanganan',
  components: { AppLayout },
  setup() {
    const router = useRouter();
    
    // Data
    const penangananData = ref([]);
    const filteredPenanganan = ref([]);
    const isLoading = ref(false);
    const isDeleting = ref(null);
    
    // Filters
    const searchQuery = ref('');
    const selectedDate = ref('');
    
    // Pagination
    const currentPage = ref(1);
    const itemsPerPage = ref(10);
    
    // Modals
    const showDetailModal = ref(false);
    const showDeleteModal = ref(false);
    const selectedDetail = ref(null);
    const deleteData = ref(null);
    
    // User role
    const userRole = ref('pasien');
    
    // Computed properties
    const totalPages = computed(() => {
      return Math.ceil(filteredPenanganan.value.length / itemsPerPage.value);
    });
    
    const paginatedData = computed(() => {
      const start = (currentPage.value - 1) * itemsPerPage.value;
      const end = start + itemsPerPage.value;
      return filteredPenanganan.value.slice(start, end);
    });
    
    const visiblePages = computed(() => {
      const pages = [];
      const maxVisible = 5;
      let start = Math.max(1, currentPage.value - Math.floor(maxVisible / 2));
      let end = Math.min(totalPages.value, start + maxVisible - 1);
      
      // Adjust start if we're at the end
      if (end - start + 1 < maxVisible) {
        start = Math.max(1, end - maxVisible + 1);
      }
      
      for (let i = start; i <= end; i++) {
        pages.push(i);
      }
      
      return pages;
    });
    
    // Methods
    const getUserRoleFromToken = () => {
      try {
        const token = localStorage.getItem("token") || sessionStorage.getItem("token");
        if (token) {
          const payload = jwtDecode(token);
          return payload.role || 'pasien';
        }
        return 'pasien';
      } catch (error) {
        console.error('Error decoding token:', error);
        return 'pasien';
      }
    };
    
    const fetchPenangananData = async () => {
      try {
        isLoading.value = true;
        const token = localStorage.getItem("token") || sessionStorage.getItem("token");
        
        const response = await axios.get(api.getEndpoint('penanganan'), {
          headers: { Authorization: `Bearer ${token}` },
        });
        
        penangananData.value = response.data.data || [];
        filteredPenanganan.value = [...penangananData.value];
        
      } catch (error) {
        console.error('Error fetching penanganan data:', error);
        alert('Gagal memuat data penanganan. Silakan refresh halaman.');
      } finally {
        isLoading.value = false;
      }
    };
    
    const filterData = () => {
      let filtered = [...penangananData.value];
      
      // Filter by search query
      if (searchQuery.value.trim()) {
        const query = searchQuery.value.toLowerCase().trim();
        filtered = filtered.filter(item => 
          item.patient_name.toLowerCase().includes(query) ||
          item.keluhan.toLowerCase().includes(query)
        );
      }
      
      // Filter by date
      if (selectedDate.value) {
        filtered = filtered.filter(item => 
          item.tanggal_penanganan === selectedDate.value
        );
      }
      
      filteredPenanganan.value = filtered;
      currentPage.value = 1; // Reset to first page
    };
    
    const viewDetail = (item) => {
      selectedDetail.value = item;
      showDetailModal.value = true;
    };
    
    const editPenanganan = (item) => {
      // Navigate to edit form with item id
      router.push({ name: 'penanganan-edit', params: { id: item.id } });
    };
    
    const deletePenanganan = (item) => {
      deleteData.value = item;
      showDeleteModal.value = true;
    };
    
    const confirmDelete = async () => {
      try {
        isDeleting.value = deleteData.value.id;
        const token = localStorage.getItem("token") || sessionStorage.getItem("token");
        
        await axios.delete(`${api.getEndpoint('penanganan')}/${deleteData.value.id}`, {
          headers: { Authorization: `Bearer ${token}` },
        });
        
        // Remove from local data
        const index = penangananData.value.findIndex(item => item.id === deleteData.value.id);
        if (index !== -1) {
          penangananData.value.splice(index, 1);
        }
        
        // Refresh filtered data
        filterData();
        
        closeDeleteModal();
        
        // Show success message
        alert(`Data penanganan untuk pasien ${deleteData.value.patient_name} berhasil dihapus.`);
        
      } catch (error) {
        console.error('Error deleting penanganan:', error);
        alert('Gagal menghapus data penanganan. Silakan coba lagi.');
      } finally {
        isDeleting.value = null;
      }
    };
    
    const closeDetailModal = () => {
      showDetailModal.value = false;
      selectedDetail.value = null;
    };
    
    const closeDeleteModal = () => {
      showDeleteModal.value = false;
      deleteData.value = null;
    };
    
    const navigateToForm = () => {
      router.push({ name: 'create-penanganan' });
    };
    
    const changePage = (page) => {
      if (page >= 1 && page <= totalPages.value) {
        currentPage.value = page;
      }
    };
    
    const updatePagination = () => {
      currentPage.value = 1;
    };
    
    const getRowNumber = (index) => {
      return (currentPage.value - 1) * itemsPerPage.value + index + 1;
    };
    
    const getInitials = (name) => {
      return name.split(' ').map(n => n[0]).join('').toUpperCase().slice(0, 2);
    };
    
    const formatDate = (dateString) => {
      const date = new Date(dateString);
      return date.toLocaleDateString('id-ID', {
        year: 'numeric',
        month: 'long',
        day: 'numeric'
      });
    };
    
    const formatTime = (dateString) => {
      const date = new Date(dateString);
      return date.toLocaleTimeString('id-ID', {
        hour: '2-digit',
        minute: '2-digit'
      });
    };
    
    const formatDateTime = (dateString) => {
      const date = new Date(dateString);
      return date.toLocaleString('id-ID', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
      });
    };
    
    const truncateText = (text, maxLength) => {
      if (!text) return '';
      return text.length > maxLength ? text.substring(0, maxLength) + '...' : text;
    };
    
    onMounted(async () => {
      userRole.value = getUserRoleFromToken();
      await fetchPenangananData();
    });
    
    return {
      penangananData,
      filteredPenanganan,
      isLoading,
      isDeleting,
      searchQuery,
      selectedDate,
      currentPage,
      itemsPerPage,
      totalPages,
      paginatedData,
      visiblePages,
      showDetailModal,
      showDeleteModal,
      selectedDetail,
      deleteData,
      userRole,
      fetchPenangananData,
      filterData,
      viewDetail,
      editPenanganan,
      deletePenanganan,
      confirmDelete,
      closeDetailModal,
      closeDeleteModal,
      navigateToForm,
      changePage,
      updatePagination,
      getRowNumber,
      getInitials,
      formatDate,
      formatTime,
      formatDateTime,
      truncateText
    };
  }
};
</script>

<style scoped>
.list-penanganan-container {
  padding: 1.5rem;
}

.text-gradient {
  background: linear-gradient(45deg, #007bff, #0056b3);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
}

.bg-gradient {
  background: linear-gradient(135deg, #007bff, #0056b3);
}

.avatar-circle {
  width: 40px;
  height: 40px;
  border-radius: 50%;
  background: linear-gradient(45deg, #007bff, #28a745);
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
  font-weight: bold;
  font-size: 0.875rem;
}

.table-row:hover {
  background-color: rgba(0, 123, 255, 0.05);
}

.keluhan-preview {
  line-height: 1.4;
  max-width: 300px;
}

.detail-label {
  font-weight: 600;
  color: #6c757d;
  font-size: 0.875rem;
  margin-bottom: 0.25rem;
  display: block;
}

.detail-value {
  font-weight: 500;
  color: #212529;
  margin-bottom: 0;
}

.detail-text {
  background-color: #f8f9fa;
  padding: 0.75rem;
  border-radius: 0.375rem;
  border-left: 4px solid #007bff;
  white-space: pre-wrap;
  line-height: 1.5;
}

.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background-color: rgba(0, 0, 0, 0.5);
  display: flex;
  justify-content: center;
  align-items: center;
  z-index: 1050;
}

.modal-dialog {
  max-width: 500px;
  width: 90%;
  margin: 1.75rem auto;
}

.modal-lg {
  max-width: 800px;
}

.modal-content {
  border: none;
  border-radius: 0.5rem;
  box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
}

.modal-header {
  border-bottom: 1px solid #dee2e6;
  padding: 1rem 1.25rem;
}

.modal-title {
  font-weight: 600;
  color: #495057;
}

.btn-close {
  background: none;
  border: none;
  font-size: 1.5rem;
  font-weight: 700;
  line-height: 1;
  color: #000;
  opacity: 0.5;
  cursor: pointer;
}

.btn-close:hover {
  opacity: 0.75;
}

.fade-enter-active, .fade-leave-active {
  transition: opacity 0.3s ease;
}

.fade-enter-from, .fade-leave-to {
  opacity: 0;
}

.btn-group .btn {
  margin-right: 0.25rem;
}

.btn-group .btn:last-child {
  margin-right: 0;
}

@media (max-width: 768px) {
  .list-penanganan-container {
    padding: 1rem;
  }
  
  .table-responsive {
    font-size: 0.875rem;
  }
  
  .btn-group {
    flex-direction: column;
    gap: 0.25rem;
  }
  
  .btn-group .btn {
    margin-right: 0;
    font-size: 0.75rem;
    padding: 0.25rem 0.5rem;
  }
}
</style>