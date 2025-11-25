<template>
    <v-btn :type="type" variant="outlined" color="primary" :disabled="disabled">
        <slot />
    </v-btn>
</template>

<script setup>
defineProps({
    type: { type: String, default: 'button' },
    disabled: { type: Boolean, default: false },
});
</script>
