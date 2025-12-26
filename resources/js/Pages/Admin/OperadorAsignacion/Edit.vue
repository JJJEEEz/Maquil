<template>
  <AuthenticatedLayout>
    <v-card class="p-6">
      <v-card-title class="d-flex justify-space-between align-center">
        <div>
          <h1 class="text-2xl mb-2 font-semibold">Asignar Operadores</h1>
          <p class="text-gray-600 text-sm">
            Proceso: {{ progreso?.procesoNodo?.nombre || 'Cargando...' }} | Lote #{{ progreso?.lote?.id || 'Cargando...' }}
          </p>
        </div>
        <div>
          <Link :href="route('admin.ordenes.lotes.show', progreso?.lote_id)">
            <v-btn color="secondary" variant="outlined">Volver</v-btn>
          </Link>
        </div>
      </v-card-title>

      <v-card-text>
        <div v-if="isLoading && (!progreso || !progreso.procesoNodo)" class="text-center py-12">
          <v-progress-circular indeterminate></v-progress-circular>
          <p class="mt-4">Cargando datos...</p>
        </div>
        <div v-else-if="!progreso">
          <p class="text-red-600">Error: No se pudo cargar el progreso</p>
        </div>
        <div v-else>
        <!-- Información del Proceso -->
        <v-card class="mb-6">
          <v-card-title>Información del Proceso</v-card-title>
          <v-card-text>
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
              <div>
                <p class="text-xs text-gray-600 mb-1">Orden</p>
                <p class="font-semibold">{{ progreso.lote?.orden?.name || 'N/A' }}</p>
              </div>
              <div>
                <p class="text-xs text-gray-600 mb-1">Proceso</p>
                <p class="font-semibold">{{ progreso.procesoNodo?.nombre || 'N/A' }}</p>
              </div>
              <div>
                <p class="text-xs text-gray-600 mb-1">Entrada</p>
                <p class="font-semibold">{{ progreso.procesoNodo?.cantidad_entrada || 'N/A' }}</p>
              </div>
              <div>
                <p class="text-xs text-gray-600 mb-1">Salida</p>
                <p class="font-semibold">{{ progreso.procesoNodo?.cantidad_salida || 'N/A' }}</p>
              </div>
            </div>
          </v-card-text>
        </v-card>

        <!-- Asignar Nuevo Operador -->
        <v-card class="mb-6">
          <v-card-title>Asignar Nuevo Operador</v-card-title>
          <v-card-text>
            <div class="space-y-4">
              <v-select
                v-model="selectedOperador"
                :items="operadoresDisponibles"
                item-title="name"
                item-value="id"
                label="Seleccionar Operador"
                outlined
                :disabled="asignando"
              />

              <v-btn
                color="primary"
                @click="asignarOperador"
                :loading="asignando"
                :disabled="!selectedOperador || asignando"
              >
                Asignar Operador
              </v-btn>
            </div>
          </v-card-text>
        </v-card>

        <!-- Operadores Asignados -->
        <v-card>
          <v-card-title>Operadores Asignados</v-card-title>
          <v-card-text v-if="operadoresAsignados.length > 0">
            <v-list>
              <v-list-item
                v-for="asignacion in operadoresAsignados"
                :key="asignacion.user_id"
              >
                <template #prepend>
                  <v-icon>mdi-account</v-icon>
                </template>

                <v-list-item-title>{{ asignacion.nombre }}</v-list-item-title>
                <v-list-item-subtitle>{{ asignacion.email }}</v-list-item-subtitle>

                <template #append>
                  <v-btn
                    icon
                    size="small"
                    color="error"
                    @click="removerOperador(asignacion.user_id)"
                    :loading="removiendo === asignacion.user_id"
                  >
                    <v-icon>mdi-delete</v-icon>
                  </v-btn>
                </template>
              </v-list-item>
            </v-list>
          </v-card-text>
          <v-card-text v-else class="text-center py-8 text-gray-500">
            No hay operadores asignados a este proceso
          </v-card-text>
        </v-card>
        </div>
      </v-card-text>
    </v-card>
  </AuthenticatedLayout>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue';
import { Link } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import axios from 'axios';

const props = defineProps({
  progreso: Object,
  operadores: Array,
  asignacionesActuales: Object,
});

const selectedOperador = ref(null);
const asignando = ref(false);
const removiendo = ref(null);
const operadoresAsignados = ref([]);
const isLoading = ref(true);

const operadoresDisponibles = computed(() => {
  if (!props.operadores) return [];
  return props.operadores.filter(op => !operadoresAsignados.value.some(a => a.user_id === op.id));
});

// Cargar datos cuando el componente monta
onMounted(async () => {
  try {
    // Si tenemos props.progreso, usa los datos del servidor
    if (props.progreso && props.progreso.id) {
      await obtenerAsignados();
    }
  } catch (error) {
    console.error('Error al cargar datos iniciales:', error);
  } finally {
    isLoading.value = false;
  }
});

// Watcher para progreso en caso de que cambie
watch(() => props.progreso, async (newVal) => {
  if (newVal && newVal.id) {
    isLoading.value = true;
    try {
      await obtenerAsignados();
    } finally {
      isLoading.value = false;
    }
  }
}, { deep: true });

const obtenerAsignados = async () => {
  try {
    const response = await axios.get(
      route('admin.operador-asignacion.getAsignados', props.progreso.id)
    );
    operadoresAsignados.value = response.data;
  } catch (error) {
    console.error('Error al obtener asignados:', error);
  }
};

const asignarOperador = async () => {
  if (!selectedOperador.value) return;

  asignando.value = true;

  try {
    await axios.post(
      route('admin.operador-asignacion.assign', props.progreso.id),
      {
        user_id: selectedOperador.value,
      }
    );

    selectedOperador.value = null;
    await obtenerAsignados();
  } catch (error) {
    console.error('Error al asignar operador:', error);
  } finally {
    asignando.value = false;
  }
};

const removerOperador = async (userId) => {
  removiendo.value = userId;

  try {
    await axios.post(
      route('admin.operador-asignacion.remove', props.progreso.id),
      {
        user_id: userId,
      }
    );

    await obtenerAsignados();
  } catch (error) {
    console.error('Error al remover operador:', error);
  } finally {
    removiendo.value = null;
  }
};
</script>
