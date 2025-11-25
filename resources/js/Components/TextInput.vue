<script setup>
import { ref, onMounted } from 'vue';

const props = defineProps({
  modelValue: [String, Number],
  type: { type: String, default: 'text' },
  label: { type: String, default: '' },
  autofocus: { type: Boolean, default: false },
  error: { type: Boolean, default: false },
  errorMessages: { type: [Array, String], default: () => [] },
});

const emit = defineEmits(['update:modelValue']);

const inputRef = ref(null);

onMounted(() => {
  if (props.autofocus) {
    // v-text-field supports autofocus, but ensure focus if requested
    inputRef.value?.focus?.();
  }
});

function updateValue(v) {
  emit('update:modelValue', v);
}

defineExpose({ focus: () => inputRef.value?.focus?.() });
</script>

<template>
  <v-text-field
    :model-value="props.modelValue"
    @update:model-value="updateValue"
    ref="inputRef"
    :type="props.type"
    :label="props.label"
    :autofocus="props.autofocus"
    variant="outlined"
    density="comfortable"
    color="primary"
    class="themed-input"
    :error="props.error"
    :error-messages="Array.isArray(props.errorMessages) ? props.errorMessages : (props.errorMessages ? [props.errorMessages] : [])"
  />
</template>

<style scoped>
/* Keep small focus hint to align with theme */
.themed-input .v-field__outline {
  transition: box-shadow 0.12s ease;
}
</style>