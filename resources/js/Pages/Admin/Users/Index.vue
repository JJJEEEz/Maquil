
<script setup>
import { ref } from 'vue';
import { Link } from '@inertiajs/vue3';
import { Inertia } from '@inertiajs/inertia';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Breadcrumbs from '@/Components/Breadcrumbs.vue';

const props = defineProps({ users: Object });
const users = props.users;

const page = ref(users.current_page || 1);

const headers = [
  { title: 'ID', value: 'id', width: 50, align: 'center' },
  { title: 'Nombre', value: 'name' },
  { title: 'Email', value: 'email' },
  { title: 'Roles', value: 'roles', sortable: false },
  { title: 'Acciones', value: 'actions', sortable: false, width: 200, align: 'center'  },
];

function remove(id) {
  if (!confirm('Eliminar usuario?')) return;
  Inertia.delete(route('admin.users.destroy', id), {}, { preserveState: true });
}

function onPageChange(value) {
  page.value = value;
  Inertia.get(route('admin.users.index'), { page: value }, { preserveState: true, replace: true });
}
</script>

<script>
export default { layout: AuthenticatedLayout };
</script>



<template>
  <v-card class="p-6">
      <v-card-title class="d-flex justify-space-between align-center">
      <v-col cols="12" md="6">
        <v-row>
          <div>
            <h1 class="text-xl mb-1 font-semibold">Usuarios</h1>
            <Breadcrumbs :items="[{ text: 'Panel ', href: route('welcome') }, { text: 'Usuarios' }]" />
          </div>
        </v-row>
        <v-row class="justify-end">
          <div>
            <Link :href="route('admin.users.create')">
              <v-btn color="primary" variant="elevated">Crear</v-btn>
            </Link>
          </div>
        </v-row>
      </v-col>
    </v-card-title>

    <v-card-text>
      <v-data-table
        :items="users.data"
        :headers="headers"
        item-key="id"
        class="elevation-1"
        dense
        disable-sort
      >
        <template v-slot:item.id="{ value }">
          <v-chip
            color="tertiary"
            variant="outlined"
            size="x-small"
            >{{ value }}</v-chip>
        </template>
        
        <template #item.roles="{ item }">
          {{ item.roles.map(r => r.name).join(', ') }}
        </template>

        <template #item.actions="{ item }">
          <Link :href="route('admin.users.edit', item.id)">
                <v-btn icon color="primary" size="small" title="Editar" aria-label="Editar">
                  <v-icon>mdi-pencil</v-icon>
                </v-btn>
              </Link>
              <v-btn icon color="error" size="small" title="Eliminar" aria-label="Eliminar" @click="remove(item.id)">
                <v-icon>mdi-delete</v-icon>
              </v-btn>
        </template>
      </v-data-table>

      <div class="d-flex justify-center mt-4">
        <v-pagination
          v-if="users.last_page > 1"
          v-model="page"
          :length="users.last_page"
          @update:model-value="onPageChange"
        />
      </div>
    </v-card-text>
  </v-card>
</template>

<style scoped>
.breadcrumbs{
  margin-top:6px;
  font-size:0.9rem;
  color:var(--v-theme-on-surface, rgba(0,0,0,0.65));
  display:flex;
  align-items:center;
  gap:8px;
}
.breadcrumb-link{
  color:var(--v-theme-primary, #4F46E5);
  text-decoration:none;
}
.breadcrumb-link:hover{ text-decoration:underline; }
.breadcrumb-sep{ color:var(--v-theme-on-surface, rgba(0,0,0,0.45)); }
.breadcrumb-current{ font-weight:600; }
</style>
