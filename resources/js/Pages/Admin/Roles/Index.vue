<template>
  <div class="p-6">
    <div class="flex justify-between items-center mb-4">
      <h1 class="text-xl font-semibold">Roles</h1>
      <Link :href="route('admin.roles.create')" class="text-white bg-indigo-600 px-3 py-1 rounded">Crear</Link>
    </div>

    <table class="min-w-full bg-white">
      <thead>
        <tr>
          <th class="px-4 py-2">ID</th>
          <th class="px-4 py-2">Nombre</th>
          <th class="px-4 py-2">Acciones</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="role in roles.data" :key="role.id" class="border-t">
          <td class="px-4 py-2">{{ role.id }}</td>
          <td class="px-4 py-2">{{ role.name }}</td>
          <td class="px-4 py-2">
            <Link :href="route('admin.roles.edit', role.id)" class="mr-2 text-indigo-600">Editar</Link>
            <button @click="remove(role.id)" class="text-red-600">Eliminar</button>
          </td>
        </tr>
      </tbody>
    </table>

    <div class="mt-4">
      <button v-if="roles.prev_page_url" @click="go(roles.prev_page_url)" class="mr-2">Anterior</button>
      <button v-if="roles.next_page_url" @click="go(roles.next_page_url)">Siguiente</button>
    </div>
  </div>
</template>

<script setup>
import { Link } from '@inertiajs/vue3';
import { Inertia } from '@inertiajs/inertia';

const props = defineProps({ roles: Object });
const roles = props.roles;

function remove(id) {
  if (!confirm('Eliminar rol?')) return;
  Inertia.delete(route('admin.roles.destroy', id));
}

function go(url) {
  Inertia.get(url.replace(window.location.origin, ''));
}
</script>
