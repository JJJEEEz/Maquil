<template>
  <AuthenticatedLayout>
    <template #header>
      <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
        Registrar Progreso - {{ orden.name }}
      </h2>
    </template>

    <div class="py-12">
      <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
          <div class="p-6">
            <div class="mb-6 p-4 bg-blue-50 dark:bg-blue-900 rounded-lg">
              <p class="text-sm">
                <strong>Lote:</strong> {{ lote.fecha }} |
                <strong>Objetivo:</strong> {{ orden.target_quantity }} prendas |
                <strong>Tipo:</strong> {{ orden.tipo_prenda.nombre }}
              </p>
            </div>

            <div class="space-y-6">
              <div
                v-for="progreso in progresos"
                :key="progreso.id"
                class="border border-gray-200 dark:border-gray-700 rounded-lg p-6"
              >
                <h3 class="text-lg font-semibold mb-2">{{ progreso.proceso_nodo.nombre }}</h3>
                <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">
                  Entrada: {{ progreso.proceso_nodo.cantidad_entrada }} piezas | 
                  Salida: {{ progreso.proceso_nodo.cantidad_salida }} piezas
                </p>

                <form @submit.prevent="() => submitProgreso(progreso.id)" class="space-y-4">
                  <div class="grid grid-cols-3 gap-4">
                    <div>
                      <label class="block text-sm font-medium mb-2">Cantidad Completada</label>
                      <input
                        v-model.number="formularios[progreso.id].cantidad_completada"
                        type="number"
                        min="0"
                        :max="orden.target_quantity"
                        class="w-full px-4 py-2 border rounded-lg dark:bg-gray-700"
                        required
                      />
                    </div>

                    <div>
                      <label class="block text-sm font-medium mb-2">Merma</label>
                      <input
                        v-model.number="formularios[progreso.id].cantidad_merma"
                        type="number"
                        min="0"
                        class="w-full px-4 py-2 border rounded-lg dark:bg-gray-700"
                      />
                    </div>

                    <div>
                      <label class="block text-sm font-medium mb-2">Excedente</label>
                      <input
                        v-model.number="formularios[progreso.id].cantidad_excedente"
                        type="number"
                        min="0"
                        class="w-full px-4 py-2 border rounded-lg dark:bg-gray-700"
                      />
                    </div>
                  </div>

                  <div>
                    <label class="block text-sm font-medium mb-2">Notas</label>
                    <textarea
                      v-model="formularios[progreso.id].notas"
                      class="w-full px-4 py-2 border rounded-lg dark:bg-gray-700"
                      rows="2"
                      placeholder="Ej: Tela defectuosa, máquina lenta..."
                    ></textarea>
                  </div>

                  <div class="flex gap-2">
                    <button
                      type="submit"
                      :disabled="cargando[progreso.id]"
                      class="bg-green-500 hover:bg-green-700 disabled:opacity-50 text-white font-bold py-2 px-4 rounded"
                    >
                      {{ cargando[progreso.id] ? 'Guardando...' : 'Guardar Progreso' }}
                    </button>
                    <button
                      v-if="progreso.estado !== 'completado'"
                      type="button"
                      @click="() => marcarCompletado(progreso.id)"
                      :disabled="cargando[progreso.id]"
                      class="bg-blue-500 hover:bg-blue-700 disabled:opacity-50 text-white font-bold py-2 px-4 rounded"
                    >
                      Marcar Completado
                    </button>
                  </div>

                  <div v-if="mensajes[progreso.id]" :class="mensajes[progreso.id].tipo === 'success' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'" class="p-3 rounded text-sm">
                    {{ mensajes[progreso.id].texto }}
                  </div>
                </form>

                <!-- Estado Actual -->
                <div v-if="progreso" class="mt-4 pt-4 border-t">
                  <p class="text-xs text-gray-600">
                    <strong>Completadas:</strong> {{ progreso.cantidad_completada }} / {{ progreso.cantidad_objetivo }} |
                    <strong>Mermas:</strong> {{ progreso.cantidad_merma }} |
                    <strong>Excedentes:</strong> {{ progreso.cantidad_excedente }} |
                    <strong>Estado:</strong> <span :class="getEstadoClass(progreso.estado)">{{ progreso.estado }}</span>
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
import { ref } from 'vue';
import axios from 'axios';

const props = defineProps({
  lote: Object,
  orden: Object,
  progresos: Array,
  procesoNodos: Array,
});

const formularios = ref({});
const cargando = ref({});
const mensajes = ref({});

// Inicializar formularios
props.progresos.forEach(progreso => {
  formularios.value[progreso.id] = {
    cantidad_completada: progreso.cantidad_completada,
    cantidad_merma: progreso.cantidad_merma,
    cantidad_excedente: progreso.cantidad_excedente,
    notas: progreso.notas || '',
  };
  cargando.value[progreso.id] = false;
  mensajes.value[progreso.id] = null;
});

const submitProgreso = async (procesoId) => {
  cargando.value[procesoId] = true;
  try {
    const progreso = props.progresos.find(p => p.id === procesoId);
    const response = await axios.post(route('operador.progreso.store', props.lote.id), {
      proceso_nodo_id: progreso.proceso_nodo.id,
      ...formularios.value[procesoId],
    });

    mensajes.value[procesoId] = {
      tipo: 'success',
      texto: 'Progreso registrado correctamente',
    };

    setTimeout(() => {
      mensajes.value[procesoId] = null;
    }, 3000);
  } catch (error) {
    console.error('Error:', error);
    mensajes.value[procesoId] = {
      tipo: 'error',
      texto: 'Error al registrar el progreso',
    };
  } finally {
    cargando.value[procesoId] = false;
  }
};

const marcarCompletado = async (procesoId) => {
  cargando.value[procesoId] = true;
  try {
    const progreso = props.progresos.find(p => p.id === procesoId);
    await axios.post(route('operador.progreso.marcar-completado', props.lote.id), {
      proceso_nodo_id: progreso.proceso_nodo.id,
    });

    mensajes.value[procesoId] = {
      tipo: 'success',
      texto: 'Proceso marcado como completado',
    };

    setTimeout(() => {
      location.reload();
    }, 1000);
  } catch (error) {
    console.error('Error:', error);
    mensajes.value[procesoId] = {
      tipo: 'error',
      texto: 'Error al marcar como completado',
    };
  } finally {
    cargando.value[procesoId] = false;
  }
};

const getEstadoClass = (estado) => {
  const clases = {
    pendiente: 'bg-gray-200 text-gray-800',
    en_progreso: 'bg-blue-200 text-blue-800',
    completado: 'bg-green-200 text-green-800',
  };
  return clases[estado] || 'bg-gray-200 text-gray-800';
};
</script>
