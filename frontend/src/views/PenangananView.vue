<template>
  <app-layout activePage="penanganan">
    <router-view />
  </app-layout>
</template>

<script>
import AppLayout from '@/components/AppLayout.vue';

export default {
  name: 'PenangananView',
  components: {
    AppLayout
  }
};
</script>
