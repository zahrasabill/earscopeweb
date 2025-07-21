<template>
  <div class="container mt-4">
    <div class="card">
      <div class="card-header bg-warning text-dark">
        <h4>Edit Profil</h4>
      </div>
      <div class="card-body">
        <div v-if="loading" class="text-center my-5">
          <div class="spinner-border text-warning" role="status">
            <span class="visually-hidden">Loading...</span>
          </div>
          <p class="mt-2">Memuat data profil...</p>
        </div>
        <div v-else-if="error" class="alert alert-danger">
          <i class="bi bi-exclamation-triangle-fill me-2"></i>
          {{ error }}
        </div>
        <form v-else @submit.prevent="submitProfile">
          <div class="row">
            <div class="col-md-6">
              <div class="card mb-4">
                <div class="card-header bg-light">
                  <h5 class="mb-0">Informasi Pribadi</h5>
                </div>
                <div class="card-body">
                  <div class="mb-3">
                    <label class="form-label fw-bold">Nama Lengkap:</label>
                    <input v-model="form.name" type="text" class="form-control" required />
                  </div>
                  <div class="mb-3">
                    <label class="form-label fw-bold">Tanggal Lahir:</label>
                    <input v-model="form.tanggal_lahir" type="date" class="form-control" />
                  </div>
                  <div class="mb-3">
                    <label class="form-label fw-bold">Nomor Telepon:</label>
                    <input v-model="form.no_telp" type="text" class="form-control" placeholder="8xxxxxxxxxx" />
                  </div>
                  <div class="mb-3" v-if="form.no_str !== undefined">
                    <label class="form-label fw-bold">Nomor STR:</label>
                    <input v-model="form.no_str" type="text" class="form-control" />
                  </div>
                  <div class="mb-3">
                    <label class="form-label fw-bold">Gender:</label>
                    <select v-model="form.gender" class="form-select">
                      <option value="">Pilih Gender</option>
                      <option value="laki-laki">Laki-laki</option>
                      <option value="perempuan">Perempuan</option>
                    </select>
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
                    <input type="text" class="form-control" :value="form.kode_akses || 'Tidak tersedia'" readonly />
                  </div>
                  <div class="mb-3" v-if="form.created_at">
                    <label class="form-label fw-bold">Terdaftar Pada:</label>
                    <input type="text" class="form-control" :value="formatDateTime(form.created_at)" readonly />
                  </div>
                </div>
              </div>
            </div>
          </div>
          <div class="d-flex justify-content-end mt-4 gap-2">
            <router-link to="/profile" class="btn btn-secondary">
              <i class="bi bi-arrow-left me-1"></i> Batal
            </router-link>
            <button type="submit" class="btn btn-warning" :disabled="saving">
              <span v-if="saving" class="spinner-border spinner-border-sm me-2"></span>
              Simpan Perubahan
            </button>
          </div>
          <div v-if="success" class="alert alert-success mt-3">
            <i class="bi bi-check-circle me-2"></i> Profil berhasil diperbarui!
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script>
import api from '@/api';
import { jwtDecode } from "jwt-decode";

export default {
  name: 'EditProfile',
  data() {
    return {
      form: {
        name: '',
        tanggal_lahir: '',
        no_telp: '',
        no_str: undefined,
        gender: '',
        kode_akses: '',
        role: '',
        created_at: ''
      },
      loading: true,
      saving: false,
      error: null,
      success: false,
    };
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
    async fetchProfile() {
      this.loading = true;
      this.error = null;
      try {
        const userId = this.getUserIdFromToken();
        if (!userId) throw new Error('ID user tidak ditemukan pada token');
        const token = this.getAuthToken();
        const response = await api.get(`users/${userId}`, {
          headers: { 'Authorization': `Bearer ${token}` }
        });
        if (response.data) {
          // Copy only editable fields
          this.form = {
            name: response.data.name || '',
            tanggal_lahir: response.data.tanggal_lahir || '',
            no_telp: response.data.no_telp || '',
            no_str: response.data.no_str !== undefined ? response.data.no_str : undefined,
            gender: response.data.gender || '',
            kode_akses: response.data.kode_akses || '',
            role: response.data.role || '',
            created_at: response.data.created_at || ''
          };
        } else {
          throw new Error('Format response tidak sesuai');
        }
      } catch (err) {
        this.error = err.message || 'Terjadi kesalahan saat memuat data profil';
      } finally {
        this.loading = false;
      }
    },
    async submitProfile() {
      this.saving = true;
      this.error = null;
      this.success = false;
      try {
        const userId = this.getUserIdFromToken();
        const token = this.getAuthToken();
        // Kirim hanya field yang boleh diubah
        const payload = {
          name: this.form.name,
          tanggal_lahir: this.form.tanggal_lahir,
          no_telp: this.form.no_telp,
          gender: this.form.gender,
        };
        if (this.form.no_str !== undefined) payload.no_str = this.form.no_str;
        await api.put(`users/${userId}`, payload, {
          headers: { 'Authorization': `Bearer ${token}` }
        });
        this.success = true;
        setTimeout(() => {
          this.$router.push('/profile');
        }, 1200);
      } catch (err) {
        this.error = err.response?.data?.message || err.message || 'Gagal memperbarui profil';
      } finally {
        this.saving = false;
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
</style>