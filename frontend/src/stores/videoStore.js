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

        // Map data video dan download blob untuk streaming
        this.videos = await Promise.all(
          response.data.map(async (video) => ({
            ...video,
            rawVideoUrl: video.raw_video_stream_url
              ? await this.fetchVideoBlob(video.raw_video_stream_url, token)
              : null,
            processedVideoUrl: video.processed_video_stream_url
              ? await this.fetchVideoBlob(video.processed_video_stream_url, token)
              : null,
            isLoading: false,
          }))
        );

        this.lastUpdated = new Date().toLocaleString("id-ID");
      } catch (error) {
        console.error("Gagal mengambil data video:", error);
      }
    },

    async fetchVideoBlob(url, token) {
      try {
        const response = await fetch(url, {
          headers: { Authorization: `Bearer ${token}` },
        });
        if (!response.ok) throw new Error("Gagal mengambil video");

        const blob = await response.blob();
        return URL.createObjectURL(blob); // Buat URL lokal dari blob
      } catch (error) {
        console.error("Error fetching video stream:", error);
        return null;
      }
    },
  },
});
