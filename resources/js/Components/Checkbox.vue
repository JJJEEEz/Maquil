<script setup>
import { computed } from 'vue';

const emit = defineEmits(['update:checked']);

const props = defineProps({
    checked: {
        type: [Array, Boolean],
        required: true,
    },
    value: {
        default: null,
    },
    label: {
        type: String,
        default: null,
    },
    name: {
        type: String,
        default: null,
    },
});

const proxyChecked = computed({
    get() {
        return props.checked;
    },
    set(val) {
        emit('update:checked', val);
    },
});
</script>

<template>
    <v-checkbox
        v-model="proxyChecked"
        :value="value"
        :label="label"
        :name="name"
        color="primary"
        variant="rounded"
        density="compact"
        class="pa-0"
    />
</template>
