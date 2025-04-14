<template>
  <div class="d-flex justify-content-between mb-4">
  <router-link to="/dokter/create" class="btn btn-primary">
        <i class="bi bi-plus-circle me-1"></i> Tambah Dokter
  </router-link>
  </div>
      <div class="card-body">
        <div class="mb-3">
          <div class="input-group">
            <input 
              type="text" 
              class="form-control" 
              placeholder="Cari dokter..." 
              v-model="searchQuery"
              @input="filterDokter"
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
                <th scope="col">Gender</th>
                <th scope="col">Phone</th>
                <th scope="col">Aksi</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="(dokter, index) in filteredDokter" :key="dokter.id">
                <th scope="row">{{ index + 1 }}</th>
                <td>{{ dokter.kodeAkses }}</td>
                <td>{{ dokter.nama }}</td>
                <td>{{ formatDate(dokter.tanggalLahir) }}</td>
                <td>{{ dokter.gender }}</td>
                <td>{{ dokter.phone }}</td>
                <td>
                  <div class="btn-group" role="group">
                    <router-link :to="`/dokter/view/${dokter.id}`" class="btn btn-sm btn-info text-white">
                      <i class="bi bi-eye"></i>
                    </router-link>
                    <router-link :to="`/dokter/edit/${dokter.id}`" class="btn btn-sm btn-warning text-white">
                      <i class="bi bi-pencil"></i>
                    </router-link>
                    <button @click="confirmDelete(dokter)" class="btn btn-sm btn-danger">
                      <i class="bi bi-trash"></i>
                    </button>
                  </div>
                </td>
              </tr>
              <tr v-if="filteredDokter.length === 0">
                <td colspan="7" class="text-center py-3">
                  <div class="alert alert-info mb-0">
                    Tidak ada data dokter yang ditemukan.
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
        
        <!-- Pagination -->
        <nav aria-label="Page navigation" v-if="filteredDokter.length > 0">
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
          <div class="modal-body" v-if="selectedDokter">
            <p>Apakah Anda yakin ingin menghapus dokter <strong>{{ selectedDokter.nama }}</strong>?</p>
            <p class="text-danger"><strong>Perhatian:</strong> Tindakan ini tidak dapat dibatalkan.</p>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
            <button type="button" class="btn btn-danger" @click="deleteDokter">Hapus</button>
          </div>
        </div>
      </div>
    </div>
</template>

<script>
import { Modal } from 'bootstrap';
import axios from 'axios'; // Make sure to import axios

export default {
  name: 'ListDokter',
  data() {
    return {
      dokterList: [],
      filteredDokter: [],
      searchQuery: '',
      currentPage: 1,
      itemsPerPage: 10,
      selectedDokter: null,
      deleteModal: null
    };
  },
  computed: {
    totalPages() {
      return Math.ceil(this.filteredDokter.length / this.itemsPerPage);
    },
    paginatedDokter() {
      const start = (this.currentPage - 1) * this.itemsPerPage;
      const end = start + this.itemsPerPage;
      return this.filteredDokter.slice(start, end);
    }
  },
  created() {
    this.loadDokterData();
  },
  mounted() {
    this.deleteModal = new Modal(document.getElementById('deleteModal'));
  },
  methods: {
    async loadDokterData() {
      try {
        // Dapatkan token dari localStorage atau dari state management (Vuex/Pinia)
        const token = localStorage.getItem('accessToken') || this.$store?.state?.auth?.token;
        
        if (!token) {
          throw new Error('Token autentikasi tidak ditemukan. Silakan login terlebih dahulu.');
        }
        
        // Panggil API untuk mengambil daftar dokter
        const response = await axios.get(
          'https://api.earscope.adrfstwn.cloud/v1/dokter',
          {
            headers: {
              'Authorization': `Bearer ${token}`,
              'Content-Type': 'application/json'
            }
          }
        );
        
        if (response.data && response.data.data) {
          this.dokterList = response.data.data;
          this.filteredDokter = [...this.dokterList];
        } else {
          throw new Error('Format response tidak sesuai');
        }
      } catch (err) {
        console.error('Error saat mengambil daftar dokter:', err);
        alert('Gagal memuat daftar dokter. Silakan coba lagi.');
      }
    },
    
    randomDate(start, end) {
      const date = new Date(start.getTime() + Math.random() * (end.getTime() - start.getTime()));
      return date.toISOString().slice(0, 10);
    },
    
    formatDate(dateString) {
      const options = { day: 'numeric', month: 'long', year: 'numeric' };
      return new Date(dateString).toLocaleDateString('id-ID', options);
    },
    
    filterDokter() {
      if (!this.searchQuery.trim()) {
        this.filteredDokter = [...this.dokterList];
      } else {
        const query = this.searchQuery.toLowerCase();
        this.filteredDokter = this.dokterList.filter(dokter => 
          dokter.nama.toLowerCase().includes(query) ||
          dokter.kodeAkses.toLowerCase().includes(query) ||
          dokter.phone.includes(query)
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
    
    confirmDelete() {
    this.deleteModal.show();
  },
  
  async deleteDokter() {
    try {
      // Dapatkan token dari localStorage atau dari state management (Vuex/Pinia)
      const token = localStorage.getItem('accessToken') || this.$store?.state?.auth?.token;
      
      if (!token) {
        throw new Error('Token autentikasi tidak ditemukan. Silakan login terlebih dahulu.');
      }
      
      // Panggil API untuk menghapus dokter
      await axios.delete(
        `https://api.earscope.adrfstwn.cloud/v1/dokter/${this.dokterId}`,
        {
          headers: {
            'Authorization': `Bearer ${token}`,
            'Content-Type': 'application/json'
          }
        }
      );
      
      console.log('Menghapus dokter:', this.dokter.kodeAkses);
      
      // Tutup modal
      this.deleteModal.hide();
      
      // Tampilkan notifikasi
      alert('Dokter berhasil dihapus!');
      
      // Redirect ke halaman list dokter
      this.$router.push('/dokter');
    } catch (err) {
      console.error('Error saat menghapus dokter:', err);
      alert('Gagal menghapus dokter. Silakan coba lagi.');
      
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