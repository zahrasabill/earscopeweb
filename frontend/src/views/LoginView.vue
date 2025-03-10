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
          Silahkan masukkan Email dan Password Anda!
        </p>
        <form @submit.prevent="login">
          <div class="form-group mb-3">
            <label for="name" class="form-label">Nama</label>
            <div class="input-wrapper">
              <input
                type="name"
                v-model="name"
                class="form-control"
                id="name"
                placeholder="Masukkan Nama Anda"
                required
              />
            </div>
          </div>
          <div class="form-group mb-4">
            <label for="password" class="form-label">Password</label>
            <div class="input-wrapper position-relative">
              <input
                :type="showPassword ? 'text' : 'password'"
                v-model="password"
                class="form-control"
                id="password"
                placeholder="Masukkan Password Anda"
                required
              />
              <button 
                type="button" 
                class="password-toggle"
                @click="showPassword = !showPassword"
              >
                <i class="bi" :class="showPassword ? 'bi-eye-slash' : 'bi-eye'"></i>
              </button>
            </div>
          </div>
          <button type="submit" class="btn btn-login w-100">MASUK</button>
          <div class="register-link">
              Belum punya akun? 
              <a href="#" @click.prevent="goToRegister" class="register-text">Daftar di sini</a>
            </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script>
import logoImage from "@/assets/logooido.png";

export default {
  data() {
    return {
      email: "",
      password: "",
      logo: logoImage,
      showPassword: false
    };
  },
  methods: {
    login() {
      console.log("Email:", this.email);
      console.log("Password:", this.password);
      alert("Login berhasil!");
    },
    goToRegister() {
      console.log("Navigasi ke halaman register");
      this.$router.push('/register');
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

.form-control::placeholder {
  color: #aaa;
}

.password-toggle {
  position: absolute;
  right: 10px;
  top: 50%;
  transform: translateY(-50%);
  background: none;
  border: none;
  color: #aaa;
  cursor: pointer;
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