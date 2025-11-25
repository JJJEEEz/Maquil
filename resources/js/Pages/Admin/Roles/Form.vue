<template>
  <v-card class="p-6">
    <v-card-title>
      <h1 class="text-lg font-semibold mb-0">{{ isEdit ? 'Editar rol' : 'Crear rol' }}</h1>
    </v-card-title>

    <v-card-text>
      <form @submit.prevent="submit">
        <Input v-model="form.name" label="Nombre" :error="!!errors.name" :error-messages="toArray(errors.name)" />

        <v-card-actions class="mt-4">
          <Button type="submit" :disabled="processing">Guardar</Button>
        </v-card-actions>
      </form>
    </v-card-text>
  </v-card>
</template>

<script setup>
import { useForm } from '@inertiajs/vue3';
import Input from '@/Components/UI/Input.vue';
import Button from '@/Components/UI/Button.vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

const props = defineProps({ role: Object });

const isEdit = !!props.role;

const form = useForm({
  name: props.role ? props.role.name : '',
});

const processing = form.processing;
const errors = form.errors;

function submit() {
  if (isEdit) {
    form.post(route('admin.roles.update', props.role.id), { _method: 'put' });
  } else {
    form.post(route('admin.roles.store'));
  }
}

function toArray(val) {
  if (!val) return [];
  return Array.isArray(val) ? val : [val];
}
</script>

<script>
export default { layout: AuthenticatedLayout };
</script>
