<template>
  <AuthenticatedLayout>
    <v-card class="p-6">
      <v-card-title>
        <div>
          <h1 class="text-2xl font-semibold mb-1">Estadísticas de Procesos</h1>
          <p class="text-gray-600 text-sm">Vista consolidada de todas las órdenes, lotes y procesos</p>
        </div>
      </v-card-title>

      <v-card-text>
        <!-- Estadísticas Globales -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
          <v-card class="p-4">
            <div class="text-sm text-gray-600 mb-2">Órdenes Totales</div>
            <div class="text-3xl font-bold text-blue-600">{{ stats.total_ordenes }}</div>
          </v-card>
          <v-card class="p-4">
            <div class="text-sm text-gray-600 mb-2">Lotes Totales</div>
            <div class="text-3xl font-bold text-purple-600">{{ stats.total_lotes }}</div>
          </v-card>
          <v-card class="p-4">
            <div class="text-sm text-gray-600 mb-2">Procesos Totales</div>
            <div class="text-3xl font-bold text-indigo-600">{{ stats.total_procesos }}</div>
          </v-card>
          <v-card class="p-4">
            <div class="text-sm text-gray-600 mb-2">Prendas Completadas</div>
            <div class="text-3xl font-bold text-green-600">{{ stats.prendas_completadas }}</div>
          </v-card>
        </div>

        <!-- Estado de Procesos -->
        <v-card class="mb-6">
          <v-card-title class="text-lg">Estado de Procesos</v-card-title>
          <v-card-text>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
              <div class="bg-blue-50 dark:bg-blue-900 p-4 rounded">
                <div class="text-sm text-blue-600 dark:text-blue-200 mb-1">Completados</div>
                <div class="text-2xl font-bold text-blue-600">{{ stats.procesos_completados }}</div>
              </div>
              <div class="bg-yellow-50 dark:bg-yellow-900 p-4 rounded">
                <div class="text-sm text-yellow-600 dark:text-yellow-200 mb-1">En Progreso</div>
                <div class="text-2xl font-bold text-yellow-600">{{ stats.procesos_en_progreso }}</div>
              </div>
              <div class="bg-gray-50 dark:bg-gray-700 p-4 rounded">
                <div class="text-sm text-gray-600 dark:text-gray-300 mb-1">Pendientes</div>
                <div class="text-2xl font-bold text-gray-600">{{ stats.procesos_pendientes }}</div>
              </div>
            </div>
          </v-card-text>
        </v-card>

        <!-- Mermas y Excedentes -->
        <v-card class="mb-6">
          <v-card-title class="text-lg">Mermas y Excedentes</v-card-title>
          <v-card-text>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
              <div class="bg-red-50 dark:bg-red-900 p-4 rounded">
                <div class="text-sm text-red-600 dark:text-red-200 mb-1">Prendas con Merma</div>
                <div class="text-2xl font-bold text-red-600">{{ stats.prendas_merma }}</div>
              </div>
              <div class="bg-cyan-50 dark:bg-cyan-900 p-4 rounded">
                <div class="text-sm text-cyan-600 dark:text-cyan-200 mb-1">Prendas Excedentes</div>
                <div class="text-2xl font-bold text-cyan-600">{{ stats.prendas_excedentes }}</div>
              </div>
            </div>
          </v-card-text>
        </v-card>

        <!-- Detalle por Orden -->
        <v-card>
          <v-card-title class="text-lg">Detalle por Orden</v-card-title>
          <v-card-text>
            <div class="space-y-4">
              <div v-for="orden in ordenes" :key="orden.id" class="border rounded-lg p-4">
                <div class="flex flex-col md:flex-row justify-between align-start md:align-center gap-4 mb-4 pb-4 border-b">
                  <div>
                    <h3 class="text-lg font-semibold">{{ orden.name }}</h3>
                    <p class="text-sm text-gray-600">Cliente: {{ orden.client }}</p>
                    <p class="text-sm text-gray-600">Tipo de Prenda: {{ orden.tipoPrenda?.nombre || 'N/A' }}</p>
                  </div>
                  <div class="text-right">
                    <div class="text-2xl font-bold">{{ orden.lotes?.length || 0 }}</div>
                    <div class="text-xs text-gray-600">Lotes</div>
                  </div>
                </div>

                <!-- Lotes de la Orden -->
                <div class="space-y-3">
                  <div v-for="lote in orden.lotes" :key="lote.id" class="bg-gray-50 dark:bg-gray-800 p-3 rounded">
                    <div class="flex justify-between items-start mb-2">
                      <div>
                        <h4 class="font-semibold">Lote #{{ lote.id }}</h4>
                        <p class="text-xs text-gray-600">Fecha: {{ formatDate(lote.fecha) }}</p>
                        <p class="text-xs text-gray-600">Estado: {{ formatEstado(lote.estado_trabajo) }}</p>
                      </div>
                      <div class="text-right">
                        <span class="text-xs font-semibold text-blue-600">{{ lote.loteProcesoProgresos?.length || 0 }} procesos</span>
                      </div>
                    </div>

                    <!-- Procesos del Lote -->
                    <div class="ml-4 space-y-2">
                      <div v-for="progreso in lote.loteProcesoProgresos" :key="progreso.id" class="bg-white dark:bg-gray-700 p-2 rounded text-sm">
                        <div class="flex justify-between items-start mb-1">
                          <span class="font-semibold">{{ progreso.procesoNodo?.nombre }}</span>
                          <v-chip
                            :color="getEstadoColor(progreso.estado)"
                            size="x-small"
                          >
                            {{ progreso.estado }}
                          </v-chip>
                        </div>
                        <div class="grid grid-cols-2 sm:grid-cols-4 gap-2 text-xs mt-1">
                          <div>
                            <span class="text-gray-600">Completadas:</span>
                            <div class="font-bold">{{ progreso.cantidad_completada }}/{{ progreso.cantidad_objetivo }}</div>
                          </div>
                          <div>
                            <span class="text-gray-600">Merma:</span>
                            <div class="font-bold text-red-600">{{ progreso.cantidad_merma }}</div>
                          </div>
                          <div>
                            <span class="text-gray-600">Excedentes:</span>
                            <div class="font-bold text-cyan-600">{{ progreso.cantidad_excedente }}</div>
                          </div>
                          <div>
                            <span class="text-gray-600">Registrado por:</span>
                            <div class="font-bold">{{ progreso.registradoPor?.name || 'N/A' }}</div>
                          </div>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-1 mt-2">
                          <div
                            class="bg-green-600 h-1 rounded-full"
                            :style="{ width: `${(progreso.cantidad_completada / (progreso.cantidad_objetivo || 1)) * 100}%` }"
                          ></div>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </v-card-text>
        </v-card>
      </v-card-text>
    </v-card>
  </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';

const props = defineProps({
  ordenes: Array,
  stats: Object,
});

const formatDate = (date) => {
  if (!date) return '-';
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
  const colors = {
    pendiente: 'gray',
    en_progreso: 'warning',
    completado: 'success',
  };
  return colors[estado] || 'gray';
};
</script>
