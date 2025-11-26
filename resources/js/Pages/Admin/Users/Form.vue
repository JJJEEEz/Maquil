<script setup>
import { useForm } from '@inertiajs/vue3';
import { computed } from 'vue';
import Input from '@/Components/UI/Input.vue';
import Button from '@/Components/UI/Button.vue';
import { Inertia } from '@inertiajs/inertia';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Breadcrumbs from '@/Components/Breadcrumbs.vue';
import { Head } from '@inertiajs/vue3';

const props = defineProps({ user: Object, roles: Array, permissions: Array, userPermissions: Array });

const isEdit = !!props.user;

const form = useForm({
  name: props.user ? props.user.name : '',
  email: props.user ? props.user.email : '',
  password: '',
  roles: props.user ? props.user.roles.map(r => r.name) : [],
  permissions: props.userPermissions ? [...props.userPermissions] : [],
});

const processing = form.processing;
const errors = form.errors;

function submit() {
  if (isEdit) {
    form.put(route('admin.users.update', props.user.id));
  } else {
    form.post(route('admin.users.store'));
  }
}

const roles = props.roles || [];

const selectedRole = computed({
  get() {
    return Array.isArray(form.roles) && form.roles.length ? form.roles[0] : null;
  },
  set(val) {
    form.roles = val ? [val] : [];
  },
});

// group permissions by resource (split by '.')
const groupedPermissions = (() => {
  const list = props.permissions || [];
  const groups = {};
  list.forEach((p) => {
    const name = p.name || p;
    const parts = String(name).split('.');
    const resource = parts[0] || 'otros';
    groups[resource] = groups[resource] || [];
    groups[resource].push({ name, id: p.id });
  });
  return groups;
})();

function toArray(val) {
  if (!val) return [];
  return Array.isArray(val) ? val : [val];
}
</script>

<script>
export default { layout: AuthenticatedLayout };
</script>



<template>
  <Head title="Usuarios" />
  <v-card class="p-6">
    <v-card-title>
      <h1 class="text-lg font-semibold mb-0">{{ isEdit ? 'Editar usuario' : 'Crear usuario' }}</h1>
      <Breadcrumbs :items="[{ text: 'Panel ', href: route('welcome') }, { text: 'Usuarios', href: route('admin.users.index') }, { text: isEdit ? 'Editar usuario' : 'Crear usuario' }]" />
    </v-card-title>

    <v-card-text>
      <form @submit.prevent="submit">
        <Input v-model="form.name" label="Nombre" :error="!!errors.name" :error-messages="toArray(errors.name)" />
        <Input v-model="form.email" label="Email" type="email" :error="!!errors.email" :error-messages="toArray(errors.email)" />
        <Input v-model="form.password" label="Password" type="password" :error="!!errors.password" :error-messages="toArray(errors.password)" />

        <div class="mt-4">
          <label class="block font-medium mb-1">Rol</label>
          <v-radio-group v-model="selectedRole">
            <v-row>
              <v-col cols="12" sm="6" md="4" v-for="role in roles" :key="role">
                <v-radio :label="role" :value="role" />
              </v-col>
            </v-row>
          </v-radio-group>
        </div>
        <v-card-actions class="mt-4">
          <Button type="submit" :disabled="processing">Guardar</Button>
        </v-card-actions>
      </form>
    </v-card-text>
  </v-card>
</template>