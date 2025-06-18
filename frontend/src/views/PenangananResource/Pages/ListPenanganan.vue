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
        <div class="card-header bg-gradient text-black">
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
                  <th scope="col" class="fw-bold">
                    <i class="fas fa-diagnoses me-1"></i>Diagnosis Manual
                  </th>
                  <th scope="col" class="fw-bold text-center">
                    <i class="fas fa-cogs me-1"></i>Action
                  </th>
                </tr>
              </thead>
              <tbody>
                <!-- Empty State -->
                <tr v-if="filteredPenanganan.length === 0">
                  <td colspan="6" class="text-center py-5">
                    <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                    <h5 class="text-muted">Tidak ada data penanganan</h5>
                  </td>
                </tr>
                
                <!-- Data Rows -->
                <tr v-else v-for="(item, index) in paginatedData" :key="item.id" class="table-row">
                  <td class="fw-bold text-dark">{{ getRowNumber(index) }}</td>
                  <td>
                    <div class="d-flex align-items-center">
                      <div class="avatar-circle me-2">
                        {{ getInitials(item.patient_name) }}
                      </div>
                      <div>
                        <div class="fw-bold">{{ item.patient_name }}</div>
                        <small class="text-muted">
                          <i class="fas fa-id-card me-1"></i>
                          {{ patientData[item.patient_id]?.kode_akses }}
                        </small>
                      </div>
                    </div>
                  </td>
                  <td>
                    <div class="fw-bold">{{ formatDate(item.tanggal_penanganan) }}</div>
                    <small class="text-muted">{{ formatTime(item.created_at) }}</small>
                  </td>
                  <td>
                    <div class="keluhan-preview" :title="item.keluhan">
                      {{ truncateText(item.keluhan, 50) }}
                    </div>
                  </td>
                  <td>
                    <div class="diagnosis-preview" :title="item.diagnosis_manual">
                      {{ truncateText(item.diagnosis_manual, 50) }}
                    </div>
                  </td>
                  <td class="text-center">
                    <div class="action-buttons">
                      <!-- View Detail Button -->
                      <button
                        @click="viewDetail(item)"
                        class="btn btn-sm btn-outline-info me-1 mb-1"
                        title="Lihat Detail"
                        v-tooltip="'Lihat Detail'"
                      >
                        <i class="fas fa-eye me-1"></i>
                        <span class="d-none d-md-inline">Detail</span>
                      </button>
                      
                      <!-- View History Button -->
                      <button
                        @click="viewHistory(item)"
                        class="btn btn-sm btn-outline-primary me-1 mb-1"
                        title="Lihat Riwayat"
                        v-tooltip="'Lihat Riwayat'"
                      >
                        <i class="fas fa-history me-1"></i>
                        <span class="d-none d-md-inline">Riwayat</span>
                      </button>
                      
                      <!-- Edit Button - Only for dokter -->
                      <button
                        v-if="userRole === 'dokter'"
                        @click="editPenanganan(item)"
                        class="btn btn-sm btn-outline-warning me-1 mb-1"
                        title="Edit Data"
                        v-tooltip="'Edit Data'"
                      >
                        <i class="fas fa-edit me-1"></i>
                        <span class="d-none d-md-inline">Edit</span>
                      </button>
                      
                      <!-- Delete Button - Only for dokter -->
                      <button
                        v-if="userRole === 'dokter'"
                        @click="deletePenanganan(item)"
                        class="btn btn-sm btn-outline-danger mb-1"
                        title="Hapus Data"
                        v-tooltip="'Hapus Data'"
                        :disabled="isDeleting === item.id"
                      >
                        <span v-if="isDeleting === item.id" class="spinner-border spinner-border-sm me-1"></span>
                        <i v-else class="fas fa-trash me-1"></i>
                        <span class="d-none d-md-inline">{{ isDeleting === item.id ? 'Hapus...' : 'Hapus' }}</span>
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
                      <span class="badge bg-info">
                        {{ patientData[selectedDetail.patient_id]?.kode_akses || 'Loading...' }}
                      </span>
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
              <button 
                v-if="userRole === 'dokter'"
                class="btn btn-warning me-2" 
                @click="editFromModal"
              >
                <i class="fas fa-edit me-1"></i>Edit Data
              </button>
              <button 
                class="btn btn-primary" 
                @click="viewFromModal"
              >
                <i class="fas fa-eye me-1"></i>Lihat Riwayat
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
    const patientData = ref({}); // Store patient data by patient_id
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
    
    const fetchPatientData = async (patientId) => {
      // Skip if already fetched
      if (patientData.value[patientId]) {
        return;
      }
      
      try {
        const token = localStorage.getItem("token") || sessionStorage.getItem("token");
        const response = await api.get(`users/${patientId}`, {
          headers: {
            'Authorization': `Bearer ${token}`
          }
        });
        
        // Store patient data
        patientData.value[patientId] = response.data.data || response.data;
        
      } catch (error) {
        console.error(`Error fetching patient data for ID ${patientId}:`, error);
        // Set fallback data
        patientData.value[patientId] = {
          kode_akses: 'N/A'
        };
      }
    };
    
    const fetchAllPatientData = async (penangananList) => {
      // Get unique patient IDs
      const patientIds = [...new Set(penangananList.map(item => item.patient_id).filter(id => id))];
      
      // Fetch data for each unique patient
      await Promise.all(patientIds.map(patientId => fetchPatientData(patientId)));
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
        
        // Fetch patient data for all patients
        await fetchAllPatientData(penangananData.value);
        
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
          item.keluhan.toLowerCase().includes(query) ||
          item.diagnosis_manual.toLowerCase().includes(query) ||
          (patientData.value[item.patient_id]?.kode_akses && 
           patientData.value[item.patient_id].kode_akses.toLowerCase().includes(query))
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
      // Navigate to view-penanganan page with item id
      router.push({ name: 'view-penanganan', params: { id: item.id } });
    };

    const viewHistory = (item) => {
      // Navigate to riwayat page with patient ID
      router.push({ 
        name: 'riwayat', 
        params: { id: item.patient_id || item.user_id } 
      });
    };
    
    const editPenanganan = (item) => {
      // Navigate to edit form with item id
      router.push({ name: 'edit-penanganan', params: { id: item.id } });
    };
    
    const viewPenanganan = (item) => {
      // Navigate to view page with item id
      router.push({ name: 'view-penanganan', params: { id: item.id } });
    };
    
    const editFromModal = () => {
      if (selectedDetail.value) {
        closeDetailModal();
        editPenanganan(selectedDetail.value);
      }
    };
    
    const viewFromModal = () => {
      if (selectedDetail.value) {
        closeDetailModal();
        viewHistory(selectedDetail.value);
      }
    };
    
    const deletePenanganan = (item) => {
      deleteData.value = item;
      showDeleteModal.value = true;
    };
    
    const confirmDelete = async () => {
  try {
    isDeleting.value = deleteData.value.id;
    const patientName = deleteData.value.patient_name;
    const token = localStorage.getItem("token") || sessionStorage.getItem("token");

    const response = await axios.delete(`${api.getEndpoint('penanganan')}/${deleteData.value.id}/force-delete`, {
      headers: { Authorization: `Bearer ${token}` },
    });

    if (response.data.success) {
      const index = penangananData.value.findIndex(item => item.id === deleteData.value.id);
      if (index !== -1) {
        penangananData.value.splice(index, 1);
      }

      filterData();
      closeDeleteModal();

      alert(`Data penanganan untuk pasien ${patientName} berhasil dihapus.`); // pakai variable ini
    } else {
      alert(`Gagal menghapus data penanganan: ${response.data.message}`);
    }

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
      patientData,
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
      viewHistory,
      editPenanganan,
      viewPenanganan,
      editFromModal,
      viewFromModal,
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

.keluhan-preview, .diagnosis-preview {
  line-height: 1.4;
  max-width: 250px;
}

.action-buttons {
  display: flex;
  flex-wrap: wrap;
  justify-content: center;
  gap: 0.25rem;
}

.action-buttons .btn {
  font-size: 0.75rem;
  padding: 0.375rem 0.5rem;
  white-space: nowrap;
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
  color: #495057;
  padding: 0.5rem;
  background-color: #f8f9fa;
  border-radius: 0.375rem;
  border: 1px solid #e9ecef;
}

.detail-text {
  white-space: pre-wrap;
  word-wrap: break-word;
  line-height: 1.6;
  min-height: 2.5rem;
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
  justify-content: center;
  align-items: center;
  z-index: 9999;
  backdrop-filter: blur(2px);
}

.modal-dialog {
  width: 90%;
  max-width: 600px;
  margin: 1rem auto;
}

.modal-dialog.modal-lg {
  max-width: 900px;
}

.modal-content {
  background: white;
  border-radius: 0.5rem;
  box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
  overflow: hidden;
  animation: modalSlideIn 0.3s ease-out;
}

.modal-header {
  padding: 1rem 1.5rem;
  border-bottom: 1px solid #e9ecef;
  background: linear-gradient(135deg, #f8f9fa, #e9ecef);
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.modal-title {
  margin: 0;
  font-weight: 600;
  color: #495057;
}

.modal-body {
  padding: 1.5rem;
  max-height: 70vh;
  overflow-y: auto;
}

.modal-footer {
  padding: 1rem 1.5rem;
  border-top: 1px solid #e9ecef;
  background-color: #f8f9fa;
  display: flex;
  justify-content: end;
  gap: 0.5rem;
}

.btn-close {
  background: none;
  border: none;
  font-size: 1.25rem;
  font-weight: 700;
  line-height: 1;
  color: #6c757d;
  opacity: 0.5;
  cursor: pointer;
  transition: opacity 0.15s ease-in-out;
}

.btn-close:hover {
  opacity: 0.75;
}

/* Transitions */
.fade-enter-active, .fade-leave-active {
  transition: opacity 0.3s ease;
}

.fade-enter-from, .fade-leave-to {
  opacity: 0;
}

@keyframes modalSlideIn {
  from {
    transform: translateY(-50px);
    opacity: 0;
  }
  to {
    transform: translateY(0);
    opacity: 1;
  }
}

/* Card enhancements */
.card {
  border: none;
  border-radius: 0.75rem;
  overflow: hidden;
}

.card.shadow {
  box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
}

.card.shadow-sm {
  box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.05);
}

.card-header {
  border-bottom: 1px solid rgba(0, 0, 0, 0.125);
  padding: 1rem 1.5rem;
}

.card-body {
  padding: 1.5rem;
}

.card-footer {
  padding: 1rem 1.5rem;
  border-top: 1px solid #e9ecef;
}

/* Table enhancements */
.table {
  margin-bottom: 0;
}

.table thead th {
  border-bottom: 2px solid #dee2e6;
  font-weight: 600;
  color: #495057;
  background-color: #f8f9fa;
  padding: 1rem 0.75rem;
  font-size: 0.875rem;
  text-transform: uppercase;
  letter-spacing: 0.025em;
}

.table tbody td {
  padding: 1rem 0.75rem;
  vertical-align: middle;
  border-top: 1px solid #dee2e6;
}

.table-hover tbody tr:hover {
  background-color: rgba(0, 123, 255, 0.075);
}

/* Pagination styles */
.pagination {
  margin: 0;
}

.page-link {
  color: #007bff;
  background-color: #fff;
  border: 1px solid #dee2e6;
  padding: 0.375rem 0.75rem;
  margin-left: -1px;
  line-height: 1.25;
  text-decoration: none;
  transition: color 0.15s ease-in-out, background-color 0.15s ease-in-out, border-color 0.15s ease-in-out;
}

.page-link:hover {
  color: #0056b3;
  background-color: #e9ecef;
  border-color: #dee2e6;
}

.page-item.active .page-link {
  color: #fff;
  background-color: #007bff;
  border-color: #007bff;
}

.page-item.disabled .page-link {
  color: #6c757d;
  background-color: #fff;
  border-color: #dee2e6;
  cursor: not-allowed;
}

/* Button enhancements */
.btn {
  border-radius: 0.375rem;
  font-weight: 500;
  transition: all 0.15s ease-in-out;
}

.btn-primary {
  background: linear-gradient(135deg, #007bff, #0056b3);
  border: none;
  color: white;
}

.btn-primary:hover {
  background: linear-gradient(135deg, #0056b3, #003d82);
  transform: translateY(-1px);
  box-shadow: 0 0.25rem 0.5rem rgba(0, 123, 255, 0.25);
}

.btn-outline-info:hover {
  background-color: #17a2b8;
  border-color: #17a2b8;
  transform: translateY(-1px);
  box-shadow: 0 0.125rem 0.25rem rgba(23, 162, 184, 0.25);
}

.btn-outline-primary:hover {
  background-color: #007bff;
  border-color: #007bff;
  transform: translateY(-1px);
  box-shadow: 0 0.125rem 0.25rem rgba(0, 123, 255, 0.25);
}

.btn-outline-warning:hover {
  background-color: #ffc107;
  border-color: #ffc107;
  transform: translateY(-1px);
  box-shadow: 0 0.125rem 0.25rem rgba(255, 193, 7, 0.25);
}

.btn-outline-danger:hover {
  background-color: #dc3545;
  border-color: #dc3545;
  transform: translateY(-1px);
  box-shadow: 0 0.125rem 0.25rem rgba(220, 53, 69, 0.25);
}

/* Form enhancements */
.form-control {
  border-radius: 0.375rem;
  border: 1px solid #ced4da;
  transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
}

.form-control:focus {
  border-color: #80bdff;
  outline: 0;
  box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
}

.form-label {
  font-weight: 500;
  color: #495057;
  margin-bottom: 0.5rem;
}

/* Badge enhancements */
.badge {
  font-size: 0.75em;
  font-weight: 500;
  padding: 0.375em 0.75em;
  border-radius: 0.375rem;
}

.badge.bg-info {
  background-color: #17a2b8 !important;
}

.badge.bg-secondary {
  background-color: #6c757d !important;
}

.badge.bg-success {
  background-color: #28a745 !important;
}

.badge.bg-warning {
  background-color: #ffc107 !important;
  color: #212529 !important;
}

.badge.bg-light {
  background-color: #f8f9fa !important;
  color: #495057 !important;
}

/* Spinner enhancements */
.spinner-border {
  animation: spinner-border 0.75s linear infinite;
}

@keyframes spinner-border {
  to {
    transform: rotate(360deg);
  }
}

/* Alert enhancements */
.alert {
  border-radius: 0.5rem;
  border: none;
  padding: 0.75rem 1rem;
}

.alert-warning {
  background-color: #fff3cd;
  color: #856404;
  border-left: 4px solid #ffc107;
}

/* Loading state */
.text-center.py-5 {
  padding: 3rem 0;
}

.text-muted {
  color: #6c757d !important;
}

/* Empty state icon */
.fa-inbox {
  opacity: 0.3;
}

/* Responsive adjustments */
@media (max-width: 768px) {
  .list-penanganan-container {
    padding: 1rem;
  }
  
  .modal-dialog {
    width: 95%;
    margin: 0.5rem auto;
  }
  
  .modal-body {
    padding: 1rem;
  }
  
  .action-buttons {
    flex-direction: column;
    align-items: center;
  }
  
  .action-buttons .btn {
    width: 100%;
    margin-bottom: 0.25rem;
  }
  
  .card-body {
    padding: 1rem;
  }
  
  .table thead th,
  .table tbody td {
    padding: 0.5rem;
    font-size: 0.875rem;
  }
  
  .avatar-circle {
    width: 32px;
    height: 32px;
    font-size: 0.75rem;
  }
}

@media (max-width: 576px) {
  .modal-dialog {
    width: 98%;
    margin: 0.25rem auto;
  }
  
  .modal-header,
  .modal-body,
  .modal-footer {
    padding: 0.75rem;
  }
  
  .table-responsive {
    font-size: 0.8rem;
  }
  
  .action-buttons .btn {
    font-size: 0.7rem;
    padding: 0.25rem 0.5rem;
  }
  
  .pagination {
    font-size: 0.875rem;
  }
}
</style>