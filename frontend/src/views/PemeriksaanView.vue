<template>
  <admin-layout active-page="pemeriksaan">
    <div class="container-fluid pemeriksaan-container">
      <div class="row">
        <div class="col-lg-12">
          <div class="row">
            <div v-for="video in videos" :key="video.id" class="col-lg-4 col-md-6 mb-4">
              <div class="card shadow">
                <div class="card-header bg-primary text-white text-center">
                  <h5 class="mb-0">Pemeriksaan Pasien - ID: {{ video.id }}</h5>
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
                        <h6 class="text-center">Video RAW</h6>
                        <video v-if="video.rawBlobUrl" :src="video.rawBlobUrl" controls class="video-stream"></video>
                      </div>
                      <div class="video-section">
                        <h6 class="text-center">Video PROCESSED</h6>
                        <video v-if="video.processedBlobUrl" :src="video.processedBlobUrl" controls
                          class="video-stream"></video>
                      </div>
                    </div>
                  </div>
                  <p><strong>Status:</strong> {{ video.status }}</p>
                  <p><strong>Diagnosis:</strong> {{ video.hasil_diagnosis }}</p>
                </div>
                <div class="card-footer text-center">
                  <small class="text-muted">Terakhir diperbarui: {{ lastUpdated }}</small>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </admin-layout>
</template>

<script>
import AdminLayout from '@/components/AdminLayout.vue';
import { useVideoStore } from '@/stores/videoStore';
import { storeToRefs } from 'pinia';

export default {
  name: 'PemeriksaanView',
  components: {
    AdminLayout
  },
  setup() {
    const videoStore = useVideoStore();
    const { videos, lastUpdated } = storeToRefs(videoStore);

    if (videos.value.length === 0) {
      videoStore.fetchVideos();
    }

    return { videoStore, videos, lastUpdated };
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