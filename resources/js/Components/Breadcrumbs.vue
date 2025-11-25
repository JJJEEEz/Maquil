<script setup>
import { Link } from '@inertiajs/vue3';
const props = defineProps({
  items: { type: Array, default: () => [] },
  separator: { type: String, default: '/' },
});
</script>

<template>
  <nav class="breadcrumbs-component" aria-label="Breadcrumb">
    <ol>
      <li v-for="(item, index) in items" :key="index" class="breadcrumb-item">
        <template v-if="item.href && index !== items.length - 1">
          <Link :href="item.href" class="breadcrumb-link">{{ item.text }}</Link>
        </template>
        <template v-else>
          <span class="breadcrumb-current">{{ item.text }}</span>
        </template>
        <span v-if="index !== items.length - 1" class="breadcrumb-sep">{{ separator }}</span>
      </li>
    </ol>
  </nav>
</template>

<style scoped>
.breadcrumbs-component ol{ list-style:none; margin:0; padding:0; display:flex; gap:8px; align-items:center; font-size:0.95rem; }
.breadcrumb-link{ color:var(--v-theme-primary, #4F46E5); text-decoration:none; }
.breadcrumb-link:hover{ text-decoration:underline; }
.breadcrumb-sep{ color:var(--v-theme-on-surface, rgba(0,0,0,0.45)); }
.breadcrumb-current{ font-weight:600; color:var(--v-theme-on-surface, rgba(0,0,0,0.75)); }
</style>
