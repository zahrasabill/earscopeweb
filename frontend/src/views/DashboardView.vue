<template>
  <app-layout activePage="dashboard">
    <div class="row">
      <div class="col-md-4">
        <div class="card mb-3 shadow-sm">
          <div class="card-body">
            <div class="d-flex justify-content-between align-items-center">
              <div>
                <h5 class="card-title text-success">Dokter</h5>
                <h2 class="mb-0">10</h2>
              </div>
              <div class="rounded-circle bg-success bg-opacity-10 p-3">
                <i class="bi bi-person-badge text-success fs-3"></i>
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
                <h5 class="card-title text-primary">Pasien</h5>
                <h2 class="mb-0">50</h2>
              </div>
              <div class="rounded-circle bg-primary bg-opacity-10 p-3">
                <i class="bi bi-people text-primary fs-3"></i>
              </div>
            </div>
            <p class="card-text mt-3 text-muted">Total pasien terdaftar</p>
          </div>
        </div>
      </div>
    </div>
  </app-layout>
</template>
<script>
import AppLayout from '@/components/AppLayout.vue';

export default {
  name: "DashboardView",
  components: {
    AppLayout
  }
};
</script>