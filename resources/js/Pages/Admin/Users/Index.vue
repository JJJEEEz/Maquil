<template>
  <div class="p-6">
    <div class="flex justify-between items-center mb-4">
      <h1 class="text-xl font-semibold">Usuarios</h1>
      <Link :href="route('admin.users.create')" class="text-white bg-indigo-600 px-3 py-1 rounded">Crear</Link>
    </div>

    <table class="min-w-full bg-white">
      <thead>
        <tr>
          <th class="px-4 py-2">ID</th>
          <th class="px-4 py-2">Nombre</th>
          <th class="px-4 py-2">Email</th>
          <th class="px-4 py-2">Roles</th>
          <th class="px-4 py-2">Acciones</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="user in users.data" :key="user.id" class="border-t">
          <td class="px-4 py-2">{{ user.id }}</td>
          <td class="px-4 py-2">{{ user.name }}</td>
          <td class="px-4 py-2">{{ user.email }}</td>
          <td class="px-4 py-2">{{ user.roles.map(r => r.name).join(', ') }}</td>
          <td class="px-4 py-2">
            <Link :href="route('admin.users.edit', user.id)" class="mr-2 text-indigo-600">Editar</Link>
            <button @click="remove(user.id)" class="text-red-600">Eliminar</button>
          </td>
        </tr>
      </tbody>
    </table>

    <div class="mt-4">
      <button v-if="users.prev_page_url" @click="go(users.prev_page_url)" class="mr-2">Anterior</button>
      <button v-if="users.next_page_url" @click="go(users.next_page_url)">Siguiente</button>
    </div>
  </div>
</template>

<script setup>
import { Link, usePage } from '@inertiajs/vue3';
import { Inertia } from '@inertiajs/inertia';

const props = defineProps({ users: Object });
const users = props.users;

function remove(id) {
  if (!confirm('Eliminar usuario?')) return;
  Inertia.delete(route('admin.users.destroy', id));
}

function go(url) {
  Inertia.get(url.replace(window.location.origin, ''));
}
</script>
