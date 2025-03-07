<template>
    <div class="register-container">
      <div class="register-left">
        <div class="logo-container">
          <img :src="logo" alt="Logo OIDO" class="logo" />
        </div>
      </div>
      <div class="register-right">
        <div class="register-card">
          <h2 class="welcome-text">Buat Akun Baru</h2>
          <p class="instruction">
            Silahkan lengkapi formulir pendaftaran dengan informasi Anda
          </p>
          <form @submit.prevent="register">
            <div class="form-group">
              <label for="name">Nama</label>
              <input
                type="text"
                v-model="name"
                class="form-control"
                id="name"
                placeholder="Masukkan Nama Anda"
                required
              />
            </div>
            <div class="form-group">
              <label for="email">Email</label>
              <input
                type="email"
                v-model="email"
                class="form-control"
                id="email"
                placeholder="Masukkan Email Anda"
                required
              />
            </div>
            <div class="form-group">
              <label for="password">Password</label>
              <input
                type="password"
                v-model="password"
                class="form-control"
                id="password"
                placeholder="Buat Password"
                required
                minlength="6"
              />
            </div>
            <div class="form-group">
              <label for="confirm-password">Konfirmasi Password</label>
              <input
                type="password"
                v-model="confirmPassword"
                class="form-control"
                id="confirm-password"
                placeholder="Ulangi Password"
                required
                minlength="6"
              />
            </div>
            <button type="submit" class="btn-register">DAFTAR</button>
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
  
  export default {
    data() {
      return {
        name: "",
        email: "",
        password: "",
        confirmPassword: "",
        logo: logoImage,
      };
    },
    methods: {
      register() {
        // Validasi password
        if (this.password !== this.confirmPassword) {
          alert("Password tidak cocok. Silakan periksa kembali.");
          return;
        }

        // Logika registrasi
        console.log("Nama:", this.name);
        console.log("Email:", this.email);
        console.log("Password:", this.password);
        
        // Simulasi registrasi berhasil
        alert("Registrasi berhasil!");
        
        // Reset form
        this.name = "";
        this.email = "";
        this.password = "";
        this.confirmPassword = "";
      },
      goToLogin() {
        // Tambahkan logika navigasi ke halaman login
        console.log("Navigasi ke halaman login");
        this.$router.push('/login');
      }
    },
  };
  </script>
  
  <style scoped>
  .register-container {
    display: flex;
    height: 100vh;
    background-color: #ffffff;
    margin: 0;
    padding: 0;
    overflow: hidden;
  }
  
  .register-left {
    flex: 1;
    background: linear-gradient(to bottom right, #003366, #66ff99);
    display: flex;
    justify-content: center;
    align-items: center;
    border-top-right-radius: 50px;
    border-bottom-right-radius: 50px;
    margin: 0;
  }
  
  .logo-container {
    text-align: center;
  }
  
  .logo {
    width: 150px;
  }
  
  .register-right {
    flex: 1;
    display: flex;
    justify-content: center;
    align-items: center;
    background-color: white;
  }
  
  .register-card {
    width: 350px;
    padding: 30px;
    box-shadow: 0 6px 10px rgba(0, 0, 0, 0.1);
    border-radius: 10px;
    text-align: center;
    border: 2px solid #c2c9d0;
  }
  
  .welcome-text {
    color: #003366;
    font-weight: bold;
    font-size: 22px;
  }
  
  .instruction {
    color: #666;
    font-size: 14px;
    margin-bottom: 20px;
  }
  
  .form-group {
    margin-bottom: 15px;
    text-align: left;
  }
  
  .form-control {
    width: 100%;
    padding: 10px;
    border: 1px solid #ccc;
    border-radius: 5px;
  }
  
  .btn-register {
    width: 100%;
    padding: 10px;
    background-color: #007bff;
    color: white;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    font-size: 16px;
    margin-bottom: 15px;
  }
  
  .btn-register:hover {
    background-color: #0056b3;
  }

  .login-link {
    text-align: center;
    color: #666;
    font-size: 14px;
  }

  .login-text {
    color: #007bff;
    text-decoration: none;
  }

  .login-text:hover {
    text-decoration: underline;
  }
  </style>