<template>
  <admin-layout active-page="pemeriksaan">
  <div class="container-fluid pemeriksaan-container">
    <div class="row">
    </div>

    <div class="row">
      <!-- Video Streaming Panel -->
      <div class="col-lg-8 mb-4">
        <div class="card shadow">
          <div class="card-header bg-primary text-white">
            <h5 class="mb-0">Video Realtime Pasien</h5>
          </div>
          <div class="card-body">
            <div class="video-container">
              <div v-if="isStreamLoading" class="text-center py-5">
                <div class="spinner-border text-primary" role="status">
                  <span class="visually-hidden">Loading...</span>
                </div>
                <p class="mt-2">Menghubungkan ke video stream...</p>
              </div>
              <img v-else-if="streamUrl" :src="streamUrl" class="img-fluid video-stream" alt="Video stream pasien">
              <div v-else class="alert alert-warning">
                <i class="bi bi-exclamation-triangle-fill me-2"></i> Video stream tidak tersedia
              </div>
            </div>
            <div class="stream-controls mt-3">
              <button class="btn btn-primary me-2" @click="refreshStream">
                <i class="bi bi-arrow-clockwise me-1"></i> Refresh Stream
              </button>
              <button class="btn btn-outline-secondary" @click="captureSnapshot">
                <i class="bi bi-camera me-1"></i> Ambil Snapshot
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- Informasi Pasien -->
      <div class="col-lg-4 mb-4">
        <div class="card shadow">
          <div class="card-header bg-info text-white">
            <h5 class="mb-0">Informasi Pasien</h5>
          </div>
          <div class="card-body">
            <div v-if="pasien">
              <div class="mb-3">
                <label class="fw-bold">ID Pasien:</label>
                <span class="ms-2">{{ pasien.id }}</span>
              </div>
              <div class="mb-3">
                <label class="fw-bold">Nama:</label>
                <span class="ms-2">{{ pasien.nama }}</span>
              </div>
              <div class="mb-3">
                <label class="fw-bold">Umur:</label>
                <span class="ms-2">{{ pasien.umur }} tahun</span>
              </div>
              <div class="mb-3">
                <label class="fw-bold">Status Pemeriksaan:</label>
                <span class="ms-2 badge" :class="getStatusBadgeClass(pasien.status)">
                  {{ pasien.status }}
                </span>
              </div>
            </div>
            <div v-else class="alert alert-info">
              Pilih pasien untuk melihat detailnya
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Hasil Analisis dan Form Deskripsi -->
    <div class="row">
      <div class="col-lg-6 mb-4">
        <div class="card shadow">
          <div class="card-header bg-success text-white">
            <h5 class="mb-0">Hasil Deep Learning</h5>
          </div>
          <div class="card-body">
            <div v-if="isLoadingDeepLearning" class="text-center py-3">
              <div class="spinner-border text-success" role="status">
                <span class="visually-hidden">Loading...</span>
              </div>
              <p class="mt-2">Memproses data...</p>
            </div>
            <div v-else-if="hasilDeepLearning">
              <div class="result-item mb-3">
                <h6>Diagnosis:</h6>
                <p class="alert alert-light">{{ hasilDeepLearning.diagnosis }}</p>
              </div>
              <div class="result-item mb-3">
                <h6>Tingkat Kepercayaan:</h6>
                <div class="progress">
                  <div 
                    class="progress-bar" 
                    :class="getConfidenceColorClass(hasilDeepLearning.confidence)"
                    role="progressbar" 
                    :style="{width: hasilDeepLearning.confidence + '%'}" 
                    :aria-valuenow="hasilDeepLearning.confidence" 
                    aria-valuemin="0" 
                    aria-valuemax="100">
                    {{ hasilDeepLearning.confidence }}%
                  </div>
                </div>
              </div>
              <div class="result-item">
                <h6>Detail Temuan:</h6>
                <ul class="list-group">
                  <li v-for="(temuan, index) in hasilDeepLearning.details" :key="index" class="list-group-item">
                    {{ temuan }}
                  </li>
                </ul>
              </div>
            </div>
            <div v-else class="alert alert-secondary">
              Hasil deep learning akan muncul setelah video diproses
            </div>
          </div>
          <div class="card-footer">
            <small class="text-muted">Terakhir diperbarui: {{ lastUpdated }}</small>
          </div>
        </div>
      </div>

      <div class="col-lg-6 mb-4">
        <div class="card shadow">
          <div class="card-header bg-warning text-dark">
            <h5 class="mb-0">Deskripsi Manual</h5>
          </div>
          <div class="card-body">
            <form @submit.prevent="saveAnalysis">
              <div class="mb-3">
                <label for="diagnosisManual" class="form-label">Diagnosis:</label>
                <input 
                  type="text" 
                  class="form-control" 
                  id="diagnosisManual" 
                  v-model="manualAnalysis.diagnosis" 
                  placeholder="Masukkan diagnosis"
                >
              </div>
              <div class="mb-3">
                <label for="descriptionManual" class="form-label">Deskripsi Rinci:</label>
                <textarea 
                  class="form-control" 
                  id="descriptionManual" 
                  v-model="manualAnalysis.description" 
                  rows="5" 
                  placeholder="Masukkan deskripsi rinci hasil pemeriksaan"
                ></textarea>
              </div>
              <div class="mb-3">
                <label for="rekomendasi" class="form-label">Rekomendasi:</label>
                <textarea 
                  class="form-control" 
                  id="rekomendasi" 
                  v-model="manualAnalysis.recommendation" 
                  rows="3" 
                  placeholder="Masukkan rekomendasi untuk pasien"
                ></textarea>
              </div>
              <div class="form-check mb-3">
                <input 
                  class="form-check-input" 
                  type="checkbox" 
                  id="useDeepLearning" 
                  v-model="manualAnalysis.includeDeepLearning"
                >
                <label class="form-check-label" for="useDeepLearning">
                  Sertakan hasil deep learning dalam laporan
                </label>
              </div>
              <div class="d-flex justify-content-between">
                <button type="button" class="btn btn-outline-secondary" @click="resetForm">
                  <i class="bi bi-x-circle me-1"></i> Reset
                </button>
                <button type="submit" class="btn btn-primary" :disabled="isSaving">
                  <span v-if="isSaving">
                    <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                    Menyimpan...
                  </span>
                  <span v-else>
                    <i class="bi bi-send me-1"></i> Kirim ke Pasien
                  </span>
                </button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>

    <!-- Riwayat Pemeriksaan -->
    <div class="row">
      <div class="col-12">
        <div class="card shadow">
          <div class="card-header bg-secondary text-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Riwayat Pemeriksaan</h5>
            <div class="input-group" style="max-width: 300px;">
              <input 
                type="text" 
                class="form-control" 
                placeholder="Cari riwayat..." 
                v-model="searchQuery"
              >
              <button class="btn btn-outline-light" type="button">
                <i class="bi bi-search"></i>
              </button>
            </div>
          </div>
          <div class="card-body">
            <div class="table-responsive">
              <table class="table table-hover">
                <thead>
                  <tr>
                    <th>ID</th>
                    <th>Tanggal</th>
                    <th>Pasien</th>
                    <th>Diagnosis</th>
                    <th>Status</th>
                    <th>Aksi</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="(record, index) in filteredRecords" :key="index">
                    <td>{{ record.id }}</td>
                    <td>{{ record.tanggal }}</td>
                    <td>{{ record.namaPasien }}</td>
                    <td>{{ record.diagnosis }}</td>
                    <td>
                      <span class="badge" :class="getStatusBadgeClass(record.status)">
                        {{ record.status }}
                      </span>
                    </td>
                    <td>
                      <div class="btn-group btn-group-sm">
                        <button class="btn btn-outline-primary" @click="viewRecord(record.id)">
                          <i class="bi bi-eye"></i>
                        </button>
                        <button class="btn btn-outline-warning" @click="editRecord(record.id)">
                          <i class="bi bi-pencil"></i>
                        </button>
                        <button class="btn btn-outline-success" @click="sendToPatient(record.id)">
                          <i class="bi bi-envelope"></i>
                        </button>
                      </div>
                    </td>
                  </tr>
                  <tr v-if="filteredRecords.length === 0">
                    <td colspan="6" class="text-center py-3">Tidak ada data pemeriksaan yang ditemukan</td>
                  </tr>
                </tbody>
              </table>
            </div>
            <nav v-if="totalPages > 1">
              <ul class="pagination justify-content-center">
                <li class="page-item" :class="{ disabled: currentPage === 1 }">
                  <a class="page-link" href="#" @click.prevent="changePage(currentPage - 1)">
                    <i class="bi bi-chevron-left"></i>
                  </a>
                </li>
                <li 
                  v-for="page in paginationItems" 
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
    </div>
  </div>
  </admin-layout>
</template>

<script>
import AdminLayout from '@/components/AdminLayout.vue';
import { Modal } from 'bootstrap';

export default {
  name: 'PemeriksaanView',
  components: {
    AdminLayout
  },
  data() {
    return {
      // Stream data
      streamUrl: null,
      isStreamLoading: true,
      
      // Pasien data
      pasien: null,
      
      // Deep learning results
      hasilDeepLearning: null,
      isLoadingDeepLearning: false,
      lastUpdated: '-',
      
      // Manual analysis form
      manualAnalysis: {
        diagnosis: '',
        description: '',
        recommendation: '',
        includeDeepLearning: true
      },
      isSaving: false,
      
      // Riwayat pemeriksaan
      pemeriksaanRecords: [],
      currentPage: 1,
      itemsPerPage: 10,
      totalItems: 0,
      searchQuery: '',
      
      // Sample data (untuk demo)
      samplePasien: {
        id: 'P12345',
        nama: 'Ahmad Sutanto',
        umur: 45,
        status: 'Dalam Pemeriksaan'
      },
      sampleDeepLearning: {
        diagnosis: 'Pneumonia Bakterial',
        confidence: 87.5,
        details: [
          'Infiltrat parenkim paru intensitas sedang hingga tinggi',
          'Konsolidasi di lobus kanan bawah',
          'Tidak ada tanda efusi pleura',
          'Tidak ada bukti kardiomegali'
        ]
      },
      sampleRecords: [
        { id: 'RM001', tanggal: '2025-03-09', namaPasien: 'Budi Santoso', diagnosis: 'Bronkitis Akut', status: 'Selesai' },
        { id: 'RM002', tanggal: '2025-03-09', namaPasien: 'Siti Nurhayati', diagnosis: 'Pneumonia', status: 'Dalam Pemeriksaan' },
        { id: 'RM003', tanggal: '2025-03-08', namaPasien: 'Dedi Kurniawan', diagnosis: 'Normal', status: 'Selesai' },
        { id: 'RM004', tanggal: '2025-03-08', namaPasien: 'Rina Anggraini', diagnosis: 'Tuberkulosis', status: 'Menunggu Konfirmasi' },
        { id: 'RM005', tanggal: '2025-03-07', namaPasien: 'Joko Widodo', diagnosis: 'Normal', status: 'Selesai' }
      ]
    };
  },
  computed: {
    filteredRecords() {
      if (!this.searchQuery) {
        return this.pemeriksaanRecords;
      }
      
      const query = this.searchQuery.toLowerCase();
      return this.pemeriksaanRecords.filter(record => {
        return record.id.toLowerCase().includes(query) ||
               record.namaPasien.toLowerCase().includes(query) ||
               record.diagnosis.toLowerCase().includes(query);
      });
    },
    totalPages() {
      return Math.ceil(this.totalItems / this.itemsPerPage);
    },
    paginationItems() {
      const items = [];
      const maxVisiblePages = 5;
      
      if (this.totalPages <= maxVisiblePages) {
        for (let i = 1; i <= this.totalPages; i++) {
          items.push(i);
        }
      } else {
        if (this.currentPage <= Math.ceil(maxVisiblePages / 2)) {
          for (let i = 1; i <= maxVisiblePages; i++) {
            items.push(i);
          }
        } else if (this.currentPage > this.totalPages - Math.floor(maxVisiblePages / 2)) {
          for (let i = this.totalPages - maxVisiblePages + 1; i <= this.totalPages; i++) {
            items.push(i);
          }
        } else {
          const offset = Math.floor(maxVisiblePages / 2);
          for (let i = this.currentPage - offset; i <= this.currentPage + offset; i++) {
            items.push(i);
          }
        }
      }
      
      return items;
    }
  },
  methods: {
    // Stream handling
    refreshStream() {
      this.isStreamLoading = true;
      // Simulasi penundaan untuk mendapatkan stream baru
      setTimeout(() => {
        this.streamUrl = `http://localhost:8080/api/stream?timestamp=${new Date().getTime()}`;
        this.isStreamLoading = false;
      }, 1500);
    },
    
    captureSnapshot() {
      // Implementasi untuk mengambil snapshot dari video stream
      this.$toast.info('Snapshot berhasil diambil dan disimpan', {
        position: 'top-right',
        timeout: 3000
      });
    },
    
    // Deep learning results
    fetchDeepLearningResults() {
      this.isLoadingDeepLearning = true;
      // Simulasi penundaan untuk mendapatkan hasil deep learning
      setTimeout(() => {
        this.hasilDeepLearning = this.sampleDeepLearning;
        this.lastUpdated = new Date().toLocaleString('id-ID');
        this.isLoadingDeepLearning = false;
      }, 2000);
    },
    
    // Form handling
    saveAnalysis() {
      this.isSaving = true;
      
      // Simulasi mengirim data ke server
      setTimeout(() => {
        // Simulasi sukses
        this.$toast.success('Hasil analisis berhasil dikirim ke pasien', {
          position: 'top-right',
          timeout: 3000
        });
        
        // Reset form setelah berhasil
        this.resetForm();
        this.isSaving = false;
      }, 2000);
    },
    
    resetForm() {
      this.manualAnalysis = {
        diagnosis: '',
        description: '',
        recommendation: '',
        includeDeepLearning: true
      };
    },
    
    // Record actions
    viewRecord(id) {
      // Implementasi untuk melihat detail rekam medis
      console.log(`Melihat rekam medis: ${id}`);
    },
    
    editRecord(id) {
      // Implementasi untuk mengedit rekam medis
      console.log(`Mengedit rekam medis: ${id}`);
    },
    
    sendToPatient(id) {
      // Implementasi untuk mengirim hasil ke pasien
      this.$toast.success(`Hasil telah dikirim ke pasien dengan ID rekam medis: ${id}`, {
        position: 'top-right',
        timeout: 3000
      });
    },
    
    // Pagination
    changePage(page) {
      if (page >= 1 && page <= this.totalPages) {
        this.currentPage = page;
        this.fetchRecords();
      }
    },
    
    fetchRecords() {
      // Simulasi mendapatkan data dari server
      // Dalam implementasi nyata, ini akan berupa panggilan API
      this.pemeriksaanRecords = this.sampleRecords;
      this.totalItems = this.sampleRecords.length;
    },
    
    // Styling helpers
    getStatusBadgeClass(status) {
      switch (status.toLowerCase()) {
        case 'selesai':
          return 'bg-success';
        case 'dalam pemeriksaan':
          return 'bg-primary';
        case 'menunggu konfirmasi':
          return 'bg-warning text-dark';
        default:
          return 'bg-secondary';
      }
    },
    
    getConfidenceColorClass(confidence) {
      if (confidence >= 80) {
        return 'bg-success';
      } else if (confidence >= 60) {
        return 'bg-info';
      } else if (confidence >= 40) {
        return 'bg-warning';
      } else {
        return 'bg-danger';
      }
    }
  },
  mounted() {
    // Load initial data
    this.refreshStream();
    this.fetchDeepLearningResults();
    this.fetchRecords();
    
    // Set sample pasien
    this.pasien = this.samplePasien;
  }
};
</script>

<style scoped>
.pemeriksaan-container {
  padding: 20px;
}

.video-container {
  min-height: 300px;
  background-color: #f8f9fa;
  border-radius: 4px;
  display: flex;
  justify-content: center;
  align-items: center;
}

.video-stream {
  max-height: 400px;
  width: 100%;
  object-fit: contain;
}

.card {
  border: none;
  transition: all 0.3s ease;
}

.card:hover {
  box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
}

.card-header {
  font-weight: 500;
}

.stream-controls {
  display: flex;
  justify-content: flex-start;
}

.badge {
  padding: 8px 12px;
  border-radius: 30px;
  font-weight: 500;
}

.pagination {
  margin-top: 1rem;
  margin-bottom: 0;
}

/* Responsive adjustments */
@media (max-width: 768px) {
  .stream-controls {
    flex-direction: column;
  }
  
  .stream-controls button {
    margin-bottom: 10px;
    width: 100%;
  }
}
</style>