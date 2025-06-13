<template>
  <app-layout activePage="penangananresource">
    <router-view />
  </app-layout>
</template>

<script>
import AppLayout from '@/components/AppLayout.vue';

export default {
  name: 'PenangananResource',
  components: {
    AppLayout
  }
};
</script>
