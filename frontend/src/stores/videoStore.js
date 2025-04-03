import { defineStore } from "pinia";
import axios from "axios";
import api from "@/api";

export const useVideoStore = defineStore("videoStore", {
  state: () => ({
    videos: [],
    lastUpdated: "-",
    filterDate: null,  // Menambahkan filter tanggal
  }),
  persist: true,
  actions: {
    async fetchVideos() {
      if (this.videos.length > 0) {
        console.log("Data video sudah ada, tidak perlu mengambil lagi.");
        return;
      }
      
      try {
        const token = localStorage.getItem("token") || sessionStorage.getItem("token");
        const response = await axios.get(api.getEndpoint("videos"), {
          headers: { Authorization: `Bearer ${token}` },
        });

        // Map data video dan download blob untuk streaming
        const fetchedVideos = await Promise.all(
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

        // Menyaring video berdasarkan filter tanggal
        if (this.filterDate) {
          const filterDate = new Date(this.filterDate);
          this.videos = fetchedVideos.filter((video) => {
            const videoDate = new Date(video.date); // Pastikan field 'date' ada pada data video
            return videoDate >= filterDate;  // Menyaring video berdasarkan tanggal
          });
        } else {
          this.videos = fetchedVideos;
        }

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

    // Menambahkan setter untuk filter tanggal
    setFilterDate(date) {
      this.filterDate = date;
    },
  },
});
