<script setup>
import { computed } from 'vue';
import { useForm } from '@inertiajs/vue3';
import Input from '@/Components/UI/Input.vue';
import Button from '@/Components/UI/Button.vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Breadcrumbs from '@/Components/Breadcrumbs.vue';
import { Head } from '@inertiajs/vue3'; 

const props = defineProps({ role: Object, permissions: Array, rolePermissions: Array });

const isEdit = !!props.role;

// permissions prop: array of permission objects { id, name }
// rolePermissions: array of permission names assigned to the role

const form = useForm({
  name: props.role ? props.role.name : '',
  permissions: props.rolePermissions ? [...props.rolePermissions] : [],
});

const processing = form.processing;
const errors = form.errors;

function submit() {
  if (isEdit) {
    form.put(route('admin.roles.update', props.role.id));
  } else {
    form.post(route('admin.roles.store'));
  }
}

function toArray(val) {
  if (!val) return [];
  return Array.isArray(val) ? val : [val];
}

// group permissions by resource (split by '.')
const groupedPermissions = computed(() => {
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
});
</script>

<script>
export default { layout: AuthenticatedLayout };
</script>

<template>
  <Head title="Roles" />
  <v-card class="p-6">
    <v-card-title>
      <h1 class="text-lg font-semibold mb-0">{{ isEdit ? 'Editar rol' : 'Crear rol' }}</h1>
      <Breadcrumbs :items="[{ text: 'Panel ', href: route('welcome') }, { text: 'Roles', href: route('admin.roles.index') }, { text: isEdit ? 'Editar rol' : 'Crear rol' }]" />
    </v-card-title>

    <v-card-text>
      <form @submit.prevent="submit">
        <Input v-model="form.name" label="Nombre" :error="!!errors.name" :error-messages="toArray(errors.name)" />

        <div class="mt-6">
          <h3 class="font-medium mb-2">Permisos</h3>
          <div v-if="Object.keys(groupedPermissions).length === 0" class="text-sm text-gray-500">No hay permisos disponibles.</div>
          <div v-else>
            <div v-for="(perms, resource) in groupedPermissions" :key="resource" class="mb-4">
              <h4 class="text-sm font-semibold capitalize mb-2">{{ resource }}</h4>
              <div class="grid grid-cols-2 gap-2">
                <v-checkbox
                  v-for="p in perms"
                  :key="p.name"
                  v-model="form.permissions"
                  :label="p.name.replace(resource + '.', '')"
                  :value="p.name"
                  hide-details
                />
              </div>
            </div>
          </div>
          <div v-if="errors.permissions" class="text-red-600 text-sm mt-2">{{ toArray(errors.permissions).join(', ') }}</div>
        </div>

        <v-card-actions class="mt-4">
          <Button type="submit" :disabled="processing">Guardar</Button>
        </v-card-actions>
      </form>
    </v-card-text>
  </v-card>
</template>
