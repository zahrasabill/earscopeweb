<template>
    <div class="container-fluid pemeriksaan-container">
      <!-- Filter Section - Enhanced -->
      <div class="filter-section mb-4">
        <div class="card shadow-sm">
          <div class="card-body">
            <div class="row align-items-end">
              <div class="col-md-3">
                <label for="dateFilter" class="form-label fw-bold">Filter Tanggal:</label>
                <input type="date" id="dateFilter" v-model="selectedDate" class="form-control" @change="filterData">
              </div>
              <div class="col-md-4" v-if="userRole === 'dokter'">
                <label for="patientFilter" class="form-label fw-bold">Filter Nama Pasien:</label>
                <select v-model="selectedPatientId" id="patientFilter" class="form-select" @change="filterData">
                  <option value="">-- Semua Pasien --</option>
                  <option v-for="user in users" :key="user.id" :value="user.id">
                    {{ user.name }}
                  </option>
                </select>
              </div>
              <div class="col-md-3">
                <button @click="resetFilter" class="btn btn-outline-secondary">
                  <i class="fas fa-sync-alt me-1"></i> Reset Filter
                </button>
              </div>
              <div class="col-md-2">
                <div class="text-muted small">
                  Total: {{ filteredVideos.length }} video
                </div>
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
      <div v-else-if="filteredVideos.length === 0" class="no-data-container">
        <div class="alert alert-info text-center" role="alert">
          <i class="fas fa-info-circle me-2"></i>
          Tidak ada video yang tersedia untuk filter yang dipilih.
        </div>
      </div>

      <!-- Videos grid -->
      <div v-else class="row g-4">
        <div v-for="video in filteredVideos" :key="video.id" class="col-lg-3 col-md-4 col-sm-6 mb-4">
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
              
              <!-- Simplified Patient Information - Only Name -->
              <div class="patient-info-section mb-3" v-if="video.user">
                <div class="patient-info-simple">
                  <h6 class="mb-2">
                    <i class="fas fa-user me-2"></i>
                    <strong>Pasien:</strong> {{ video.user.name }}
                  </h6>
                </div>
              </div>
              
              <div class="video-info">
                <p class="mb-2" v-if="!video.user">
                  <strong>Status:</strong> 
                  <span class="text-warning">Belum di-assign ke pasien</span>
                </p>
                <p class="mb-2"><strong>Diagnosis:</strong> {{ video.hasil_diagnosis || 'normal' }}</p>
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
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
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
    
    // Filter states
    const selectedPatientId = ref('');
    
    // Use a computed property with getter and setter for the selectedDate
    const selectedDate = computed({
      get: () => videoStore.selectedDate,
      set: (value) => videoStore.setSelectedDate(value)
    });
    
    // Computed property for filtered videos
    const filteredVideos = computed(() => {
      let filtered = videos.value;
      
      // Filter by patient if selected
      if (selectedPatientId.value) {
        filtered = filtered.filter(video => 
          video.user && video.user.id.toString() === selectedPatientId.value.toString()
        );
      }
      
      return filtered;
    });
    
    const showAssignModal = ref(false);
    const showUnassignModal = ref(false);
    const selectedUserId = ref("");
    const videoToAssign = ref(null);
    const videoToUnassign = ref(null);
    const assignLoading = ref(false);
    const unassignLoading = ref(false);
    const assignSuccess = ref(false);
    const unassignSuccess = ref(false);
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

    const filterData = async () => {
      try {
        // First filter by date
        await videoStore.filterByDate(selectedDate.value);
        // Patient filtering is handled by computed property
      } catch (error) {
        console.error("Error filtering data:", error);
      }
    };

    const resetFilter = async () => {
      selectedPatientId.value = '';
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

    return {
      videos,
      users,
      filteredVideos,
      selectedPatientId,
      showAssignModal,
      showUnassignModal,
      selectedUserId,
      videoToAssign,
      videoToUnassign,
      isLoading,
      selectedDate,
      assignLoading,
      unassignLoading,
      assignSuccess,
      unassignSuccess,
      openAssignModal,
      openUnassignModal,
      closeAssignModal,
      closeUnassignModal,
      assignToUser,
      unassignVideo,
      formatDate,
      filterData,
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

/* Patient Information Card Styling */
.patient-info-section {
  margin-bottom: 15px;
}

.patient-info-card {
  background: linear-gradient(135deg, #f8f9ff 0%, #e3f2fd 100%);
  border: 1px solid #e3f2fd;
  border-radius: 8px;
  padding: 12px;
  margin-bottom: 10px;
}

.patient-info-title {
  color: #1976d2;
  font-size: 0.9rem;
  font-weight: 600;
  margin-bottom: 8px;
  border-bottom: 1px solid #bbdefb;
  padding-bottom: 4px;
}

.patient-details {
  display: flex;
  flex-direction: column;
  gap: 4px;
}

.detail-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
  font-size: 0.85rem;
}

.detail-label {
  font-weight: 500;
  color: #555;
  flex-shrink: 0;
  min-width: 120px;
}

.detail-value {
  color: #333;
  text-align: right;
  font-weight: 400;
}

.badge-sm {
  font-size: 0.7rem;
  padding: 0.25em 0.5em;
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
  color: white;
  border-bottom: none;
  position: relative;
}

.card-header .badge {
  position: absolute;
  top: 50%;
  right: 1rem;
  transform: translateY(-50%);
}

.card-body {
  padding: 1rem;
}

.card-footer {
  background-color: #f8f9fa;
  border-top: 1px solid #dee2e6;
  padding: 0.75rem 1rem;
}

/* Video container styling */
.video-container {
  background: #f8f9fa;
  border-radius: 8px;
  padding: 10px;
  margin-bottom: 15px;
}

.video-row {
  display: flex;
  flex-direction: column;
  gap: 15px;
}

.video-item {
  display: flex;
  flex-direction: column;
  align-items: center;
}

.video-label {
  font-size: 0.9rem;
  font-weight: 600;
  color: #495057;
  margin-bottom: 8px;
  text-align: center;
}

.video-stream {
  width: 100%;
  max-width: 280px;
  height: 160px;
  border-radius: 8px;
  object-fit: cover;
  border: 2px solid #e9ecef;
  background-color: #000;
}

.video-stream:hover {
  border-color: #4285f4;
  transition: border-color 0.3s ease;
}

/* Video info styling */
.video-info {
  font-size: 0.9rem;
  line-height: 1.4;
}

.video-info p {
  margin-bottom: 8px;
}

.video-info strong {
  color: #495057;
  font-weight: 600;
}

.diagnosis-text {
  background-color: #f8f9fa;
  padding: 8px 12px;
  border-radius: 6px;
  border-left: 3px solid #28a745;
  margin-top: 5px;
  font-size: 0.85rem;
  line-height: 1.5;
  color: #495057;
}

/* Button styling */
.btn {
  border-radius: 6px;
  font-weight: 500;
  transition: all 0.3s ease;
}

.btn:hover {
  transform: translateY(-1px);
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.btn-primary {
  background: linear-gradient(135deg, #4285f4, #1a73e8);
  border: none;
}

.btn-primary:hover {
  background: linear-gradient(135deg, #1a73e8, #1557b0);
}

.btn-success {
  background: linear-gradient(135deg, #34a853, #137333);
  border: none;
}

.btn-success:hover {
  background: linear-gradient(135deg, #137333, #0d652d);
}

.btn-danger {
  background: linear-gradient(135deg, #ea4335, #d33b2c);
  border: none;
}

.btn-danger:hover {
  background: linear-gradient(135deg, #d33b2c, #b52d20);
}

.btn-outline-secondary {
  border-color: #6c757d;
  color: #6c757d;
}

.btn-outline-secondary:hover {
  background-color: #6c757d;
  border-color: #6c757d;
  color: #fff;
}

/* Modal transitions */
.fade-enter-active, .fade-leave-active {
  transition: opacity 0.3s ease;
}

.fade-enter-from, .fade-leave-to {
  opacity: 0;
}

/* Responsive design */
@media (max-width: 768px) {
  .pemeriksaan-container {
    padding: 10px;
  }
  
  .card-body {
    padding: 0.75rem;
  }
  
  .video-stream {
    height: 140px;
  }
  
  .filter-section .row {
    gap: 10px;
  }
  
  .filter-section .col-md-3,
  .filter-section .col-md-4 {
    margin-bottom: 10px;
  }
  
  .patient-info-card {
    padding: 10px;
  }
  
  .detail-row {
    flex-direction: column;
    align-items: flex-start;
    gap: 2px;
  }
  
  .detail-label {
    min-width: auto;
    font-size: 0.8rem;
  }
  
  .detail-value {
    text-align: left;
    font-size: 0.85rem;
  }
}

@media (max-width: 576px) {
  .video-row {
    gap: 10px;
  }
  
  .video-stream {
    height: 120px;
  }
  
  .modal-dialog {
    margin: 0.5rem;
  }
  
  .simple-modal .modal-body {
    padding: 1rem;
  }
  
  .card-header {
    padding: 0.5rem 1rem;
  }
  
  .patient-info-title {
    font-size: 0.85rem;
  }
  
  .patient-details {
    gap: 2px;
  }
}

/* Loading spinner styling */
.spinner-border {
  width: 2rem;
  height: 2rem;
}

.spinner-border-sm {
  width: 1rem;
  height: 1rem;
}

/* Form styling */
.form-control, .form-select {
  border-radius: 6px;
  border: 1px solid #ced4da;
  transition: border-color 0.3s ease, box-shadow 0.3s ease;
}

.form-control:focus, .form-select:focus {
  border-color: #4285f4;
  box-shadow: 0 0 0 0.2rem rgba(66, 133, 244, 0.25);
}

.form-label {
  font-weight: 600;
  color: #495057;
  margin-bottom: 0.5rem;
}

/* Alert styling */
.alert {
  border-radius: 8px;
  border: none;
}

.alert-info {
  background-color: #e3f2fd;
  color: #0d47a1;
}

/* Badge styling */
.badge {
  font-size: 0.75rem;
  padding: 0.35em 0.65em;
  border-radius: 0.375rem;
}

.bg-success {
  background-color: #28a745 !important;
}

.bg-warning {
  background-color: #ffc107 !important;
  color: #212529 !important;
}

.bg-primary {
  background-color: #007bff !important;
}

.bg-info {
  background-color: #17a2b8 !important;
}

/* Text colors */
.text-success {
  color: #28a745 !important;
}

.text-warning {
  color: #ffc107 !important;
}

.text-muted {
  color: #6c757d !important;
}

.text-danger {
  color: #dc3545 !important;
}

/* Utility classes */
.shadow {
  box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075) !important;
}

.shadow-sm {
  box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075) !important;
}

.fw-bold {
  font-weight: 700 !important;
}

.small {
  font-size: 0.875em;
}

/* Animation for success icons */
@keyframes checkmark {
  0% {
    transform: scale(0);
    opacity: 0;
  }
  50% {
    transform: scale(1.2);
    opacity: 1;
  }
  100% {
    transform: scale(1);
    opacity: 1;
  }
}

.fas.fa-check-circle {
  animation: checkmark 0.6s ease-in-out;
}

/* Hover effects for cards */
.card-body:hover .patient-info-card {
  background: linear-gradient(135deg, #f0f7ff 0%, #e1f5fe 100%);
  transition: background 0.3s ease;
}

/* Focus states for accessibility */
.btn:focus {
  outline: 2px solid #4285f4;
  outline-offset: 2px;
}

.form-control:focus,
.form-select:focus {
  outline: none;
}

/* Custom scrollbar for diagnosis text */
.diagnosis-text {
  max-height: 100px;
  overflow-y: auto;
}

.diagnosis-text::-webkit-scrollbar {
  width: 4px;
}

.diagnosis-text::-webkit-scrollbar-track {
  background: #f1f1f1;
  border-radius: 4px;
}

.diagnosis-text::-webkit-scrollbar-thumb {
  background: #c1c1c1;
  border-radius: 4px;
}

.diagnosis-text::-webkit-scrollbar-thumb:hover {
  background: #a8a8a8;
}
</style>