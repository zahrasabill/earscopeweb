<template>
  <admin-layout active-page="pasien">
      <div class="d-flex justify-content-start mb-4">
        <button class="btn btn-primary" @click="showAddModal">
          <i class="bi bi-plus-circle me-1"></i> Tambah Pasien
        </button>
      </div>
  
      <!-- Search and Filter -->
      <div class="row mb-3">
        <div class="col-md-8">
          <div class="input-group">
            <span class="input-group-text"><i class="bi bi-search"></i></span>
            <input 
              type="text" 
              class="form-control" 
              placeholder="Cari pasien..." 
              v-model="searchQuery"
            >
          </div>
        </div>
        <div class="col-md-4">
          <select class="form-select" v-model="filterGender">
            <option value="">Semua Gender</option>
            <option value="Laki-laki">laki-laki</option>
            <option value="Perempuan">perempuan</option>
          </select>
        </div>
      </div>
  
      <!-- Table -->
      <div class="table-responsive">
        <table class="table table-striped table-hover">
          <thead class="table-dark">
            <tr>
              <th>ID</th>
              <th>Nama</th>
              <th>Email</th>
              <th>Phone</th>
              <th>Gender</th>
              <th>Umur</th>
              <th>Role</th>
              <th>Aksi</th>
            </tr>
          </thead>
          <tbody>
            <tr v-if="paginatedPatients.length === 0">
              <td colspan="8" class="text-center">Tidak ada data pasien</td>
            </tr>
            <tr v-for="pasien in paginatedPatients" :key="pasien.id">
              <td>{{ pasien.id }}</td>
              <td>{{ pasien.nama }}</td>
              <td>{{ pasien.email }}</td>
              <td>{{ pasien.phone }}</td>
              <td>{{ pasien.gender }}</td>
              <td>{{ pasien.umur }} tahun</td>
              <td>
                <span class="badge bg-primary">{{ pasien.role }}</span>
              </td>
              <td>
                <div class="btn-group">
                  <button class="btn btn-sm btn-info me-1" @click="showEditModal(pasien)" title="Edit">
                    <i class="bi bi-pencil"></i>
                  </button>
                  <button class="btn btn-sm btn-danger" @click="confirmDelete(pasien)" title="Hapus">
                    <i class="bi bi-trash"></i>
                  </button>
                  <button class="btn btn-sm btn-success ms-1" @click="goToPemeriksaanView(pasien)" title="Periksa">
                    <i class="bi bi-arrow-right-square"></i>
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
  
      <!-- Pagination -->
      <div class="d-flex justify-content-between align-items-center">
        <div>
          Menampilkan {{ paginatedPatients.length }} dari {{ filteredPatients.length }} pasien
        </div>
        <nav v-if="totalPages > 1">
          <ul class="pagination">
            <li class="page-item" :class="{ disabled: currentPage === 1 }">
              <a class="page-link" href="#" @click.prevent="currentPage--">Previous</a>
            </li>
            <li v-for="page in totalPages" :key="page" class="page-item" :class="{ active: page === currentPage }">
              <a class="page-link" href="#" @click.prevent="currentPage = page">{{ page }}</a>
            </li>
            <li class="page-item" :class="{ disabled: currentPage === totalPages }">
              <a class="page-link" href="#" @click.prevent="currentPage++">Next</a>
            </li>
          </ul>
        </nav>
      </div>
  
      <!-- Add/Edit Modal -->
      <div class="modal fade" id="patientModal" tabindex="-1" ref="patientModal">
        <div class="modal-dialog">
          <div class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title">{{ isEditing ? 'Edit Pasien' : 'Tambah Pasien' }}</h5>
              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
              <form @submit.prevent="isEditing ? updatePatient() : addPatient()">
                <div class="mb-3">
                  <label for="nama" class="form-label">Nama Lengkap</label>
                  <input type="text" class="form-control" id="nama" v-model="formData.nama" required>
                </div>
                
                <div class="mb-3">
                  <label for="email" class="form-label">Email</label>
                  <input type="email" class="form-control" id="email" v-model="formData.email" required>
                </div>
                
                <div class="mb-3">
                  <label for="password" class="form-label">Password</label>
                  <input type="password" class="form-control" id="password" v-model="formData.password" :required="!isEditing">
                  <small class="text-muted" v-if="isEditing">Biarkan kosong jika tidak ingin mengubah password</small>
                </div>
                
                <div class="mb-3">
                  <label for="phone" class="form-label">Nomor Telepon</label>
                  <input type="tel" class="form-control" id="phone" v-model="formData.phone" required>
                </div>
                
                <div class="mb-3">
                  <label class="form-label">Gender</label>
                  <div class="d-flex">
                    <div class="form-check me-3">
                      <input class="form-check-input" type="radio" name="gender" id="male" value="Laki-laki" v-model="formData.gender" required>
                      <label class="form-check-label" for="male">Laki-laki</label>
                    </div>
                    <div class="form-check">
                      <input class="form-check-input" type="radio" name="gender" id="female" value="Perempuan" v-model="formData.gender">
                      <label class="form-check-label" for="female">Perempuan</label>
                    </div>
                  </div>
                </div>
                
                <div class="mb-3">
                  <label for="umur" class="form-label">Umur</label>
                  <input type="number" class="form-control" id="umur" v-model="formData.umur" min="0" max="120" required>
                </div>
                
                <div class="mb-3">
                  <label for="role" class="form-label">Role</label>
                  <select class="form-select" id="role" v-model="formData.role" required disabled>
                    <option value="pasien">Pasien</option>
                  </select>
                  <small class="text-muted">Role default untuk semua user yang ditambahkan</small>
                </div>
                
                <div class="modal-footer">
                  <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                  <button type="submit" class="btn btn-primary">
                    {{ isEditing ? 'Simpan Perubahan' : 'Tambah Pasien' }}
                  </button>
                </div>
              </form>
            </div>
          </div>
        </div>
      </div>
  
      <!-- Delete Confirmation Modal -->
      <div class="modal fade" id="deleteModal" tabindex="-1" ref="deleteModal">
        <div class="modal-dialog">
          <div class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title">Konfirmasi Hapus</h5>
              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
              <p>Apakah Anda yakin ingin menghapus pasien <strong>{{ selectedPatient.nama }}</strong>?</p>
              <p class="text-danger">Tindakan ini tidak dapat dibatalkan.</p>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
              <button type="button" class="btn btn-danger" @click="deletePatient">Hapus</button>
            </div>
          </div>
        </div>
      </div>
  </admin-layout>
</template>

<script>
import AdminLayout from '@/components/AppLayout.vue';
import { Modal } from 'bootstrap';

export default {
  name: "PasienView",
  components: { 
    AdminLayout 
  },
  data() {
    return {
      patients: [
        { 
          id: 1, 
          nama: 'Budi Santoso', 
          email: 'budi@example.com', 
          password: 'password123', 
          phone: '08123456789', 
          gender: 'Laki-laki', 
          umur: 35,
          role: 'pasien'
        },
        { 
          id: 2, 
          nama: 'Siti Aminah', 
          email: 'siti@example.com', 
          password: 'password456', 
          phone: '08567891234', 
          gender: 'Perempuan', 
          umur: 28,
          role: 'pasien'
        },
        { 
          id: 3, 
          nama: 'Joko Widodo', 
          email: 'joko@example.com', 
          password: 'password789', 
          phone: '08912345678', 
          gender: 'Laki-laki', 
          umur: 42,
          role: 'pasien'
        },
      ],
      formData: {
        id: null,
        nama: '',
        email: '',
        password: '',
        phone: '',
        gender: '',
        umur: '',
        role: 'pasien'
      },
      selectedPatient: {},
      isEditing: false,
      searchQuery: '',
      filterGender: '',
      currentPage: 1,
      itemsPerPage: 5,
      patientModal: null,
      deleteModal: null
    };
  },
  computed: {
    filteredPatients() {
      let filtered = this.patients;
      
      // Search filter
      if (this.searchQuery) {
        const query = this.searchQuery.toLowerCase();
        filtered = filtered.filter(patient => 
          patient.nama.toLowerCase().includes(query) || 
          patient.email.toLowerCase().includes(query) ||
          patient.phone.includes(query)
        );
      }
      
      // Gender filter
      if (this.filterGender) {
        filtered = filtered.filter(patient => patient.gender === this.filterGender);
      }
      
      return filtered;
    },
    paginatedPatients() {
      const start = (this.currentPage - 1) * this.itemsPerPage;
      const end = start + this.itemsPerPage;
      return this.filteredPatients.slice(start, end);
    },
    totalPages() {
      return Math.ceil(this.filteredPatients.length / this.itemsPerPage);
    }
  },
  mounted() {
    this.initModals();
  },
  methods: {
    initModals() {
      // Inisialisasi Bootstrap modal dengan benar
      this.patientModal = new Modal(this.$refs.patientModal);
      this.deleteModal = new Modal(this.$refs.deleteModal);
    },
    
    showAddModal() {
      this.isEditing = false;
      this.resetForm();
      this.patientModal.show();
    },
    
    showEditModal(patient) {
      this.isEditing = true;
      this.formData = { ...patient };
      this.formData.password = ''; // Clear password for security
      this.patientModal.show();
    },
    
    resetForm() {
      this.formData = {
        id: null,
        nama: '',
        email: '',
        password: '',
        phone: '',
        gender: '',
        umur: '',
        role: 'pasien'
      };
    },
    
    addPatient() {
      // Generate a new ID (in a real app, this would be handled by the backend)
      const newId = Math.max(...this.patients.map(pat => pat.id), 0) + 1;
      
      const newPatient = {
        ...this.formData,
        id: newId
      };
      
      this.patients.push(newPatient);
      this.patientModal.hide();
      this.resetForm();
      
      // Show success message (in a real app)
      alert('Pasien berhasil ditambahkan!');
    },
    
    updatePatient() {
      const index = this.patients.findIndex(pat => pat.id === this.formData.id);
      
      if (index !== -1) {
        // If password is empty, keep the old password
        if (!this.formData.password) {
          this.formData.password = this.patients[index].password;
        }
        
        // Update the patient data
        this.patients[index] = { ...this.formData };
        this.patientModal.hide();
        
        // Show success message (in a real app)
        alert('Data pasien berhasil diperbarui!');
      }
    },
    
    confirmDelete(patient) {
      this.selectedPatient = patient;
      this.deleteModal.show();
    },
    
    deletePatient() {
      const index = this.patients.findIndex(pat => pat.id === this.selectedPatient.id);
      
      if (index !== -1) {
        this.patients.splice(index, 1);
        this.deleteModal.hide();
        
        // Show success message (in a real app)
        alert('Pasien berhasil dihapus!');
      }
    },

    goToPemeriksaanView(pasien){
      this.$store.commit('setPasienTerpilih', pasien);
      this.$router.push({
        name: 'Pemeriksaan',
        params: {id: pasien.id}
      });
    }
  }
};
</script>

<style scoped>
.password-mask {
  letter-spacing: 1px;
}

.table th {
  vertical-align: middle;
}

.badge {
  font-size: 0.85em;
}

.btn-group .btn {
  display: flex;
  align-items: center;
  justify-content: center;
}
</style>