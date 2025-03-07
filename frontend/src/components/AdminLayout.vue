<template>
    <div class="d-flex">
      <!-- Sidebar -->
      <div class="sidebar bg-primary p-3 text-white">
        <div class="sidebar-header mb-4">
          <h4 class="text-center">Admin Page</h4>
          <div class="text-center mb-3">
          </div>
          <div class="text-center small text-white-50 mb-3">
            <p class="mb-0">Dr. {{ adminName }}</p>
            <p class="mb-0">{{ adminRole }}</p>
          </div>
        </div>
  
        <ul class="nav flex-column">
          <li class="nav-item mb-2">
            <router-link to="/dashboard" class="nav-link text-white d-flex align-items-center" :class="{ 'active': activePage === 'dashboard' }">
              <i class="bi bi-speedometer2 me-3"></i>
              <span>Dashboard</span>
            </router-link>
          </li>
          <li class="nav-item mb-2">
            <router-link to="/dokter" class="nav-link text-white d-flex align-items-center" :class="{ 'active': activePage === 'dokter' }">
              <i class="bi bi-person-badge me-3"></i>
              <span>Dokter</span>
            </router-link>
          </li>
          <li class="nav-item mb-2">
            <router-link to="/pasien" class="nav-link text-white d-flex align-items-center" :class="{ 'active': activePage === 'pasien' }">
              <i class="bi bi-people me-3"></i>
              <span>Pasien</span>
            </router-link>
          </li>
          <li class="nav-item mb-2">
            <router-link to="/pemeriksaan" class="nav-link text-white d-flex align-items-center" :class="{ 'active': activePage === 'pemeriksaan' }">
              <i class="bi bi-clipboard2-pulse me-3"></i>
              <span>Hasil Pemeriksaan</span>
            </router-link>
            <li class="nav-item mb-2">
            <router-link to="/konsultasi" class="nav-link text-white d-flex align-items-center" :class="{ 'active': activePage === 'konsultasi' }">
              <i class="bi bi-clipboard2-pulse me-3"></i>
              <span>Konsultasi</span>
            </router-link>
            </li>
          </li>
        </ul>
  
        <div class="mt-auto pt-4">
          <hr class="border-light" />
          <ul class="nav flex-column">
            <li class="nav-item mb-2">
              <router-link to="/admin/settings" class="nav-link text-white d-flex align-items-center">
                <i class="bi bi-gear me-3"></i>
                <span>Pengaturan</span>
              </router-link>
            </li>
            <li class="nav-item">
              <a href="#" @click.prevent="logout" class="nav-link text-white d-flex align-items-center">
                <i class="bi bi-box-arrow-right me-3"></i>
                <span>Logout</span>
              </a>
            </li>
          </ul>
        </div>
      </div>
  
      <!-- Main Content -->
      <div class="content-area">
        <div class="container-fluid p-4">
          <!-- Page Header -->
          <div class="page-header mb-4 d-flex justify-content-between align-items-center">
            <h2>{{ pageTitle }}</h2>
            <div>
              <div class="dropdown">
                <button class="btn btn-light dropdown-toggle" type="button" id="dropdownMenuButton" data-bs-toggle="dropdown" aria-expanded="false">
                  <i class="bi bi-person-circle me-1"></i> {{ adminName }}
                </button>
                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="dropdownMenuButton">
                  <li><a class="dropdown-item" href="#"><i class="bi bi-person me-2"></i>Profil</a></li>
                  <li><a class="dropdown-item" href="#"><i class="bi bi-gear me-2"></i>Pengaturan</a></li>
                  <li><hr class="dropdown-divider"></li>
                  <li><a class="dropdown-item" href="#" @click.prevent="logout"><i class="bi bi-box-arrow-right me-2"></i>Logout</a></li>
                </ul>
              </div>
            </div>
          </div>
          
          <!-- Main Content Goes Here -->
          <slot></slot>
        </div>
      </div>
    </div>
  </template>
  
  <script>
  export default {
    name: "AdminLayout",
    props: {
      activePage: {
        type: String,
        default: 'dashboard'
      },
      activePage: {
        type: String,
        default: 'dokter'
      },
      activePage: {
        type: String,
        default: 'pasien'
      },
      activePage: {
        type: String,
        default: 'pemeriksaan'
      },
      activePage: {
        type: String,
        default: 'konsultasi'
      }
    },
    data() {
      return {
        adminName: 'Admin Rumah Sakit',
        adminRole: 'Administrator',
        pageTitle: 'Dashboard'
      };
    },
    watch: {
      activePage: {
        immediate: true,
        handler(newVal) {
          this.updatePageTitle(newVal);
        }
      }
    },
    methods: {
      updatePageTitle(page) {
        const titles = {
          'dashboard': 'Dashboard',
          'dokter': 'Kelola Dokter',
          'pasien': 'Kelola Pasien',
          'pemeriksaan': 'Hasil Pemeriksaan',
          'konsultasi': 'Konsultasi'
        };
        this.pageTitle = titles[page] || 'Dashboard';
      },
      logout() {
        // Implement logout logic here
        console.log('Logging out...');
        this.$router.push('/login');
      }
    }
  };
  </script>
  
  <style scoped>
  .sidebar {
    width: 250px;
    height: 100vh;
    position: fixed;
    left: 0;
    top: 0;
    overflow-y: auto;
    background-color: #004178 !important;
    display: flex;
    flex-direction: column;
  }
  
  .content-area {
    margin-left: 250px;
    width: calc(100% - 250px);
    min-height: 100vh;
    background-color: #f8f9fa;
  }
  
  .nav-link {
    border-radius: 5px;
    padding: 10px 15px;
    transition: all 0.3s;
  }
  
  .nav-link:hover, .nav-link.active {
    background-color: rgba(255, 255, 255, 0.2);
  }
  
  .sidebar-header {
    border-bottom: 1px solid rgba(255, 255, 255, 0.2);
    padding-bottom: 10px;
  }
  
  .page-header {
    border-bottom: 1px solid #dee2e6;
    padding-bottom: 10px;
  }
  
  @media (max-width: 768px) {
    .sidebar {
      width: 70px;
      padding: 10px;
    }
    
    .sidebar span, .sidebar .sidebar-header p, .sidebar h4 {
      display: none;
    }
    
    .content-area {
      margin-left: 70px;
      width: calc(100% - 70px);
    }
    
    .sidebar img {
      width: 40px;
      height: 40px;
    }
    
    .nav-link {
      justify-content: center !important;
      padding: 10px;
    }
    
    .nav-link i {
      margin-right: 0 !important;
      font-size: 1.2rem;
    }
  }
  </style>