<template>
  <AuthenticatedLayout>
    <v-card class="p-6">
      <v-card-title>
        <div>
          <h1 class="text-2xl mb-1 font-semibold">Mi Panel de Operador</h1>
          <p class="text-gray-600 text-sm">Gestiona tus procesos asignados</p>
        </div>
      </v-card-title>

      <v-card-text>
        <!-- Procesos Asignados -->
        <div v-if="progresos && progresos.length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
          <v-card
            v-for="progreso in progresos"
            :key="progreso.id"
            class="cursor-pointer hover:shadow-lg transition-shadow"
            @click="irAlProceso(progreso)"
          >
            <v-card-title class="text-base">
              {{ progreso?.lote?.orden?.name || 'Orden sin nombre' }}
            </v-card-title>
            
            <v-card-text>
              <div class="space-y-3">
                <!-- Información de la Orden -->
                <div>
                  <p class="text-xs text-gray-600">Orden</p>
                  <p class="font-semibold">{{ progreso?.lote?.orden?.name || 'N/A' }}</p>
                </div>

                <!-- Tipo de Prenda -->
                <div>
                  <p class="text-xs text-gray-600">Tipo de Prenda</p>
                  <p class="font-semibold">{{ progreso?.lote?.orden?.tipoPrenda?.nombre || 'N/A' }}</p>
                </div>

                <!-- Proceso Actual -->
                <div>
                  <p class="text-xs text-gray-600">Proceso</p>
                  <p class="font-semibold">{{ progreso?.procesoNodo?.nombre || 'N/A' }}</p>
                </div>

                <!-- Cantidad Objetivo -->
                <div>
                  <p class="text-xs text-gray-600">Cantidad Objetivo</p>
                  <p class="font-semibold text-lg">{{ progreso?.cantidad_objetivo || 0 }}</p>
                </div>

                <!-- Progreso -->
                <div>
                  <p class="text-xs text-gray-600 mb-1">Progreso</p>
                  <div class="flex items-center gap-2">
                    <div class="flex-1 bg-gray-200 rounded-full h-2">
                      <div
                        class="bg-blue-600 h-2 rounded-full transition-all"
                        :style="{ width: `${(progreso?.cantidad_completada || 0) / (progreso?.cantidad_objetivo || 1) * 100}%` }"
                      ></div>
                    </div>
                    <span class="text-sm font-semibold whitespace-nowrap">
                      {{ progreso?.cantidad_completada || 0 }}/{{ progreso?.cantidad_objetivo || 0 }}
                    </span>
                  </div>
                  <p class="text-xs text-gray-600 mt-1">
                    {{ Math.round(((progreso?.cantidad_completada || 0) / (progreso?.cantidad_objetivo || 1)) * 100) }}%
                  </p>
                </div>

                <!-- Estado -->
                <div>
                  <v-chip
                    :color="getEstadoColor(progreso.estado)"
                    :text-color="getEstadoTextColor(progreso.estado)"
                    size="small"
                  >
                    {{ progreso.estado }}
                  </v-chip>
                </div>

                <!-- Botón de Acción -->
                <v-btn
                  color="primary"
                  variant="tonal"
                  block
                  @click.stop="irAlProceso(progreso)"
                >
                  Ver Detalle
                </v-btn>
              </div>
            </v-card-text>
          </v-card>
        </div>

        <!-- Sin Asignaciones -->
        <div v-else class="text-center py-12">
          <v-icon size="64" class="text-gray-400 mb-4">mdi-inbox-empty</v-icon>
          <p class="text-gray-600 text-lg">No tienes procesos asignados en este momento</p>
          <p class="text-gray-500 text-sm mt-2">Contacta a tu supervisor para que te asigne un proceso</p>
        </div>
      </v-card-text>
    </v-card>
  </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { router } from '@inertiajs/vue3';

const props = defineProps({
  progresos: Array,
  asignaciones: Array,
});

const irAlProceso = (progreso) => {
  router.visit(route('operador.proceso.detalle', progreso.id));
};

const getEstadoColor = (estado) => {
  const colors = {
    pendiente: 'gray',
    en_progreso: 'blue',
    completado: 'green',
  };
  return colors[estado] || 'gray';
};

const getEstadoTextColor = (estado) => {
  return 'white';
};
</script>
