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

import { defineStore } from "pinia";
import axios from "axios";
import api from "@/api";

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
          rawBlobUrl: video.raw_video_stream_url, // 🎯 Gunakan URL streaming langsung
          processedBlobUrl: video.processed_video_stream_url,
          isLoading: false,
        }));

        this.lastUpdated = new Date().toLocaleString("id-ID");
      } catch (error) {
        console.error("Gagal mengambil data video:", error);
      }
    },
  },
});
