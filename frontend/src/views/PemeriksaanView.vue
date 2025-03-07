<template>
    <admin-layout active-page="pemeriksaan">
    <div>
      <h2>Pemeriksaan View</h2>
      
    </div>
    </admin-layout>
  </template>
  
  <script>
import AdminLayout from '@/components/AdminLayout.vue';
import { Modal } from 'bootstrap';

export default {
  name: "PemeriksaanView",
  components: {
    AdminLayout
  }
  };
  </script>
  
  <style scoped>
  h1 {
    color: black;
  }
  </style>
  