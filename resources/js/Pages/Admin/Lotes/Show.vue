<template>
  <AuthenticatedLayout>
    <v-card class="p-6">
      <v-card-title class="d-flex flex-col md:flex-row justify-space-between align-start md:align-center gap-4">
        <div>
          <h1 class="text-xl mb-1 font-semibold">Detalle del Lote #{{ lote.id }}</h1>
          <div class="text-sm">Orden: {{ lote.orden.name }} | Cliente: {{ lote.orden.client }}</div>
          <Breadcrumbs :items="[
            { text: 'Panel', href: route('welcome') },
            { text: 'Ordenes', href: route('admin.ordenes.index') },
            { text: lote.orden.name, href: route('admin.ordenes.lotes.index', lote.orden.id) },
            { text: `Lote #${lote.id}` }
          ]" />
        </div>
        <div>
          <Link :href="route('admin.ordenes.lotes.index', lote.orden.id)">
            <v-btn color="secondary" variant="outlined">Volver</v-btn>
          </Link>
        </div>
      </v-card-title>

      <v-card-text>
        <!-- Información General -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
          <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
            <div class="text-sm text-gray-600 dark:text-gray-400">Fecha</div>
            <div class="text-2xl font-bold">{{ formatDate(lote.fecha) }}</div>
          </div>
          <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
            <div class="text-sm text-gray-600 dark:text-gray-400">Prendas Terminadas</div>
            <div class="text-2xl font-bold text-green-600">{{ lote.total_prendas_terminadas }}</div>
          </div>
          <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
            <div class="text-sm text-gray-600 dark:text-gray-400">Mermas</div>
            <div class="text-2xl font-bold text-red-600">{{ lote.total_mermas }}</div>
          </div>
          <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
            <div class="text-sm text-gray-600 dark:text-gray-400">Estado Trabajo</div>
            <div :class="getEstadoClass(lote.estado_trabajo)" class="px-3 py-1 rounded text-sm font-semibold inline-block">
              {{ lote.estado_trabajo }}
            </div>
          </div>
        </div>

        <!-- Progreso por Proceso -->
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
          <div class="p-6">
            <div class="d-flex flex-col md:flex-row justify-space-between align-start md:align-center gap-4 mb-4">
              <h3 class="text-lg font-semibold">Progreso por Proceso</h3>
              <Link v-if="(!lote.lote_proceso_progresos || lote.lote_proceso_progresos.length === 0) && lote.orden?.tipo_prenda_id" 
                    :href="route('admin.lotes.initializeProcesos', lote.id)" 
                    method="post" 
                    as="button">
                <v-btn color="primary" size="small">Inicializar Procesos</v-btn>
              </Link>
            </div>
            <div v-if="!lote.orden?.tipo_prenda_id" class="text-center py-8">
              <div class="text-red-600 font-semibold mb-2">La orden no tiene un tipo de prenda asignado</div>
              <div class="text-gray-600 text-sm">
                Para poder crear procesos, primero debes asignar un tipo de prenda a la orden.
                <Link :href="route('admin.ordenes.edit', lote.orden.id)" class="text-blue-600 underline">Editar orden</Link>
              </div>
            </div>
            <div v-else-if="!lote.lote_proceso_progresos || lote.lote_proceso_progresos.length === 0" class="text-center py-8 text-gray-500">
              No hay procesos registrados para este lote. Haz clic en "Inicializar Procesos" para crearlos automáticamente.
            </div>
            <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
              <div v-for="progreso in lote.lote_proceso_progresos" :key="progreso.id" class="border rounded-lg p-4">
                <div class="flex justify-between items-start mb-2">
                  <div>
                    <h4 class="font-semibold">{{ progreso.proceso_nodo.nombre }}</h4>
                    <p class="text-sm text-gray-600 dark:text-gray-400">
                      Entrada: {{ progreso.proceso_nodo.cantidad_entrada }} | Salida: {{ progreso.proceso_nodo.cantidad_salida }}
                    </p>
                  </div>
                  <span :class="getEstadoProgresoClass(progreso.estado)" class="px-2 py-1 rounded text-sm">
                    {{ progreso.estado }}
                  </span>
                </div>

                <!-- Botón para asignar operador -->
                <div class="mb-3">
                  <Link :href="route('admin.operador-asignacion.edit', progreso.id)">
                    <v-btn color="secondary" size="x-small" variant="outlined" class="w-full mb-2">
                      Asignar Operador
                    </v-btn>
                  </Link>
                </div>

                <div class="grid grid-cols-2 sm:grid-cols-4 gap-2 text-sm mb-3">
                  <div>
                    <span class="text-gray-600">Completadas:</span>
                    <div class="font-bold">{{ progreso.cantidad_completada }} / {{ progreso.cantidad_objetivo }}</div>
                  </div>
                  <div>
                    <span class="text-gray-600">Mermas:</span>
                    <div class="font-bold text-red-600">{{ progreso.cantidad_merma }}</div>
                  </div>
                  <div>
                    <span class="text-gray-600">Excedentes:</span>
                    <div class="font-bold text-blue-600">{{ progreso.cantidad_excedente }}</div>
                  </div>
                  <div>
                    <span class="text-gray-600">Registrado por:</span>
                    <div class="font-bold">{{ progreso.registrado_por?.name || 'N/A' }}</div>
                  </div>
                </div>

                <div v-if="progreso.notas" class="bg-yellow-50 dark:bg-yellow-900 p-2 rounded text-sm">
                  <strong>Notas:</strong> {{ progreso.notas }}
                </div>

                <!-- Barra de Progreso -->
                <div class="mt-3">
                  <div class="w-full bg-gray-200 rounded-full h-2">
                    <div
                      class="bg-green-600 h-2 rounded-full transition-all"
                      :style="{ width: `${(progreso.cantidad_completada / progreso.cantidad_objetivo) * 100}%` }"
                    ></div>
                  </div>
                  <p class="text-xs text-gray-600 mt-1">
                    {{ Math.round((progreso.cantidad_completada / progreso.cantidad_objetivo) * 100) }}%
                  </p>
                </div>
              </div>
            </div>
          </div>
        </div>
      </v-card-text>
    </v-card>
  </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Breadcrumbs from '@/Components/Breadcrumbs.vue';
import { Link } from '@inertiajs/vue3';
import { onMounted } from 'vue';

const props = defineProps({
  lote: Object,
});

onMounted(() => {
  console.log('Lote completo:', props.lote);
  console.log('Progresos:', props.lote?.lote_proceso_progresos);
  console.log('Orden:', props.lote?.orden);
  console.log('Tipo Prenda:', props.lote?.orden?.tipo_prenda);
  console.log('¿Tiene tipo_prenda_id?:', props.lote?.orden?.tipo_prenda_id);
});

const formatDate = (date) => {
  return new Date(date).toLocaleDateString('es-ES');
};

const getEstadoClass = (estado) => {
  const classes = {
    trabajado: 'bg-green-100 text-green-800',
    no_trabajado: 'bg-gray-100 text-gray-800',
    interrumpido: 'bg-red-100 text-red-800',
  };
  return classes[estado] || 'bg-gray-100 text-gray-800';
};

const getEstadoProgresoClass = (estado) => {
  const classes = {
    pendiente: 'bg-gray-100 text-gray-800',
    en_progreso: 'bg-blue-100 text-blue-800',
    completado: 'bg-green-100 text-green-800',
  };
  return classes[estado] || 'bg-gray-100 text-gray-800';
};
</script>
