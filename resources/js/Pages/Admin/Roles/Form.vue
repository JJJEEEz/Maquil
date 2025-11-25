<template>
  <div class="p-6">
    <h1 class="text-lg font-semibold mb-4">{{ isEdit ? 'Editar rol' : 'Crear rol' }}</h1>
    <form @submit.prevent="submit">
      <Input v-model="form.name" label="Nombre" :error="errors.name" />
      <div class="mt-4">
        <Button type="submit" :disabled="processing">Guardar</Button>
      </div>
    </form>
  </div>
</template>

<script setup>
import { useForm } from '@inertiajs/vue3';
import Input from '@/Components/UI/Input.vue';
import Button from '@/Components/UI/Button.vue';

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
</script>
