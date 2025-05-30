<template>
  <div class="container mt-4">
    <div class="card">
      <div class="card-header bg-primary text-white">
        <h4>Tambah Pasien</h4>
      </div>
      <div class="card-body">
        <div v-if="loading" class="text-center my-3">
          <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">Loading...</span>
          </div>
        </div>
        <div v-if="error" class="alert alert-danger">
          {{ error }}
        </div>
        <form @submit.prevent="createPasien" v-if="!loading">
          <div class="mb-3">
            <label for="nama" class="form-label">Nama</label>
            <input type="text" class="form-control" id="nama" v-model.trim="pasien.nama" required
              placeholder="Masukkan nama pasien" />
          </div>
          <div class="mb-3">
            <label for="tanggalLahir" class="form-label">Tanggal Lahir</label>
            <input type="date" class="form-control" id="tanggalLahir" v-model="pasien.tanggalLahir" required 
              @change="calculateAge" />
          </div>
          <div class="mb-3">
            <label for="usia" class="form-label">Usia</label>
            <input type="number" class="form-control" id="usia" v-model="pasien.usia" readonly
              placeholder="Usia akan otomatis terisi" />
            <small class="text-muted">Usia akan otomatis dihitung berdasarkan tanggal lahir</small>
          </div>
          <div class="mb-3">
            <label for="phone" class="form-label">Nomor Telepon</label>
            <div class="input-group">
              <span class="input-group-text">+62</span>
              <input type="tel" class="form-control" id="phone" v-model.trim="pasien.phone" required
                placeholder="8xxxxxxxxxx" pattern="[0-9]+" />
            </div>
            <small class="text-muted">Format: 8xxxxxxxxxx (tanpa kode negara)</small>
          </div>
          <div class="mb-3">
            <label class="form-label">Gender</label>
            <div class="d-flex gap-4">
              <div class="form-check">
                <input class="form-check-input" type="radio" name="gender" id="laki" value="laki-laki"
                  v-model="pasien.gender" required />
                <label class="form-check-label" for="laki">Laki-laki</label>
              </div>
              <div class="form-check">
                <input class="form-check-input" type="radio" name="gender" id="perempuan" value="perempuan"
                  v-model="pasien.gender" />
                <label class="form-check-label" for="perempuan">Perempuan</label>
              </div>
            </div>
          </div>

          <div class="d-flex justify-content-between mt-4">
            <button type="button" class="btn btn-secondary" @click="$router.push('/pasien')">
              <i class="bi bi-arrow-left me-1"></i> Kembali
            </button>
            <button type="submit" class="btn btn-primary" :disabled="loading">
              <i class="bi bi-save me-1"></i> Simpan
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- Modal untuk menampilkan kode akses dan password -->
    <div class="modal fade" id="credentialsModal" tabindex="-1" aria-labelledby="credentialsModalLabel"
      aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header bg-success text-white">
            <h5 class="modal-title" id="credentialsModalLabel">Informasi Akun Pasien</h5>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close" @click="redirectToList"></button>
          </div>
          <div class="modal-body">
            <div class="alert alert-warning">
              <i class="bi bi-exclamation-triangle-fill me-2"></i>
              <strong>Penting!</strong> Silahkan catat informasi di bawah ini. Informasi ini hanya ditampilkan sekali.
            </div>
            <div class="mb-3">
              <label class="form-label fw-bold">Kode Akses:</label>
              <div class="input-group">
                <input type="text" class="form-control" readonly :value="generatedCredentials.kodeAkses"
                  ref="kodeAksesInput" />
                <button class="btn btn-outline-secondary" type="button" @click="copyToClipboard('kodeAkses')">
                  <i class="bi bi-clipboard"></i>
                </button>
              </div>
            </div>
            <div class="mb-3">
              <label class="form-label fw-bold">Password:</label>
              <div class="input-group">
                <input type="text" class="form-control" readonly :value="generatedCredentials.password"
                  ref="passwordInput" />
                <button class="btn btn-outline-secondary" type="button" @click="copyToClipboard('password')">
                  <i class="bi bi-clipboard"></i>
                </button>
              </div>
            </div>
            <div class="d-grid gap-2 mt-4">
              <button class="btn btn-primary" @click="printCredentials">
                <i class="bi bi-file-earmark-pdf me-1"></i> Cetak Informasi
              </button>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-success" data-bs-dismiss="modal" @click="redirectToList">
              <i class="bi bi-check-lg me-1"></i> Selesai
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Hidden div for PDF generation -->
    <div id="printArea" class="d-none">
      <div style="padding: 20px; font-family: Arial, sans-serif; max-width: 800px; margin: 0 auto;">
        <div style="text-align: center; margin-bottom: 30px; border-bottom: 2px solid #0066cc; padding-bottom: 20px;">
          <h1 style="color: #0066cc; margin-bottom: 5px;">INFORMASI LENGKAP PASIEN</h1>
          <p style="color: #666; margin: 0; font-size: 14px;">Tanggal Cetak: {{ getCurrentDate() }}</p>
        </div>
        
        <div style="background-color: #f8f9fa; padding: 20px; border-radius: 8px; margin-bottom: 20px;">
          <h3 style="color: #333; margin-bottom: 15px; border-bottom: 1px solid #dee2e6; padding-bottom: 10px;">Data Pribadi</h3>
          <table style="width: 100%; border-collapse: collapse;">
            <tr style="border-bottom: 1px solid #eee;">
              <td style="padding: 8px 0; font-weight: bold; width: 30%;">Nama Lengkap</td>
              <td style="padding: 8px 0;">: {{ pasien.nama || '-' }}</td>
            </tr>
            <tr style="border-bottom: 1px solid #eee;">
              <td style="padding: 8px 0; font-weight: bold;">Tanggal Lahir</td>
              <td style="padding: 8px 0;">: {{ formatDateForDisplay(pasien.tanggalLahir) || '-' }}</td>
            </tr>
            <tr style="border-bottom: 1px solid #eee;">
              <td style="padding: 8px 0; font-weight: bold;">Usia</td>
              <td style="padding: 8px 0;">: {{ pasien.usia ? pasien.usia + ' tahun' : '-' }}</td>
            </tr>
            <tr style="border-bottom: 1px solid #eee;">
              <td style="padding: 8px 0; font-weight: bold;">Nomor Telepon</td>
              <td style="padding: 8px 0;">: {{ pasien.phone ? '+62' + pasien.phone : '-' }}</td>
            </tr>
            <tr>
              <td style="padding: 8px 0; font-weight: bold;">Gender</td>
              <td style="padding: 8px 0;">: {{ capitalizeFirst(pasien.gender) || '-' }}</td>
            </tr>
          </table>
        </div>
        
        <div style="background-color: #e7f3ff; padding: 20px; border-radius: 8px; margin-bottom: 20px;">
          <h3 style="color: #333; margin-bottom: 15px; border-bottom: 1px solid #b3d9ff; padding-bottom: 10px;">Informasi Akun</h3>
          <table style="width: 100%; border-collapse: collapse;">
            <tr style="border-bottom: 1px solid #b3d9ff;">
              <td style="padding: 8px 0; font-weight: bold; width: 30%;">Kode Akses</td>
              <td style="padding: 8px 0;">: {{ generatedCredentials.kodeAkses || '-' }}</td>
            </tr>
            <tr>
              <td style="padding: 8px 0; font-weight: bold;">Role</td>
              <td style="padding: 8px 0;">: Pasien</td>
            </tr>
          </table>
        </div>
        
        <div style="border: 2px solid #dc3545; padding: 20px; border-radius: 8px; background-color: #fff5f5;">
          <h3 style="color: #dc3545; text-align: center; margin-bottom: 20px;">KREDENSIAL LOGIN</h3>
          
          <div style="background-color: white; padding: 15px; border-radius: 5px; margin-bottom: 15px;">
            <p style="margin: 5px 0;"><strong>Password:</strong> {{ generatedCredentials.password || '-' }}</p>
          </div>
          
          <div style="background-color: #ffe6e6; padding: 15px; border-radius: 5px; border-left: 4px solid #dc3545;">
            <p style="margin: 0; font-weight: bold; color: #dc3545; text-align: center;">
              ⚠️ PENTING: Informasi kredensial ini hanya ditampilkan sekali. Harap simpan dengan aman.
            </p>
          </div>
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
import { Modal } from 'bootstrap';
import api from '@/api';

export default {
  name: 'CreatePasien',
  data() {
    return {
      pasien: {
        nama: '',
        tanggalLahir: '',
        usia: '',
        phone: '',
        gender: ''
      },
      generatedCredentials: {
        kodeAkses: '',
        password: ''
      },
      modal: null,
      loading: false,
      error: null,
    };
  },
  mounted() {
    // Component siap digunakan
  },
  methods: {
    calculateAge() {
      if (!this.pasien.tanggalLahir) {
        this.pasien.usia = '';
        return;
      }

      const birthDate = new Date(this.pasien.tanggalLahir);
      const today = new Date();
      
      let age = today.getFullYear() - birthDate.getFullYear();
      const monthDiff = today.getMonth() - birthDate.getMonth();
      
      if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birthDate.getDate())) {
        age--;
      }
      
      this.pasien.usia = age >= 0 ? age : 0;
    },

    generateKodeAkses() {
      // Generate kode akses dengan format PRS- + 5 karakter random uppercase
      const chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
      let randomStr = '';
      for (let i = 0; i < 5; i++) {
        randomStr += chars.charAt(Math.floor(Math.random() * chars.length));
      }
      return `PRS-${randomStr}`;
    },

    generatePassword() {
      // Generate password dengan kombinasi huruf dan angka
      const chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
      let password = '';
      for (let i = 0; i < 8; i++) {
        password += chars.charAt(Math.floor(Math.random() * chars.length));
      }
      return password;
    },

    togglePasswordVisibility() {
      this.showPassword = !this.showPassword;
    },

    async createPasien() {
      this.loading = true;
      this.error = null;

      try {
        const token = localStorage.getItem("token") || sessionStorage.getItem("token");

        if (!token) {
          throw new Error('Token autentikasi tidak ditemukan. Silakan login terlebih dahulu.');
        }

        // Format phone number with prefix
        const phoneNumber = this.pasien.phone.startsWith('0')
          ? this.pasien.phone.substring(1)
          : this.pasien.phone;

        // Pastikan format data sesuai yang diharapkan API
        const formattedData = {
          name: this.pasien.nama,
          tanggal_lahir: this.formatDate(this.pasien.tanggalLahir),
          usia: parseInt(this.pasien.usia),
          no_telp: phoneNumber,
          gender: this.pasien.gender
        };

        console.log('Data yang dikirim ke API:', formattedData);

        const response = await api.post(`register/pasien`, formattedData, {
          headers: {
            'Authorization': `Bearer ${token}`
          }
        });

        // Simpan credentials dari response API
        if (response.data && response.data.data) {
          this.generatedCredentials.kodeAkses = response.data.data.kode_akses || '';
          this.generatedCredentials.password = response.data.data.password || '';

          console.log('Pasien berhasil dibuat');

          // Refresh list data
          localStorage.setItem('pasienDataUpdated', Date.now().toString());

          // Tampilkan modal dengan credentials
          this.showCredentialsModal();
        } else {
          throw new Error('Format response tidak sesuai');
        }
      } catch (err) {
        console.error('Error saat membuat pasien:', err);

        // Tampilkan detail error yang lebih spesifik
        if (err.response) {
          console.error('Response error data:', err.response.data);

          // Pesan error yang lebih spesifik berdasarkan response
          if (err.response.status === 400) {
            this.error = `Bad Request: ${err.response.data.message || 'Format data tidak valid'}`;
          } else if (err.response.status === 401) {
            this.error = 'Tidak memiliki akses. Silakan login kembali untuk mendapatkan token yang valid.';
            // Redirect ke halaman login setelah 2 detik
            setTimeout(() => {
              this.$router.push('/login');
            }, 2000);
          } else {
            this.error = err.response.data.message || 'Terjadi kesalahan saat membuat pasien.';
          }
        } else if (err.request) {
          this.error = 'Tidak ada respons dari server. Periksa koneksi Anda.';
        } else {
          this.error = err.message || 'Terjadi kesalahan saat membuat pasien. Silakan coba lagi.';
        }
      } finally {
        this.loading = false;
      }
    },

    formatDate(dateString) {
      if (!dateString) return '';

      // Validasi string dengan format YYYY-MM-DD
      const regex = /^\d{4}-\d{2}-\d{2}$/;
      if (!regex.test(dateString)) {
        throw new Error('Format tanggal tidak valid. Harus dalam format YYYY-MM-DD.');
      }

      return dateString;
    },

    formatDateForDisplay(dateString) {
      if (!dateString) return '';

      const date = new Date(dateString);
      return date.toLocaleDateString('id-ID', {
        day: 'numeric',
        month: 'long',
        year: 'numeric'
      });
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

    showCredentialsModal() {
      // Inisialisasi atau perbarui modal
      if (!this.modal) {
        const modalElement = document.getElementById('credentialsModal');
        this.modal = new Modal(modalElement, {
          backdrop: 'static',
          keyboard: false
        });
      }
      this.modal.show();
    },

    copyToClipboard(type) {
      try {
        const input = type === 'kodeAkses' ? this.$refs.kodeAksesInput : this.$refs.passwordInput;
        input.select();
        document.execCommand('copy');

        // Tampilkan indikator bahwa teks telah disalin
        const button = input.nextElementSibling;
        const originalHTML = button.innerHTML;
        button.innerHTML = '<i class="bi bi-check-lg"></i>';
        button.classList.add('btn-success');
        button.classList.remove('btn-outline-secondary');

        // Kembalikan tampilan button setelah 1.5 detik
        setTimeout(() => {
          button.innerHTML = originalHTML;
          button.classList.remove('btn-success');
          button.classList.add('btn-outline-secondary');
        }, 1500);
      } catch (err) {
        console.error('Error saat menyalin ke clipboard:', err);
      }
    },

    // Ganti method downloadAsPDF dengan printCredentials di CreatePasien.vue

printCredentials() {
  try {
    const filename = `Informasi Akun Pasien - ${this.pasien.nama || 'Unknown'}`;
    const printWindow = window.open('', '_blank', 'width=800,height=600');
    const printContent = document.getElementById('printArea').innerHTML;
    
    printWindow.document.write(`
      <!DOCTYPE html>
      <html>
      <head>
        <title>${filename}</title>
        <style>
          body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            background: white;
          }
          @media print {
            @page {
              margin: 1cm;
              size: A4;
            }
            body {
              -webkit-print-color-adjust: exact;
              print-color-adjust: exact;
            }
          }
          .d-none { display: block !important; }
        </style>
      </head>
      <body>
        ${printContent}
      </body>
      </html>
    `);
    
    printWindow.document.close();
    
    // Tunggu sampai konten dimuat, lalu print
    printWindow.addEventListener('load', () => {
      printWindow.focus();
      printWindow.print();
      
      // Tutup window print setelah print selesai atau dibatalkan
      printWindow.addEventListener('afterprint', () => {
        printWindow.close();
      });
      
      // Alternatif: tutup window setelah delay (jika afterprint tidak bekerja)
      setTimeout(() => {
        if (!printWindow.closed) {
          printWindow.close();
        }
      }, 1000);
    });
    
    // Fallback jika load event tidak fire
    setTimeout(() => {
      if (printWindow && !printWindow.closed) {
        printWindow.focus();
        printWindow.print();
        
        setTimeout(() => {
          if (!printWindow.closed) {
            printWindow.close();
          }
        }, 1000);
      }
    }, 500);
  } catch (err) {
    console.error('Error saat mencetak:', err);
    alert('Terjadi kesalahan saat mencetak. Silahkan coba lagi.');
  }
},
    redirectToList() {
      // Tutup modal jika masih terbuka
      if (this.modal) {
        this.modal.hide();
      }
      this.$router.push('/pasien');
    }
    }
  };
</script>

<style scoped>
.card {
  box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
  border-radius: 8px;
  border: none;
}

.card-header {
  border-radius: 8px 8px 0 0;
}

.form-control:focus,
.input-group-text {
  border-color: #86b7fe;
  box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
}

.btn {
  border-radius: 5px;
  padding: 8px 16px;
}

.btn-primary {
  background-color: #0d6efd;
  border-color: #0d6efd;
}

.btn-primary:hover {
  background-color: #0b5ed7;
  border-color: #0a58ca;
}

.modal-content {
  border-radius: 8px;
  border: none;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

.modal-header {
  border-radius: 8px 8px 0 0;
}

.form-control[readonly] {
  background-color: #f8f9fa;
}
</style>