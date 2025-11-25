<template>
  <div>
    <v-text-field
      v-model="internalValue"
      :label="label"
      :type="type"
      :error="!!error"
      :error-messages="error ? [error] : []"
      density="comfortable"
      color="primary"
      variant="outlined"
      class="w-full"
      @input="onInput"
    />
  </div>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
  modelValue: [String, Number],
  label: String,
  type: { type: String, default: 'text' },
  error: String,
});

const emit = defineEmits(['update:modelValue']);

const internalValue = computed({
  get() {
    return props.modelValue;
  },
  set(val) {
    emit('update:modelValue', val);
  },
});

function onInput(v) {
  // v-text-field emits the value directly
  emit('update:modelValue', v);
}
</script>