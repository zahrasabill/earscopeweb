<template>
  <div class="container mt-4">
    <div class="card">
      <div class="card-header bg-info text-white">
        <h4>Profil Saya</h4>
      </div>
      <div class="card-body">
        <div v-if="loading" class="text-center my-5">
          <div class="spinner-border text-info" role="status">
            <span class="visually-hidden">Loading...</span>
          </div>
          <p class="mt-2">Memuat data profil...</p>
        </div>
        <div v-else-if="error" class="alert alert-danger">
          <i class="bi bi-exclamation-triangle-fill me-2"></i>
          {{ error }}
        </div>
        <div v-else>
          <div class="row">
            <div class="col-md-6">
              <div class="card mb-4">
                <div class="card-header bg-light">
                  <h5 class="mb-0">Informasi Pribadi</h5>
                </div>
                <div class="card-body">
                  <div class="mb-3">
                    <label class="form-label fw-bold">Nama Lengkap:</label>
                    <p>{{ user.name }}</p>
                  </div>
                  <div class="mb-3" v-if="user.tanggal_lahir">
                    <label class="form-label fw-bold">Tanggal Lahir:</label>
                    <p>{{ formatDate(user.tanggal_lahir) }}</p>
                  </div>
                  <div class="mb-3" v-if="user.usia">
                    <label class="form-label fw-bold">Usia:</label>
                    <p>{{ user.usia }} tahun</p>
                  </div>
                  <div class="mb-3" v-if="user.no_telp">
                    <label class="form-label fw-bold">Nomor Telepon:</label>
                    <p>+62{{ user.no_telp }}</p>
                  </div>
                  <div class="mb-3" v-if="user.no_str">
                    <label class="form-label fw-bold">Nomor STR:</label>
                    <p>{{ user.no_str }}</p>
                  </div>
                  <div class="mb-3" v-if="user.gender">
                    <label class="form-label fw-bold">Gender:</label>
                    <p>
                      <span 
                        :class="[
                          'badge',
                          user.gender === 'laki-laki' ? 'bg-primary' : 'bg-info'
                        ]"
                      >
                        {{ capitalizeFirst(user.gender) }}
                      </span>
                    </p>
                  </div>
                </div>
              </div>
            </div>
            <div class="col-md-6">
              <div class="card mb-4">
                <div class="card-header bg-light">
                  <h5 class="mb-0">Informasi Akun</h5>
                </div>
                <div class="card-body">
                  <div class="mb-3">
                    <label class="form-label fw-bold">Kode Akses:</label>
                    <div class="input-group">
                      <input 
                        type="text" 
                        class="form-control" 
                        :value="user.kode_akses || 'Tidak tersedia'" 
                        readonly
                        ref="kodeAksesInput"
                      />
                      <button 
                        class="btn btn-outline-secondary" 
                        type="button" 
                        @click="copyToClipboard('kodeAksesInput')"
                        v-if="user.kode_akses"
                      >
                        <i class="bi bi-clipboard"></i>
                      </button>
                    </div>
                  </div>
                  <div class="mb-3" v-if="user.created_at">
                    <label class="form-label fw-bold">Terdaftar Pada:</label>
                    <p>{{ formatDateTime(user.created_at) }}</p>
                  </div>
                  <div class="mb-3" v-if="user.role">
                    <label class="form-label fw-bold">Role:</label>
                    <p>
                      <span 
                        :class="[
                          'badge',
                          getRoleBadgeClass(user.role)
                        ]"
                      >
                        {{ capitalizeFirst(user.role) }}
                      </span>
                    </p>
                  </div>
                </div>
              </div>
              <div class="card mb-4">
                <div class="card-header bg-light">
                  <h5 class="mb-0">Informasi Password</h5>
                </div>
                <div class="card-body">
                  <p>Password yang sudah dibuat tidak bisa diubah</p>
                </div>
              </div>
            </div>
          </div>
          
          <!-- Alert for restricted access -->
          <div v-if="userRole === 'pasien'" class="alert alert-warning mt-3">
            <i class="bi bi-info-circle me-2"></i>
            Sebagai pasien, Anda tidak dapat mengedit profil sendiri. Silakan hubungi dokter atau admin untuk mengubah data profil.
          </div>
          
          <div class="d-flex justify-content-end mt-4">
            <!-- Edit button - only visible for dokter and admin -->
            <router-link 
              v-if="canEditProfile" 
              to="/profile/edit" 
              class="btn btn-warning"
            >
              <i class="bi bi-pencil me-1"></i> Edit Profil
            </router-link>
            
            <!-- Disabled button for pasien with tooltip -->
            <button 
              v-else-if="userRole === 'pasien'" 
              type="button" 
              class="btn btn-secondary" 
              disabled
              title="Hanya dokter dan admin yang dapat mengedit profil pasien"
            >
              <i class="bi bi-pencil me-1"></i> Edit Profil
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import api from '@/api';
import { jwtDecode } from "jwt-decode";

export default {
  name: 'ViewProfile',
  data() {
    return {
      user: {},
      userRole: null,
      loading: true,
      error: null,
    };
  },
  computed: {
    canEditProfile() {
      // Only dokter and admin can edit profiles
      return this.userRole === 'dokter' || this.userRole === 'admin';
    }
  },
  created() {
    this.fetchProfile();
  },
  methods: {
    getAuthToken() {
      const token = localStorage.getItem("token") || sessionStorage.getItem("token");
      if (!token) throw new Error('Token autentikasi tidak ditemukan');
      return token;
    },
    getUserIdFromToken() {
      const token = this.getAuthToken();
      const payload = jwtDecode(token);
      return payload.id || payload.user_id || null;
    },
    getUserRoleFromToken() {
      try {
        const token = this.getAuthToken();
        const payload = jwtDecode(token);
        return payload.role || payload.user_role || null;
      } catch (error) {
        console.error('Error decoding token for role:', error);
        return null;
      }
    },
    async fetchProfile() {
      this.loading = true;
      this.error = null;
      try {
        const userId = this.getUserIdFromToken();
        if (!userId) throw new Error('ID user tidak ditemukan pada token');
        
        // Get user role from token
        this.userRole = this.getUserRoleFromToken();
        
        const token = this.getAuthToken();
        const response = await api.get(`users/${userId}`, {
          headers: { 'Authorization': `Bearer ${token}` }
        });
        if (response.data) {
          this.user = response.data;
          // If role is not in user data, use role from token
          if (!this.user.role && this.userRole) {
            this.user.role = this.userRole;
          }
        } else {
          throw new Error('Format response tidak sesuai');
        }
      } catch (err) {
        this.error = err.message || 'Terjadi kesalahan saat memuat data profil';
      } finally {
        this.loading = false;
      }
    },
    formatDate(dateString) {
      if (!dateString) return '-';
      try {
        const date = new Date(dateString);
        return date.toLocaleDateString('id-ID', {
          day: 'numeric', month: 'long', year: 'numeric'
        });
      } catch (error) {
        return dateString;
      }
    },
    formatDateTime(dateTimeString) {
      if (!dateTimeString) return '-';
      try {
        const date = new Date(dateTimeString);
        return date.toLocaleDateString('id-ID', {
          day: 'numeric', month: 'long', year: 'numeric',
          hour: '2-digit', minute: '2-digit'
        });
      } catch (error) {
        return dateTimeString;
      }
    },
    capitalizeFirst(string) {
      if (!string) return '';
      return string.charAt(0).toUpperCase() + string.slice(1);
    },
    getRoleBadgeClass(role) {
      switch(role) {
        case 'admin':
          return 'bg-danger';
        case 'dokter':
          return 'bg-success';
        case 'pasien':
          return 'bg-primary';
        default:
          return 'bg-secondary';
      }
    },
    copyToClipboard(refName) {
      try {
        const input = this.$refs[refName];
        input.select();
        document.execCommand('copy');
        const button = input.nextElementSibling;
        const originalHTML = button.innerHTML;
        button.innerHTML = '<i class="bi bi-check-lg"></i>';
        button.classList.add('btn-success');
        button.classList.remove('btn-outline-secondary');
        setTimeout(() => {
          button.innerHTML = originalHTML;
          button.classList.remove('btn-success');
          button.classList.add('btn-outline-secondary');
        }, 1500);
      } catch (err) {
        console.error('Error saat menyalin ke clipboard:', err);
      }
    },
  }
};
</script>

<style scoped>
.card {
  box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
  border-radius: 8px;
  border: none;
  margin-bottom: 16px;
}
.card-header {
  border-radius: 8px 8px 0 0;
}
.form-control:focus, .input-group-text {
  border-color: #86b7fe;
  box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
}
.btn {
  border-radius: 5px;
  padding: 8px 16px;
}
.badge {
  font-weight: 500;
  padding: 0.5em 0.75em;
}
.btn:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}
</style>