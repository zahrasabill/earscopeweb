<template>
    <admin-layout activePage="dokterresource" data-v-inspector="src/views/DokterResource.vue">
      <router-view data-v-inspector="src/views/DokterResource.vue"></router-view>
    </admin-layout>
  </template>
  
  <script>
  import AdminLayout from '@/components/AdminLayout.vue';
  
  export default {
    name: 'DokterResource',
    components: {
      AdminLayout
    }
  };
  </script>