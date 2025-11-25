<script setup>
import { ref } from 'vue';
import { Link } from '@inertiajs/vue3';
import { Inertia } from '@inertiajs/inertia';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

const props = defineProps({ roles: Object });
const roles = props.roles;

const page = ref(roles.current_page || 1);

const headers = [
  { title: 'ID', value: 'id', width: 50, align: 'center' },
  { title: 'Nombre', value: 'name' },
  { title: 'Acciones', value: 'actions', sortable: false, width: 90, align: 'center' },
];

function remove(id) {
  if (!confirm('Eliminar rol?')) return;
  Inertia.delete(route('admin.roles.destroy', id), {}, { preserveState: true });
}

function onPageChange(value) {
  page.value = value;
  Inertia.get(route('admin.roles.index'), { page: value }, { preserveState: true, replace: true });
}
</script>


<template>
  <AuthenticatedLayout>
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
          :items="roles.data"
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
          <template #item.actions="{ item }">
            <div class="actions-cell">
              <Link :href="route('admin.roles.edit', item.id)">
                <v-btn icon color="primary" size="small" title="Editar" aria-label="Editar">
                  <v-icon>mdi-pencil</v-icon>
                </v-btn>
              </Link>
              <v-btn icon color="error" size="small" title="Eliminar" aria-label="Eliminar" @click="remove(item.id)">
                <v-icon>mdi-delete</v-icon>
              </v-btn>
            </div>
          </template>
        </v-data-table>

        <div class="d-flex justify-center mt-4">
          <v-pagination
            v-if="roles.last_page > 1"
            v-model="page"
            :length="roles.last_page"
            @update:model-value="onPageChange"
          />
        </div>
      </v-card-text>
    </v-card>
  </AuthenticatedLayout>
</template>

  <style scoped>
  .actions-cell{
    display:flex;
    gap:6px;
    justify-content:center;
    width:90px;
    max-width:90px;
    white-space:nowrap;
  }
  .actions-cell .v-btn{
    min-width:0;
    padding:6px;
  }
  @media (max-width:480px){
    .actions-cell{ width:70px; max-width:70px; gap:4px; }
  }
  </style>

<style scoped>
.custom-container .v-data-table .v-data-table__tr:hover {
/* Aumentamos la opacidad del hover. El valor por defecto es ~0.04 */
--v-hover-opacity: 0.20 !important;
}
</style>