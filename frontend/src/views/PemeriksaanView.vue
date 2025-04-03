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

      <!-- Modal Assign - Clean White Design -->
      <transition name="fade">
        <div v-if="showAssignModal" class="modal-overlay" @click.self="closeAssignModal">
          <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
              <div class="modal-header">
                <h5 class="modal-title">Assign Video ke Pasien</h5>
                <button type="button" class="btn-close" @click="closeAssignModal"></button>
              </div>
              <div class="modal-body p-4">
                <div v-if="assignSuccess" class="success-animation text-center py-4">
                  <div class="checkmark-circle">
                    <div class="checkmark draw"></div>
                  </div>
                  <h4 class="mt-4 text-success">Berhasil!</h4>
                  <p>Video telah berhasil di-assign ke pasien</p>
                </div>
                <div v-else class="form-group">
                  <label for="assignUser" class="form-label fw-bold mb-3">Pilih Pasien:</label>
                  <select v-model="selectedUserId" id="assignUser" class="form-select form-select-lg">
                    <option value="" disabled selected>-- Pilih Pasien --</option>
                    <option v-for="user in users" :key="user.id" :value="user.id">
                      {{ user.name }}
                    </option>
                  </select>
                </div>
              </div>
              <div class="modal-footer">
                <button v-if="assignSuccess" class="btn btn-primary w-100" @click="closeAssignModal">
                  <i class="fas fa-check-circle me-2"></i> Selesai
                </button>
                <template v-else>
                  <button class="btn btn-outline-secondary" @click="closeAssignModal">
                    <i class="fas fa-times"></i> Batal
                  </button>
                  <button class="btn btn-primary" :disabled="!selectedUserId || assignLoading" @click="assignToUser">
                    <div v-if="assignLoading" class="spinner-border spinner-border-sm me-2" role="status">
                      <span class="visually-hidden">Loading...</span>
                    </div>
                    <i v-else class="fas fa-check me-1"></i>
                    Assign
                  </button>
                </template>
              </div>
            </div>
          </div>
        </div>
      </transition>

      <!-- Modal Unassign - Clean White Design -->
      <transition name="fade">
        <div v-if="showUnassignModal" class="modal-overlay" @click.self="closeUnassignModal">
          <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
              <div class="modal-header">
                <h5 class="modal-title">Konfirmasi Unassign</h5>
                <button type="button" class="btn-close" @click="closeUnassignModal"></button>
              </div>
              <div class="modal-body p-4">
                <div v-if="unassignSuccess" class="success-animation text-center py-4">
                  <div class="checkmark-circle">
                    <div class="checkmark draw"></div>
                  </div>
                  <h4 class="mt-4 text-success">Berhasil!</h4>
                  <p>Video telah berhasil di-unassign</p>
                </div>
                <div v-else>
                  <div class="text-center mb-4">
                    <i class="fas fa-question-circle text-warning fa-4x"></i>
                  </div>
                  <h5 class="text-center mb-3">Apakah Anda yakin?</h5>
                  <p class="text-center">Video ini akan di-unassign dari pasien. Tindakan ini tidak dapat dibatalkan.
                  </p>
                </div>
              </div>
              <div class="modal-footer">
                <button v-if="unassignSuccess" class="btn btn-primary w-100" @click="closeUnassignModal">
                  <i class="fas fa-check-circle me-2"></i> Selesai
                </button>
                <template v-else>
                  <button class="btn btn-outline-secondary" @click="closeUnassignModal">
                    <i class="fas fa-times"></i> Batal
                  </button>
                  <button class="btn btn-danger" :disabled="unassignLoading" @click="unassignVideo">
                    <div v-if="unassignLoading" class="spinner-border spinner-border-sm me-2" role="status">
                      <span class="visually-hidden">Loading...</span>
                    </div>
                    <i v-else class="fas fa-user-minus me-1"></i>
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
          Tidak ada video yang tersedia untuk tanggal yang dipilih.
        </div>
      </div>

      <!-- Videos grid -->
      <div v-else class="row g-4">
        <div v-for="video in filteredVideos" :key="video.id" class="col-lg-3 col-md-4 col-sm-6 mb-4">
          <div class="card shadow h-100">
            <div class="card-header bg-gradient text-white text-center py-2"> <!-- Mengurangi padding -->
              <h5 class="mb-0 fw-bold">Video Pemeriksaan</h5>
            </div>
            <div class="card-body">
              <div class="video-container mb-3">
                <div class="video-section mb-3">
                  <h6 class="text-primary mb-2">Video Asli</h6>
                  <video v-if="video.rawVideoUrl" :src="video.rawVideoUrl" controls class="video-stream mb-2"></video>
                  <p v-else class="text-center text-muted">Video asli tidak tersedia</p>
                </div>
                <div class="video-section">
                  <h6 class="text-primary mb-2">Video Hasil</h6>
                  <video v-if="video.processedVideoUrl" :src="video.processedVideoUrl" controls
                    class="video-stream mb-2"></video>
                  <p v-else class="text-center text-muted">Video hasil tidak tersedia</p>
                </div>
              </div>
              <div class="video-info">
                <p class="mb-2">
                  <span class="badge" :class="video.status === 'assigned' ? 'bg-success' : 'bg-warning'">
                    {{ video.status === 'assigned' ? 'Assigned' : 'Unassigned' }}
                  </span>
                </p>
                <p class="mb-2"><strong>Pasien:</strong> {{ video.user ? video.user.name : 'Belum di-assign' }}</p>
                <!-- Pindah ke bawah status -->
                <p class="mb-0"><strong>Diagnosis:</strong> {{ video.hasil_diagnosis || 'Belum ada diagnosis' }}</p>
              </div>
            </div>
            <div class="card-footer bg-light">
              <small class="text-muted d-block mb-2">Diupload pada: {{ formatDate(video.created_at) }}</small>
              <div class="d-grid">
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
  </app-layout>
</template>

<script>
import { ref, onMounted, computed } from 'vue';
import AppLayout from '@/components/AppLayout.vue';
import { useVideoStore } from '@/stores/videoStore';
import { useUserStore } from '@/stores/userStore';
import { storeToRefs } from 'pinia';
import api from '@/api';
import axios from 'axios';

export default {
  name: 'PemeriksaanView',
  components: { AppLayout },
  setup() {
    const videoStore = useVideoStore();
    const userStore = useUserStore();
    const { videos } = storeToRefs(videoStore);
    const { users } = storeToRefs(userStore);
    const showAssignModal = ref(false);
    const showUnassignModal = ref(false);
    const selectedUserId = ref("");
    const videoToAssign = ref(null);
    const videoToUnassign = ref(null);
    const isLoading = ref(true);
    const selectedDate = ref("");
    const assignLoading = ref(false);
    const unassignLoading = ref(false);
    const assignSuccess = ref(false);
    const unassignSuccess = ref(false);

    // Computed property for filtered videos
    const filteredVideos = computed(() => {
      if (!selectedDate.value) {
        return videos.value;
      }

      const filterDate = new Date(selectedDate.value);
      filterDate.setHours(0, 0, 0, 0);

      return videos.value.filter(video => {
        const videoDate = new Date(video.created_at);
        videoDate.setHours(0, 0, 0, 0);
        return videoDate.getTime() === filterDate.getTime();
      });
    });

    onMounted(async () => {
      selectedDate.value = new Date().toISOString().split('T')[0]; // Set default date to today
      if (videos.value.length === 0) {
        try {
          await videoStore.fetchVideos();
          await userStore.fetchUsers();
        } catch (error) {
          console.error('Error fetching data:', error);
        } finally {
          isLoading.value = false;
        }
      } else {
        isLoading.value = false;
      }
    });

    const filterByDate = () => {
      // In the future, you can modify this to make an API request with the selected date
      // For now, we're filtering client-side with the computed property
      console.log("Filtering by date:", selectedDate.value);
    };

    const resetFilter = () => {
      selectedDate.value = "";
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

        await videoStore.fetchVideos();
        assignSuccess.value = true;

        // Close modal automatically after 3 seconds
        setTimeout(() => {
          if (showAssignModal.value) {
            closeAssignModal();
          }
        }, 3000);

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

        await videoStore.fetchVideos();
        unassignSuccess.value = true;

        // Close modal automatically after 3 seconds
        setTimeout(() => {
          if (showUnassignModal.value) {
            closeUnassignModal();
          }
        }, 3000);

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
      showAssignModal,
      showUnassignModal,
      selectedUserId,
      videoToAssign,
      videoToUnassign,
      isLoading,
      selectedDate,
      filteredVideos,
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
      filterByDate,
      resetFilter
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

/* Modal styling - Clean white design */
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
}

.modal-content {
  border-radius: 12px;
  overflow: hidden;
  box-shadow: 0 5px 20px rgba(0, 0, 0, 0.15);
  border: none;
  background-color: #fff;
}

.modal-header {
  background-color: #fff;
  color: #333;
  padding: 15px 20px;
  border-bottom: 1px solid #eee;
}

.modal-body {
  padding: 20px;
  background-color: #fff;
}

.modal-footer {
  padding: 15px 20px;
  border-top: 1px solid #eee;
  background-color: #fff;
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
}

.video-container {
  background-color: #f8f9fa;
  border-radius: 8px;
  padding: 15px;
}

.video-section {
  text-align: center;
}

.video-stream {
  width: 100%;
  max-height: 120px;
  border-radius: 8px;
  box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
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
  transform: translateY(-2px);
}

.btn-danger {
  background-color: #ea4335;
  border-color: #ea4335;
}

.btn-danger:hover {
  background-color: #d33426;
  border-color: #d33426;
  transform: translateY(-2px);
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

/* Checkmark animation */
.success-animation {
  margin: 20px auto;
}

.checkmark-circle {
  width: 80px;
  height: 80px;
  position: relative;
  display: inline-block;
  vertical-align: top;
  margin-left: auto;
  margin-right: auto;
}

.checkmark-circle .background {
  width: 80px;
  height: 80px;
  border-radius: 50%;
  background: #34a853;
  position: absolute;
}

.checkmark {
  border-radius: 50%;
  display: block;
  stroke-width: 6;
  stroke: #34a853;
  stroke-miterlimit: 10;
  box-shadow: inset 0px 0px 0px #34a853;
  animation: fill 0.4s ease-in-out 0.4s forwards, scale 0.3s ease-in-out 0.9s both;
  position: relative;
  top: 4px;
  left: 4px;
  width: 72px;
  height: 72px;
}

.checkmark.draw:after {
  content: '';
  transform: translateX(24px) translateY(35px) rotate(45deg);
  transform-origin: 0 0;
  width: 25px;
  height: 8px;
  position: absolute;
  border-bottom: 6px solid #34a853;
  border-right: 6px solid #34a853;
  animation: check 0.6s ease 0s forwards;
}

@keyframes check {
  0% {
    height: 0;
    width: 0;
    opacity: 1;
  }

  20% {
    height: 0;
    width: 25px;
    opacity: 1;
  }

  40% {
    height: 8px;
    width: 25px;
    opacity: 1;
  }

  100% {
    height: 8px;
    width: 25px;
    opacity: 1;
  }
}

@keyframes fill {
  100% {
    box-shadow: inset 0px 0px 0px 38px #34a853;
  }
}

@keyframes scale {

  0%,
  100% {
    transform: none;
  }

  50% {
    transform: scale3d(1.1, 1.1, 1);
  }
}

@media (max-width: 768px) {
  .pemeriksaan-container {
    padding: 10px;
  }

  .video-stream {
    max-height: 100px;
  }

  .filter-section .btn {
    margin-top: 10px;
    width: 100%;
  }
}
</style>