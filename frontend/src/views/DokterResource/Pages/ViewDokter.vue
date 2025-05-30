<template>
  <div class="container mt-4">
    <div class="card">
      <div class="card-header bg-info text-white">
        <h4>Detail Dokter</h4>
      </div>
      <div class="card-body">
        <div v-if="loading" class="text-center my-5">
          <div class="spinner-border text-info" role="status">
            <span class="visually-hidden">Loading...</span>
          </div>
          <p class="mt-2">Memuat data dokter...</p>
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
                    <p>{{ dokter.name }}</p>
                  </div>
                  <div class="mb-3">
                    <label class="form-label fw-bold">Tanggal Lahir:</label>
                    <p>{{ formatDate(dokter.tanggal_lahir) }}</p>
                  </div>
                  <div class="mb-3">
                    <label class="form-label fw-bold">Nomor Telepon:</label>
                    <p>+62{{ dokter.no_telp }}</p>
                  </div>
                  <div class="mb-3">
                    <label class="form-label fw-bold">Nomor STR:</label>
                    <p>{{ dokter.no_str !== null && dokter.no_str !== '' ? dokter.no_str: '-' }}</p>
                  </div>
                  <div class="mb-3">
                    <label class="form-label fw-bold">Gender:</label>
                    <p>
                      <span 
                        :class="[
                          'badge',
                          dokter.gender === 'laki-laki' ? 'bg-primary' : 'bg-info'
                        ]"
                      >
                        {{ capitalizeFirst(dokter.gender) }}
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
                        :value="dokter.kode_akses || 'Tidak tersedia'" 
                        readonly
                        ref="kodeAksesInput"
                      />
                      <button 
                        class="btn btn-outline-secondary" 
                        type="button" 
                        @click="copyToClipboard('kodeAksesInput')"
                        v-if="dokter.kode_akses"
                      >
                        <i class="bi bi-clipboard"></i>
                      </button>
                    </div>
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
        </div>
        
        <div class="d-flex justify-content-between mt-4">
          <button type="button" class="btn btn-secondary" @click="$router.push('/dokter')">
            <i class="bi bi-arrow-left me-1"></i> Kembali
          </button>
          
          <div v-if="dokter && dokter.id" class="d-flex gap-2">
            <button 
              class="btn btn-info text-white" 
              @click="printDokterComplete"
            >
              <i class="bi bi-printer-fill me-1"></i> Print
            </button>
            
            <router-link 
              :to="`/dokter/edit/${dokter.id}`" 
              class="btn btn-warning"
            >
              <i class="bi bi-pencil me-1"></i> Edit
            </router-link>
          </div>
        </div>
      </div>
    </div>
    
    <!-- Hidden div for printing - Doctor Info Only -->
    <div id="printAreaDokter" class="d-none">
      <div style="padding: 20px; font-family: Arial, sans-serif; max-width: 800px; margin: 0 auto;">
        <div style="text-align: center; margin-bottom: 30px; border-bottom: 2px solid #0066cc; padding-bottom: 20px;">
          <h1 style="color: #0066cc; margin-bottom: 5px;">INFORMASI DOKTER</h1>
          <p style="color: #666; margin: 0; font-size: 14px;">Tanggal Cetak: {{ getCurrentDate() }}</p>
        </div>
        
        <div style="background-color: #f8f9fa; padding: 20px; border-radius: 8px; margin-bottom: 20px;">
          <h3 style="color: #333; margin-bottom: 15px; border-bottom: 1px solid #dee2e6; padding-bottom: 10px;">Data Pribadi</h3>
          <table style="width: 100%; border-collapse: collapse;">
            <tr style="border-bottom: 1px solid #eee;">
              <td style="padding: 8px 0; font-weight: bold; width: 30%;">Nama Lengkap</td>
              <td style="padding: 8px 0;">: {{ dokter && dokter.name ? dokter.name : '-' }}</td>
            </tr>
            <tr style="border-bottom: 1px solid #eee;">
              <td style="padding: 8px 0; font-weight: bold;">Tanggal Lahir</td>
              <td style="padding: 8px 0;">: {{ dokter && dokter.tanggal_lahir ? formatDate(dokter.tanggal_lahir) : '-' }}</td>
            </tr>
            <tr style="border-bottom: 1px solid #eee;">
              <td style="padding: 8px 0; font-weight: bold;">Nomor Telepon</td>
              <td style="padding: 8px 0;">: {{ dokter && dokter.no_telp ? '+62' + dokter.no_telp : '-' }}</td>
            </tr>
            <tr style="border-bottom: 1px solid #eee;">
              <td style="padding: 8px 0; font-weight: bold;">Nomor STR</td>
              <td style="padding: 8px 0;">: {{ dokter && dokter.no_str ? dokter.no_str : '-' }}</td>
            </tr>
            <tr>
              <td style="padding: 8px 0; font-weight: bold;">Gender</td>
              <td style="padding: 8px 0;">: {{ dokter && dokter.gender ? capitalizeFirst(dokter.gender) : '-' }}</td>
            </tr>
          </table>
        </div>
        
        <div style="background-color: #e7f3ff; padding: 20px; border-radius: 8px;">
          <h3 style="color: #333; margin-bottom: 15px; border-bottom: 1px solid #b3d9ff; padding-bottom: 10px;">Informasi Akun</h3>
          <table style="width: 100%; border-collapse: collapse;">
            <tr style="border-bottom: 1px solid #b3d9ff;">
              <td style="padding: 8px 0; font-weight: bold; width: 30%;">Kode Akses</td>
              <td style="padding: 8px 0;">: {{ dokter && dokter.kode_akses ? dokter.kode_akses : '-' }}</td>
            </tr>
            <tr style="border-bottom: 1px solid #b3d9ff;" v-if="dokter.role">
              <td style="padding: 8px 0; font-weight: bold;">Role</td>
              <td style="padding: 8px 0;">: {{ capitalizeFirst(dokter.role) }}</td>
            </tr>
            <tr v-if="dokter.created_at">
              <td style="padding: 8px 0; font-weight: bold;">Terdaftar Pada</td>
              <td style="padding: 8px 0;">: {{ formatDateTime(dokter.created_at) }}</td>
            </tr>
          </table>
        </div>
        
        <!-- Footer for print -->
        <div style="position: fixed; bottom: 20px; left: 20px; font-size: 12px; color: #666;">
          EarScope@2025
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import api from '@/api';

export default {
  name: 'ViewDokter',
  data() {
    return {
      dokter: {},
      loading: true,
      error: null,
      showPassword: false,
      password: '',
    };
  },
  watch: {
    '$route.params.id': function(newId) {
      if (newId) {
        this.fetchDokter();
      } else {
        this.error = 'ID dokter tidak ditemukan pada URL';
        this.loading = false;
      }
    }
  },
  created() {
    if (this.$route.params.id) {
      this.fetchDokter();
    } else {
      this.error = 'ID dokter tidak ditemukan pada URL';
      this.loading = false;
    }
    
    // Check if viewing after creation (with password)
    if (this.$route.query && this.$route.query.password) {
      this.showPassword = true;
      this.password = this.$route.query.password;
    }
  },
  methods: {
    getAuthToken() {
      const token = localStorage.getItem("token") || sessionStorage.getItem("token");
      if (!token) {
        throw new Error('Token autentikasi tidak ditemukan');
      }
      return token;
    },
    
    async fetchDokter() {
      this.loading = true;
      this.error = null;
      
      try {
        if (!this.$route.params.id) {
          throw new Error('ID dokter tidak ditemukan pada URL');
        }
        
        const dokterId = this.$route.params.id;
        const token = this.getAuthToken();
        
        const response = await api.get(`users/${dokterId}`, {
          headers: {
            'Authorization': `Bearer ${token}`
          }
        });
        
        if (response.data) {
          this.dokter = response.data;
        } else {
          throw new Error('Format response tidak sesuai');
        }
      } catch (err) {
        console.error('Error saat memuat detail dokter:', err);
        
        if (err.response) {
          if (err.response.status === 401) {
            this.error = 'Sesi login Anda telah berakhir. Silakan login kembali.';
            setTimeout(() => {
              this.$router.push('/login');
            }, 2000);
          } else if (err.response.status === 404) {
            this.error = 'Dokter tidak ditemukan. ID dokter mungkin tidak valid.';
          } else {
            this.error = err.response.data?.message || 
                        'Terjadi kesalahan saat memuat data dokter';
          }
        } else {
          this.error = err.message || 'Terjadi kesalahan saat memuat data dokter';
        }
      } finally {
        this.loading = false;
      }
    },
    
    formatDate(dateString) {
      if (!dateString) return '-';
      
      try {
        const date = new Date(dateString);
        return date.toLocaleDateString('id-ID', {
          day: 'numeric',
          month: 'long',
          year: 'numeric'
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
          day: 'numeric',
          month: 'long',
          year: 'numeric',
          hour: '2-digit',
          minute: '2-digit'
        });
      } catch (error) {
        return dateTimeString;
      }
    },
    
    getCurrentDate() {
      const now = new Date();
      return now.toLocaleDateString('id-ID', {
        day: 'numeric',
        month: 'long',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
      });
    },
    
    capitalizeFirst(string) {
      if (!string) return '';
      return string.charAt(0).toUpperCase() + string.slice(1);
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
    
    // Method untuk print dengan nama file custom
    printWithCustomFilename(elementId, filename) {
      // Set document title untuk nama file saat print/save as PDF
      const originalTitle = document.title;
      document.title = filename;
      
      // Dapatkan konten yang akan dicetak
      const printContent = document.getElementById(elementId).innerHTML;
      const originalContent = document.body.innerHTML;
      
      // Ganti seluruh konten halaman dengan konten print
      document.body.innerHTML = printContent;
      
      // Trigger print dialog
      window.print();
      
      // Restore konten asli dan title
      document.body.innerHTML = originalContent;
      document.title = originalTitle;
      
      // Re-initialize Vue setelah restore
      this.$nextTick(() => {
        window.location.reload();
      });
    },
    
    // Method untuk button print utama (sebelah kiri edit)
    printDokterComplete() {
      const filename = `Informasi Akun Dokter - ${this.dokter.name || 'Unknown'}`;
      
      // Pilih area print yang sesuai berdasarkan ada tidaknya password
      const printAreaId = this.showPassword ? 'printAreaComplete' : 'printAreaDokter';
      this.printWithCustomFilename(printAreaId, filename);
    }
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

.gap-2 {
  gap: 0.5rem;
}

@media print {
  body * {
    visibility: hidden;
  }
  #printAreaDokter, #printAreaDokter *,
  #printAreaComplete, #printAreaComplete * {
    visibility: visible;
  }
  #printAreaDokter, #printAreaComplete {
    position: absolute;
    left: 0;
    top: 0;
    width: 100%;
  }
  
  /* Print specific styles */
  @page {
    margin: 1cm;
    size: A4;
  }
}
</style>