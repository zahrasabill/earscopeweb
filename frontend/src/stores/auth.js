// stores/auth.js
import { defineStore } from 'pinia'

export const useAuthStore = defineStore('auth', {
  state: () => ({
    token: localStorage.getItem('accessToken') || null, // ambil dari localStorage kalau ada
    user: null
  }),
  actions: {
    setToken(newToken) {
      this.token = newToken
      localStorage.setItem('accessToken', newToken) // simpan ke localStorage
    },
    clearToken() {
      this.token = null
      localStorage.removeItem('accessToken')
    },
    setUser(userData) {
      this.user = userData
    },
    logout() {
      this.clearToken()
      this.user = null
    }
  }
})
