<template>
  <app-layout active-page="pasien">
      <div class="">
        <button class="btn btn-primary" @click="showAddModal">
          <i class="bi bi-plus-circle me-1"></i> Tambah Pasien
        </button>
        
        <div class="d-flex gap-2">
          <button class="btn btn-outline-secondary" @click="refreshData">
            <i class="bi bi-arrow-clockwise me-1"></i> Refresh
          </button>
        </div>
      </div>
  
      <!-- Search and Filter -->
      <div class="row mb-3">
        <div class="col-md-6">
          <div class="input-group">
            <span class="input-group-text"><i class="bi bi-search"></i></span>
            <input 
              type="text" 
              class="form-control" 
              placeholder="Cari berdasarkan nomor kode..." 
              v-model="searchQuery"
              @input="currentPage = 1"
            >
          </div>
        </div>
        <div class="col-md-3">
          <select class="form-select" v-model="filterGender" @change="currentPage = 1">
            <option value="">Semua Gender</option>
            <option value="Laki-laki">Laki-laki</option>
            <option value="Perempuan">Perempuan</option>
          </select>
        </div>
        <div class="col-md-3">
          <select class="form-select" v-model="itemsPerPage" @change="currentPage = 1">
            <option :value="5">5 per halaman</option>
            <option :value="10">10 per halaman</option>
            <option :value="25">25 per halaman</option>
            <option :value="50">50 per halaman</option>
          </select>
        </div>
      </div>
  
      <!-- Table -->
      <div class="table-responsive">
        <table class="table table-striped table-hover">
          <thead class="table-dark">
            <tr>
              <th @click="sortBy('id')" class="sortable">
                ID <i class="bi" :class="getSortIcon('id')"></i>
              </th>
              <th @click="sortBy('nomor')" class="sortable">
                Nomor Kode <i class="bi" :class="getSortIcon('nomor')"></i>
              </th>
              <th @click="sortBy('gender')" class="sortable">
                Gender <i class="bi" :class="getSortIcon('gender')"></i>
              </th>
              <th @click="sortBy('umur')" class="sortable">
                Umur <i class="bi" :class="getSortIcon('umur')"></i>
              </th>
              <th @click="sortBy('role')" class="sortable">
                Role <i class="bi" :class="getSortIcon('role')"></i>
              </th>
              <th>Aksi</th>
            </tr>
          </thead>
          <tbody>
            <tr v-if="loading">
              <td colspan="6" class="text-center py-4">
                <div class="spinner-border text-primary" role="status">
                  <span class="visually-hidden">Loading...</span>
                </div>
              </td>
            </tr>
            <tr v-else-if="paginatedPatients.length === 0">
              <td colspan="6" class="text-center py-4">
                <div class="alert alert-info mb-0">
                  <i class="bi bi-info-circle me-2"></i> Tidak ada data pasien ditemukan
                </div>
              </td>
            </tr>
            <tr v-for="pasien in paginatedPatients" :key="pasien.id">
              <td>{{ pasien.id }}</td>
              <td>{{ pasien.nomor }}</td>
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
                  <button class="btn btn-sm btn-success ms-1" @click="goToPemeriksaanView(pasien)" title="Detail Pemeriksaan">
                    <i class="bi bi-eye"></i>
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
  
      <!-- Pagination -->
      <div class="d-flex justify-content-between align-items-center mt-3">
        <div>
          Menampilkan {{ startIndex + 1 }}-{{ endIndex }} dari {{ filteredPatients.length }} pasien
        </div>
        <nav v-if="totalPages > 1">
          <ul class="pagination">
            <li class="page-item" :class="{ disabled: currentPage === 1 }">
              <a class="page-link" href="#" @click.prevent="currentPage = 1" title="Halaman Pertama">
                <i class="bi bi-chevron-double-left"></i>
              </a>
            </li>
            <li class="page-item" :class="{ disabled: currentPage === 1 }">
              <a class="page-link" href="#" @click.prevent="currentPage--" title="Halaman Sebelumnya">
                <i class="bi bi-chevron-left"></i>
              </a>
            </li>
            
            <li v-for="page in displayedPages" :key="page" class="page-item" :class="{ active: page === currentPage }">
              <a class="page-link" href="#" @click.prevent="currentPage = page">{{ page }}</a>
            </li>
            
            <li class="page-item" :class="{ disabled: currentPage === totalPages }">
              <a class="page-link" href="#" @click.prevent="currentPage++" title="Halaman Berikutnya">
                <i class="bi bi-chevron-right"></i>
              </a>
            </li>
            <li class="page-item" :class="{ disabled: currentPage === totalPages }">
              <a class="page-link" href="#" @click.prevent="currentPage = totalPages" title="Halaman Terakhir">
                <i class="bi bi-chevron-double-right"></i>
              </a>
            </li>
          </ul>
        </nav>
      </div>
  
      <!-- Add/Edit Modal -->
      <div class="modal fade" id="patientModal" tabindex="-1" ref="patientModal">
        <div class="modal-dialog">
          <div class="modal-content">
            <div class="modal-header" :class="isEditing ? 'bg-info text-white' : 'bg-primary text-white'">
              <h5 class="modal-title">{{ isEditing ? 'Edit Pasien' : 'Tambah Pasien' }}</h5>
              <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
              <form @submit.prevent="isEditing ? updatePatient() : addPatient()">
                <div class="mb-3">
                  <label for="nomor" class="form-label">Nomor Kode <span class="text-danger">*</span></label>
                  <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-hash"></i></span>
                    <input 
                      type="text" 
                      class="form-control" 
                      id="nomor" 
                      v-model="formData.nomor" 
                      required
                      :class="{ 'is-invalid': errors.nomor }"
                    >
                    <div class="invalid-feedback">{{ errors.nomor }}</div>
                  </div>
                  <small class="text-muted">Gunakan kode yang unik untuk setiap pasien</small>
                </div>
                
                <div class="mb-3">
                  <label for="password" class="form-label">Password <span v-if="!isEditing" class="text-danger">*</span></label>
                  <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-key"></i></span>
                    <input 
                      :type="showPassword ? 'text' : 'password'" 
                      class="form-control" 
                      id="password" 
                      v-model="formData.password" 
                      :required="!isEditing"
                      autocomplete="new-password"
                      :class="{ 'is-invalid': errors.password }"
                    >
                    <button 
                      class="btn btn-outline-secondary" 
                      type="button" 
                      @click="showPassword = !showPassword"
                    >
                      <i class="bi" :class="showPassword ? 'bi-eye-slash' : 'bi-eye'"></i>
                    </button>
                    <div class="invalid-feedback">{{ errors.password }}</div>
                  </div>
                  <small class="text-muted" v-if="isEditing">Biarkan kosong jika tidak ingin mengubah password</small>
                </div>
                
                <div class="mb-3">
                  <label class="form-label">Gender <span class="text-danger">*</span></label>
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
                  <label for="umur" class="form-label">Umur <span class="text-danger">*</span></label>
                  <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-calendar"></i></span>
                    <input 
                      type="number" 
                      class="form-control" 
                      id="umur" 
                      v-model="formData.umur" 
                      min="0" 
                      max="120" 
                      required
                      :class="{ 'is-invalid': errors.umur }"
                    >
                    <span class="input-group-text">tahun</span>
                    <div class="invalid-feedback">{{ errors.umur }}</div>
                  </div>
                </div>
                
                <div class="mb-3">
                  <label for="role" class="form-label">Role <span class="text-danger">*</span></label>
                  <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-person-badge"></i></span>
                    <select class="form-select" id="role" v-model="formData.role" required disabled>
                      <option value="pasien">Pasien</option>
                    </select>
                  </div>
                  <small class="text-muted">Role default untuk semua user yang ditambahkan</small>
                </div>
                
                <div class="modal-footer">
                  <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="bi bi-x-circle me-1"></i> Batal
                  </button>
                  <button type="submit" class="btn" :class="isEditing ? 'btn-info' : 'btn-primary'" :disabled="isSubmitting">
                    <i class="bi" :class="isEditing ? 'bi-save me-1' : 'bi-plus-circle me-1'"></i>
                    {{ isEditing ? 'Simpan Perubahan' : 'Tambah Pasien' }}
                    <span v-if="isSubmitting" class="spinner-border spinner-border-sm ms-1" role="status" aria-hidden="true"></span>
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
            <div class="modal-header bg-danger text-white">
              <h5 class="modal-title"><i class="bi bi-exclamation-triangle me-2"></i>Konfirmasi Hapus</h5>
              <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
              <p>Apakah Anda yakin ingin menghapus pasien dengan:</p>
              <ul class="list-group mb-3">
                <li class="list-group-item d-flex justify-content-between">
                  <span>ID:</span>
                  <strong>{{ selectedPatient.id }}</strong>
                </li>
                <li class="list-group-item d-flex justify-content-between">
                  <span>Nomor Kode:</span>
                  <strong>{{ selectedPatient.nomor }}</strong>
                </li>
                <li class="list-group-item d-flex justify-content-between">
                  <span>Gender:</span>
                  <strong>{{ selectedPatient.gender }}</strong>
                </li>
              </ul>
              <div class="alert alert-warning">
                <i class="bi bi-exclamation-circle me-2"></i>
                Tindakan ini tidak dapat dibatalkan. Semua data terkait pasien ini juga akan dihapus.
              </div>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                <i class="bi bi-x-circle me-1"></i> Batal
              </button>
              <button type="button" class="btn btn-danger" @click="deletePatient" :disabled="isDeleting">
                <i class="bi bi-trash me-1"></i> Hapus
                <span v-if="isDeleting" class="spinner-border spinner-border-sm ms-1" role="status" aria-hidden="true"></span>
              </button>
            </div>
          </div>
        </div>
      </div>
      
      <!-- Toast Notifications -->
      <div class="position-fixed bottom-0 end-0 p-3" style="z-index: 5">
        <div id="successToast" class="toast hide" role="alert" aria-live="assertive" aria-atomic="true" ref="successToast">
          <div class="toast-header bg-success text-white">
            <i class="bi bi-check-circle me-2"></i>
            <strong class="me-auto">Sukses</strong>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast" aria-label="Close"></button>
          </div>
          <div class="toast-body">
            {{ toastMessage }}
          </div>
        </div>
      </div>
  </app-layout>
</template>

<script>
import AppLayout from '@/components/AppLayout.vue';
import { Modal, Toast } from 'bootstrap';

export default {
  name: "PasienView",
  components: { 
    AppLayout 
  },
  data() {
    return {
      patients: [
        { 
          id: 1, 
          nomor: 'P001',  // Changed to proper nomor kode
          gender: 'Laki-laki', 
          umur: 35,
          role: 'pasien'
        },
        { 
          id: 2, 
          nomor: 'P002',  // Changed to proper nomor kode
          gender: 'Perempuan', 
          umur: 28,
          role: 'pasien'
        },
        { 
          id: 3, 
          nomor: 'P003',  // Changed to proper nomor kode
          gender: 'Laki-laki', 
          umur: 42,
          role: 'pasien'
        },
        { 
          id: 4, 
          nomor: 'P004',
          gender: 'Perempuan', 
          umur: 23,
          role: 'pasien'
        },
        { 
          id: 5, 
          nomor: 'P005',
          gender: 'Laki-laki', 
          umur: 55,
          role: 'pasien'
        },
        { 
          id: 6, 
          nomor: 'P006',
          gender: 'Perempuan', 
          umur: 37,
          role: 'pasien'
        },
      ],
      formData: {
        id: null,
        nomor: '',
        password: '',
        gender: '',
        umur: '',
        role: 'pasien'
      },
      errors: {
        nomor: '',
        password: '',
        umur: ''
      },
      selectedPatient: {},
      isEditing: false,
      isSubmitting: false,
      isDeleting: false,
      loading: false,
      showPassword: false,
      searchQuery: '',
      filterGender: '',
      currentPage: 1,
      itemsPerPage: 5,
      patientsPerPageOptions: [5, 10, 25, 50],
      patientModal: null,
      deleteModal: null,
      successToast: null,
      toastMessage: '',
      sortField: 'id',
      sortDirection: 'asc'
    };
  },
  computed: {
    filteredPatients() {
      let filtered = [...this.patients];
      
      // Search filter - now searches for nomor kode
      if (this.searchQuery) {
        const query = this.searchQuery.toLowerCase();
        filtered = filtered.filter(patient => 
          patient.nomor.toLowerCase().includes(query)
        );
      }
      
      // Gender filter
      if (this.filterGender) {
        filtered = filtered.filter(patient => patient.gender === this.filterGender);
      }
      
      // Sort patients
      filtered.sort((a, b) => {
        let modifier = this.sortDirection === 'asc' ? 1 : -1;
        
        // Handle numeric fields
        if (['id', 'umur'].includes(this.sortField)) {
          return modifier * (a[this.sortField] - b[this.sortField]);
        }
        
        // Handle string fields
        if (a[this.sortField] < b[this.sortField]) return -1 * modifier;
        if (a[this.sortField] > b[this.sortField]) return 1 * modifier;
        return 0;
      });
      
      return filtered;
    },
    paginatedPatients() {
      const start = (this.currentPage - 1) * this.itemsPerPage;
      const end = start + this.itemsPerPage;
      return this.filteredPatients.slice(start, end);
    },
    totalPages() {
      return Math.ceil(this.filteredPatients.length / this.itemsPerPage);
    },
    // For pagination display
    displayedPages() {
      let pages = [];
      const maxPagesToShow = 5;
      
      if (this.totalPages <= maxPagesToShow) {
        for (let i = 1; i <= this.totalPages; i++) {
          pages.push(i);
        }
      } else {
        // Always show current page and some pages before and after it
        let startPage = Math.max(1, this.currentPage - Math.floor(maxPagesToShow / 2));
        let endPage = Math.min(this.totalPages, startPage + maxPagesToShow - 1);
        
        // Adjust if we're at the end of the range
        if (endPage - startPage + 1 < maxPagesToShow) {
          startPage = Math.max(1, endPage - maxPagesToShow + 1);
        }
        
        for (let i = startPage; i <= endPage; i++) {
          pages.push(i);
        }
      }
      
      return pages;
    },
    startIndex() {
      return (this.currentPage - 1) * this.itemsPerPage;
    },
    endIndex() {
      const end = this.startIndex + this.itemsPerPage;
      return Math.min(end, this.filteredPatients.length);
    }
  },
  mounted() {
    this.initModals();
  },
  methods: {
    initModals() {
      // Initialize Bootstrap modals and toast
      this.patientModal = new Modal(this.$refs.patientModal);
      this.deleteModal = new Modal(this.$refs.deleteModal);
      this.successToast = new Toast(this.$refs.successToast);
    },
    
    showAddModal() {
      this.isEditing = false;
      this.resetForm();
      this.patientModal.show();
    },
    
    showEditModal(patient) {
      this.isEditing = true;
      this.resetErrors();
      this.formData = { ...patient };
      this.formData.password = ''; // Clear password for security
      this.patientModal.show();
    },
    
    resetForm() {
      this.formData = {
        id: null,
        nomor: '',
        password: '',
        gender: '',
        umur: '',
        role: 'pasien'
      };
      this.resetErrors();
    },
    
    resetErrors() {
      this.errors = {
        nomor: '',
        password: '',
        umur: ''
      };
    },
    
    validateForm() {
      this.resetErrors();
      let isValid = true;
      
      // Validate nomor kode
      if (!this.formData.nomor) {
        this.errors.nomor = 'Nomor kode wajib diisi';
        isValid = false;
      } else if (
        !this.isEditing && 
        this.patients.some(p => p.nomor === this.formData.nomor)
      ) {
        this.errors.nomor = 'Nomor kode sudah digunakan';
        isValid = false;
      }
      
      // Validate password
      if (!this.isEditing && !this.formData.password) {
        this.errors.password = 'Password wajib diisi';
        isValid = false;
      } else if (this.formData.password && this.formData.password.length < 6) {
        this.errors.password = 'Password minimal 6 karakter';
        isValid = false;
      }
      
      // Validate umur
      if (!this.formData.umur) {
        this.errors.umur = 'Umur wajib diisi';
        isValid = false;
      } else if (this.formData.umur < 0 || this.formData.umur > 120) {
        this.errors.umur = 'Umur harus antara 0-120 tahun';
        isValid = false;
      }
      
      return isValid;
    },
    
    async addPatient() {
      if (!this.validateForm()) return;
      
      this.isSubmitting = true;
      
      try {
        // Simulate API request delay
        await new Promise(resolve => setTimeout(resolve, 800));
        
        // Generate a new ID (in a real app, this would be handled by the backend)
        const newId = Math.max(...this.patients.map(pat => pat.id), 0) + 1;
        
        const newPatient = {
          ...this.formData,
          id: newId
        };
        
        this.patients.push(newPatient);
        this.patientModal.hide();
        this.resetForm();
        
        // Show success message
        this.showToast('Pasien berhasil ditambahkan!');
      } catch (error) {
        console.error('Error adding patient:', error);
        alert('Terjadi kesalahan saat menambahkan pasien');
      } finally {
        this.isSubmitting = false;
      }
    },
    
    async updatePatient() {
      if (!this.validateForm()) return;
      
      this.isSubmitting = true;
      
      try {
        // Simulate API request delay
        await new Promise(resolve => setTimeout(resolve, 800));
        
        const index = this.patients.findIndex(pat => pat.id === this.formData.id);
        
        if (index !== -1) {
          // If password is empty, keep the old password
          if (!this.formData.password) {
            const { password, ...formWithoutPassword } = this.formData;
            this.patients[index] = { 
              ...this.patients[index],
              ...formWithoutPassword
            };
          } else {
            // Update the patient data with new password
            this.patients[index] = { ...this.formData };
          }
          
          this.patientModal.hide();
          
          // Show success message
          this.showToast('Data pasien berhasil diperbarui!');
        }
      } catch (error) {
        console.error('Error updating patient:', error);
        alert('Terjadi kesalahan saat memperbarui data pasien');
      } finally {
        this.isSubmitting = false;
      }
    },
    
    confirmDelete(patient) {
      this.selectedPatient = patient;
      this.deleteModal.show();
    },
    
    async deletePatient() {
      this.isDeleting = true;
      
      try {
        // Simulate API request delay
        await new Promise(resolve => setTimeout(resolve, 800));
        
        const index = this.patients.findIndex(pat => pat.id === this.selectedPatient.id);
        
        if (index !== -1) {
          this.patients.splice(index, 1);
          this.deleteModal.hide();
          
          // Show success message
          this.showToast('Pasien berhasil dihapus!');
        }
      } catch (error) {
        console.error('Error deleting patient:', error);
        alert('Terjadi kesalahan saat menghapus pasien');
      } finally {
        this.isDeleting = false;
      }
    },
    
    goToPemeriksaanView(pasien) {
      // Save selected patient to store
      this.$store.commit('setPasienTerpilih', pasien);
      
      // Navigate to pemeriksaan view
      this.$router.push({
        name: 'Pemeriksaan',
        params: { id: pasien.id }
      });
    },
    
    sortBy(field) {
      if (this.sortField === field) {
        // Toggle sort direction if clicking on the same field
        this.sortDirection = this.sortDirection === 'asc' ? 'desc' : 'asc';
      } else {
        // Default to ascending order for a new sort field
        this.sortField = field;
        this.sortDirection = 'asc';
      }
    },
    
    getSortIcon(field) {
      if (this.sortField !== field) {
        return 'bi-arrow-down-up text-muted';
      }
      return this.sortDirection === 'asc' ? 'bi-sort-down-alt' : 'bi-sort-up';
    },
    
    async refreshData() {
      this.loading = true;
      
      try {
        // Simulate API request delay
        await new Promise(resolve => setTimeout(resolve, 1000));
        // In a real app, this would fetch the latest data from the server
        
        // Reset pagination to first page
        this.currentPage = 1;
        
        this.showToast('Data pasien berhasil disegarkan!');
      } catch (error) {
        console.error('Error refreshing data:', error);
        alert('Terjadi kesalahan saat menyegarkan data');
      } finally {
        this.loading = false;
      }
    },
    
    // exportToExcel() {
    //   // This would be implemented with a library like ExcelJS or SheetJS
    //   // For now, just show a message
    //   this.showToast('File Excel berhasil diunduh!');
    // },
    
    showToast(message) {
      this.toastMessage = message;
      this.successToast.show();
    }
  }
};
</script>

<style scoped>
.sortable {
  cursor: pointer;
  user-select: none;
}

.sortable:hover {
  background-color: rgba(0, 0, 0, 0.6);
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

/* Animation for loading state */
.spinner-border {
  width: 1rem;
  height: 1rem;
}
</style>