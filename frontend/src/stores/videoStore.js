import { defineStore } from "pinia";
import axios from "axios";
import api from "@/api";

// export const useVideoStore = defineStore("videoStore", {
//   state: () => ({
//     videos: [],
//     lastUpdated: "-",
//   }),
//   persist: true,
//   actions: {
//     async fetchVideos() {
//       try {
//         const token = localStorage.getItem("token") || sessionStorage.getItem("token");
//         const response = await axios.get(api.getEndpoint("videos"), {
//           headers: { Authorization: `Bearer ${token}` },
//         });

//         this.videos = response.data.map((video) => ({
//           ...video,
//           rawBlobUrl: null,
//           processedBlobUrl: null,
//           isLoading: true,
//         }));

//         for (const [index, video] of this.videos.entries()) {
//           await this.loadVideoBlob(video, index, token);
//         }

//         this.lastUpdated = new Date().toLocaleString("id-ID");
//       } catch (error) {
//         console.error("Gagal mengambil data video:", error);
//       }
//     },

//     async loadVideoBlob(video, index, token) {
//       try {
//         if (video.raw_video_stream_url) {
//           video.rawBlobUrl = await this.fetchVideoBlob(video.raw_video_stream_url, token);
//         }
//         if (video.processed_video_stream_url) {
//           video.processedBlobUrl = await this.fetchVideoBlob(video.processed_video_stream_url, token);
//         }
//       } catch (error) {
//         console.error("Error loading video blob:", error);
//       } finally {
//         this.videos[index] = { ...video, isLoading: false };
//         this.videos = [...this.videos]; // Pastikan Vue mendeteksi perubahan
//       }
//     },

//     async fetchVideoBlob(url, token) {
//       try {
//         const response = await fetch(url, {
//           headers: { Authorization: `Bearer ${token}` },
//         });
//         if (!response.ok) throw new Error("Gagal mengambil video");

//         const blob = await response.blob();
//         return URL.createObjectURL(blob);
//       } catch (error) {
//         console.error("Error fetching video stream:", error);
//         return null;
//       }
//     },
//   },
// });

export const useVideoStore = defineStore("videoStore", {
  state: () => ({
    videos: [],
    lastUpdated: "-",
  }),
  persist: true,
  actions: {
    async fetchVideos() {
      try {
        const token = localStorage.getItem("token") || sessionStorage.getItem("token");
        const response = await axios.get(api.getEndpoint("videos"), {
          headers: { Authorization: `Bearer ${token}` },
        });

        this.videos = response.data.map((video) => ({
          ...video,
          rawBlobUrl: null,
          processedBlobUrl: null,
          isLoading: true,
          controller: new AbortController(), // Tambahkan AbortController untuk membatalkan request jika perlu
        }));

        for (const [index, video] of this.videos.entries()) {
          await this.loadVideoBlob(video, index, token);
        }

        this.lastUpdated = new Date().toLocaleString("id-ID");
      } catch (error) {
        console.error("Gagal mengambil data video:", error);
      }
    },

    async fetchVideoBlob(url, token, controller) {
      try {
        const response = await fetch(url, {
          headers: {
            Authorization: `Bearer ${token}`,
            Range: "bytes=0-", // Minta video dalam bentuk streaming (penting!)
          },
          signal: controller.signal, // Gunakan AbortController untuk membatalkan jika perlu
        });

        if (!response.ok) throw new Error("Gagal mengambil video");

        const blob = await response.blob();
        return URL.createObjectURL(blob);
      } catch (error) {
        console.error("Error fetching video stream:", error);
        return null;
      }
    },

    abortVideoFetch(index) {
      // Fungsi untuk membatalkan fetch jika pengguna menavigasi atau tidak memerlukan video lagi
      if (this.videos[index]?.controller) {
        this.videos[index].controller.abort();
        this.videos[index].isLoading = false;
      }
    },
  },
});
