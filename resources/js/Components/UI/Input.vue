<template>
  <div>
    <label v-if="label" class="block text-sm font-medium text-gray-700">{{ label }}</label>
    <input
      :type="type"
      :value="modelValue"
      @input="$emit('update:modelValue', $event.target.value)"
      class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
    />
    <p v-if="error" class="text-red-600 text-sm mt-1">{{ error }}</p>
  </div>
</template>

<script setup>
defineProps({
  modelValue: [String, Number],
  label: String,
  type: { type: String, default: 'text' },
  error: String,
});
</script>
