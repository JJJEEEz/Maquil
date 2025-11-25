<template>
  <v-btn :type="type" color="primary" variant="elevated" :disabled="disabled">
    <slot />
  </v-btn>
</template>

<script setup>
defineProps({
  type: { type: String, default: 'button' },
  disabled: { type: Boolean, default: false },
});
</script>
