<template>
  <button
    :type="type"
    class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white rounded hover:bg-indigo-700 disabled:opacity-50"
    :disabled="disabled"
  >
    <slot />
  </button>
</template>

<script setup>
defineProps({
  type: { type: String, default: 'button' },
  disabled: { type: Boolean, default: false },
});
</script>
