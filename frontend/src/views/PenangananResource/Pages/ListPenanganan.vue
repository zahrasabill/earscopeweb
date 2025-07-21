<template>
  <div class="container-fluid list-penanganan-container">
    <div class="header-section mb-4">
      <div class="card shadow-sm">
        <div class="card-header bg-gradient text-black">
          <div class="d-flex justify-content-between align-items-center">
            <h4 class="mb-0">
              <i class="fas fa-list-alt me-2"></i>
              {{ userRole === 'dokter' ? 'Daftar Penanganan Pasien' : 'Daftar Penanganan Saya' }}
            </h4>
            <div class="header-actions">
              <button
                v-if="userRole === 'dokter'"
                @click="navigateToForm"
                class="btn btn-light btn-sm me-2"
              >
                <i class="fas fa-plus me-1"></i>Tambah Penanganan
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
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

    <!-- Debug Info (hapus setelah testing) -->
    <div class="alert alert-info" v-if="isDebugMode">
      <h6>Debug Info:</h6>
      <p>Total data: {{ penangananData.length }}</p>
      <p>Filtered data: {{ filteredPenanganan.length }}</p>
      <p>Patient data keys: {{ Object.keys(patientData).length }}</p>
      <pre v-if="penangananData.length > 0">{{ JSON.stringify(penangananData[0], null, 2) }}</pre>
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
                        {{ getInitials(getPatientName(item)) }}
                      </div>
                      <div>
                        <div class="fw-bold">{{ getPatientName(item) }}</div>
                        <small class="text-muted">
                          <i class="fas fa-id-card me-1"></i>
                          {{ getPatientCode(item) }}
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
                    <div class="detail-value">{{ getPatientName(selectedDetail) }}</div>
                  </div>
                  <div class="detail-group mb-3">
                    <label class="detail-label">Kode Akses</label>
                    <div class="detail-value">
                      <span class="badge bg-info">
                        {{ getPatientCode(selectedDetail) }}
                      </span>
                    </div>
                  </div>
                  <div class="detail-group mb-3">
                    <label class="detail-label">Tanggal Penanganan</label>
                    <div class="detail-value">{{ formatDate(selectedDetail.tanggal_penanganan) }}</div>
                  </div>
                  <div class="detail-group mb-3">
                    <label class="detail-label">Telinga Diperiksa</label>
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
              <p>Apakah Anda yakin ingin menghapus data penanganan untuk pasien <strong>{{ getPatientName(deleteData) }}</strong>?</p>
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
    const isDebugMode = ref(false); // Set to true for debugging
    
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
    
    // Helper function to get patient name
    const getPatientName = (item) => {
      if (!item) return 'Tidak Diketahui';
      
      // Coba berbagai kemungkinan field nama
      const possibleNames = [
        item.patient_name,
        item.patient?.name,
        item.patient?.nama,
        item.user?.name,
        item.user?.nama,
        item.nama_pasien,
        item.nama
      ];
      
      for (const name of possibleNames) {
        if (name && typeof name === 'string' && name.trim()) {
          return name.trim();
        }
      }
      
      // Jika tidak ada nama dari item, coba dari patientData
      const patientId = item.patient_id || item.user_id;
      if (patientId && patientData.value[patientId]) {
        const patient = patientData.value[patientId];
        const patientPossibleNames = [
          patient.name,
          patient.nama,
          patient.full_name,
          patient.nama_lengkap
        ];
        
        for (const name of patientPossibleNames) {
          if (name && typeof name === 'string' && name.trim()) {
            return name.trim();
          }
        }
      }
      
      return 'Nama Tidak Tersedia';
    };
    
    // Helper function to get patient code
    const getPatientCode = (item) => {
      if (!item) return 'N/A';
      
      const patientId = item.patient_id || item.user_id;
      if (patientId && patientData.value[patientId]) {
        const patient = patientData.value[patientId];
        return patient.kode_akses || patient.patient_code || patient.code || 'N/A';
      }
      
      return item.kode_akses || item.patient_code || 'N/A';
    };
    
    const fetchPatientData = async (patientId) => {
      // Skip if already fetched
      if (patientData.value[patientId]) {
        return;
      }
      
      try {
        const token = localStorage.getItem("token") || sessionStorage.getItem("token");
        
        // Coba berbagai endpoint yang mungkin
        const endpoints = [
          `users/${patientId}`,
          `patients/${patientId}`,
          `user/${patientId}`
        ];
        
        let response = null;
        
        for (const endpoint of endpoints) {
          try {
            response = await api.get(endpoint, {
              headers: {
                'Authorization': `Bearer ${token}`
              }
            });
            
            if (response.data) {
              break;
            }
          } catch (err) {
            console.warn(`Failed to fetch from ${endpoint}:`, err);
            continue;
          }
        }
        
        if (response && response.data) {
          // Store patient data - handle different response structures
          const userData = response.data.data || response.data.user || response.data;
          patientData.value[patientId] = userData;
          
          if (isDebugMode.value) {
            console.log(`Patient data for ${patientId}:`, userData);
          }
        } else {
          throw new Error('No valid response from any endpoint');
        }
        
      } catch (error) {
        console.error(`Error fetching patient data for ID ${patientId}:`, error);
        // Set fallback data
        patientData.value[patientId] = {
          name: 'Nama Tidak Tersedia',
          kode_akses: 'N/A'
        };
      }
    };
    
    const fetchAllPatientData = async (penangananList) => {
      // Get unique patient IDs from different possible fields
      const patientIds = [...new Set(
        penangananList.map(item => item.patient_id || item.user_id)
          .filter(id => id && id !== null && id !== undefined)
      )];
      
      if (isDebugMode.value) {
        console.log('Patient IDs to fetch:', patientIds);
      }
      
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
        
        if (isDebugMode.value) {
          console.log('Penanganan data:', penangananData.value);
        }
        
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
        filtered = filtered.filter(item => {
          const patientName = getPatientName(item).toLowerCase();
          const patientCode = getPatientCode(item).toLowerCase();
          const keluhan = (item.keluhan || '').toLowerCase();
          const diagnosis = (item.diagnosis_manual || '').toLowerCase();
          
          return patientName.includes(query) ||
                 patientCode.includes(query) ||
                 keluhan.includes(query) ||
                 diagnosis.includes(query);
        });
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
        const patientName = getPatientName(deleteData.value);
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

          alert(`Data penanganan untuk pasien ${patientName} berhasil dihapus.`);
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
      if (!name || typeof name !== 'string') return 'N/A';
      const words = name.trim().split(' ');
      if (words.length === 1) {
        return words[0].substring(0, 2).toUpperCase();
      }
      return (words[0][0] + words[words.length - 1][0]).toUpperCase();
    };
    
    const formatDate = (dateString) => {
      if (!dateString) return 'Tidak tersedia';
      try {
        const date = new Date(dateString);
        const options = { 
          year: 'numeric', 
          month: 'long', 
          day: 'numeric',
          timeZone: 'Asia/Jakarta'
        };
        return date.toLocaleDateString('id-ID', options);
      } catch (error) {
        return 'Format tanggal tidak valid';
      }
    };
    
    const formatTime = (dateString) => {
      if (!dateString) return '';
      try {
        const date = new Date(dateString);
        const options = { 
          hour: '2-digit', 
          minute: '2-digit',
          timeZone: 'Asia/Jakarta'
        };
        return date.toLocaleTimeString('id-ID', options);
      } catch (error) {
        return '';
      }
    };
    
    const formatDateTime = (dateString) => {
      if (!dateString) return 'Tidak tersedia';
      try {
        const date = new Date(dateString);
        const options = { 
          year: 'numeric', 
          month: 'long', 
          day: 'numeric',
          hour: '2-digit', 
          minute: '2-digit',
          timeZone: 'Asia/Jakarta'
        };
        return date.toLocaleDateString('id-ID', options);
      } catch (error) {
        return 'Format tanggal tidak valid';
      }
    };
    
    const truncateText = (text, length = 50) => {
      if (!text || typeof text !== 'string') return 'Tidak tersedia';
      if (text.length <= length) return text;
      return text.substring(0, length) + '...';
    };
    
    // Lifecycle
    onMounted(() => {
      userRole.value = getUserRoleFromToken();
      fetchPenangananData();
    });
    
    return {
      // Data
      penangananData,
      filteredPenanganan,
      patientData,
      isLoading,
      isDeleting,
      isDebugMode,
      
      // Filters
      searchQuery,
      selectedDate,
      
      // Pagination
      currentPage,
      itemsPerPage,
      totalPages,
      paginatedData,
      visiblePages,
      
      // Modals
      showDetailModal,
      showDeleteModal,
      selectedDetail,
      deleteData,
      
      // User
      userRole,
      
      // Methods
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
      getPatientName,
      getPatientCode,
      formatDate,
      formatTime,
      formatDateTime,
      truncateText
    };
  }
};
</script>

<style scoped>
/* Container Styles */
.list-penanganan-container {
  padding: 20px;
  background-color: #f8f9fa;
  min-height: 100vh;
}

/* Header Styles */
.header-section .card {
  border: none;
  border-radius: 15px;
  background: linear-gradient(135deg, #ffffff 0%, #ffffff 100%);
  color: black;
}

.text-gradient {
  background: linear-gradient(45deg, #ffffff, #ffffff);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
  color: #000000;
}

/* Filter Section */
.filters-section .card {
  border: none;
  border-radius: 12px;
  box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}

.form-label {
  color: #495057;
  font-size: 0.9rem;
}

.form-control {
  border-radius: 8px;
  border: 1px solid #dee2e6;
  transition: all 0.3s ease;
}

.form-control:focus {
  border-color: #667eea;
  box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
}

/* Table Styles */
.table-section .card {
  border: none;
  border-radius: 12px;
  box-shadow: 0 4px 20px rgba(0,0,0,0.1);
  overflow: hidden;
}

.card-header {
  background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
  border-bottom: 1px solid #dee2e6;
  padding: 1rem 1.5rem;
}

.table {
  margin-bottom: 0;
}

.table th {
  background-color: #f8f9fa;
  border-bottom: 2px solid #dee2e6;
  color: #495057;
  font-weight: 600;
  padding: 1rem 0.75rem;
  vertical-align: middle;
}

.table td {
  padding: 1rem 0.75rem;
  vertical-align: middle;
  border-bottom: 1px solid #f1f3f4;
}

.table-row {
  transition: all 0.3s ease;
}

.table-row:hover {
  background-color: #f8f9fa;
  transform: translateY(-1px);
  box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

/* Avatar Circle */
.avatar-circle {
  width: 40px;
  height: 40px;
  border-radius: 50%;
  background: linear-gradient(135deg, #667eea, #764ba2);
  color: white;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: bold;
  font-size: 0.8rem;
  flex-shrink: 0;
}

/* Text Preview Styles */
.keluhan-preview,
.diagnosis-preview {
  max-width: 200px;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
  cursor: help;
}

/* Action Buttons */
.action-buttons {
  display: flex;
  flex-wrap: wrap;
  gap: 0.25rem;
  justify-content: center;
}

.btn-sm {
  padding: 0.375rem 0.75rem;
  font-size: 0.8rem;
  border-radius: 6px;
  transition: all 0.3s ease;
}

.btn-outline-info:hover {
  transform: translateY(-1px);
  box-shadow: 0 2px 8px rgba(23, 162, 184, 0.3);
}

.btn-outline-primary:hover {
  transform: translateY(-1px);
  box-shadow: 0 2px 8px rgba(0, 123, 255, 0.3);
}

.btn-outline-warning:hover {
  transform: translateY(-1px);
  box-shadow: 0 2px 8px rgba(255, 193, 7, 0.3);
}

.btn-outline-danger:hover {
  transform: translateY(-1px);
  box-shadow: 0 2px 8px rgba(220, 53, 69, 0.3);
}

/* Pagination Styles */
.card-footer {
  border-top: 1px solid #dee2e6;
  padding: 1rem 1.5rem;
}

.pagination {
  margin: 0;
}

.page-link {
  border-radius: 6px;
  margin: 0 2px;
  border: 1px solid #dee2e6;
  color: #495057;
  transition: all 0.3s ease;
}

.page-link:hover {
  background-color: #667eea;
  border-color: #667eea;
  color: white;
  transform: translateY(-1px);
}

.page-item.active .page-link {
  background-color: #667eea;
  border-color: #667eea;
}

/* Modal Styles */
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
  backdrop-filter: blur(2px);
}

.modal-dialog {
  background: white;
  border-radius: 12px;
  max-width: 800px;
  width: 90%;
  max-height: 90vh;
  overflow-y: auto;
  box-shadow: 0 10px 40px rgba(0,0,0,0.3);
}

.modal-content {
  border: none;
  border-radius: 12px;
}

.modal-header {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
  border-radius: 12px 12px 0 0;
  padding: 1.5rem;
}

.modal-title {
  font-weight: 600;
  font-size: 1.2rem;
}

.btn-close {
  filter: invert(1);
  opacity: 0.8;
}

.btn-close:hover {
  opacity: 1;
}

.modal-body {
  padding: 2rem;
}

.modal-footer {
  border-top: 1px solid #dee2e6;
  padding: 1rem 2rem;
  background-color: #f8f9fa;
  border-radius: 0 0 12px 12px;
}

/* Detail Styles */
.detail-group {
  margin-bottom: 1rem;
}

.detail-label {
  font-weight: 600;
  color: #495057;
  font-size: 0.9rem;
  margin-bottom: 0.5rem;
  display: block;
}

.detail-value {
  color: #212529;
  font-size: 1rem;
  line-height: 1.5;
}

.detail-text {
  background-color: #f8f9fa;
  padding: 0.75rem;
  border-radius: 6px;
  border-left: 4px solid #667eea;
  white-space: pre-wrap;
  word-wrap: break-word;
}

/* Badge Styles */
.badge {
  font-size: 0.8rem;
  padding: 0.5rem 0.75rem;
  border-radius: 6px;
}

/* Loading Spinner */
.spinner-border {
  animation: spinner-border 0.75s linear infinite;
}

@keyframes spinner-border {
  to {
    transform: rotate(360deg);
  }
}

/* Transitions */
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.3s ease;
}

.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}

/* Responsive Design */
@media (max-width: 768px) {
  .list-penanganan-container {
    padding: 10px;
  }
  
  .header-section .col-md-4 {
    text-align: center !important;
    margin-top: 1rem;
  }
  
  .action-buttons {
    flex-direction: column;
  }
  
  .action-buttons .btn {
    width: 100%;
    margin-bottom: 0.25rem;
  }
  
  .modal-dialog {
    width: 95%;
    margin: 1rem;
  }
  
  .modal-body {
    padding: 1rem;
  }
  
  .table-responsive {
    font-size: 0.9rem;
  }
  
  .avatar-circle {
    width: 35px;
    height: 35px;
    font-size: 0.7rem;
  }
}

@media (max-width: 576px) {
  .keluhan-preview,
  .diagnosis-preview {
    max-width: 150px;
  }
  
  .filters-section .row .col-md-6 {
    margin-bottom: 1rem;
  }
  
  .card-footer .row .col-md-6:first-child {
    margin-bottom: 1rem;
  }
}

/* Debug Alert */
.alert-info {
  border-radius: 8px;
  border-left: 4px solid #17a2b8;
}

.alert-info pre {
  background-color: #f8f9fa;
  padding: 0.5rem;
  border-radius: 4px;
  font-size: 0.8rem;
  max-height: 200px;
  overflow-y: auto;
}

/* Empty State */
.fa-inbox {
  opacity: 0.3;
}

/* Hover Effects */
.card:hover {
  transform: translateY(-2px);
  transition: all 0.3s ease;
}

.form-control:hover {
  border-color: #adb5bd;
}

/* Focus States */
.btn:focus {
  box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
}

/* Custom Scrollbar */
.modal-dialog::-webkit-scrollbar {
  width: 6px;
}

.modal-dialog::-webkit-scrollbar-track {
  background: #f1f1f1;
  border-radius: 3px;
}

.modal-dialog::-webkit-scrollbar-thumb {
  background: #888;
  border-radius: 3px;
}

.modal-dialog::-webkit-scrollbar-thumb:hover {
  background: #555;
}
.table-section .card-header h5 {
  color: #111 !important;
}
</style>