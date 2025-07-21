<template>
  <app-layout activePage="profileresource">
    <router-view />
  </app-layout>
</template>

<script>
import AppLayout from '@/components/AppLayout.vue';

export default {
  name: 'ProfileResource',
  components: {
    AppLayout
  }
};
</script>
