<template>
  <AppLayout v-if="showLayout">
    <router-view />
  </AppLayout>
  <router-view v-else />
  <ToastContainer />
</template>

<script setup lang="ts">
import { computed } from 'vue';
import { useRoute } from 'vue-router';
import AppLayout from '@/core/layouts/AppLayout.vue';
import ToastContainer from '@/core/components/ToastContainer.vue';

const route = useRoute();

const showLayout = computed(() => {
  return route.name !== 'login';
});
</script>
