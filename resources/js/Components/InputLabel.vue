<script setup>
defineProps({
    value: {
        type: String,
    },
});
</script>

<template>
    <label
        style="color: var(--v-theme-on-surface); font-weight: 600; font-size: 0.875rem; display: inline-block;"
    >
        <span v-if="value">{{ value }}</span>
        <span v-else><slot /></span>
    </label>
</template>
