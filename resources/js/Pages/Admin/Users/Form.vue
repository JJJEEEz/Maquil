
<template>
  <v-card class="p-6">
    <v-card-title>
      <h1 class="text-lg font-semibold mb-0">{{ isEdit ? 'Editar usuario' : 'Crear usuario' }}</h1>
    </v-card-title>

    <v-card-text>
      <form @submit.prevent="submit">
        <Input v-model="form.name" label="Nombre" :error="!!errors.name" :error-messages="toArray(errors.name)" />
        <Input v-model="form.email" label="Email" type="email" :error="!!errors.email" :error-messages="toArray(errors.email)" />
        <Input v-model="form.password" label="Password" type="password" :error="!!errors.password" :error-messages="toArray(errors.password)" />

        <div class="mt-4">
          <label class="block font-medium mb-1">Roles</label>
          <v-row>
            <v-col cols="12" sm="6" md="4" v-for="role in roles" :key="role">
              <v-checkbox
                :label="role"
                :value="role"
                v-model="form.roles"
              />
            </v-col>
          </v-row>
        </div>

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
import { Inertia } from '@inertiajs/inertia';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

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

function toArray(val) {
  if (!val) return [];
  return Array.isArray(val) ? val : [val];
}
</script>

<script>
export default { layout: AuthenticatedLayout };
</script>
