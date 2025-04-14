<template>
  <div class="d-flex justify-content-between mb-4">
    <router-link to="/pasien/create" class="btn btn-primary">
      <i class="bi bi-plus-circle me-1"></i> Tambah Pasien
    </router-link>
  </div>
  <div class="card-body">
    <div class="mb-3">
      <div class="input-group">
        <input 
          type="text" 
          class="form-control" 
          placeholder="Cari pasien..." 
          v-model="searchQuery"
          @input="filterPasien"
        />
        <button class="btn btn-outline-secondary" type="button">
          <i class="bi bi-search"></i>
        </button>
      </div>
    </div>
    
    <div class="table-responsive">
      <table class="table table-striped table-hover">
        <thead class="table-light">
          <tr>
            <th scope="col">#</th>
            <th scope="col">Kode Akses</th>
            <th scope="col">Nama</th>
            <th scope="col">Tanggal Lahir</th>
            <th scope="col">Usia</th>
            <th scope="col">Gender</th>
            <th scope="col">Phone</th>
            <th scope="col">Aksi</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="(pasien, index) in paginatedPasien" :key="pasien.id">
            <th scope="row">{{ index + 1 + (currentPage - 1) * itemsPerPage }}</th>
            <td>{{ pasien.kodeAkses }}</td>
            <td>{{ pasien.nama }}</td>
            <td>{{ formatDate(pasien.tanggalLahir) }}</td>
            <td>{{ pasien.usia }}</td>
            <td>{{ pasien.gender === 'L' ? 'Laki-laki' : 'Perempuan' }}</td>
            <td>{{ pasien.phone }}</td>
            <td>
              <div class="btn-group" role="group">
                <router-link :to="`/pasien/view/${pasien.id}`" class="btn btn-sm btn-info text-white">
                  <i class="bi bi-eye"></i>
                </router-link>
                <router-link :to="`/pasien/edit/${pasien.id}`" class="btn btn-sm btn-warning text-white">
                  <i class="bi bi-pencil"></i>
                </router-link>
                <button @click="confirmDelete(pasien)" class="btn btn-sm btn-danger">
                  <i class="bi bi-trash"></i>
                </button>
              </div>
            </td>
          </tr>
          <tr v-if="filteredPasien.length === 0">
            <td colspan="8" class="text-center py-3">
              <div class="alert alert-info mb-0">
                Tidak ada data pasien yang ditemukan.
              </div>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
    
    <!-- Pagination -->
    <nav aria-label="Page navigation" v-if="filteredPasien.length > 0">
      <ul class="pagination justify-content-center">
        <li class="page-item" :class="{ disabled: currentPage === 1 }">
          <a class="page-link" href="#" @click.prevent="changePage(currentPage - 1)">Previous</a>
        </li>
        <li 
          v-for="page in totalPages" 
          :key="page" 
          class="page-item"
          :class="{ active: page === currentPage }"
        >
          <a class="page-link" href="#" @click.prevent="changePage(page)">{{ page }}</a>
        </li>
        <li class="page-item" :class="{ disabled: currentPage === totalPages }">
          <a class="page-link" href="#" @click.prevent="changePage(currentPage + 1)">Next</a>
        </li>
      </ul>
    </nav>
  </div>

  <!-- Modal Konfirmasi Hapus -->
  <div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header bg-danger text-white">
          <h5 class="modal-title" id="deleteModalLabel">Konfirmasi Hapus</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body" v-if="selectedPasien">
          <p>Apakah Anda yakin ingin menghapus pasien <strong>{{ selectedPasien.nama }}</strong>?</p>
          <p class="text-danger"><strong>Perhatian:</strong> Tindakan ini tidak dapat dibatalkan.</p>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
          <button type="button" class="btn btn-danger" @click="deletePasien">Hapus</button>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { Modal } from 'bootstrap';
import axios from 'axios';

export default {
  name: 'ListPasien',
  data() {
    return {
      pasienList: [],
      filteredPasien: [],
      searchQuery: '',
      currentPage: 1,
      itemsPerPage: 10,
      selectedPasien: null,
      deleteModal: null
    };
  },
  computed: {
    totalPages() {
      return Math.ceil(this.filteredPasien.length / this.itemsPerPage);
    },
    paginatedPasien() {
      const start = (this.currentPage - 1) * this.itemsPerPage;
      const end = start + this.itemsPerPage;
      return this.filteredPasien.slice(start, end);
    }
  },
  created() {
    this.loadPasienData();
  },
  mounted() {
    this.deleteModal = new Modal(document.getElementById('deleteModal'));
  },
  methods: {
    async loadPasienData() {
      try {
        // Dapatkan token dari localStorage atau dari state management (Vuex/Pinia)
        const token = localStorage.getItem('accessToken') || this.$store?.state?.auth?.token;
        
        if (!token) {
          throw new Error('Token autentikasi tidak ditemukan. Silakan login terlebih dahulu.');
        }
        
        // Panggil API untuk mengambil daftar pasien
        const response = await axios.get(
          'https://api.earscope.adrfstwn.cloud/v1/pasien',
          {
            headers: {
              'Authorization': `Bearer ${token}`,
              'Content-Type': 'application/json'
            }
          }
        );
        
        if (response.data && response.data.data) {
          this.pasienList = response.data.data;
          this.filteredPasien = [...this.pasienList];
        } else {
          throw new Error('Format response tidak sesuai');
        }
      } catch (err) {
        console.error('Error saat mengambil daftar pasien:', err);
        alert('Gagal memuat daftar pasien. Silakan coba lagi.');
      }
    },
    
    formatDate(dateString) {
      const options = { day: 'numeric', month: 'long', year: 'numeric' };
      return new Date(dateString).toLocaleDateString('id-ID', options);
    },
    
    filterPasien() {
      if (!this.searchQuery.trim()) {
        this.filteredPasien = [...this.pasienList];
      } else {
        const query = this.searchQuery.toLowerCase();
        this.filteredPasien = this.pasienList.filter(pasien => 
          pasien.nama.toLowerCase().includes(query) ||
          pasien.kodeAkses.toLowerCase().includes(query) ||
          pasien.phone.includes(query)
        );
      }
      
      // Reset ke halaman pertama setelah pencarian
      this.currentPage = 1;
    },
    
    changePage(page) {
      if (page >= 1 && page <= this.totalPages) {
        this.currentPage = page;
      }
    },
    
    confirmDelete(pasien) {
      this.selectedPasien = pasien;
      this.deleteModal.show();
    },
    
    async deletePasien() {
      try {
        // Dapatkan token dari localStorage atau dari state management (Vuex/Pinia)
        const token = localStorage.getItem('accessToken') || this.$store?.state?.auth?.token;
        
        if (!token) {
          throw new Error('Token autentikasi tidak ditemukan. Silakan login terlebih dahulu.');
        }
        
        // Panggil API untuk menghapus pasien
        await axios.delete(
          `https://api.earscope.adrfstwn.cloud/v1/pasien/${this.selectedPasien.id}`,
          {
            headers: {
              'Authorization': `Bearer ${token}`,
              'Content-Type': 'application/json'
            }
          }
        );
        
        console.log('Menghapus pasien:', this.selectedPasien.kodeAkses);
        
        // Tutup modal
        this.deleteModal.hide();
        
        // Tampilkan notifikasi
        alert('Pasien berhasil dihapus!');
        
        // Refresh data pasien
        this.loadPasienData();
      } catch (err) {
        console.error('Error saat menghapus pasien:', err);
        alert('Gagal menghapus pasien. Silakan coba lagi.');
        
        // Tutup modal
        this.deleteModal.hide();
      }
    }
  }
};
</script>

<style scoped>
.card {
  box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
}

.table th, .table td {
  vertical-align: middle;
}

.btn-group .btn {
  margin-right: 3px;
}
</style>