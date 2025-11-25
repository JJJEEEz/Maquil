<template>
  <div class="p-6">
    <h1 class="text-lg font-semibold mb-4">{{ isEdit ? 'Editar usuario' : 'Crear usuario' }}</h1>
    <form @submit.prevent="submit">
      <Input v-model="form.name" label="Nombre" :error="errors.name" />
      <Input v-model="form.email" label="Email" type="email" :error="errors.email" />
      <Input v-model="form.password" label="Password" type="password" :error="errors.password" />

      <div class="mt-4">
        <label class="block font-medium mb-1">Roles</label>
        <div v-for="role in roles" :key="role">
          <label>
            <input type="checkbox" :value="role" v-model="form.roles" /> {{ role }}
          </label>
        </div>
      </div>

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
import { Inertia } from '@inertiajs/inertia';

const props = defineProps({ user: Object, roles: Array });

const isEdit = !!props.user;

const form = useForm({
  name: props.user ? props.user.name : '',
  email: props.user ? props.user.email : '',
  password: '',
  roles: props.user ? props.user.roles.map(r => r.name) : [],
});

const processing = form.processing;
const errors = form.errors;

function submit() {
  if (isEdit) {
    form.post(route('admin.users.update', props.user.id), { _method: 'put' });
  } else {
    form.post(route('admin.users.store'));
  }
}

const roles = props.roles || [];
</script>
