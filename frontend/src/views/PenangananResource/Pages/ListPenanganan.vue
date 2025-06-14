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

          <!-- Empty State -->
          <div v-else-if="filteredPenanganan.length === 0" class="text-center py-5">
            <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
            <h5 class="text-muted">Tidak ada data penanganan</h5>
            <p class="text-muted">Belum ada data penanganan yang tersedia</p>
          </div>

          <!-- Table -->
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
                    <i class="fas fa-id-badge me-1"></i>Kode Akses
                  </th>
                  <th scope="col" class="fw-bold">
                    <i class="fas fa-calendar me-1"></i>Tanggal Penanganan
                  </th>
                  <th scope="col" class="fw-bold">
                    <i class="fas fa-stethoscope me-1"></i>Diagnosis
                  </th>
                  <th scope="col" class="fw-bold">
                    <i class="fas fa-info-circle me-1"></i>Status
                  </th>
                  <th scope="col" class="fw-bold text-center">
                    <i class="fas fa-cogs me-1"></i>Action
                  </th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="(item, index) in paginatedData" :key="item.id" class="table-row">
                  <td class="fw-bold text-primary">{{ getRowNumber(index) }}</td>
                  <td>
                    <div class="d-flex align-items-center">
                      <div class="avatar-circle me-2">
                        {{ getInitials(item.patient_name) }}
                      </div>
                      <div>
                        <div class="fw-bold">{{ item.patient_name }}</div>
                        <small class="text-muted">{{ item.patient_email || 'Email tidak tersedia' }}</small>
                      </div>
                    </div>
                  </td>
                  <td>
                    <span class="badge bg-info">{{ item.kode_akses || 'N/A' }}</span>
                  </td>
                  <td>
                    <div class="fw-bold">{{ formatDate(item.tanggal_penanganan) }}</div>
                    <small class="text-muted">{{ formatTime(item.created_at) }}</small>
                  </td>
                  <td>
                    <div class="diagnosis-preview">
                      {{ truncateText(item.diagnosis_manual, 50) }}
                    </div>
                  </td>
                  <td>
                    <span 
                      class="badge"
                      :class="item.is_assigned ? 'bg-success' : 'bg-warning'"
                    >
                      <i :class="item.is_assigned ? 'fas fa-check-circle' : 'fas fa-clock'" class="me-1"></i>
                      {{ item.is_assigned ? 'Assigned' : 'Unassigned' }}
                    </span>
                  </td>
                  <td class="text-center">
                    <div class="btn-group" role="group">
                      <button
                        @click="toggleAssignment(item)"
                        class="btn btn-sm"
                        :class="item.is_assigned ? 'btn-outline-danger' : 'btn-outline-success'"
                        :disabled="isUpdating === item.id"
                        :title="item.is_assigned ? 'Unassign Pasien' : 'Assign Pasien'"
                      >
                        <span v-if="isUpdating === item.id" class="spinner-border spinner-border-sm me-1"></span>
                        <i v-else :class="item.is_assigned ? 'fas fa-user-times' : 'fas fa-user-check'" class="me-1"></i>
                        {{ item.is_assigned ? 'Unassign' : 'Assign' }}
                      </button>
                      <button
                        @click="viewDetail(item)"
                        class="btn btn-sm btn-outline-primary"
                        title="Lihat Detail"
                      >
                        <i class="fas fa-eye"></i>
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
                    <label class="detail-label">Kode Akses</label>
                    <div class="detail-value">
                      <span class="badge bg-info">{{ selectedDetail.kode_akses || 'N/A' }}</span>
                    </div>
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
                    <label class="detail-label">Dibuat Pada</label>
                    <div class="detail-value">{{ formatDateTime(selectedDetail.created_at) }}</div>
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
                  <div class="detail-group mb-3">
                    <label class="detail-label">Tindakan</label>
                    <div class="detail-value detail-text">{{ selectedDetail.tindakan }}</div>
                  </div>
                  <div class="detail-group mb-0" v-if="selectedDetail.catatan_tambahan">
                    <label class="detail-label">Catatan Tambahan</label>
                    <div class="detail-value detail-text">{{ selectedDetail.catatan_tambahan }}</div>
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

    <!-- Confirmation Modal -->
    <transition name="fade">
      <div v-if="showConfirmModal" class="modal-overlay" @click.self="closeConfirmModal">
        <div class="modal-dialog">
          <div class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title">
                <i class="fas fa-question-circle me-2"></i>Konfirmasi
              </h5>
              <button type="button" class="btn-close" @click="closeConfirmModal"></button>
            </div>
            <div class="modal-body">
              <p v-if="confirmAction === 'assign'">
                Apakah Anda yakin ingin <strong>assign</strong> pasien <strong>{{ confirmData?.patient_name }}</strong>?
              </p>
              <p v-else>
                Apakah Anda yakin ingin <strong>unassign</strong> pasien <strong>{{ confirmData?.patient_name }}</strong>?
              </p>
            </div>
            <div class="modal-footer">
              <button class="btn btn-secondary" @click="closeConfirmModal">
                <i class="fas fa-times me-1"></i>Batal
              </button>
              <button 
                class="btn"
                :class="confirmAction === 'assign' ? 'btn-success' : 'btn-danger'"
                @click="confirmAssignment"
                :disabled="isUpdating"
              >
                <span v-if="isUpdating" class="spinner-border spinner-border-sm me-2"></span>
                <i v-else :class="confirmAction === 'assign' ? 'fas fa-check' : 'fas fa-times'" class="me-1"></i>
                {{ confirmAction === 'assign' ? 'Ya, Assign' : 'Ya, Unassign' }}
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
  name: 'ListPenangananView',
  components: { AppLayout },
  setup() {
    const router = useRouter();
    
    // Data
    const penangananData = ref([]);
    const filteredPenanganan = ref([]);
    const isLoading = ref(false);
    const isUpdating = ref(null);
    
    // Filters
    const searchQuery = ref('');
    const selectedDate = ref('');
    
    // Pagination
    const currentPage = ref(1);
    const itemsPerPage = ref(10);
    
    // Modals
    const showDetailModal = ref(false);
    const showConfirmModal = ref(false);
    const selectedDetail = ref(null);
    const confirmData = ref(null);
    const confirmAction = ref('');
    
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
      const start = Math.max(1, currentPage.value - Math.floor(maxVisible / 2));
      const end = Math.min(totalPages.value, start + maxVisible - 1);
      
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
          (item.kode_akses && item.kode_akses.toLowerCase().includes(query))
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
    
    const toggleAssignment = (item) => {
      confirmData.value = item;
      confirmAction.value = item.is_assigned ? 'unassign' : 'assign';
      showConfirmModal.value = true;
    };
    
    const confirmAssignment = async () => {
      try {
        isUpdating.value = confirmData.value.id;
        const token = localStorage.getItem("token") || sessionStorage.getItem("token");
        
        const endpoint = confirmAction.value === 'assign' 
          ? `${api.getEndpoint('penanganan')}/${confirmData.value.id}/assign`
          : `${api.getEndpoint('penanganan')}/${confirmData.value.id}/unassign`;
        
        await axios.post(endpoint, {}, {
          headers: { Authorization: `Bearer ${token}` },
        });
        
        // Update local data
        const index = penangananData.value.findIndex(item => item.id === confirmData.value.id);
        if (index !== -1) {
          penangananData.value[index].is_assigned = confirmAction.value === 'assign';
        }
        
        // Refresh filtered data
        filterData();
        
        closeConfirmModal();
        
      } catch (error) {
        console.error('Error updating assignment:', error);
        alert('Gagal mengupdate status assignment. Silakan coba lagi.');
      } finally {
        isUpdating.value = null;
      }
    };
    
    const viewDetail = (item) => {
      selectedDetail.value = item;
      showDetailModal.value = true;
    };
    
    const closeDetailModal = () => {
      showDetailModal.value = false;
      selectedDetail.value = null;
    };
    
    const closeConfirmModal = () => {
      showConfirmModal.value = false;
      confirmData.value = null;
      confirmAction.value = '';
    };
    
    const navigateToForm = () => {
      router.push({name : 'penanganan-view'});
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
      isUpdating,
      searchQuery,
      selectedDate,
      currentPage,
      itemsPerPage,
      totalPages,
      paginatedData,
      visiblePages,
      showDetailModal,
      showConfirmModal,
      selectedDetail,
      confirmData,
      confirmAction,
      userRole,
      fetchPenangananData,
      filterData,
      toggleAssignment,
      confirmAssignment,
      viewDetail,
      closeDetailModal,
      closeConfirmModal,
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
  padding: 20px;
  max-width: 1400px;
  margin: 0 auto;
}

/* Header Section */
.header-section .card {
  border-radius: 12px;
  border: none;
}

.header-section .card-body {
  padding: 1.5rem;
}

/* Filters Section */
.filters-section .card {
  border-radius: 12px;
  border: none;
}

.filters-section .card-body {
  padding: 1.5rem;
}

/* Table Section */
.table-section .card {
  border-radius: 12px;
  border: none;
  overflow: hidden;
}

.card-header {
  background: linear-gradient(135deg, #4285f4, #34a853);
  color: white;
  border-bottom: none;
  padding: 1rem 1.5rem;
}

.card-body {
  padding: 0;
}

/* Table styling */
.table {
  margin-bottom: 0;
}

.table thead th {
  background-color: #f8f9fa;
  border-bottom: 2px solid #dee2e6;
  font-weight: 600;
  color: #495057;
  padding: 1rem 0.75rem;
  vertical-align: middle;
}

.table tbody td {
  padding: 1rem 0.75rem;
  vertical-align: middle;
  border-bottom: 1px solid #dee2e6;
}

.table-row:hover {
  background-color: #f8f9fa;
}

/* Avatar circle */
.avatar-circle {
  width: 40px;
  height: 40px;
  border-radius: 50%;
  background: linear-gradient(135deg, #4285f4, #34a853);
  color: white;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: bold;
  font-size: 0.9rem;
}

/* Diagnosis preview */
.diagnosis-preview {
  max-width: 200px;
  word-wrap: break-word;
  line-height: 1.4;
}

/* Badges */
.badge {
  font-size: 0.75rem;
  padding: 0.35rem 0.65rem;
  border-radius: 0.375rem;
}

/* Buttons */
.btn {
  border-radius: 6px;
  font-weight: 500;
  transition: all 0.3s ease;
}

.btn:hover {
  transform: translateY(-1px);
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.btn-group .btn {
  margin-right: 0.25rem;
}

.btn-group .btn:last-child {
  margin-right: 0;
}

.btn-sm {
  padding: 0.375rem 0.75rem;
  font-size: 0.875rem;
}

.btn-primary {
  background: linear-gradient(135deg, #4285f4, #1a73e8);
  border: none;
}

.btn-primary:hover {
  background: linear-gradient(135deg, #1a73e8, #1557b0);
}

.btn-outline-primary {
  border-color: #4285f4;
  color: #4285f4;
}

.btn-outline-primary:hover {
  background-color: #4285f4;
  border-color: #4285f4;
  color: #fff;
}

.btn-outline-success {
  border-color: #28a745;
  color: #28a745;
}

.btn-outline-success:hover {
  background-color: #28a745;
  border-color: #28a745;
  color: #fff;
}

.btn-outline-danger {
  border-color: #dc3545;
  color: #dc3545;
}

.btn-outline-danger:hover {
  background-color: #dc3545;
  border-color: #dc3545;
  color: #fff;
}

.btn-success {
  background: linear-gradient(135deg, #28a745, #1e7e34);
  border: none;
}

.btn-success:hover {
  background: linear-gradient(135deg, #1e7e34, #155724);
}

.btn-danger {
  background: linear-gradient(135deg, #dc3545, #c82333);
  border: none;
}

.btn-danger:hover {
  background: linear-gradient(135deg, #c82333, #a02622);
}

.btn-secondary {
  background: linear-gradient(135deg, #6c757d, #545b62);
  border: none;
}

.btn-secondary:hover {
  background: linear-gradient(135deg, #545b62, #3d4142);
}

/* Form controls */
.form-control,
.form-select {
  border-radius: 6px;
  border: 1px solid #dee2e6;
  padding: 0.5rem 0.75rem;
  font-size: 0.875rem;
  transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
}

.form-control:focus,
.form-select:focus {
  border-color: #4285f4;
  box-shadow: 0 0 0 0.2rem rgba(66, 133, 244, 0.25);
  outline: 0;
}

.form-label {
  font-weight: 600;
  color: #495057;
  margin-bottom: 0.5rem;
}

/* Pagination */
.pagination {
  margin-bottom: 0;
}

.page-link {
  color: #4285f4;
  border: 1px solid #dee2e6;
  padding: 0.375rem 0.75rem;
  font-size: 0.875rem;
  border-radius: 6px;
  margin: 0 0.125rem;
  transition: all 0.3s ease;
}

.page-link:hover {
  color: #1a73e8;
  background-color: #f8f9fa;
  border-color: #4285f4;
  transform: translateY(-1px);
}

.page-item.active .page-link {
  background: linear-gradient(135deg, #4285f4, #1a73e8);
  border-color: #4285f4;
  color: #fff;
}

.page-item.disabled .page-link {
  color: #6c757d;
  background-color: #fff;
  border-color: #dee2e6;
  cursor: not-allowed;
}

/* Loading states */
.spinner-border {
  width: 1rem;
  height: 1rem;
  border-width: 0.125em;
}

.spinner-border-sm {
  width: 0.875rem;
  height: 0.875rem;
  border-width: 0.1em;
}

/* Empty state */
.text-muted {
  color: #6c757d !important;
}

/* Modal styles */
.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background-color: rgba(0, 0, 0, 0.5);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1050;
  backdrop-filter: blur(4px);
}

.modal-dialog {
  position: relative;
  width: auto;
  margin: 1.75rem;
  pointer-events: none;
  max-width: 500px;
}

.modal-dialog.modal-lg {
  max-width: 800px;
}

.modal-content {
  position: relative;
  display: flex;
  flex-direction: column;
  width: 100%;
  pointer-events: auto;
  background-color: #fff;
  background-clip: padding-box;
  border: 1px solid rgba(0, 0, 0, 0.2);
  border-radius: 12px;
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
  outline: 0;
  animation: modalFadeIn 0.3s ease-out;
}

@keyframes modalFadeIn {
  from {
    opacity: 0;
    transform: translateY(-50px) scale(0.95);
  }
  to {
    opacity: 1;
    transform: translateY(0) scale(1);
  }
}

.modal-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 1.5rem;
  border-bottom: 1px solid #dee2e6;
  border-top-left-radius: 12px;
  border-top-right-radius: 12px;
  background: linear-gradient(135deg, #f8f9fa, #e9ecef);
}

.modal-title {
  margin-bottom: 0;
  line-height: 1.5;
  font-weight: 600;
  color: #495057;
}

.btn-close {
  padding: 0.5rem;
  margin: -0.5rem -0.5rem -0.5rem auto;
  background: transparent;
  border: 0;
  border-radius: 6px;
  opacity: 0.5;
  font-size: 1.25rem;
  cursor: pointer;
  transition: opacity 0.15s ease-in-out;
}

.btn-close:hover {
  opacity: 0.75;
}

.btn-close::before {
  content: "×";
  font-weight: bold;
  color: #000;
}

.modal-body {
  position: relative;
  flex: 1 1 auto;
  padding: 1.5rem;
  max-height: 70vh;
  overflow-y: auto;
}

.modal-footer {
  display: flex;
  flex-wrap: wrap;
  align-items: center;
  justify-content: flex-end;
  padding: 1rem 1.5rem;
  border-top: 1px solid #dee2e6;
  border-bottom-right-radius: 12px;
  border-bottom-left-radius: 12px;
  background: linear-gradient(135deg, #f8f9fa, #e9ecef);
  gap: 0.5rem;
}

/* Detail modal styles */
.detail-group {
  margin-bottom: 1rem;
}

.detail-label {
  font-weight: 600;
  color: #495057;
  font-size: 0.875rem;
  margin-bottom: 0.25rem;
  display: block;
}

.detail-value {
  color: #212529;
  font-size: 0.9rem;
  line-height: 1.5;
}

.detail-text {
  background-color: #f8f9fa;
  padding: 0.75rem;
  border-radius: 6px;
  border-left: 3px solid #4285f4;
  white-space: pre-wrap;
  word-wrap: break-word;
}

/* Transitions */
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.3s;
}

.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}

/* Gradient text */
.text-gradient {
  background: linear-gradient(135deg, #4285f4, #34a853);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
  font-weight: 700;
}

/* Card footer */
.card-footer {
  background-color: #f8f9fa;
  border-top: 1px solid #dee2e6;
  padding: 1rem 1.5rem;
}

/* Responsive design */
@media (max-width: 768px) {
  .list-penanganan-container {
    padding: 15px;
  }
  
  .header-section .col-md-4 {
    text-align: left !important;
    margin-top: 1rem;
  }
  
  .filters-section .row {
    margin-bottom: 0;
  }
  
  .filters-section .col-md-6 {
    margin-bottom: 1rem;
  }
  
  .table-responsive {
    border-radius: 6px;
  }
  
  .table thead {
    display: none;
  }
  
  .table,
  .table tbody,
  .table tr,
  .table td {
    display: block;
    width: 100%;
  }
  
  .table tr {
    border: 1px solid #dee2e6;
    border-radius: 8px;
    margin-bottom: 1rem;
    padding: 1rem;
    background-color: #fff;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
  }
  
  .table td {
    border: none;
    border-bottom: 1px solid #dee2e6;
    position: relative;
    padding-left: 50% !important;
    padding-top: 0.5rem;
    padding-bottom: 0.5rem;
  }
  
  .table td:last-child {
    border-bottom: none;
  }
  
  .table td::before {
    content: attr(data-label);
    position: absolute;
    left: 6px;
    width: 45%;
    padding-right: 10px;
    white-space: nowrap;
    font-weight: bold;
    color: #495057;
  }
  
  .btn-group {
    display: flex;
    flex-direction: column;
    width: 100%;
  }
  
  .btn-group .btn {
    margin-right: 0;
    margin-bottom: 0.5rem;
  }
  
  .btn-group .btn:last-child {
    margin-bottom: 0;
  }
  
  .modal-dialog {
    margin: 1rem;
    max-width: calc(100vw - 2rem);
  }
  
  .pagination {
    flex-wrap: wrap;
    justify-content: center;
  }
  
  .card-footer .row {
    text-align: center;
  }
  
  .card-footer .col-md-6 {
    margin-bottom: 1rem;
  }
  
  .card-footer .col-md-6:last-child {
    margin-bottom: 0;
  }
}

@media (max-width: 576px) {
  .header-section .card-body,
  .filters-section .card-body {
    padding: 1rem;
  }
  
  .btn-lg {
    font-size: 0.9rem;
    padding: 0.5rem 1rem;
  }
  
  .modal-header,
  .modal-body,
  .modal-footer {
    padding: 1rem;
  }
  
  .detail-group {
    margin-bottom: 0.75rem;
  }
  
  .avatar-circle {
    width: 35px;
    height: 35px;
    font-size: 0.8rem;
  }
}

/* Print styles */
@media print {
  .list-penanganan-container {
    padding: 0;
  }
  
  .header-section .btn,
  .filters-section,
  .card-footer,
  .table td:last-child {
    display: none !important;
  }
  
  .card {
    border: none !important;
    box-shadow: none !important;
  }
  
  .card-header {
    background: #f8f9fa !important;
    color: #000 !important;
    -webkit-print-color-adjust: exact;
  }
  
  .table {
    font-size: 0.8rem;
  }
  
  .badge {
    border: 1px solid #000;
    color: #000 !important;
    background: transparent !important;
  }
}

/* Accessibility improvements */
.btn:focus,
.form-control:focus,
.form-select:focus,
.page-link:focus {
  outline: 2px solid #4285f4;
  outline-offset: 2px;
}

.btn:disabled {
  opacity: 0.65;
  cursor: not-allowed;
}
</style>