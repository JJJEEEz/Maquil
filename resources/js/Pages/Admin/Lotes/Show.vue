<template>
  <AuthenticatedLayout>
    <template #header>
      <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
        Detalle Lote #{{ lote.id }} - {{ lote.orden.name }}
      </h2>
    </template>

    <div class="py-12">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <!-- Información General -->
        <div class="grid grid-cols-4 gap-4 mb-6">
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
            <h3 class="text-lg font-semibold mb-4">Progreso por Proceso</h3>
            <div class="space-y-4">
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

                <div class="grid grid-cols-4 gap-2 text-sm mb-3">
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
      </div>
    </div>
  </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

defineProps({
  lote: Object,
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
