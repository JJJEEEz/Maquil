<template>
    <v-btn :type="type" color="error" variant="elevated" :disabled="disabled">
        <slot />
    </v-btn>
</template>

<script setup>
defineProps({
    type: { type: String, default: 'button' },
    disabled: { type: Boolean, default: false },
});
</script>
