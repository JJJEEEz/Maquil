<template>
  <AuthenticatedLayout>
    <template #header>
      <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
        Dashboard - Lote #{{ lote.id }} | {{ orden.name }}
      </h2>
    </template>

    <div class="py-12">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <!-- Resumen del Día -->
        <div class="grid grid-cols-4 gap-4 mb-6">
          <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
            <div class="text-sm text-gray-600 dark:text-gray-400">Fecha</div>
            <div class="text-2xl font-bold">{{ formatDate(lote.fecha) }}</div>
          </div>
          <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
            <div class="text-sm text-gray-600 dark:text-gray-400">Cantidad Objetivo</div>
            <div class="text-2xl font-bold">{{ orden.target_quantity }}</div>
          </div>
          <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
            <div class="text-sm text-gray-600 dark:text-gray-400">Completadas Hoy</div>
            <div class="text-2xl font-bold text-green-600">{{ lote.total_prendas_terminadas }}</div>
          </div>
          <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
            <div class="text-sm text-gray-600 dark:text-gray-400">Mermas</div>
            <div class="text-2xl font-bold text-red-600">{{ lote.total_mermas }}</div>
          </div>
        </div>

        <!-- Diagrama de Procesos interactivo -->
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg mb-6">
          <div class="p-6">
            <h3 class="text-lg font-semibold mb-4">Procesos en Tiempo Real</h3>
            <button
              @click="refreshProgress"
              class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded mb-4"
            >
              🔄 Actualizar (Último: {{ lastRefresh }})
            </button>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
              <div
                v-for="progreso in progresos"
                :key="progreso.id"
                :class="getProcesoCardClass(progreso)"
                class="border rounded-lg p-4 transition-all cursor-pointer hover:shadow-lg"
                @click="selectProceso(progreso)"
              >
                <div class="flex justify-between items-start mb-2">
                  <h4 class="font-semibold text-sm">{{ progreso.proceso_nodo.nombre }}</h4>
                  <span :class="getEstadoLabel(progreso.estado)" class="text-xs px-2 py-1 rounded">
                    {{ progreso.estado }}
                  </span>
                </div>

                <div class="text-xs text-gray-600 dark:text-gray-400 mb-2">
                  Entrada: {{ progreso.proceso_nodo.cantidad_entrada }} | Salida: {{ progreso.proceso_nodo.cantidad_salida }}
                </div>

                <!-- Barra de Progreso -->
                <div class="mb-2">
                  <div class="flex justify-between text-xs mb-1">
                    <span>Progreso:</span>
                    <span class="font-bold">{{ progreso.cantidad_completada }} / {{ progreso.cantidad_objetivo }}</span>
                  </div>
                  <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                    <div
                      class="bg-green-600 h-2 rounded-full transition-all"
                      :style="{ width: `${(progreso.cantidad_completada / progreso.cantidad_objetivo) * 100}%` }"
                    ></div>
                  </div>
                  <p class="text-xs text-gray-600 mt-1">
                    {{ Math.round((progreso.cantidad_completada / progreso.cantidad_objetivo) * 100) }}%
                  </p>
                </div>

                <div class="text-xs text-gray-600 dark:text-gray-400">
                  <div v-if="progreso.cantidad_merma > 0">🔴 Mermas: {{ progreso.cantidad_merma }}</div>
                  <div v-if="progreso.cantidad_excedente > 0">🔵 Excedentes: {{ progreso.cantidad_excedente }}</div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Modal para registrar progreso -->
        <div v-if="procesoSeleccionado" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
          <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg max-w-md w-full mx-4 p-6">
            <h3 class="text-lg font-semibold mb-4">{{ procesoSeleccionado.proceso_nodo.nombre }}</h3>

            <form @submit.prevent="registrarProgreso" class="space-y-4">
              <div>
                <label class="block text-sm font-medium mb-2">Cantidad Completada</label>
                <input
                  v-model.number="formulario.cantidad_completada"
                  type="number"
                  min="0"
                  class="w-full px-4 py-2 border rounded-lg dark:bg-gray-700 dark:border-gray-600"
                  required
                />
              </div>

              <div>
                <label class="block text-sm font-medium mb-2">Cantidad Merma</label>
                <input
                  v-model.number="formulario.cantidad_merma"
                  type="number"
                  min="0"
                  class="w-full px-4 py-2 border rounded-lg dark:bg-gray-700 dark:border-gray-600"
                />
              </div>

              <div>
                <label class="block text-sm font-medium mb-2">Cantidad Excedente</label>
                <input
                  v-model.number="formulario.cantidad_excedente"
                  type="number"
                  min="0"
                  class="w-full px-4 py-2 border rounded-lg dark:bg-gray-700 dark:border-gray-600"
                />
              </div>

              <div>
                <label class="block text-sm font-medium mb-2">Notas (opcional)</label>
                <textarea
                  v-model="formulario.notas"
                  class="w-full px-4 py-2 border rounded-lg dark:bg-gray-700 dark:border-gray-600"
                  rows="3"
                  placeholder="Ej: Tela defectuosa, máquina lenta"
                ></textarea>
              </div>

              <div class="flex gap-2">
                <button
                  type="submit"
                  :disabled="cargando"
                  class="flex-1 bg-green-500 hover:bg-green-700 disabled:opacity-50 text-white font-bold py-2 px-4 rounded"
                >
                  Registrar
                </button>
                <button
                  type="button"
                  @click="procesoSeleccionado = null"
                  class="flex-1 bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded"
                >
                  Cancelar
                </button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { ref, onMounted, onUnmounted } from 'vue';
import axios from 'axios';

const props = defineProps({
  lote: Object,
  orden: Object,
  progresos: Array,
  procesoNodos: Array,
});

const procesoSeleccionado = ref(null);
const cargando = ref(false);
const lastRefresh = ref(new Date().toLocaleTimeString());
let refreshInterval = null;

const formulario = ref({
  cantidad_completada: 0,
  cantidad_merma: 0,
  cantidad_excedente: 0,
  notas: '',
});

const formatDate = (date) => {
  return new Date(date).toLocaleDateString('es-ES');
};

const getProcesoCardClass = (progreso) => {
  const clases = {
    pendiente: 'border-gray-300 bg-gray-50',
    en_progreso: 'border-blue-500 bg-blue-50',
    completado: 'border-green-500 bg-green-50',
  };
  return clases[progreso.estado] || 'border-gray-300';
};

const getEstadoLabel = (estado) => {
  const labels = {
    pendiente: 'bg-gray-200 text-gray-800',
    en_progreso: 'bg-blue-200 text-blue-800',
    completado: 'bg-green-200 text-green-800',
  };
  return labels[estado] || 'bg-gray-200 text-gray-800';
};

const selectProceso = (progreso) => {
  procesoSeleccionado.value = progreso;
  formulario.value = {
    cantidad_completada: progreso.cantidad_completada,
    cantidad_merma: progreso.cantidad_merma,
    cantidad_excedente: progreso.cantidad_excedente,
    notas: progreso.notas || '',
  };
};

const registrarProgreso = async () => {
  cargando.value = true;
  try {
    await axios.post(route('operador.progreso.store', props.lote.id), {
      proceso_nodo_id: procesoSeleccionado.value.proceso_nodo.id,
      ...formulario.value,
    });

    procesoSeleccionado.value = null;
    await refreshProgress();
  } catch (error) {
    console.error('Error registrando progreso:', error);
    alert('Error al registrar el progreso');
  } finally {
    cargando.value = false;
  }
};

const refreshProgress = async () => {
  try {
    const response = await axios.get(route('operador.progreso.api', props.lote.id));
    // Aquí actualizar los progresos locales
    lastRefresh.value = new Date().toLocaleTimeString();
    location.reload(); // Por ahora, recargamos la página
  } catch (error) {
    console.error('Error refrescando progreso:', error);
  }
};

onMounted(() => {
  // Actualizar cada 30 segundos automáticamente
  refreshInterval = setInterval(refreshProgress, 30000);
});

onUnmounted(() => {
  if (refreshInterval) clearInterval(refreshInterval);
});
</script>
