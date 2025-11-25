<template>
    <v-btn :type="type" color="primary" variant="elevated" :disabled="disabled">
        <slot />
    </v-btn>
</template>

<script setup>
defineProps({
    // Default to `submit` so PrimaryButton inside forms submits by default
    type: { type: String, default: 'submit' },
    disabled: { type: Boolean, default: false },
});
</script>
