<template>
  <admin-layout activePage="dashboard">
    <div class="row">
      <div class="col-md-4">
        <div class="card mb-3 shadow-sm">
          <div class="card-body">
            <div class="d-flex justify-content-between align-items-center">
              <div>
                <h5 class="card-title text-primary">Dokter</h5>
                <h2 class="mb-0">10</h2>
              </div>
              <div class="rounded-circle bg-primary bg-opacity-10 p-3">
                <i class="bi bi-person-badge text-primary fs-3"></i>
              </div>
            </div>
            <p class="card-text mt-3 text-muted">Total dokter terdaftar</p>
          </div>
        </div>
      </div>
      <div class="col-md-4">
        <div class="card mb-3 shadow-sm">
          <div class="card-body">
            <div class="d-flex justify-content-between align-items-center">
              <div>
                <h5 class="card-title text-success">Pasien</h5>
                <h2 class="mb-0">50</h2>
              </div>
              <div class="rounded-circle bg-success bg-opacity-10 p-3">
                <i class="bi bi-people text-success fs-3"></i>
              </div>
            </div>
            <p class="card-text mt-3 text-muted">Total pasien terdaftar</p>
          </div>
        </div>
      </div>
    </div>
    
    <div class="row mt-4">
      <div class="col-md-8">
        <div class="card shadow-sm">
          <div class="card-header bg-white">
            <h5 class="card-title mb-0">Statistik Kunjungan</h5>
          </div>
          <div class="card-body">
            <div class="chart-container" style="height: 300px;">
              <!-- Chart placeholder -->
              <div class="d-flex justify-content-center align-items-center h-100 bg-light">
                <span class="text-muted">Area Grafik Statistik Kunjungan</span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </admin-layout>
</template>

<script>
import AdminLayout from '@/components/AdminLayout.vue';

export default {
  name: "DashboardView",
  components: {
    AdminLayout
  }
};
</script>