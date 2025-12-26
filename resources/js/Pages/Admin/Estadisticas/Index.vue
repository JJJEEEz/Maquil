<template>
  <AuthenticatedLayout>
    <v-card class="p-6">
      <v-card-title>
        <div>
          <h1 class="text-2xl font-semibold mb-1">Estadísticas de Procesos</h1>
          <p class="text-gray-600 text-sm">Vista consolidada de todas las órdenes, lotes y procesos</p>
        </div>
      </v-card-title>

      <!-- Estadísticas Globales -->
      <v-card-text>
        <v-expansion-panels>
          <!-- Estado de Ordenes -->
          <v-expansion-panel>
            <v-expansion-panel-title class="font-semibold">
              Órdenes
            </v-expansion-panel-title>
            <v-expansion-panel-text>
              <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="bg-blue-50 dark:bg-blue-900 p-4 rounded">
                  <div class="text-sm text-blue-600 dark:text-blue-200 mb-1">Órdenes Totales</div>
                  <div class="text-2xl font-bold text-blue-600">{{ stats.total_ordenes }}</div>
                </div>
                <div class="bg-blue-50 dark:bg-blue-900 p-4 rounded">
                  <div class="text-sm text-blue-600 dark:text-blue-200 mb-1">Lotes totales</div>
                  <div class="text-2xl font-bold text-blue-600">{{ stats.total_lotes }}</div>
                </div>
              </div>
            </v-expansion-panel-text>
          </v-expansion-panel>

          <!-- Estado de Procesos -->
          <v-expansion-panel>
            <v-expansion-panel-title class="font-semibold">
              Procesos
            </v-expansion-panel-title>
            <v-expansion-panel-text>
              <div class="grid grid-cols-1 sm:grid-cols-4 gap-4">
                <div class="bg-blue-50 dark:bg-blue-900 p-4 rounded">
                  <div class="text-sm text-blue-600 dark:text-blue-200 mb-1">Procesos Totales</div>
                  <div class="text-2xl font-bold text-blue-600">{{ stats.total_procesos }}</div>
                </div>
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
            </v-expansion-panel-text>
          </v-expansion-panel>

          <!-- Mermas y Excedentes -->
          <v-expansion-panel>
            <v-expansion-panel-title class="font-semibold">
              Mermas y Excedentes
            </v-expansion-panel-title>
            <v-expansion-panel-text>
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
            </v-expansion-panel-text>
          </v-expansion-panel>
        </v-expansion-panels>

        <!-- Detalle por Orden - Acordeón Interactivo -->
        <v-expansion-panels>
          <v-expansion-panel>
            <v-expansion-panel-title class="font-semibold">
              Detalle por Orden
            </v-expansion-panel-title>
            <v-expansion-panel-text>
              <v-expansion-panels>
                <v-expansion-panel v-for="orden in ordenes" :key="orden.id">
                <v-expansion-panel-title class="font-semibold">
                  <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center w-full gap-2 pr-4">
                    <div class="flex-1">
                      <h3 class="text-base font-semibold">{{ orden.name }}</h3>
                      <p class="text-xs text-gray-600">{{ orden.client }} • {{ orden.tipoPrenda?.nombre || 'N/A' }}</p>
                    </div>
                    <div class="flex items-center gap-4">
                      <div class="text-right">
                        <div class="text-sm font-bold">{{ getOrdenProgress(orden) }}%</div>
                        <div class="text-xs text-gray-600">Progreso</div>
                      </div>
                      <v-progress-linear
                        :model-value="getOrdenProgress(orden)"
                        height="4"
                        class="w-24"
                        :color="getProgressColor(getOrdenProgress(orden))"
                      ></v-progress-linear>
                    </div>
                  </div>
                </v-expansion-panel-title>

                <v-expansion-panel-text>
                  <!-- Resumen de Progreso -->
                  <div class="bg-gray-50 dark:bg-gray-800 p-4 rounded mb-4">
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 text-sm">
                      <div>
                        <span class="text-gray-600 text-xs">Objetivo Total</span>
                        <div class="font-bold text-lg">{{ getOrdenStats(orden).objetivo }}</div>
                      </div>
                      <div>
                        <span class="text-gray-600 text-xs">Completadas</span>
                        <div class="font-bold text-lg text-green-600">{{ getOrdenStats(orden).completado }}</div>
                      </div>
                      <div>
                        <span class="text-gray-600 text-xs">En Progreso</span>
                        <div class="font-bold text-lg text-yellow-600">{{ getOrdenStats(orden).enProgreso }}</div>
                      </div>
                      <div>
                        <span class="text-gray-600 text-xs">Pendientes</span>
                        <div class="font-bold text-lg text-red-600">{{ getOrdenStats(orden).pendiente }}</div>
                      </div>
                    </div>
                  </div>

                  <!-- Selector de Lote -->
                  <div class="mb-6">
                    <label class="text-sm font-semibold block mb-2">Seleccionar Lote:</label>
                    <v-select
                      :model-value="selectedLoteByOrden[orden.id]"
                      :items="orden.lotes"
                      item-title="id"
                      :return-object="true"
                      label="Selecciona un lote..."
                      variant="outlined"
                      density="compact"
                      @update:model-value="(newLote) => { selectedLoteByOrden[orden.id] = newLote; onLoteSelected(orden.id); }"
                    >
                      <template v-slot:item="{ props, item }">
                        <v-list-item v-bind="props" :title="`Lote #${item.raw.id}`">
                          <template v-slot:subtitle>
                            {{ formatDate(item.raw.fecha) }} • {{ formatEstado(item.raw.estado_trabajo) }}
                          </template>
                        </v-list-item>
                      </template>
                      <template v-slot:selection="{ item }">
                        <span v-if="item.raw">Lote #{{ item.raw.id }} - {{ formatDate(item.raw.fecha) }}</span>
                      </template>
                    </v-select>
                  </div>

                  <!-- Procesos del Lote Seleccionado -->
                  <div v-if="selectedLoteByOrden[orden.id]" class="space-y-3">
                    <h4 class="font-semibold text-sm mb-3">Procesos - Lote #{{ selectedLoteByOrden[orden.id].id }}</h4>
                    <div v-if="getLoteProgresos(orden, selectedLoteByOrden[orden.id]).length > 0" class="space-y-2">
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
                      <div v-for="progreso in getLoteProgresos(orden, selectedLoteByOrden[orden.id])" :key="progreso.id" class="bg-white dark:bg-gray-700 p-3 rounded border border-gray-200 dark:border-gray-600">
                        <div class="flex justify-between items-start mb-2">
                          <div class="flex-1">
                            <h5 class="font-semibold text-sm">{{ progreso.procesoNodo?.nombre }}</h5>
                            <p class="text-xs text-gray-600">Estado: {{ progreso.estado }}</p>
                          </div>
                          <v-chip
                            :color="getEstadoColor(progreso.estado)"
                            size="x-small"
                            text-color="white"
                            class="ml-2"
                          >
                            {{ progreso.estado }}
                          </v-chip>
                        </div>

                        <div class="space-y-2 text-xs mb-2">
                          <div class="flex justify-between">
                            <span class="text-gray-600">Completadas:</span>
                            <span class="font-bold">{{ progreso.cantidad_completada }}/{{ progreso.cantidad_objetivo }}</span>
                          </div>
                          <div class="flex justify-between">
                            <span class="text-gray-600">Merma:</span>
                            <span class="font-bold text-red-600">{{ progreso.cantidad_merma }}</span>
                          </div>
                          <div class="flex justify-between">
                            <span class="text-gray-600">Excedentes:</span>
                            <span class="font-bold text-cyan-600">{{ progreso.cantidad_excedente }}</span>
                          </div>
                          <div class="flex justify-between">
                            <span class="text-gray-600">Registrado por:</span>
                            <span class="font-bold text-xs">{{ progreso.registradoPor?.name || 'N/A' }}</span>
                          </div>
                        </div>

                        <div class="w-full bg-gray-200 rounded-full h-2">
                          <div
                            class="bg-green-600 h-2 rounded-full transition-all"
                            :style="{ width: `${(progreso.cantidad_completada / (progreso.cantidad_objetivo || 1)) * 100}%` }"
                          ></div>
                        </div>
                      </div>
                    </div>
                    </div>
                    <div v-else-if="selectedLoteByOrden[orden.id]" class="text-sm text-gray-600 italic">
                      Este lote no tiene procesos registrados
                    </div>
                    <div v-else class="text-sm text-gray-600 italic py-4">
                      Selecciona un lote para ver los procesos
                    </div>
                  </div>
                </v-expansion-panel-text>
              </v-expansion-panel>
            </v-expansion-panels>
            </v-expansion-panel-text>
          </v-expansion-panel>
        </v-expansion-panels>
      </v-card-text>
    </v-card>
  </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import { ref, watch } from 'vue';

const props = defineProps({
  ordenes: Array,
  stats: Object,
});

// Inicializar selectedLoteByOrden con un objeto reactivo
const selectedLoteByOrden = ref({});

// Watcher para debuggear cambios
watch(
  () => selectedLoteByOrden.value,
  (newVal) => {
    console.log('selectedLoteByOrden actualizado:', newVal);
  },
  { deep: true }
);

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

const getProgressColor = (percentage) => {
  if (percentage >= 80) return 'success';
  if (percentage >= 50) return 'warning';
  return 'error';
};

const getOrdenStats = (orden) => {
  let objetivo = 0;
  let completado = 0;
  let enProgreso = 0;
  let pendiente = 0;

  if (orden.lotes && orden.lotes.length > 0) {
    orden.lotes.forEach(lote => {
      if (lote.loteProcesoProgresos && lote.loteProcesoProgresos.length > 0) {
        lote.loteProcesoProgresos.forEach(progreso => {
          objetivo += progreso.cantidad_objetivo;
          completado += progreso.cantidad_completada;
          if (progreso.cantidad_completada < progreso.cantidad_objetivo) {
            enProgreso += (progreso.cantidad_objetivo - progreso.cantidad_completada);
          }
        });
      }
    });
  }

  pendiente = objetivo - completado - enProgreso;
  if (pendiente < 0) pendiente = 0;

  return { objetivo, completado, enProgreso, pendiente };
};

const getOrdenProgress = (orden) => {
  const stats = getOrdenStats(orden);
  if (stats.objetivo === 0) return 0;
  return Math.round(((stats.completado + stats.enProgreso) / stats.objetivo) * 100);
};

const onLoteSelected = (ordenId) => {
  const loteSeleccionado = selectedLoteByOrden.value[ordenId];
  console.log(`Lote seleccionado para orden ${ordenId}:`, loteSeleccionado);
  console.log('¿Tiene loteProcesoProgresos?', loteSeleccionado?.loteProcesoProgresos);
};

const getLoteProgresos = (orden, loteSeleccionado) => {
  if (!loteSeleccionado || !loteSeleccionado.id) {
    console.log('Sin lote seleccionado');
    return [];
  }
  
  console.log('===== DEBUG getLoteProgresos =====');
  console.log('Orden:', orden.id);
  console.log('Lotes en orden:', orden.lotes);
  console.log('Lote seleccionado:', loteSeleccionado);
  console.log('Buscando lote con ID:', loteSeleccionado.id);
  
  // Buscar el lote completo en orden.lotes para obtener sus progresos
  const loteFull = orden.lotes.find(l => l.id === loteSeleccionado.id);
  console.log('Lote encontrado:', loteFull);
  console.log('loteProcesoProgresos:', loteFull?.loteProcesoProgresos);
  console.log('lote_proceso_progresos:', loteFull?.lote_proceso_progresos);
  console.log('Cantidad de progresos:', loteFull?.loteProcesoProgresos?.length || loteFull?.lote_proceso_progresos?.length || 0);
  
  return loteFull?.loteProcesoProgresos || loteFull?.lote_proceso_progresos || [];
};
</script>
