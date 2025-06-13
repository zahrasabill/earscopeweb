<template>
  <app-layout activePage="riwayat">
  <div class="container-fluid riwayat-container">
    <!-- Header Section -->
    <div class="header-section mb-4">
      <div class="card shadow-sm">
        <div class="card-body">
          <div class="row align-items-center">
            <div class="col-md-8">
              <h4 class="mb-0 text-primary">
                <i class="fas fa-history me-2"></i>Riwayat Pemeriksaan
              </h4>
              <p class="text-muted mb-0 mt-1">Riwayat pemeriksaan video yang telah di-assign dan didiagnosis</p>
            </div>
            <div class="col-md-4 text-end">
              <button @click="exportToPDF" class="btn btn-outline-primary me-2" :disabled="filteredHistory.length === 0">
                <i class="fas fa-file-pdf me-1"></i>Export PDF
              </button>
              <button @click="exportToExcel" class="btn btn-outline-success" :disabled="filteredHistory.length === 0">
                <i class="fas fa-file-excel me-1"></i>Export Excel
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Advanced Filter Section -->
    <div class="filter-section mb-4">
      <div class="card shadow-sm">
        <div class="card-body">
          <div class="row align-items-end">
            <div class="col-md-3">
              <label for="dateRangeStart" class="form-label fw-bold">Tanggal Mulai:</label>
              <input type="date" id="dateRangeStart" v-model="dateRangeStart" class="form-control" @change="filterData">
            </div>
            <div class="col-md-3">
              <label for="dateRangeEnd" class="form-label fw-bold">Tanggal Akhir:</label>
              <input type="date" id="dateRangeEnd" v-model="dateRangeEnd" class="form-control" @change="filterData">
            </div>
            <div class="col-md-3">
              <label for="patientFilter" class="form-label fw-bold">Filter Pasien:</label>
              <select v-model="selectedPatientId" id="patientFilter" class="form-select" @change="filterData">
                <option value="">-- Semua Pasien --</option>
                <option v-for="user in users" :key="user.id" :value="user.id">
                  {{ user.name }} ({{ user.kode_akses || 'No RM' }})
                </option>
              </select>
            </div>
            <div class="col-md-2">
              <label for="diagnosisFilter" class="form-label fw-bold">Filter Diagnosis:</label>
              <select v-model="selectedDiagnosis" id="diagnosisFilter" class="form-select" @change="filterData">
                <option value="">-- Semua Diagnosis --</option>
                <option v-for="diagnosis in uniqueDiagnoses" :key="diagnosis" :value="diagnosis">
                  {{ diagnosis }}
                </option>
              </select>
            </div>
            <div class="col-md-1">
              <button @click="resetFilter" class="btn btn-outline-secondary">
                <i class="fas fa-sync-alt"></i>
              </button>
            </div>
          </div>
          <div class="row mt-3">
            <div class="col-md-4">
              <label for="searchQuery" class="form-label fw-bold">Pencarian:</label>
              <input 
                type="text" 
                id="searchQuery" 
                v-model="searchQuery" 
                class="form-control" 
                placeholder="Cari nama pasien, diagnosis, atau keterangan..."
                @input="filterData"
              >
            </div>
            <div class="col-md-8">
              <div class="statistics-row">
                <div class="stat-item">
                  <span class="stat-label">Total Pemeriksaan:</span>
                  <span class="stat-value">{{ filteredHistory.length }}</span>
                </div>
                <div class="stat-item">
                  <span class="stat-label">Pasien Unik:</span>
                  <span class="stat-value">{{ uniquePatients }}</span>
                </div>
                <div class="stat-item">
                  <span class="stat-label">Bulan Ini:</span>
                  <span class="stat-value">{{ thisMonthCount }}</span>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal Detail Pemeriksaan -->
    <transition name="fade">
      <div v-if="showDetailModal" class="modal-overlay" @click.self="closeDetailModal">
        <div class="modal-dialog modal-lg">
          <div class="modal-content detail-modal">
            <div class="modal-header">
              <h5 class="modal-title">Detail Pemeriksaan</h5>
              <button type="button" class="btn-close" @click="closeDetailModal"></button>
            </div>
            <div class="modal-body" v-if="selectedHistory">
              <div class="row">
                <!-- Patient Info -->
                <div class="col-md-6">
                  <div class="detail-section">
                    <h6 class="section-title">
                      <i class="fas fa-user-circle me-2"></i>Informasi Pasien
                    </h6>
                    <div class="detail-grid">
                      <div class="detail-item">
                        <span class="detail-label">Nama:</span>
                        <span class="detail-value">{{ selectedHistory.user?.name }}</span>
                      </div>
                      <div class="detail-item">
                        <span class="detail-label">No. RM:</span>
                        <span class="detail-value">{{ selectedHistory.user?.kode_akses || '-' }}</span>
                      </div>
                      <div class="detail-item">
                        <span class="detail-label">Usia:</span>
                        <span class="detail-value">{{ selectedHistory.user?.usia || '-' }} tahun</span>
                      </div>
                      <div class="detail-item">
                        <span class="detail-label">Gender:</span>
                        <span class="detail-value">{{ capitalizeFirst(selectedHistory.user?.gender) || '-' }}</span>
                      </div>
                    </div>
                  </div>
                </div>
                
                <!-- Examination Info -->
                <div class="col-md-6">
                  <div class="detail-section">
                    <h6 class="section-title">
                      <i class="fas fa-stethoscope me-2"></i>Informasi Pemeriksaan
                    </h6>
                    <div class="detail-grid">
                      <div class="detail-item">
                        <span class="detail-label">Tanggal:</span>
                        <span class="detail-value">{{ formatDate(selectedHistory.created_at) }}</span>
                      </div>
                      <div class="detail-item">
                        <span class="detail-label">Status:</span>
                        <span class="badge bg-success">{{ selectedHistory.status }}</span>
                      </div>
                      <div class="detail-item">
                        <span class="detail-label">Diagnosis Sistem:</span>
                        <span class="detail-value">{{ selectedHistory.hasil_diagnosis || '-' }}</span>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
              
              <!-- Videos Section -->
              <div class="detail-section mt-4">
                <h6 class="section-title">
                  <i class="fas fa-video me-2"></i>Video Pemeriksaan
                </h6>
                <div class="row">
                  <div class="col-md-6" v-if="selectedHistory.rawVideoUrl">
                    <div class="video-detail-item">
                      <p class="video-label">Raw Video</p>
                      <video :src="selectedHistory.rawVideoUrl" controls class="detail-video"></video>
                    </div>
                  </div>
                  <div class="col-md-6" v-if="selectedHistory.processedVideoUrl">
                    <div class="video-detail-item">
                      <p class="video-label">Processed Video</p>
                      <video :src="selectedHistory.processedVideoUrl" controls class="detail-video"></video>
                    </div>
                  </div>
                </div>
              </div>
              
              <!-- Diagnosis Section -->
              <div class="detail-section mt-4" v-if="selectedHistory.keterangan">
                <h6 class="section-title">
                  <i class="fas fa-notes-medical me-2"></i>Diagnosis Manual
                </h6>
                <div class="diagnosis-detail">
                  {{ selectedHistory.keterangan }}
                </div>
              </div>
            </div>
            <div class="modal-footer">
              <button class="btn btn-outline-primary" @click="printDetail">
                <i class="fas fa-print me-1"></i>Print
              </button>
              <button class="btn btn-secondary" @click="closeDetailModal">Tutup</button>
            </div>
          </div>
        </div>
      </div>
    </transition>

    <!-- Modal Edit Diagnosis -->
    <transition name="fade">
      <div v-if="showEditModal" class="modal-overlay" @click.self="closeEditModal">
        <div class="modal-dialog">
          <div class="modal-content simple-modal">
            <div class="modal-header">
              <h5 class="modal-title">Edit Diagnosis Manual</h5>
              <button type="button" class="btn-close" @click="closeEditModal"></button>
            </div>
            <div class="modal-body">
              <div v-if="editSuccess" class="text-center py-3">
                <i class="fas fa-check-circle text-success" style="font-size: 48px;"></i>
                <h5 class="mt-3 text-success">Berhasil!</h5>
                <p>Diagnosis manual telah berhasil diupdate</p>
              </div>
              <div v-else>
                <label for="editDiagnosisText" class="form-label">Diagnosis Manual:</label>
                <textarea 
                  v-model="editDiagnosisText" 
                  id="editDiagnosisText" 
                  class="form-control" 
                  rows="4" 
                  placeholder="Masukkan diagnosis manual (maksimal 100 kata)"
                  @input="countEditWords"
                ></textarea>
                <small class="form-text text-muted">
                  {{ editWordCount }}/100 kata
                </small>
                <div v-if="editWordCount > 100" class="text-danger small mt-1">
                  Diagnosis melebihi batas maksimal 100 kata
                </div>
              </div>
            </div>
            <div class="modal-footer">
              <button v-if="editSuccess" class="btn btn-primary" @click="closeEditModal">
                Selesai
              </button>
              <template v-else>
                <button class="btn btn-secondary" @click="closeEditModal">Batal</button>
                <button 
                  class="btn btn-primary" 
                  :disabled="!editDiagnosisText.trim() || editWordCount > 100 || editLoading" 
                  @click="updateDiagnosis"
                >
                  <span v-if="editLoading" class="spinner-border spinner-border-sm me-2"></span>
                  Update
                </button>
              </template>
            </div>
          </div>
        </div>
      </div>
    </transition>

    <!-- Loading indicator -->
    <div v-if="isLoading" class="loading-container">
      <div class="loading-content">
        <div class="spinner-border text-primary" role="status">
          <span class="visually-hidden">Memuat riwayat...</span>
        </div>
        <p class="mt-3 fw-bold">Memuat riwayat pemeriksaan...</p>
      </div>
    </div>

    <!-- No data message -->
    <div v-else-if="filteredHistory.length === 0" class="no-data-container">
      <div class="alert alert-info text-center" role="alert">
        <i class="fas fa-info-circle me-2"></i>
        Tidak ada riwayat pemeriksaan untuk filter yang dipilih.
      </div>
    </div>

    <!-- History Table -->
    <div v-else class="history-table-section">
      <div class="card shadow-sm">
        <div class="card-body p-0">
          <div class="table-responsive">
            <table class="table table-hover mb-0">
              <thead class="table-header">
                <tr>
                  <th scope="col" class="sortable" @click="sortBy('created_at')">
                    Tanggal
                    <i class="fas fa-sort ms-1" :class="getSortIcon('created_at')"></i>
                  </th>
                  <th scope="col" class="sortable" @click="sortBy('user.name')">
                    Pasien
                    <i class="fas fa-sort ms-1" :class="getSortIcon('user.name')"></i>
                  </th>
                  <th scope="col">No. RM</th>
                  <th scope="col">Diagnosis Sistem</th>
                  <th scope="col">Diagnosis Manual</th>
                  <th scope="col">Status</th>
                  <th scope="col" class="text-center">Aksi</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="history in paginatedHistory" :key="history.id" class="table-row">
                  <td>
                    <div class="date-cell">
                      <strong>{{ formatDate(history.created_at, 'date') }}</strong>
                      <small class="text-muted d-block">{{ formatDate(history.created_at, 'time') }}</small>
                    </div>
                  </td>
                  <td>
                    <div class="patient-cell">
                      <strong>{{ history.user?.name || 'Unknown' }}</strong>
                      <small class="text-muted d-block" v-if="history.user?.usia || history.user?.gender">
                        {{ history.user?.usia ? history.user.usia + ' tahun' : '' }}
                        {{ history.user?.gender ? '• ' + capitalizeFirst(history.user.gender) : '' }}
                      </small>
                    </div>
                  </td>
                  <td>
                    <span class="badge bg-light text-dark">{{ history.user?.kode_akses || '-' }}</span>
                  </td>
                  <td>
                    <span class="diagnosis-cell" :title="history.hasil_diagnosis">
                      {{ truncateText(history.hasil_diagnosis, 30) || '-' }}
                    </span>
                  </td>
                  <td>
                    <div class="manual-diagnosis-cell">
                      <span v-if="history.keterangan" :title="history.keterangan">
                        {{ truncateText(history.keterangan, 40) }}
                      </span>
                      <span v-else class="text-muted">Belum ada</span>
                      <button 
                        v-if="userRole === 'dokter'" 
                        @click="openEditModal(history)"
                        class="btn btn-sm btn-outline-primary ms-2"
                        title="Edit diagnosis manual"
                      >
                        <i class="fas fa-edit"></i>
                      </button>
                    </div>
                  </td>
                  <td>
                    <span class="badge bg-success">{{ capitalizeFirst(history.status) }}</span>
                  </td>
                  <td class="text-center">
                    <div class="action-buttons">
                      <button @click="viewDetail(history)" class="btn btn-sm btn-outline-info" title="Lihat Detail">
                        <i class="fas fa-eye"></i>
                      </button>
                      <button @click="downloadReport(history)" class="btn btn-sm btn-outline-success ms-1" title="Download Laporan">
                        <i class="fas fa-download"></i>
                      </button>
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <!-- Pagination -->
      <div class="pagination-section mt-4" v-if="totalPages > 1">
        <nav aria-label="Page navigation">
          <ul class="pagination justify-content-center">
            <li class="page-item" :class="{ disabled: currentPage === 1 }">
              <button class="page-link" @click="changePage(currentPage - 1)" :disabled="currentPage === 1">
                <i class="fas fa-chevron-left"></i>
              </button>
            </li>
            <li v-for="page in visiblePages" :key="page" class="page-item" :class="{ active: page === currentPage }">
              <button class="page-link" @click="changePage(page)">{{ page }}</button>
            </li>
            <li class="page-item" :class="{ disabled: currentPage === totalPages }">
              <button class="page-link" @click="changePage(currentPage + 1)" :disabled="currentPage === totalPages">
                <i class="fas fa-chevron-right"></i>
              </button>
            </li>
          </ul>
        </nav>
        <div class="pagination-info text-center mt-2">
          <small class="text-muted">
            Menampilkan {{ (currentPage - 1) * itemsPerPage + 1 }} - 
            {{ Math.min(currentPage * itemsPerPage, filteredHistory.length) }} 
            dari {{ filteredHistory.length }} data
          </small>
        </div>
      </div>
    </div>
  </div>
  </app-layout>
</template>

<script>
import { ref, onMounted, computed, watch } from 'vue';
import AppLayout from '@/components/AppLayout.vue';
import { useVideoStore } from '@/stores/videoStore';
import { useUserStore } from '@/stores/userStore';
import { storeToRefs } from 'pinia';
import api from '@/api';
import axios from 'axios';
import { jwtDecode } from "jwt-decode";

export default {
  name: 'RiwayatPemeriksaanView',
  components: { AppLayout },
  setup() {
    const videoStore = useVideoStore();
    const userStore = useUserStore();
    const { users } = storeToRefs(userStore);
    
    // User role from JWT
    const userRole = ref('pasien');
    
    // Data state
    const historyData = ref([]);
    const isLoading = ref(false);
    
    // Filter states
    const dateRangeStart = ref('');
    const dateRangeEnd = ref('');
    const selectedPatientId = ref('');
    const selectedDiagnosis = ref('');
    const searchQuery = ref('');
    
    // Modal states
    const showDetailModal = ref(false);
    const showEditModal = ref(false);
    const selectedHistory = ref(null);
    const editDiagnosisText = ref('');
    const editWordCount = ref(0);
    const editLoading = ref(false);
    const editSuccess = ref(false);
    
    // Pagination
    const currentPage = ref(1);
    const itemsPerPage = ref(10);
    
    // Sorting
    const sortField = ref('created_at');
    const sortDirection = ref('desc');
    
    // Computed properties
    const filteredHistory = computed(() => {
      let filtered = historyData.value;
      
      // Filter by date range
      if (dateRangeStart.value) {
        filtered = filtered.filter(item => 
          new Date(item.created_at) >= new Date(dateRangeStart.value)
        );
      }
      if (dateRangeEnd.value) {
        filtered = filtered.filter(item => 
          new Date(item.created_at) <= new Date(dateRangeEnd.value + 'T23:59:59')
        );
      }
      
      // Filter by patient
      if (selectedPatientId.value) {
        filtered = filtered.filter(item => 
          item.user && item.user.id.toString() === selectedPatientId.value.toString()
        );
      }
      
      // Filter by diagnosis
      if (selectedDiagnosis.value) {
        filtered = filtered.filter(item => 
          item.hasil_diagnosis === selectedDiagnosis.value
        );
      }
      
      // Search filter
      if (searchQuery.value) {
        const query = searchQuery.value.toLowerCase();
        filtered = filtered.filter(item => 
          (item.user?.name || '').toLowerCase().includes(query) ||
          (item.hasil_diagnosis || '').toLowerCase().includes(query) ||
          (item.keterangan || '').toLowerCase().includes(query)
        );
      }
      
      // Apply sorting
      filtered.sort((a, b) => {
        let aVal, bVal;
        
        if (sortField.value === 'user.name') {
          aVal = a.user?.name || '';
          bVal = b.user?.name || '';
        } else if (sortField.value === 'created_at') {
          aVal = new Date(a.created_at);
          bVal = new Date(b.created_at);
        } else {
          aVal = a[sortField.value] || '';
          bVal = b[sortField.value] || '';
        }
        
        if (sortDirection.value === 'asc') {
          return aVal > bVal ? 1 : -1;
        } else {
          return aVal < bVal ? 1 : -1;
        }
      });
      
      return filtered;
    });
    
    const paginatedHistory = computed(() => {
      const start = (currentPage.value - 1) * itemsPerPage.value;
      const end = start + itemsPerPage.value;
      return filteredHistory.value.slice(start, end);
    });
    
    const totalPages = computed(() => {
      return Math.ceil(filteredHistory.value.length / itemsPerPage.value);
    });
    
    const visiblePages = computed(() => {
      const pages = [];
      const maxVisible = 5;
      let start = Math.max(1, currentPage.value - Math.floor(maxVisible / 2));
      let end = Math.min(totalPages.value, start + maxVisible - 1);
      
      if (end - start < maxVisible - 1) {
        start = Math.max(1, end - maxVisible + 1);
      }
      
      for (let i = start; i <= end; i++) {
        pages.push(i);
      }
      return pages;
    });
    
    const uniqueDiagnoses = computed(() => {
      const diagnoses = [...new Set(historyData.value
        .map(item => item.hasil_diagnosis)
        .filter(diagnosis => diagnosis))];
      return diagnoses.sort();
    });
    
    const uniquePatients = computed(() => {
      const patients = new Set(filteredHistory.value
        .map(item => item.user?.id)
        .filter(id => id));
      return patients.size;
    });
    
    const thisMonthCount = computed(() => {
      const now = new Date();
      const thisMonth = now.getMonth();
      const thisYear = now.getFullYear();
      
      return filteredHistory.value.filter(item => {
        const itemDate = new Date(item.created_at);
        return itemDate.getMonth() === thisMonth && itemDate.getFullYear() === thisYear;
      }).length;
    });
    
    // Functions
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
    
    const fetchHistoryData = async () => {
      try {
        isLoading.value = true;
        const token = localStorage.getItem("token") || sessionStorage.getItem("token");
        
        // Fetch assigned videos (history data)
        const response = await axios.get(api.getEndpoint('videos/assigned'), {
          headers: { Authorization: `Bearer ${token}` },
        });
        
        historyData.value = response.data.data || [];
        
        // Load video URLs for each history item
        historyData.value.forEach(async (item) => {
          if (item.raw_video_stream_url) {
            await videoStore.loadVideoUrl(item.id, 'raw');
          }
          if (item.processed_video_stream_url) {
            await videoStore.loadVideoUrl(item.id, 'processed');
          }
        });
        
      } catch (error) {
        console.error('Error fetching history data:', error);
      } finally {
        isLoading.value = false;
      }
    };
    
    const filterData = () => {
      currentPage.value = 1; // Reset to first page when filtering
    };
    
    const resetFilter = () => {
      dateRangeStart.value = '';
      dateRangeEnd.value = '';
      selectedPatientId.value = '';
      selectedDiagnosis.value = '';
      searchQuery.value = '';
      currentPage.value = 1;
    };
    
    const sortBy = (field) => {
      if (sortField.value === field) {
        sortDirection.value = sortDirection.value === 'asc' ? 'desc' : 'asc';
      } else {
        sortField.value = field;
        sortDirection.value = 'asc';
      }
    };
    
    const getSortIcon = (field) => {
      if (sortField.value !== field) return '';
      return sortDirection.value === 'asc' ? 'fa-sort-up' : 'fa-sort-down';
    };
    
    const changePage = (page) => {
      if (page >= 1 && page <= totalPages.value) {
        currentPage.value = page;
      }
    };
    
    const viewDetail = (history) => {
      selectedHistory.value = history;
      showDetailModal.value = true;
    };
    
    const closeDetailModal = () => {
      showDetailModal.value = false;
      selectedHistory.value = null;
    };
    
    const openEditModal = (history) => {
      selectedHistory.value = history;
      editDiagnosisText.value = history.keterangan || '';
      countEditWords();
      editSuccess.value = false;
      showEditModal.value = true;
    };
    
    const closeEditModal = () => {
      showEditModal.value = false;
      setTimeout(() => {
        editSuccess.value = false;
        editDiagnosisText.value = '';
        editWordCount.value = 0;
        selectedHistory.value = null;
      }, 300);
    };
    
    const countEditWords = () => {
      const text = editDiagnosisText.value.trim();
      editWordCount.value = text ? text.split(/\s+/).length : 0;
    };
    
    const updateDiagnosis = async () => {
      if (!editDiagnosisText.value.trim() || editWordCount.value > 100) return;
      
      try {
        editLoading.value = true;
        const token = localStorage.getItem("token") || sessionStorage.getItem("token");
        
        await axios.put(api.getEndpoint(`videos/${selectedHistory.value.id}`), {
          keterangan: editDiagnosisText.value.trim()
        }, {
          headers: { Authorization: `Bearer ${token}` },
        });
        
        // Update local data
        const historyIndex = historyData.value.findIndex(item => item.id === selectedHistory.value.id);
        if (historyIndex !== -1) {
          historyData.value[historyIndex].keterangan = editDiagnosisText.value.trim();
        }
        
        editSuccess.value = true;
        
        setTimeout(() => {
          if (showEditModal.value) {
            closeEditModal();
          }
        }, 2000);
        
      } catch (error) {
        console.error('Error updating diagnosis:', error);
        alert('Gagal mengupdate diagnosis manual. Silakan coba lagi.');
      } finally {
        editLoading.value = false;
      }
    };
    
    const formatDate = (dateString, type = 'full') => {
      const date = new Date(dateString);
      if (type === 'date') {
        return date.toLocaleDateString('id-ID', {
          day: 'numeric',
          month: 'short',
          year: 'numeric'
        });
      } else if (type === 'time') {
        return date.toLocaleTimeString('id-ID', {
          hour: '2-digit',
          minute: '2-digit'
        });
      } else {
        return date.toLocaleString('id-ID', {
          day: 'numeric',
          month: 'long',
          year: 'numeric',
          hour: '2-digit',
          minute: '2-digit'
        });
      }
    };
    
    const capitalizeFirst = (string) => {
      if (!string) return '';
      return string.charAt(0).toUpperCase() + string.slice(1);
    };
    
    const truncateText = (text, maxLength) => {
      if (!text) return '';
      return text.length > maxLength ? text.substring(0, maxLength) + '...' : text;
    };
    
   const downloadReport = async (history) => {
      try {
        const token = localStorage.getItem("token") || sessionStorage.getItem("token");
        
        // Create PDF report content
        const reportContent = {
          patientName: history.user?.name || 'Unknown',
          patientRM: history.user?.kode_akses || '-',
          patientAge: history.user?.usia || '-',
          patientGender: capitalizeFirst(history.user?.gender) || '-',
          examinationDate: formatDate(history.created_at),
          systemDiagnosis: history.hasil_diagnosis || '-',
          manualDiagnosis: history.keterangan || 'Belum ada diagnosis manual',
          status: capitalizeFirst(history.status)
        };
        
        // Generate PDF using a simple approach
        const printWindow = window.open('', '_blank');
        const reportHtml = `
          <!DOCTYPE html>
          <html>
          <head>
            <title>Laporan Pemeriksaan - ${reportContent.patientName}</title>
            <style>
              body { 
                font-family: Arial, sans-serif; 
                margin: 40px; 
                line-height: 1.6;
                color: #333;
              }
              .header { 
                text-align: center; 
                border-bottom: 2px solid #007bff; 
                padding-bottom: 20px; 
                margin-bottom: 30px;
              }
              .header h1 { 
                color: #007bff; 
                margin: 0;
                font-size: 24px;
              }
              .header p { 
                margin: 5px 0; 
                color: #666;
              }
              .section { 
                margin-bottom: 25px; 
                padding: 15px;
                background: #f8f9fa;
                border-left: 4px solid #007bff;
              }
              .section h3 { 
                color: #007bff; 
                margin-top: 0;
                margin-bottom: 15px;
                font-size: 18px;
              }
              .info-grid { 
                display: grid; 
                grid-template-columns: 1fr 1fr; 
                gap: 15px;
              }
              .info-item { 
                padding: 8px 0;
                border-bottom: 1px solid #dee2e6;
              }
              .info-label { 
                font-weight: bold; 
                color: #495057;
                display: inline-block;
                width: 120px;
              }
              .info-value { 
                color: #212529;
              }
              .diagnosis-section {
                background: #fff;
                border: 1px solid #dee2e6;
                padding: 20px;
                margin: 20px 0;
                border-radius: 5px;
              }
              .diagnosis-section h4 {
                color: #28a745;
                margin-top: 0;
                margin-bottom: 10px;
              }
              .diagnosis-content {
                background: #f8f9fa;
                padding: 15px;
                border-radius: 3px;
                border-left: 4px solid #28a745;
              }
              .footer { 
                margin-top: 50px; 
                padding-top: 20px; 
                border-top: 1px solid #dee2e6; 
                text-align: center;
                color: #666;
                font-size: 12px;
              }
              @media print {
                body { margin: 20px; }
                .no-print { display: none; }
              }
            </style>
          </head>
          <body>
            <div class="header">
              <h1>LAPORAN PEMERIKSAAN MEDIS</h1>
              <p>Sistem Diagnosa Berbasis Video</p>
              <p>Tanggal Cetak: ${formatDate(new Date())}</p>
            </div>
            
            <div class="section">
              <h3>📋 Informasi Pasien</h3>
              <div class="info-grid">
                <div class="info-item">
                  <span class="info-label">Nama:</span>
                  <span class="info-value">${reportContent.patientName}</span>
                </div>
                <div class="info-item">
                  <span class="info-label">No. RM:</span>
                  <span class="info-value">${reportContent.patientRM}</span>
                </div>
                <div class="info-item">
                  <span class="info-label">Usia:</span>
                  <span class="info-value">${reportContent.patientAge} tahun</span>
                </div>
                <div class="info-item">
                  <span class="info-label">Jenis Kelamin:</span>
                  <span class="info-value">${reportContent.patientGender}</span>
                </div>
              </div>
            </div>
            
            <div class="section">
              <h3>🏥 Informasi Pemeriksaan</h3>
              <div class="info-item">
                <span class="info-label">Tanggal:</span>
                <span class="info-value">${reportContent.examinationDate}</span>
              </div>
              <div class="info-item">
                <span class="info-label">Status:</span>
                <span class="info-value">${reportContent.status}</span>
              </div>
            </div>
            
            <div class="diagnosis-section">
              <h4>🔬 Diagnosis Sistem</h4>
              <div class="diagnosis-content">
                ${reportContent.systemDiagnosis}
              </div>
            </div>
            
            <div class="diagnosis-section">
              <h4>👨‍⚕️ Diagnosis Manual (Dokter)</h4>
              <div class="diagnosis-content">
                ${reportContent.manualDiagnosis}
              </div>
            </div>
            
            <div class="footer">
              <p>Laporan ini digenerate secara otomatis oleh sistem</p>
              <p>© 2024 Sistem Diagnosa Medis Berbasis Video</p>
            </div>
            
            <div class="no-print" style="margin-top: 30px; text-align: center;">
              <button onclick="window.print()" style="background: #007bff; color: white; border: none; padding: 10px 20px; border-radius: 5px; cursor: pointer; margin-right: 10px;">
                🖨️ Print Laporan
              </button>
              <button onclick="window.close()" style="background: #6c757d; color: white; border: none; padding: 10px 20px; border-radius: 5px; cursor: pointer;">
                ❌ Tutup
              </button>
            </div>
          </body>
          </html>
        `;
        
        printWindow.document.write(reportHtml);
        printWindow.document.close();
        
      } catch (error) {
        console.error('Error generating report:', error);
        alert('Gagal membuat laporan. Silakan coba lagi.');
      }
    };
    
    const printDetail = () => {
      if (!selectedHistory.value) return;
      
      const printWindow = window.open('', '_blank');
      const detailHtml = `
        <!DOCTYPE html>
        <html>
        <head>
          <title>Detail Pemeriksaan - ${selectedHistory.value.user?.name}</title>
          <style>
            body { 
              font-family: Arial, sans-serif; 
              margin: 30px; 
              line-height: 1.6;
            }
            .header { 
              text-align: center; 
              border-bottom: 2px solid #007bff; 
              padding-bottom: 15px; 
              margin-bottom: 25px;
            }
            .section { 
              margin-bottom: 20px; 
              padding: 15px;
              border: 1px solid #dee2e6;
              border-radius: 5px;
            }
            .section h3 { 
              color: #007bff; 
              margin-top: 0;
            }
            .info-row { 
              display: flex; 
              margin: 8px 0;
            }
            .info-label { 
              font-weight: bold; 
              width: 150px;
            }
            @media print {
              body { margin: 15px; }
            }
          </style>
        </head>
        <body>
          <div class="header">
            <h2>DETAIL PEMERIKSAAN</h2>
            <p>Tanggal: ${formatDate(selectedHistory.value.created_at)}</p>
          </div>
          
          <div class="section">
            <h3>Informasi Pasien</h3>
            <div class="info-row">
              <span class="info-label">Nama:</span>
              <span>${selectedHistory.value.user?.name || '-'}</span>
            </div>
            <div class="info-row">
              <span class="info-label">No. RM:</span>
              <span>${selectedHistory.value.user?.kode_akses || '-'}</span>
            </div>
            <div class="info-row">
              <span class="info-label">Usia:</span>
              <span>${selectedHistory.value.user?.usia || '-'} tahun</span>
            </div>
            <div class="info-row">
              <span class="info-label">Jenis Kelamin:</span>
              <span>${capitalizeFirst(selectedHistory.value.user?.gender) || '-'}</span>
            </div>
          </div>
          
          <div class="section">
            <h3>Hasil Pemeriksaan</h3>
            <div class="info-row">
              <span class="info-label">Diagnosis Sistem:</span>
              <span>${selectedHistory.value.hasil_diagnosis || '-'}</span>
            </div>
            <div class="info-row">
              <span class="info-label">Diagnosis Manual:</span>
              <span>${selectedHistory.value.keterangan || 'Belum ada'}</span>
            </div>
            <div class="info-row">
              <span class="info-label">Status:</span>
              <span>${capitalizeFirst(selectedHistory.value.status)}</span>
            </div>
          </div>
          
          <div style="margin-top: 30px; text-align: center;">
            <button onclick="window.print()" style="background: #007bff; color: white; border: none; padding: 8px 16px; border-radius: 4px; margin-right: 10px;">
              Print
            </button>
            <button onclick="window.close()" style="background: #6c757d; color: white; border: none; padding: 8px 16px; border-radius: 4px;">
              Tutup
            </button>
          </div>
        </body>
        </html>
      `;
      
      printWindow.document.write(detailHtml);
      printWindow.document.close();
    };
    
    const exportToPDF = async () => {
      if (filteredHistory.value.length === 0) return;
      
      try {
        const printWindow = window.open('', '_blank');
        let tableRows = '';
        
        filteredHistory.value.forEach((history, index) => {
          tableRows += `
            <tr>
              <td>${index + 1}</td>
              <td>${formatDate(history.created_at, 'date')}</td>
              <td>${history.user?.name || 'Unknown'}</td>
              <td>${history.user?.kode_akses || '-'}</td>
              <td>${history.hasil_diagnosis || '-'}</td>
              <td>${history.keterangan || 'Belum ada'}</td>
              <td>${capitalizeFirst(history.status)}</td>
            </tr>
          `;
        });
        
        const pdfHtml = `
          <!DOCTYPE html>
          <html>
          <head>
            <title>Laporan Riwayat Pemeriksaan</title>
            <style>
              body { 
                font-family: Arial, sans-serif; 
                margin: 20px;
                font-size: 12px;
              }
              .header { 
                text-align: center; 
                margin-bottom: 30px;
                border-bottom: 2px solid #007bff;
                padding-bottom: 15px;
              }
              .header h1 { 
                color: #007bff; 
                margin: 0;
              }
              table { 
                width: 100%; 
                border-collapse: collapse; 
                margin-top: 20px;
              }
              th, td { 
                border: 1px solid #ddd; 
                padding: 8px; 
                text-align: left;
              }
              th { 
                background-color: #f8f9fa; 
                font-weight: bold;
                color: #495057;
              }
              tr:nth-child(even) { 
                background-color: #f8f9fa;
              }
              .summary {
                background: #e9ecef;
                padding: 15px;
                margin-bottom: 20px;
                border-radius: 5px;
              }
              .footer {
                margin-top: 30px;
                text-align: center;
                font-size: 10px;
                color: #666;
                border-top: 1px solid #ddd;
                padding-top: 15px;
              }
              @media print {
                body { margin: 10px; }
                .no-print { display: none; }
              }
            </style>
          </head>
          <body>
            <div class="header">
              <h1>LAPORAN RIWAYAT PEMERIKSAAN</h1>
              <p>Periode: ${dateRangeStart.value || 'Semua'} - ${dateRangeEnd.value || 'Semua'}</p>
              <p>Dicetak pada: ${formatDate(new Date())}</p>
            </div>
            
            <div class="summary">
              <strong>Ringkasan:</strong><br>
              Total Pemeriksaan: ${filteredHistory.value.length} | 
              Pasien Unik: ${uniquePatients.value} | 
              Bulan Ini: ${thisMonthCount.value}
            </div>
            
            <table>
              <thead>
                <tr>
                  <th>No</th>
                  <th>Tanggal</th>
                  <th>Pasien</th>
                  <th>No. RM</th>
                  <th>Diagnosis Sistem</th>
                  <th>Diagnosis Manual</th>
                  <th>Status</th>
                </tr>
              </thead>
              <tbody>
                ${tableRows}
              </tbody>
            </table>
            
            <div class="footer">
              <p>Laporan ini digenerate secara otomatis oleh sistem</p>
              <p>© 2024 Sistem Diagnosa Medis Berbasis Video</p>
            </div>
            
            <div class="no-print" style="margin-top: 20px; text-align: center;">
              <button onclick="window.print()" style="background: #dc3545; color: white; border: none; padding: 10px 20px; border-radius: 5px; margin-right: 10px;">
                📄 Print PDF
              </button>
              <button onclick="window.close()" style="background: #6c757d; color: white; border: none; padding: 10px 20px; border-radius: 5px;">
                ❌ Tutup
              </button>
            </div>
          </body>
          </html>
        `;
        
        printWindow.document.write(pdfHtml);
        printWindow.document.close();
        
      } catch (error) {
        console.error('Error exporting to PDF:', error);
        alert('Gagal mengekspor ke PDF. Silakan coba lagi.');
      }
    };
    
    const exportToExcel = () => {
      if (filteredHistory.value.length === 0) return;
      
      try {
        // Prepare data for CSV/Excel export
        const csvData = filteredHistory.value.map((history, index) => ({
          'No': index + 1,
          'Tanggal': formatDate(history.created_at),
          'Nama Pasien': history.user?.name || 'Unknown',
          'No. RM': history.user?.kode_akses || '-',
          'Usia': history.user?.usia || '-',
          'Jenis Kelamin': capitalizeFirst(history.user?.gender) || '-',
          'Diagnosis Sistem': history.hasil_diagnosis || '-',
          'Diagnosis Manual': history.keterangan || 'Belum ada',
          'Status': capitalizeFirst(history.status)
        }));
        
        // Convert to CSV
        const headers = Object.keys(csvData[0]);
        const csvContent = [
          headers.join(','),
          ...csvData.map(row => headers.map(header => {
            const value = row[header];
            // Escape commas and quotes in CSV
            return typeof value === 'string' && (value.includes(',') || value.includes('"')) 
              ? `"${value.replace(/"/g, '""')}"` 
              : value;
          }).join(','))
        ].join('\n');
        
        // Create download link
        const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
        const link = document.createElement('a');
        const url = URL.createObjectURL(blob);
        link.setAttribute('href', url);
        link.setAttribute('download', `riwayat_pemeriksaan_${new Date().toISOString().split('T')[0]}.csv`);
        link.style.visibility = 'hidden';
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
        
      } catch (error) {
        console.error('Error exporting to Excel:', error);
        alert('Gagal mengekspor ke Excel. Silakan coba lagi.');
      }
    };
    
    // Watchers
    watch([dateRangeStart, dateRangeEnd, selectedPatientId, selectedDiagnosis], () => {
      currentPage.value = 1;
    });
    
    // Lifecycle
    onMounted(async () => {
      userRole.value = getUserRoleFromToken();
      await userStore.fetchUsers();
      await fetchHistoryData();
    });
    
    return {
      // Data
      historyData,
      isLoading,
      userRole,
      users,
      
      // Filters
      dateRangeStart,
      dateRangeEnd,
      selectedPatientId,
      selectedDiagnosis,
      searchQuery,
      
      // Modals
      showDetailModal,
      showEditModal,
      selectedHistory,
      editDiagnosisText,
      editWordCount,
      editLoading,
      editSuccess,
      
      // Pagination
      currentPage,
      itemsPerPage,
      totalPages,
      visiblePages,
      
      // Sorting
      sortField,
      sortDirection,
      
      // Computed
      filteredHistory,
      paginatedHistory,
      uniqueDiagnoses,
      uniquePatients,
      thisMonthCount,
      
      // Methods
      fetchHistoryData,
      filterData,
      resetFilter,
      sortBy,
      getSortIcon,
      changePage,
      viewDetail,
      closeDetailModal,
      openEditModal,
      closeEditModal,
      countEditWords,
      updateDiagnosis,
      formatDate,
      capitalizeFirst,
      truncateText,
      downloadReport,
      printDetail,
      exportToPDF,
      exportToExcel
    };
  }
};
</script>

<style scoped>
/* Main Container */
.riwayat-container {
  padding: 20px;
  background-color: #f8f9fa;
  min-height: 100vh;
}

/* Header Section */
.header-section .card {
  border: none;
  border-radius: 12px;
}

.header-section h4 {
  font-weight: 600;
}

/* Filter Section */
.filter-section .card {
  border: none;
  border-radius: 12px;
}

.statistics-row {
  display: flex;
  gap: 20px;
  align-items: center;
  margin-top: 10px;
}

.stat-item {
  display: flex;
  flex-direction: column;
  text-align: center;
}

.stat-label {
  font-size: 0.85rem;
  color: #6c757d;
  font-weight: 500;
}

.stat-value {
  font-size: 1.5rem;
  font-weight: bold;
  color: #007bff;
}

/* Loading */
.loading-container {
  display: flex;
  justify-content: center;
  align-items: center;
  min-height: 400px;
  flex-direction: column;
}

.loading-content {
  text-align: center;
}

/* No Data */
.no-data-container {
  margin-top: 20px;
}

/* Table Styles */
.history-table-section .card {
  border: none;
  border-radius: 12px;
  overflow: hidden;
}

.table-header {
  background-color: #007bff;
  color: white;
}

.table-header th {
  border: none;
  font-weight: 600;
  padding: 15px 12px;
}

.sortable {
  cursor: pointer;
  user-select: none;
  transition: background-color 0.3s ease;
}

.sortable:hover {
  background-color: rgba(255, 255, 255, 0.1);
}

.table-row {
  transition: all 0.3s ease;
}

.table-row:hover {
  background-color: #f8f9fa;
  transform: translateY(-1px);
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.table td {
  border-color: #e9ecef;
  padding: 12px;
  vertical-align: middle;
}

/* Table Cell Styles */
.date-cell strong {
  color: #495057;
  font-size: 0.95rem;
}

.patient-cell strong {
  color: #212529;
  font-size: 0.95rem;
}

.diagnosis-cell {
  font-size: 0.9rem;
  color: #495057;
}

.manual-diagnosis-cell {
  display: flex;
  align-items: center;
  justify-content: space-between;
}

.action-buttons {
  display: flex;
  gap: 5px;
  justify-content: center;
}

/* Pagination */
.pagination-section {
  display: flex;
  flex-direction: column;
  align-items: center;
}

.pagination .page-link {
  color: #007bff;
  border-color: #dee2e6;
  padding: 8px 12px;
}

.pagination .page-item.active .page-link {
  background-color: #007bff;
  border-color: #007bff;
}

.pagination .page-link:hover {
  color: #0056b3;
  background-color: #e9ecef;
  border-color: #dee2e6;
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
}

.modal-dialog {
  background: white;
  border-radius: 12px;
  width: 90%;
  max-width: 800px;
  max-height: 90vh;
  overflow-y: auto;
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
}

.detail-modal .modal-body {
  padding: 25px;
}

.simple-modal .modal-body {
  padding: 20px;
}

.modal-header {
  background-color: #007bff;
  color: white;
  border-radius: 12px 12px 0 0;
  padding: 15px 25px;
  border: none;
}

.modal-title {
  font-weight: 600;
  margin: 0;
}

.btn-close {
  background: transparent;
  border: none;
  color: white;
  font-size: 1.5rem;
  cursor: pointer;
  padding: 0;
  width: 24px;
  height: 24px;
  display: flex;
  align-items: center;
  justify-content: center;
}

.btn-close:hover {
  opacity: 0.8;
}

.btn-close::before {
  content: '×';
  font-size: 24px;
  line-height: 1;
}

/* Detail Modal Sections */
.detail-section {
  margin-bottom: 25px;
}

.section-title {
  color: #007bff;
  font-weight: 600;
  margin-bottom: 15px;
  padding-bottom: 8px;
  border-bottom: 2px solid #e9ecef;
}

.detail-grid {
  display: grid;
  grid-template-columns: 1fr;
  gap: 12px;
}

.detail-item {
  display: grid;
  grid-template-columns: 120px 1fr;
  gap: 15px;
  align-items: center;
  padding: 8px 0;
  border-bottom: 1px solid #f1f3f4;
}

.detail-label {
  font-weight: 600;
  color: #495057;
}

.detail-value {
  color: #212529;
}

/* Video Detail */
.video-detail-item {
  text-align: center;
}

.video-label {
  font-weight: 600;
  color: #495057;
  margin-bottom: 10px;
}

.detail-video {
  width: 100%;
  max-width: 300px;
  height: auto;
  border-radius: 8px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.diagnosis-detail {
  background-color: #f8f9fa;
  padding: 15px;
  border-radius: 8px;
  border-left: 4px solid #28a745;
  font-style: italic;
  color: #495057;
}

/* Transitions */
.fade-enter-active, .fade-leave-active {
  transition: opacity 0.3s ease;
}

.fade-enter-from, .fade-leave-to {
  opacity: 0;
}

/* Responsive */
@media (max-width: 768px) {
  .riwayat-container {
    padding: 10px;
  }
  
  .statistics-row {
    flex-direction: column;
    gap: 10px;
  }
  
  .stat-item {
    text-align: left;
  }
  
  .detail-item {
    grid-template-columns: 1fr;
    gap: 5px;
  }
  
  .manual-diagnosis-cell {
    flex-direction: column;
    align-items: flex-start;
    gap: 8px;
  }
  
  .action-buttons {
    justify-content: flex-start;
  }
  
  .modal-dialog {
    width: 95%;
    margin: 10px;
  }
}

@media (max-width: 576px) {
  .table-responsive {
    font-size: 0.875rem;
  }
  
  .table td, .table th {
    padding: 8px 6px;
  }
  
  .action-buttons {
    flex-direction: column;
    gap: 3px;
  }
  
  .btn-sm {
    padding: 4px 8px;
    font-size: 0.75rem;
  }
}

/* Button Styles */
.btn {
  border-radius: 6px;
  font-weight: 500;
  transition: all 0.3s ease;
}

.btn:hover {
  transform: translateY(-1px);
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
}

.btn-outline-primary:hover {
  background-color: #007bff;
  border-color: #007bff;
}

.btn-outline-success:hover {
  background-color: #28a745;
  border-color: #28a745;
}

.btn-outline-info:hover {
  background-color: #17a2b8;
  border-color: #17a2b8;
}

.btn-outline-secondary:hover {
  background-color: #6c757d;
  border-color: #6c757d;
}

/* Badge Styles */
.badge {
  font-size: 0.75rem;
  padding: 6px 12px;
  border-radius: 20px;
  font-weight: 500;
}

/* Form Styles */
.form-control, .form-select {
  border-radius: 6px;
  border: 1px solid #ced4da;
  transition: border-color 0.3s ease, box-shadow 0.3s ease;
}

.form-control:focus, .form-select:focus {
  border-color: #007bff;
  box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
}

.form-label {
  color: #495057;
  margin-bottom: 8px;
}
</style>