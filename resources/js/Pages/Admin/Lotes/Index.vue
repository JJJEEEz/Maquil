<template>
  <Head title="Lotes" />
  <AuthenticatedLayout>
    <v-card class="p-6">
      <v-card-title class="d-flex justify-space-between align-center">
        <v-col cols="12" md="12">
          <v-row>
            <div>
              <h1 class="text-xl mb-1 font-semibold">Lotes</h1>
            </div>
          </v-row>
        </v-col>
      </v-card-title>

      <v-card-text>
        <div v-if="$page.props.flash?.success" class="mb-4 p-4 bg-green-100 text-green-700 rounded">
          {{ $page.props.flash?.success }}
        </div>

        <v-data-table
          :items="lotes.data"
          :headers="headers"
          item-key="id"
          dense
          class="elevation-1"
        >
          <template #item.fecha="{ item }">
            {{ formatDate(item.fecha) }}
          </template>

          <template #item.estado_trabajo="{ item }">
            <v-chip :color="getEstadoColor(item.estado_trabajo)" variant="tonal" size="small">
              {{ formatEstado(item.estado_trabajo) }}
            </v-chip>
          </template>

          <template #item.actions="{ item }">
            <div class="actions-cell">
              <Link :href="route('admin.lotes.show', item.id)">
                <v-btn icon color="primary" size="small" title="Ver Detalle">
                  <v-icon>mdi-eye</v-icon>
                </v-btn>
              </Link>
              <Link :href="route('operador.lotes.dashboard', item.id)">
                <v-btn icon color="secondary" size="small" title="Dashboard">
                  <v-icon>mdi-chart-box</v-icon>
                </v-btn>
              </Link>
            </div>
          </template>
        </v-data-table>

        <div class="d-flex justify-center mt-4">
          <v-pagination
            v-if="lotes.last_page > 1"
            v-model="pageNumber"
            :length="lotes.last_page"
            @update:model-value="onPageChange"
          />
        </div>
      </v-card-text>
    </v-card>
  </AuthenticatedLayout>
</template>

<script setup>
import { ref } from 'vue';
import { Head } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Link } from '@inertiajs/vue3';

const props = defineProps({
  lotes: Object,
});

const pageNumber = ref(props.lotes?.current_page || 1);

const onPageChange = (newPage) => {
  window.location.href = `${route('admin.lotes.index')}?page=${newPage}`;
};

const headers = [
  { title: 'ID', value: 'id', width: 60, align: 'center' },
  { title: 'Orden', value: 'orden.name' },
  { title: 'Fecha', value: 'fecha' },
  { title: 'Estado', value: 'estado_trabajo' },
  { title: 'Prendas', value: 'total_prendas_terminadas', align: 'right' },
  { title: 'Mermas', value: 'total_mermas', align: 'right' },
  { title: 'Acciones', value: 'actions', sortable: false, width: 140, align: 'center' },
];

const formatDate = (date) => {
  return new Date(date).toLocaleDateString('es-ES');
};

const formatEstado = (estado) => {
  const map = {
    trabajado: 'Trabajado',
    no_trabajado: 'No trabajado',
    interrumpido: 'Interrumpido',
  };
  return map[estado] || estado;
};

const getEstadoColor = (estado) => {
  switch (estado) {
    case 'trabajado':
      return 'success';
    case 'interrumpido':
      return 'error';
    case 'no_trabajado':
    default:
      return 'grey';
  }
};
</script>

<style scoped>
.actions-cell {
  display: flex;
  gap: 6px;
  align-items: center;
}
</style>
