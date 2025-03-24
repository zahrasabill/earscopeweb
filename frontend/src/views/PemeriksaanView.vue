<template>
  <admin-layout active-page="pemeriksaan">
    <div class="container-fluid pemeriksaan-container">
      <div class="row">
        <div class="col-lg-12">
          <div class="row">
            <div v-for="video in videos" :key="video.id" class="col-lg-4 col-md-6 mb-4">
              <div class="card shadow">
                <div class="card-header bg-primary text-white text-center">
                  <h5 class="mb-0">ID: {{ video.id }}</h5>
                </div>
                <div class="card-body">
                  <div class="video-container mb-3">
                    <div v-if="video.isLoading" class="text-center py-3">
                      <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                      </div>
                      <p class="mt-2">Menghubungkan ke video stream...</p>
                    </div>
                    <div v-else class="video-wrapper">
                      <div class="video-section">
                        <video v-if="video.rawBlobUrl" :src="video.rawBlobUrl" controls class="video-stream"></video>
                      </div>
                      <div class="video-section">
                        <video v-if="video.processedBlobUrl" :src="video.processedBlobUrl" controls
                          class="video-stream"></video>
                      </div>
                    </div>
                  </div>
                  <p><strong>Status:</strong> {{ video.status }}</p>
                  <p><strong>Diagnosis:</strong> {{ video.hasil_diagnosis }}</p>
                </div>
                <div class="card-footer text-center">
                  <small class="text-muted">Terakhir diperbarui: {{ new Date(video.created_at).toLocaleString() }}</small>
                  
                  <!-- Tombol Assign hanya muncul jika video belum di-assign -->
                  <div v-if="video.status !== 'assigned'">
                    <button @click="openAssignModal(video)" class="btn btn-primary" :disabled="video.status === 'assigned'">
                      Assign to User
                    </button>
                  </div>

                  <!-- Tombol Unassign hanya muncul jika status video adalah 'assigned' -->
                  <div v-if="video.status === 'assigned'">
                    <button @click="confirmUnassign(video)" class="btn btn-danger" :disabled="video.status !== 'assigned'">
                      Unassign
                    </button>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal Assign User -->
    <div v-if="showAssignModal" class="modal fade show" tabindex="-1" style="display: block;">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">Assign Video to User</h5>
            <button type="button" class="btn-close" @click="closeAssignModal"></button>
          </div>
          <div class="modal-body">
            <div>
              <label for="user">Select User:</label>
              <select v-model="selectedUserId" id="user" class="form-control">
                <option v-for="user in users" :key="user.id" :value="user.id">
                  {{ user.name }}
                </option>
              </select>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" @click="closeAssignModal">Close</button>
            <button type="button" class="btn btn-primary" @click="assignToUser" :disabled="!selectedUserId">
              Assign
            </button>
          </div>
        </div>
      </div>
    </div>
  </admin-layout>
</template>

<script>
import { ref } from 'vue';
import AdminLayout from '@/components/AdminLayout.vue';
import { useVideoStore } from '@/stores/videoStore';
import { useUserStore } from '@/stores/userStore';
import { storeToRefs } from 'pinia';
import api from '@/api'; // Pastikan api.js diimpor
import axios from 'axios';

export default {
  name: 'PemeriksaanView',
  components: {
    AdminLayout
  },
  setup() {
    const videoStore = useVideoStore();
    const userStore = useUserStore();
    const { videos, lastUpdated } = storeToRefs(videoStore);
    const { users } = storeToRefs(userStore);

    const showAssignModal = ref(false);
    const selectedUserId = ref(null);
    const videoToAssign = ref(null);

    // Ambil video dan pengguna jika data belum ada
    if (videos.value.length === 0) {
      videoStore.fetchVideos();
    }

    if (users.value.length === 0) {
      userStore.fetchUsers();
    }

    const openAssignModal = (video) => {
      videoToAssign.value = video;
      showAssignModal.value = true;
    };

    const closeAssignModal = () => {
      showAssignModal.value = false;
      selectedUserId.value = null;
    };

    const assignToUser = async () => {
      if (selectedUserId.value) {
        try {
          const videoId = videoToAssign.value.id;
          const userId = selectedUserId.value;
          const token = localStorage.getItem("token") || sessionStorage.getItem("token");

          // Assign the video to user via API
          const response = await axios.post(api.getEndpoint(`videos/${videoId}/assign/${userId}`), {}, {
            headers: { Authorization: `Bearer ${token}` },
          });

          // Update the video object to reflect the assignment status from the response
          videoToAssign.value.user = response.data.user;
          videoToAssign.value.status = 'assigned';  // Update status to 'assigned'
          
          // Fetch the latest video list to reflect the changes
          await videoStore.fetchVideos();
          closeAssignModal();
        } catch (error) {
          console.error('Failed to assign video:', error);
        }
      }
    };

    const confirmUnassign = async (video) => {
      if (confirm('Apakah Anda yakin ingin unassign video ini?')) {
        try {
          const videoId = video.id;
          const token = localStorage.getItem("token") || sessionStorage.getItem("token");

          // Unassign the video via API
          const response = await axios.patch(api.getEndpoint(`videos/${videoId}`), {}, {
            headers: { Authorization: `Bearer ${token}` },
          });

          // Update the video object to reflect the unassigned status from the response
          video.user = null;
          video.status = 'unassigned'; // Update status to 'unassigned'
          
          // Fetch the latest video list to reflect the changes
          await videoStore.fetchVideos();
        } catch (error) {
          console.error('Failed to unassign video:', error);
        }
      }
    };

    return {
      videoStore,
      userStore,
      videos,
      users,
      lastUpdated,
      showAssignModal,
      selectedUserId,
      videoToAssign,
      openAssignModal,
      closeAssignModal,
      assignToUser,
      confirmUnassign
    };
  }
};
</script>

<style scoped>
.pemeriksaan-container {
  padding: 20px;
}

.video-container {
  min-height: 200px;
  background-color: #f8f9fa;
  border-radius: 4px;
  display: flex;
  flex-direction: column;
  justify-content: center;
  align-items: center;
  padding: 10px;
}

.video-wrapper {
  display: flex;
  flex-direction: column;
  gap: 10px;
  width: 100%;
}

.video-section {
  text-align: center;
}

.video-stream {
  max-height: 160px;
  width: 100%;
  object-fit: contain;
  border: 1px solid #ccc;
  border-radius: 5px;
}

.card {
  border: none;
  transition: all 0.3s ease;
  padding: 10px;
  border-radius: 10px;
  max-width: 100%;
}

.card:hover {
  box-shadow: 0 8px 16px rgba(0, 0, 0, 0.1);
}

.card-header {
  font-weight: 500;
  text-align: center;
}

.modal-dialog-centered {
  display: flex;
  justify-content: center;
  align-items: center;
}

@media (max-width: 768px) {
  .video-wrapper {
    flex-direction: column;
  }

  .col-md-6 {
    flex: 0 0 100%;
    max-width: 100%;
  }
}
</style>
