<template>
  <div class="login-container">
    <div class="left-panel">
      <div class="logo-container">
        <img :src="logo" alt="OIDO" class="logo" />
      </div>
    </div>
    <div class="right-panel">
      <div class="login-form-container">
        <h2 class="welcome-title">Selamat Datang!</h2>
        <p class="instruction-text">
          Silahkan masukkan Kode Akses dan Password Anda!
        </p>
        <form @submit.prevent="login">
          <div class="form-group mb-3">
            <label for="kode_akses" class="form-label">Kode Akses</label>
            <div class="input-wrapper">
              <input
                type="text"
                v-model="kode_akses"
                class="form-control"
                id="kode_akses"
                placeholder="Masukkan Kode Akses Anda"
                required
              />
            </div>
          </div>
          <div class="form-group mb-4">
            <label for="password" class="form-label">Password</label>
            <div class="input-wrapper">
              <input
                type="password"
                v-model="password"
                class="form-control"
                id="password"
                placeholder="Masukkan Password Anda"
                required
              />
              <i 
                class="bi" 
                :class="showPassword ? 'bi-eye-slash' : 'bi-eye'" 
                @click="togglePasswordVisibility"
                style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%); cursor: pointer;"
              ></i>
            </div>
          </div>
          <div class="form-group mb-3">
            <div class="form-check">
              <input 
                type="checkbox" 
                v-model="rememberMe" 
                class="form-check-input" 
                id="rememberMe"
              />
              <label class="form-check-label" for="rememberMe">
                Ingat Saya
              </label>
            </div>
          </div>
          <button type="submit" class="btn btn-login w-100" :disabled="loading">
            {{ loading ? 'MEMPROSES...' : 'MASUK' }}
          </button>
          <div v-if="error" class="error-message mt-2">
            {{ error }}
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
      accessCode: "",
      password: "",
      showPassword: false,
      rememberMe: false,
      logo: logoImage,
      loading: false,
      error: null,
      sessionId: null
    };
  },
  created() {
    // Cek apakah ada sessionId di cookie
    this.checkExistingSession();
  },
  methods: {
    async checkExistingSession() {
      const sessionId = this.getCookie('session_id');
      if (sessionId) {
        try {
          // Verifikasi session dengan server
          const response = await axios.post('https://api.earscope.adrfstwn.cloud/v1/verify-session', {
            sessionId: sessionId
          });
          
          if (response.data.valid) {
            // Session valid, redirect ke dashboard
            this.$router.push('/dashboard');
          }
        } catch (error) {
          console.error('Session verification error:', error);
          // Hapus cookie jika session tidak valid
          this.deleteCookie('session_id');
        }
      }
    },
    
    async login() {
      this.loading = true;
      this.error = null;
      
      console.log("Kode Akses:", this.accessCode);
      console.log("Password:", this.password);
      console.log("Remember Me:", this.rememberMe);
      
      try {
        // Memanggil API dari backend untuk login
        const response = await axios.post('https://api.earscope.adrfstwn.cloud/v1/login', {
          accessCode: this.accessCode,
          password: this.password,
          rememberMe: this.rememberMe
        });
        
        // Dapatkan sessionId dari server
        const sessionId = response.data.sessionId;
        
        // Simpan sessionId ke cookie dengan waktu kedaluwarsa sesuai
        // rememberMe: true = 30 hari, false = sampai browser ditutup
        if (this.rememberMe) {
          this.setCookie('session_id', sessionId, 30);
        } else {
          this.setCookie('session_id', sessionId, '');
        }
        
        // Redirect ke dashboard
        this.$router.push('/dashboard');
        
      } catch (error) {
        console.error('Login error:', error);
        this.error = error.response?.data?.message || 'Login gagal. Silakan coba lagi.';
      } finally {
        this.loading = false;
      }
    },
    
    // Fungsi untuk mengelola cookie
    setCookie(name, value, days) {
      let expires = '';
      if (days) {
        const date = new Date();
        date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000));
        expires = '; expires=' + date.toUTCString();
      }
      document.cookie = name + '=' + (value || '') + expires + '; path=/; SameSite=Strict; Secure';
    },
    
    getCookie(name) {
      const nameEQ = name + '=';
      const ca = document.cookie.split(';');
      for (let i = 0; i < ca.length; i++) {
        let c = ca[i];
        while (c.charAt(0) === ' ') c = c.substring(1, c.length);
        if (c.indexOf(nameEQ) === 0) return c.substring(nameEQ.length, c.length);
      }
      return null;
    },
    
    deleteCookie(name) {
      document.cookie = name + '=; Max-Age=-99999999; path=/';
    },
    
    togglePasswordVisibility() {
      this.showPassword = !this.showPassword;
      const passwordInput = document.getElementById('password');
      if (passwordInput) {
        passwordInput.type = this.showPassword ? 'text' : 'password';
      }
    }
  },
};
</script>

<style scoped>
/* Import Bootstrap Icons */
@import url("https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css");

.login-container {
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

.login-form-container {
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

.input-wrapper {
  position: relative;
}

.form-control::placeholder {
  color: #aaa;
}

.form-check {
  display: flex;
  align-items: center;
}

.form-check-input {
  margin-right: 8px;
}

.form-check-label {
  font-size: 14px;
  color: #555;
}

.btn-login {
  background-color: #0099ff;
  border: none;
  color: white;
  height: 45px;
  font-weight: 500;
  border-radius: 6px;
  margin-top: 10px;
}

.btn-login:hover {
  background-color: #0088ee;
}

.btn-login:disabled {
  background-color: #cccccc;
  cursor: not-allowed;
}

.error-message {
  color: #dc3545;
  font-size: 14px;
  text-align: center;
}

.register-link {
  margin-top: 15px;
  text-align: center;
  font-size: 14px;
  color: #555;
}

.register-text {
  color: #0099ff;
  text-decoration: none;
  font-weight: 500;
}

.register-text:hover {
  text-decoration: underline;
}

/* Responsive adjustments */
@media (max-width: 992px) {
  .left-panel {
    flex: 0 0 40%;
  }
}

@media (max-width: 768px) {
  .login-container {
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