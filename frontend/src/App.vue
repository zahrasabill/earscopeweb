<script setup>
import { RouterLink, RouterView } from 'vue-router'
</script>

<template>
  <header v-if="$route.name != 'login'">
  <header v-if="$route.name != 'dashboard'"></header>
    <nav class="navbar navbar-expand-lg bg-body-tertiary">
  <div class="container-fluid">
    <a class="navbar-brand" href="#"></a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarSupportedContent">
      <ul class="navbar-nav me-auto mb-2 mb-lg-0">
        <li class="nav-item me-3">
          <RouterLink to="/">Home</RouterLink>
        </li>
        <li class="nav-item">
          <a href="#">Logout</a>
        </li>
      </ul>
    </div>
  </div>
</nav>
  </header>
  

  <RouterView />
</template>

<style scoped>

</style>
