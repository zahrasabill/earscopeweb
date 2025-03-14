<template>
  <div class="register-container">
    <div class="left-panel">
      <div class="logo-container">
        <img :src="logo" alt="OIDO" class="logo" />
      </div>
    </div>
    <div class="right-panel">
      <div class="register-form-container">
        <h2 class="welcome-title">Daftar Akun</h2>
        <p class="instruction-text">
          Silahkan isi data berikut untuk membuat akun Anda!
        </p>
        <div v-if="error" class="alert alert-danger">
          {{ error }}
        </div>
        <div v-if="loading" class="alert alert-info">
          Sedang memproses pendaftaran...
        </div>
        <form @submit.prevent="register">
          <div class="form-group mb-3">
            <label for="name" class="form-label">Nama</label>
            <div class="input-wrapper">
              <input
                type="text"
                v-model="name"
                class="form-control"
                id="name"
                placeholder="Masukkan Nama Anda"
                required
              />
            </div>
          </div>
          <div class="form-group mb-4">
            <label for="birthDate" class="form-label">Tanggal Lahir</label>
            <div class="input-wrapper">
              <input
                type="date"
                v-model="birthDate"
                class="form-control"
                id="birthDate"
                required
              />
            </div>
          </div>
          <button type="submit" class="btn btn-register w-100" :disabled="loading">
            {{ loading ? 'MEMPROSES...' : 'DAFTAR' }}
          </button>
          <div class="login-link">
            Sudah punya akun?
            <a href="#" @click.prevent="goToLogin" class="login-text">Masuk di sini</a>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script>
import logoImage from "@/assets/logooido.png";
import axios from "axios";
import router from "@/router";

export default {
  data() {
    return {
      name: "",
      birthDate: "",
      logo: logoImage,
      loading: false,
      error: null,
      apiUrl: "https://api.earscope.adrfstwn.cloud/v1"
    };
  },
  methods: {
    async register() {
      this.loading = true;
      this.error = null;
      
      try {
        // Memformat tanggal lahir jika perlu (format YYYY-MM-DD untuk API)
        const formattedBirthDate = this.birthDate;
        
        // Data yang akan dikirim ke API
        const userData = {
          name: this.name,
          birth_date: formattedBirthDate
        };
        
        // Memanggil API untuk registrasi
        const response = await axios.post(`${this.apiUrl}/register`, userData, {
          headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json'
          }
        });
        
        // Jika berhasil
        console.log("Registrasi berhasil:", response.data);
        
        // Simpan data user atau token jika perlu
        if (response.data.token) {
          localStorage.setItem('userToken', response.data.token);
        }
        
        // Tampilkan pesan sukses
        alert(response.data.message || "Registrasi berhasil! Password akan dikirimkan secara otomatis.");
        
        // Arahkan ke halaman login
        this.$router.push('/login');
      } catch (error) {
        // Tangani error
        console.error("Error saat registrasi:", error);
        
        if (error.response) {
          // Tampilkan pesan error dari server jika ada
          this.error = error.response.data.message || "Terjadi kesalahan saat mendaftar. Silakan coba lagi.";
        } else if (error.request) {
          // Tidak ada respons dari server
          this.error = "Tidak dapat terhubung ke server. Periksa koneksi internet Anda.";
        } else {
          // Error lainnya
          this.error = "Terjadi kesalahan. Silakan coba lagi.";
        }
      } finally {
        this.loading = false;
      }
    },
    goToLogin() {
      console.log("Navigasi ke halaman login");
      this.$router.push('/login');
    }
  },
};
</script>

<style scoped>
/* Import Bootstrap Icons */
@import url("https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css");

.register-container {
  display: flex;
  height: 100vh;
  width: 100%;
  overflow: hidden;
}

.left-panel {
  flex: 0 0 45%;
  background-color: #004178;
  position: relative;
  display: flex;
  justify-content: center;
  align-items: center;
}

.left-panel::before {
  content: '';
  position: absolute;
  top: 0;
  right: 0;
  height: 100%;
  width: 80px;
  background-color: #4DCDC8;
  z-index: 1;
}

.left-panel::after {
  content: '';
  position: absolute;
  top: 0;
  right: 0;
  height: 100%;
  width: 120px;
  background-color: #B9EF89;
  z-index: 0;
}

.logo-container {
  position: relative;
  z-index: 2;
  margin-right: 100px;
}

.logo {
  width: 180px;
  height: auto;
}

.right-panel {
  flex: 1;
  display: flex;
  justify-content: center;
  align-items: center;
  padding: 20px;
}

.register-form-container {
  width: 400px;
  max-width: 100%;
  padding: 20px 30px;
}

.welcome-title {
  color: #004178;
  font-weight: 600;
  font-size: 24px;
  margin-bottom: 10px;
  text-align: left;
}

.instruction-text {
  color: #555;
  font-size: 14px;
  margin-bottom: 30px;
  text-align: left;
}

.form-label {
  display: block;
  color: #555;
  font-size: 14px;
  font-weight: 500;
  margin-bottom: 5px;
  text-align: left;
}

.form-control {
  width: 100%;
  height: 45px;
  padding: 8px 15px;
  border: 1px solid #e0e0e0;
  border-radius: 6px;
  font-size: 14px;
  background-color: #f8f9fa;
}

.form-control::placeholder {
  color: #aaa;
}

.password-info {
  background-color: #f0f8ff;
  padding: 12px 15px;
  border-radius: 6px;
  font-size: 13px;
  color: #555;
  display: flex;
  align-items: flex-start;
  gap: 10px;
}

.password-info i {
  color: #0099ff;
  font-size: 16px;
  margin-top: 2px;
}

.btn-register {
  background-color: #0099ff;
  border: none;
  color: white;
  height: 45px;
  font-weight: 500;
  border-radius: 6px;
  margin-top: 10px;
}

.btn-register:hover {
  background-color: #0088ee;
}

.login-link {
  margin-top: 15px;
  text-align: center;
  font-size: 14px;
  color: #555;
}

.login-text {
  color: #0099ff;
  text-decoration: none;
  font-weight: 500;
}

.login-text:hover {
  text-decoration: underline;
}

.alert {
  padding: 10px 15px;
  border-radius: 6px;
  margin-bottom: 15px;
  font-size: 14px;
}

.alert-danger {
  background-color: #ffebee;
  color: #d32f2f;
  border: 1px solid #ffcdd2;
}

.alert-info {
  background-color: #e3f2fd;
  color: #1976d2;
  border: 1px solid #bbdefb;
}

/* Responsive adjustments */
@media (max-width: 992px) {
  .left-panel {
    flex: 0 0 40%;
  }
}

@media (max-width: 768px) {
  .register-container {
    flex-direction: column;
  }
  
  .left-panel {
    flex: 0 0 200px;
  }
  
  .left-panel::before,
  .left-panel::after {
    width: 100%;
    height: 30px;
    top: auto;
    bottom: 0;
  }
  
  .logo-container {
    margin-right: 0;
    margin-bottom: 60px;
  }
}
</style>