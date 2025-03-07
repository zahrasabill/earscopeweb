<template>
  <admin-layout active-page="dokter">
    <div class="container">
      <div class="d-flex justify-content-start mb-4">
          <button class="btn btn-primary" @click="showAddModal">
            <i class="bi bi-plus-circle me-1"></i> Tambah Dokter
          </button>
      </div>
  
      <!-- Search and Filter -->
      <div class="row mb-3">
        <div class="col-md-6">
          <div class="input-group">
            <span class="input-group-text"><i class="bi bi-search"></i></span>
            <input 
              type="text" 
              class="form-control" 
              placeholder="Cari dokter..." 
              v-model="searchQuery"
            >
          </div>
        </div>
        <div class="col-md-3">
          <select class="form-select" v-model="filterGender">
            <option value="">Semua Gender</option>
            <option value="Laki-laki">Laki-laki</option>
            <option value="Perempuan">Perempuan</option>
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
              <th>Password</th>
              <th>Phone</th>
              <th>Gender</th>
              <th>Role</th>
              <th>Aksi</th>
            </tr>
          </thead>
          <tbody>
            <tr v-if="filteredDoctors.length === 0">
              <td colspan="8" class="text-center">Tidak ada data dokter</td>
            </tr>
            <tr v-for="dokter in filteredDoctors" :key="dokter.id">
              <td>{{ dokter.id }}</td>
              <td>{{ dokter.nama }}</td>
              <td>{{ dokter.email }}</td>
              <td>
                <span class="password-mask">••••••••</span>
              </td>
              <td>{{ dokter.phone }}</td>
              <td>{{ dokter.gender }}</td>
              <td>
                <span class="badge bg-success">{{ dokter.role }}</span>
              </td>
              <td>
                <div class="btn-group">
                  <button class="btn btn-sm btn-info me-1" @click="showEditModal(dokter)">
                    <i class="bi bi-pencil"></i>
                  </button>
                  <button class="btn btn-sm btn-danger" @click="confirmDelete(dokter)">
                    <i class="bi bi-trash"></i>
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
          Menampilkan {{ filteredDoctors.length }} dari {{ doctors.length }} dokter
        </div>
        <nav>
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
      <div class="modal fade" id="doctorModal" tabindex="-1" ref="doctorModal">
        <div class="modal-dialog">
          <div class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title">{{ isEditing ? 'Edit Dokter' : 'Tambah Dokter' }}</h5>
              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
              <form @submit.prevent="isEditing ? updateDoctor() : addDoctor()">
                <div class="mb-3">
                  <label for="nama" class="form-label">Nama</label>
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
                  <div class="form-check">
                    <input class="form-check-input" type="radio" name="gender" id="male" value="Laki-laki" v-model="formData.gender">
                    <label class="form-check-label" for="male">Laki-laki</label>
                  </div>
                  <div class="form-check">
                    <input class="form-check-input" type="radio" name="gender" id="female" value="Perempuan" v-model="formData.gender">
                    <label class="form-check-label" for="female">Perempuan</label>
                  </div>
                </div>
                <div class="mb-3">
                  <label for="role" class="form-label">Role</label>
                  <select class="form-select" id="role" v-model="formData.role" required>
                    <option value="dokter">Dokter</option>
                    <option value="dokter spesialis">Dokter Spesialis</option>
                  </select>
                </div>
                <div class="modal-footer">
                  <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                  <button type="submit" class="btn btn-primary">
                    {{ isEditing ? 'Simpan Perubahan' : 'Tambah Dokter' }}
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
              <p>Apakah Anda yakin ingin menghapus dokter <strong>{{ selectedDoctor.nama }}</strong>?</p>
              <p class="text-danger">Tindakan ini tidak dapat dibatalkan.</p>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
              <button type="button" class="btn btn-danger" @click="deleteDoctor">Hapus</button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </admin-layout>
</template>

<script>
import AdminLayout from '@/components/AdminLayout.vue';
import { Modal } from 'bootstrap';

export default {
  name: "DokterView",
  components: { 
    AdminLayout 
  },
  data() {
    return {
      doctors: [
        { id: 1, nama: 'Dr. Bambang Sutejo', email: 'bambang@example.com', password: 'password123', phone: '08123456789', gender: 'Laki-laki', role: 'dokter' },
        { id: 2, nama: 'Dr. Ratna Dewi', email: 'ratna@example.com', password: 'password456', phone: '08567891234', gender: 'Perempuan', role: 'dokter spesialis' },
        { id: 3, nama: 'Dr. Ahmad Hidayat', email: 'ahmad@example.com', password: 'password789', phone: '08912345678', gender: 'Laki-laki', role: 'dokter' },
      ],
      formData: {
        id: null,
        nama: '',
        email: '',
        password: '',
        phone: '',
        gender: '',
        role: 'dokter'
      },
      selectedDoctor: {},
      isEditing: false,
      searchQuery: '',
      filterGender: '',
      currentPage: 1,
      itemsPerPage: 5,
      doctorModal: null,
      deleteModal: null
    };
  },
  computed: {
    filteredDoctors() {
      let filtered = this.doctors;
      
      // Search filter
      if (this.searchQuery) {
        const query = this.searchQuery.toLowerCase();
        filtered = filtered.filter(doctor => 
          doctor.nama.toLowerCase().includes(query) || 
          doctor.email.toLowerCase().includes(query) ||
          doctor.phone.includes(query)
        );
      }
      
      // Gender filter
      if (this.filterGender) {
        filtered = filtered.filter(doctor => doctor.gender === this.filterGender);
      }
      
      return filtered;
    },
    totalPages() {
      return Math.ceil(this.filteredDoctors.length / this.itemsPerPage);
    }
  },
  mounted() {
    this.initModals();
  },
  methods: {
    initModals() {
      // Inisialisasi Bootstrap modal dengan benar
      this.doctorModal = new Modal(this.$refs.doctorModal);
      this.deleteModal = new Modal(this.$refs.deleteModal);
    },
    
    showAddModal() {
      this.isEditing = false;
      this.resetForm();
      this.doctorModal.show();
    },
    
    showEditModal(doctor) {
      this.isEditing = true;
      this.formData = { ...doctor };
      this.formData.password = ''; // Clear password for security
      this.doctorModal.show();
    },
    
    resetForm() {
      this.formData = {
        id: null,
        nama: '',
        email: '',
        password: '',
        phone: '',
        gender: '',
        role: 'dokter'
      };
    },
    
    addDoctor() {
      // Generate a new ID (in a real app, this would be handled by the backend)
      const newId = Math.max(...this.doctors.map(doc => doc.id), 0) + 1;
      
      const newDoctor = {
        ...this.formData,
        id: newId
      };
      
      this.doctors.push(newDoctor);
      this.doctorModal.hide();
      this.resetForm();
      
      // Show success message (in a real app)
      alert('Dokter berhasil ditambahkan!');
    },
    
    updateDoctor() {
      const index = this.doctors.findIndex(doc => doc.id === this.formData.id);
      
      if (index !== -1) {
        // If password is empty, keep the old password
        if (!this.formData.password) {
          this.formData.password = this.doctors[index].password;
        }
        
        // Update the doctor data
        this.doctors[index] = { ...this.formData };
        this.doctorModal.hide();
        
        // Show success message (in a real app)
        alert('Data dokter berhasil diperbarui!');
      }
    },
    
    confirmDelete(doctor) {
      this.selectedDoctor = doctor;
      this.deleteModal.show();
    },
    
    deleteDoctor() {
      const index = this.doctors.findIndex(doc => doc.id === this.selectedDoctor.id);
      
      if (index !== -1) {
        this.doctors.splice(index, 1);
        this.deleteModal.hide();
        
        // Show success message (in a real app)
        alert('Dokter berhasil dihapus!');
      }
    }
  }
};
</script>

<style>
.password-mask {
  letter-spacing: 1px;
}

.table th {
  vertical-align: middle;
}

.badge {
  font-size: 0.85em;
}
</style>