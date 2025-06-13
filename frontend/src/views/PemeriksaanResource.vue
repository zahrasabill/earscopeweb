<template>
  <app-layout activePage="pemeriksaanresource">
    <router-view />
  </app-layout>
</template>

<script>
import AppLayout from '@/components/AppLayout.vue';

export default {
  name: 'PemeriksaanResource',
  components: {
    AppLayout
  }
};
</script>
