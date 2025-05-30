<template>
  <app-layout active-page="pemeriksaan">
    <div class="container-fluid pemeriksaan-container">
      <!-- Date Filter Section -->
      <div class="filter-section mb-4">
        <div class="card shadow-sm">
          <div class="card-body">
            <div class="row align-items-end">
              <div class="col-md-4">
                <label for="dateFilter" class="form-label fw-bold">Filter Berdasarkan Tanggal:</label>
                <input type="date" id="dateFilter" v-model="selectedDate" class="form-control" @change="filterByDate">
              </div>
              <div class="col-md-4">
                <button @click="resetFilter" class="btn btn-outline-secondary">
                  <i class="fas fa-sync-alt me-1"></i> Reset Filter
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Modal Assign - Simple Design -->
      <transition name="fade">
        <div v-if="showAssignModal" class="modal-overlay" @click.self="closeAssignModal">
          <div class="modal-dialog">
            <div class="modal-content simple-modal">
              <div class="modal-header">
                <h5 class="modal-title">Assign Video ke Pasien</h5>
                <button type="button" class="btn-close" @click="closeAssignModal"></button>
              </div>
              <div class="modal-body">
                <div v-if="assignSuccess" class="text-center py-3">
                  <i class="fas fa-check-circle text-success" style="font-size: 48px;"></i>
                  <h5 class="mt-3 text-success">Berhasil!</h5>
                  <p>Video telah berhasil di-assign ke pasien</p>
                </div>
                <div v-else>
                  <label for="assignUser" class="form-label">Pilih Pasien:</label>
                  <select v-model="selectedUserId" id="assignUser" class="form-select">
                    <option value="" disabled selected>-- Pilih Pasien --</option>
                    <option v-for="user in users" :key="user.id" :value="user.id">
                      {{ user.name }}
                    </option>
                  </select>
                </div>
              </div>
              <div class="modal-footer">
                <button v-if="assignSuccess" class="btn btn-primary" @click="closeAssignModal">
                  Selesai
                </button>
                <template v-else>
                  <button class="btn btn-secondary" @click="closeAssignModal">Batal</button>
                  <button class="btn btn-primary" :disabled="!selectedUserId || assignLoading" @click="assignToUser">
                    <span v-if="assignLoading" class="spinner-border spinner-border-sm me-2"></span>
                    Assign
                  </button>
                </template>
              </div>
            </div>
          </div>
        </div>
      </transition>

      <!-- Modal Unassign - Simple Design -->
      <transition name="fade">
        <div v-if="showUnassignModal" class="modal-overlay" @click.self="closeUnassignModal">
          <div class="modal-dialog">
            <div class="modal-content simple-modal">
              <div class="modal-header">
                <h5 class="modal-title">Konfirmasi Unassign</h5>
                <button type="button" class="btn-close" @click="closeUnassignModal"></button>
              </div>
              <div class="modal-body">
                <div v-if="unassignSuccess" class="text-center py-3">
                  <i class="fas fa-check-circle text-success" style="font-size: 48px;"></i>
                  <h5 class="mt-3 text-success">Berhasil!</h5>
                  <p>Video telah berhasil di-unassign</p>
                </div>
                <div v-else class="text-center">
                  <i class="fas fa-question-circle text-warning" style="font-size: 48px;"></i>
                  <h5 class="mt-3">Apakah Anda yakin?</h5>
                  <p>Video ini akan di-unassign dari pasien.</p>
                </div>
              </div>
              <div class="modal-footer">
                <button v-if="unassignSuccess" class="btn btn-primary" @click="closeUnassignModal">
                  Selesai
                </button>
                <template v-else>
                  <button class="btn btn-secondary" @click="closeUnassignModal">Batal</button>
                  <button class="btn btn-danger" :disabled="unassignLoading" @click="unassignVideo">
                    <span v-if="unassignLoading" class="spinner-border spinner-border-sm me-2"></span>
                    Unassign
                  </button>
                </template>
              </div>
            </div>
          </div>
        </div>
      </transition>

      <!-- Modal Diagnosis Manual - Simple Design -->
      <transition name="fade">
        <div v-if="showDiagnosisModal" class="modal-overlay" @click.self="closeDiagnosisModal">
          <div class="modal-dialog">
            <div class="modal-content simple-modal">
              <div class="modal-header">
                <h5 class="modal-title">Input Diagnosis Manual</h5>
                <button type="button" class="btn-close" @click="closeDiagnosisModal"></button>
              </div>
              <div class="modal-body">
                <div v-if="diagnosisSuccess" class="text-center py-3">
                  <i class="fas fa-check-circle text-success" style="font-size: 48px;"></i>
                  <h5 class="mt-3 text-success">Berhasil!</h5>
                  <p>Diagnosis manual telah berhasil disimpan</p>
                </div>
                <div v-else>
                  <label for="diagnosisText" class="form-label">Diagnosis Manual:</label>
                  <textarea 
                    v-model="diagnosisText" 
                    id="diagnosisText" 
                    class="form-control" 
                    rows="4" 
                    placeholder="Masukkan diagnosis manual (maksimal 100 kata)"
                    @input="countWords"
                  ></textarea>
                  <small class="form-text text-muted">
                    {{ wordCount }}/100 kata
                  </small>
                  <div v-if="wordCount > 100" class="text-danger small mt-1">
                    Diagnosis melebihi batas maksimal 100 kata
                  </div>
                </div>
              </div>
              <div class="modal-footer">
                <button v-if="diagnosisSuccess" class="btn btn-primary" @click="closeDiagnosisModal">
                  Selesai
                </button>
                <template v-else>
                  <button class="btn btn-secondary" @click="closeDiagnosisModal">Batal</button>
                  <button 
                    class="btn btn-primary" 
                    :disabled="!diagnosisText.trim() || wordCount > 100 || diagnosisLoading" 
                    @click="saveDiagnosis"
                  >
                    <span v-if="diagnosisLoading" class="spinner-border spinner-border-sm me-2"></span>
                    Simpan
                  </button>
                </template>
              </div>
            </div>
          </div>
        </div>
      </transition>

      <!-- Loading indicator - centered -->
      <div v-if="isLoading" class="loading-container">
        <div class="loading-content">
          <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">Memuat video...</span>
          </div>
          <p class="mt-3 fw-bold">Memuat daftar video...</p>
        </div>
      </div>

      <!-- No data message -->
      <div v-else-if="videos.length === 0" class="no-data-container">
        <div class="alert alert-info text-center" role="alert">
          <i class="fas fa-info-circle me-2"></i>
          Tidak ada video yang tersedia untuk tanggal yang dipilih.
        </div>
      </div>

      <!-- Videos grid -->
      <div v-else class="row g-4">
        <div v-for="video in videos" :key="video.id" class="col-lg-3 col-md-4 col-sm-6 mb-4">
          <div class="card shadow h-100">
            <div class="card-header bg-gradient text-white text-center py-2">
              <span class="badge float-end" :class="video.status === 'assigned' ? 'bg-success' : 'bg-warning'">
                {{ video.status === 'assigned' ? 'Assigned' : 'Unassigned' }}
              </span>
            </div>
            <div class="card-body p-3">
              <div class="video-container mb-3">
                <!-- Videos stacked vertically with labels -->
                <div class="video-row">
                  <div class="video-item">
                    <p class="text-center video-label">Raw Video</p>
                    <!-- Raw Video -->
                    <div v-if="video.raw_video_stream_url && !video.isRawLoaded" class="text-center p-2">
                      <div class="spinner-border spinner-border-sm text-primary" role="status">
                        <span class="visually-hidden">Memuat video...</span>
                      </div>
                    </div>
                    <video v-else-if="video.rawVideoUrl" :src="video.rawVideoUrl" controls class="video-stream mb-3"></video>
                    <p v-else class="text-center text-muted small">Video tidak tersedia</p>
                  </div>

                  <div class="video-item">
                    <p class="text-center video-label">Processed Video</p>
                    <!-- Processed Video -->
                    <div v-if="video.processed_video_stream_url && !video.isProcessedLoaded" class="text-center p-2">
                      <div class="spinner-border spinner-border-sm text-primary" role="status">
                        <span class="visually-hidden">Memuat video...</span>
                      </div>
                    </div>
                    <video v-else-if="video.processedVideoUrl" :src="video.processedVideoUrl" controls class="video-stream mb-2"></video>
                    <p v-else class="text-center text-muted small">Video tidak tersedia</p>
                  </div>
                </div>
              </div>
              <div class="video-info">
                <p class="mb-2"><strong>Pasien:</strong> {{ video.user ? video.user.name : 'Belum di-assign' }}</p>
                <p class="mb-2"><strong>Diagnosis:</strong> {{ video.hasil_diagnosis || 'Belum ada diagnosis' }}</p>
                <div class="mb-2">
                  <strong>Diagnosis Manual:</strong>
                  <div v-if="video.keterangan" class="diagnosis-text">
                    {{ video.keterangan }}
                  </div>
                  <div v-else class="text-muted small">
                    Belum ada diagnosis manual
                  </div>
                </div>
              </div>
            </div>
            <div class="card-footer bg-light">
              <small class="text-muted d-block mb-2">Diupload pada: {{ formatDate(video.created_at) }}</small>
              <div class="d-grid gap-2" v-if="userRole === 'dokter'">
                <button v-if="video.status !== 'assigned'" @click="openAssignModal(video)"
                  class="btn btn-primary btn-sm">
                  <i class="fas fa-user-plus me-1"></i> Assign ke Pasien
                </button>
                <button v-if="video.status === 'assigned'" @click="openUnassignModal(video)"
                  class="btn btn-danger btn-sm">
                  <i class="fas fa-user-minus me-1"></i> Unassign
                </button>
                <button @click="openDiagnosisModal(video)" class="btn btn-success btn-sm">
                  <i class="fas fa-edit me-1"></i> Input Diagnosis Manual
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>

    </div>
  </app-layout>
</template>

<script>
import { ref, onMounted, computed, onBeforeUnmount, watch } from 'vue';
import AppLayout from '@/components/AppLayout.vue';
import { useVideoStore } from '@/stores/videoStore';
import { useUserStore } from '@/stores/userStore';
import { storeToRefs } from 'pinia';
import api from '@/api';
import axios from 'axios';
import { jwtDecode } from "jwt-decode";

export default {
  name: 'PemeriksaanView',
  components: { AppLayout },
  setup() {
    const videoStore = useVideoStore();
    const userStore = useUserStore();
    const { videos } = storeToRefs(videoStore);
    const { users } = storeToRefs(userStore);
    
    // User role from JWT
    const userRole = ref('pasien'); // Default role
    
    // Use a computed property with getter and setter for the selectedDate
    const selectedDate = computed({
      get: () => videoStore.selectedDate,
      set: (value) => videoStore.setSelectedDate(value)
    });
    
    const showAssignModal = ref(false);
    const showUnassignModal = ref(false);
    const showDiagnosisModal = ref(false);
    const selectedUserId = ref("");
    const videoToAssign = ref(null);
    const videoToUnassign = ref(null);
    const videoToDiagnose = ref(null);
    const assignLoading = ref(false);
    const unassignLoading = ref(false);
    const diagnosisLoading = ref(false);
    const assignSuccess = ref(false);
    const unassignSuccess = ref(false);
    const diagnosisSuccess = ref(false);
    const diagnosisText = ref("");
    const wordCount = ref(0);
    const isLoading = computed(() => videoStore.isLoading);

    // Function to get the user role from JWT
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

    onMounted(async () => {
      try {
        // Set user role from JWT
        userRole.value = getUserRoleFromToken();
        
        // Load users data only once
        if (users.value.length === 0) {
          await userStore.fetchUsers();
        }
        
        // If no date is set in the store, set it to today
        if (!videoStore.selectedDate) {
          selectedDate.value = new Date().toISOString().split('T')[0];
        }
        
        // Check if we need to fetch videos or use cached data
        if (videoStore.videos.length === 0 || videoStore.currentDate !== videoStore.selectedDate) {
          await videoStore.fetchVideos(videoStore.selectedDate);
        }
        
        // Preload all videos that are available
        videos.value.forEach(video => {
          if (video.raw_video_stream_url && !video.isRawLoaded) {
            videoStore.loadVideoUrl(video.id, 'raw');
          }
          if (video.processed_video_stream_url && !video.isProcessedLoaded) {
            videoStore.loadVideoUrl(video.id, 'processed');
          }
        });
      } catch (error) {
        console.error('Error fetching data:', error);
      }
    });
    
    // Watch for changes in videos to preload them
    watch(videos, (newVideos) => {
      newVideos.forEach(video => {
        if (video.raw_video_stream_url && !video.isRawLoaded) {
          videoStore.loadVideoUrl(video.id, 'raw');
        }
        if (video.processed_video_stream_url && !video.isProcessedLoaded) {
          videoStore.loadVideoUrl(video.id, 'processed');
        }
      });
    }, { deep: true });
    
    // Clean up object URLs when component is destroyed
    onBeforeUnmount(() => {
      videoStore.clearVideoUrls();
    });

    const filterByDate = async () => {
      try {
        // Use the store action to filter by date, which will also update the store's state
        await videoStore.filterByDate(selectedDate.value);
      } catch (error) {
        console.error("Error filtering by date:", error);
      }
    };

    const resetFilter = async () => {
      await videoStore.resetDateFilter();
    };

    const formatDate = (dateString) => {
      return new Date(dateString).toLocaleString('id-ID', {
        day: 'numeric',
        month: 'long',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
      });
    };

    const openAssignModal = (video) => {
      videoToAssign.value = video;
      selectedUserId.value = "";
      assignSuccess.value = false;
      showAssignModal.value = true;
    };

    const closeAssignModal = () => {
      showAssignModal.value = false;
      setTimeout(() => {
        assignSuccess.value = false;
      }, 300);
    };

    const openUnassignModal = (video) => {
      videoToUnassign.value = video;
      unassignSuccess.value = false;
      showUnassignModal.value = true;
    };

    const closeUnassignModal = () => {
      showUnassignModal.value = false;
      setTimeout(() => {
        unassignSuccess.value = false;
      }, 300);
    };

    const openDiagnosisModal = (video) => {
      videoToDiagnose.value = video;
      diagnosisText.value = video.keterangan || "";
      countWords();
      diagnosisSuccess.value = false;
      showDiagnosisModal.value = true;
    };

    const closeDiagnosisModal = () => {
      showDiagnosisModal.value = false;
      setTimeout(() => {
        diagnosisSuccess.value = false;
        diagnosisText.value = "";
        wordCount.value = 0;
      }, 300);
    };

    const countWords = () => {
      const text = diagnosisText.value.trim();
      wordCount.value = text ? text.split(/\s+/).length : 0;
    };

    const assignToUser = async () => {
      if (!selectedUserId.value) return;
      try {
        assignLoading.value = true;
        const videoId = videoToAssign.value.id;
        const userId = selectedUserId.value;
        const token = localStorage.getItem("token") || sessionStorage.getItem("token");

        await axios.post(api.getEndpoint(`videos/${videoId}/assign/${userId}`), {}, {
          headers: { Authorization: `Bearer ${token}` },
        });

        // Refresh videos with current date filter
        await videoStore.fetchVideos(selectedDate.value, true); // Force refresh
        assignSuccess.value = true;

        // Close modal automatically after 2 seconds
        setTimeout(() => {
          if (showAssignModal.value) {
            closeAssignModal();
          }
        }, 2000);

      } catch (error) {
        console.error('Gagal assign video:', error);
        alert('Gagal assign video ke pasien. Silakan coba lagi.');
      } finally {
        assignLoading.value = false;
      }
    };

    const unassignVideo = async () => {
      try {
        unassignLoading.value = true;
        const videoId = videoToUnassign.value.id;
        const token = localStorage.getItem("token") || sessionStorage.getItem("token");

        await axios.patch(api.getEndpoint(`videos/${videoId}`), {}, {
          headers: { Authorization: `Bearer ${token}` },
        });

        // Refresh videos with current date filter
        await videoStore.fetchVideos(selectedDate.value, true); // Force refresh
        unassignSuccess.value = true;

        // Close modal automatically after 2 seconds
        setTimeout(() => {
          if (showUnassignModal.value) {
            closeUnassignModal();
          }
        }, 2000);

      } catch (error) {
        console.error('Gagal unassign video:', error);
        alert('Gagal unassign video. Silakan coba lagi.');
      } finally {
        unassignLoading.value = false;
      }
    };

    const saveDiagnosis = async () => {
      if (!diagnosisText.value.trim() || wordCount.value > 100) return;
      
      try {
        diagnosisLoading.value = true;
        const videoId = videoToDiagnose.value.id;
        const token = localStorage.getItem("token") || sessionStorage.getItem("token");

        // Update video with diagnosis manual (keterangan)
        await axios.put(api.getEndpoint(`videos/${videoId}`), {
          keterangan: diagnosisText.value.trim()
        }, {
          headers: { Authorization: `Bearer ${token}` },
        });

        // Refresh videos with current date filter
        await videoStore.fetchVideos(selectedDate.value, true); // Force refresh
        diagnosisSuccess.value = true;

        // Close modal automatically after 2 seconds
        setTimeout(() => {
          if (showDiagnosisModal.value) {
            closeDiagnosisModal();
          }
        }, 2000);

      } catch (error) {
        console.error('Gagal menyimpan diagnosis:', error);
        alert('Gagal menyimpan diagnosis manual. Silakan coba lagi.');
      } finally {
        diagnosisLoading.value = false;
      }
    };

    return {
      videos,
      users,
      showAssignModal,
      showUnassignModal,
      showDiagnosisModal,
      selectedUserId,
      videoToAssign,
      videoToUnassign,
      videoToDiagnose,
      isLoading,
      selectedDate,
      assignLoading,
      unassignLoading,
      diagnosisLoading,
      assignSuccess,
      unassignSuccess,
      diagnosisSuccess,
      diagnosisText,
      wordCount,
      openAssignModal,
      openUnassignModal,
      openDiagnosisModal,
      closeAssignModal,
      closeUnassignModal,
      closeDiagnosisModal,
      assignToUser,
      unassignVideo,
      saveDiagnosis,
      countWords,
      formatDate,
      filterByDate,
      resetFilter,
      userRole
    };
  }
};
</script>

<style scoped>
.pemeriksaan-container {
  padding: 20px;
  max-width: 1400px;
  margin: 0 auto;
}

/* Filter section */
.filter-section {
  margin-bottom: 20px;
}

.filter-section .btn {
  height: 38px;
  margin-bottom: 1px;
}

/* No data message */
.no-data-container {
  padding: 30px 0;
}

/* Loading styling - centered */
.loading-container {
  display: flex;
  justify-content: center;
  align-items: center;
  min-height: 300px;
  width: 100%;
}

.loading-content {
  text-align: center;
}

/* Simple Modal styling */
.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: rgba(0, 0, 0, 0.5);
  display: flex;
  justify-content: center;
  align-items: center;
  z-index: 1050;
}

.modal-dialog {
  width: 100%;
  max-width: 500px;
  margin: 1rem;
}

.simple-modal {
  border-radius: 8px;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
  border: none;
  background-color: #fff;
}

.simple-modal .modal-header {
  background-color: #f8f9fa;
  border-bottom: 1px solid #dee2e6;
  padding: 1rem;
}

.simple-modal .modal-body {
  padding: 1.5rem;
}

.simple-modal .modal-footer {
  background-color: #f8f9fa;
  border-top: 1px solid #dee2e6;
  padding: 1rem;
}

/* Card styling */
.card {
  border-radius: 12px;
  overflow: hidden;
  transition: all 0.3s ease;
  border: none;
  height: 100%;
}

.card:hover {
  transform: translateY(-5px);
  box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1) !important;
}

.card-header {
  background: linear-gradient(135deg, #4285f4, #34a853);
  font-weight: 500;
  border-bottom: none;
  padding: 10px;
  min-height: 40px;
}

.video-container {
  background-color: #f8f9fa;
  border-radius: 8px;
  padding: 10px;
  margin-top: 0;
}

/* Improved video layout styles */
.video-row {
  display: flex;
  flex-direction: column;
  gap: 15px;
}

.video-item {
  width: 100%;
}

.video-label {
  font-size: 0.85rem;
  font-weight: 500;
  color: #555;
  margin-bottom: 5px;
}

.video-stream {
  width: 100%;
  height: auto;
  max-height: 130px;
  border-radius: 8px;
  box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
  display: block;
  margin: 0 auto;
}

.diagnosis-text {
  background-color: #f8f9fa;
  padding: 8px;
  border-radius: 4px;
  border-left: 3px solid #28a745;
  margin-top: 4px;
  font-size: 0.9rem;
  line-height: 1.4;
}

.card-footer {
  background-color: #f8f9fa;
  border-top: none;
  padding: 12px 16px;
}

/* Button styling */
.btn {
  border-radius: 6px;
  font-weight: 500;
  transition: all 0.2s ease;
}

.btn-primary {
  background-color: #4285f4;
  border-color: #4285f4;
}

.btn-primary:hover {
  background-color: #3b78e7;
  border-color: #3b78e7;
}

.btn-danger {
  background-color: #ea4335;
  border-color: #ea4335;
}

.btn-danger:hover {
  background-color: #d33426;
  border-color: #d33426;
}

.btn-success {
  background-color: #28a745;
  border-color: #28a745;
}

.btn-success:hover {
  background-color: #218838;
  border-color: #1e7e34;
}

/* Fade transition */
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.3s;
}

.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}

@media (max-width: 768px) {
  .pemeriksaan-container {
    padding: 10px;
  }

  .filter-section .btn {
    margin-top: 10px;
    width: 100%;
  }
  
  .video-stream {
    max-height: 100px;
  }

  .modal-dialog {
    margin: 0.5rem;
  }
}
</style>