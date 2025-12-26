<template>
  <AuthenticatedLayout>
    <v-card class="p-6">
      <v-card-title class="d-flex justify-space-between align-center">
        <div>
          <h1 class="text-2xl mb-2 font-semibold">{{ procesoNodo.nombre }}</h1>
          <p class="text-gray-600 text-sm">Orden: {{ orden.name }} | Cliente: {{ orden.client }}</p>
        </div>
        <div>
          <Link :href="route('operador.dashboard')">
            <v-btn color="secondary" variant="outlined">Volver</v-btn>
          </Link>
        </div>
      </v-card-title>

      <v-card-text>
        <!-- Información de la Orden -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
          <v-card class="p-4">
            <div class="text-xs text-gray-600 mb-1">Orden</div>
            <div class="text-lg font-bold">{{ orden.name }}</div>
          </v-card>
          <v-card class="p-4">
            <div class="text-xs text-gray-600 mb-1">Tipo de Prenda</div>
            <div class="text-lg font-bold">{{ orden?.tipoPrenda?.nombre || progreso?.lote?.orden?.tipoPrenda?.nombre || 'N/A' }}</div>
          </v-card>
          <v-card class="p-4">
            <div class="text-xs text-gray-600 mb-1">Cliente</div>
            <div class="text-lg font-bold">{{ orden.client }}</div>
          </v-card>
          <v-card class="p-4">
            <div class="text-xs text-gray-600 mb-1">Cantidad Objetivo</div>
            <div class="text-lg font-bold">{{ progreso.cantidad_objetivo }}</div>
          </v-card>
        </div>

        <!-- Panel de Progreso -->
        <v-card class="mb-6">
          <v-card-title>Progreso Actual</v-card-title>
          <v-card-text>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-4">
              <div>
                <p class="text-xs text-gray-600 mb-2">Completadas</p>
                <p class="text-3xl font-bold text-green-600">{{ progreso.cantidad_completada }}</p>
                <p class="text-xs text-gray-500">de {{ progreso.cantidad_objetivo }}</p>
              </div>
              <div>
                <p class="text-xs text-gray-600 mb-2">Mermas</p>
                <p class="text-3xl font-bold text-red-600">{{ progreso.cantidad_merma || 0 }}</p>
              </div>
              <div>
                <p class="text-xs text-gray-600 mb-2">Excedentes</p>
                <p class="text-3xl font-bold text-blue-600">{{ progreso.cantidad_excedente || 0 }}</p>
              </div>
              <div>
                <p class="text-xs text-gray-600 mb-2">Progreso</p>
                <p class="text-3xl font-bold">{{ Math.round((progreso.cantidad_completada / progreso.cantidad_objetivo) * 100) }}%</p>
              </div>
            </div>

            <!-- Barra de Progreso -->
            <div class="mb-4">
              <div class="w-full bg-gray-200 rounded-full h-4">
                <div
                  class="bg-gradient-to-r from-blue-500 to-blue-600 h-4 rounded-full transition-all"
                  :style="{ width: `${(progreso.cantidad_completada / progreso.cantidad_objetivo) * 100}%` }"
                ></div>
              </div>
            </div>

            <!-- Notas -->
            <div v-if="progreso.notas" class="bg-yellow-50 dark:bg-yellow-900 p-4 rounded">
              <strong class="text-sm">Notas:</strong>
              <p class="text-sm mt-2">{{ progreso.notas }}</p>
            </div>
          </v-card-text>
        </v-card>

        <!-- Formulario de Registro -->
        <v-card>
          <v-card-title>Registrar Progreso</v-card-title>
          <v-card-text>
            <form @submit.prevent="registrarProgreso" class="space-y-4">
              <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <v-text-field
                  v-model.number="form.cantidad_completada"
                  label="Prendas Completadas"
                  type="number"
                  min="0"
                  :max="progreso.cantidad_objetivo"
                  outlined
                  :error-messages="form.errors.cantidad_completada ? [form.errors.cantidad_completada] : []"
                />
                <v-text-field
                  v-model.number="form.cantidad_merma"
                  label="Mermas"
                  type="number"
                  min="0"
                  outlined
                  :error-messages="form.errors.cantidad_merma ? [form.errors.cantidad_merma] : []"
                />
                <v-text-field
                  v-model.number="form.cantidad_excedente"
                  label="Excedentes"
                  type="number"
                  min="0"
                  outlined
                  :error-messages="form.errors.cantidad_excedente ? [form.errors.cantidad_excedente] : []"
                />
              </div>

              <v-textarea
                v-model="form.notas"
                label="Notas (opcional)"
                outlined
                rows="3"
                :error-messages="form.errors.notas ? [form.errors.notas] : []"
              />

              <div class="flex gap-4">
                <v-btn
                  type="submit"
                  color="primary"
                  :loading="loading"
                  :disabled="loading"
                >
                  Registrar Progreso
                </v-btn>
                <v-btn
                  v-if="progreso.cantidad_completada >= progreso.cantidad_objetivo"
                  color="success"
                  variant="tonal"
                  @click="completarProceso"
                  :loading="completingProcess"
                  :disabled="completingProcess || loading"
                >
                  Marcar como Completado
                </v-btn>
              </div>
            </form>
          </v-card-text>
        </v-card>
      </v-card-text>
    </v-card>
  </AuthenticatedLayout>
</template>

<script setup>
import { ref } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import axios from 'axios';

const props = defineProps({
  progreso: Object,
  procesoNodo: Object,
  lote: Object,
  orden: Object,
});

const loading = ref(false);
const completingProcess = ref(false);

const form = ref({
  cantidad_completada: props.progreso.cantidad_completada || 0,
  cantidad_merma: props.progreso.cantidad_merma || 0,
  cantidad_excedente: props.progreso.cantidad_excedente || 0,
  notas: props.progreso.notas || '',
  errors: {},
});

const registrarProgreso = async () => {
  loading.value = true;
  form.value.errors = {};

  try {
    const response = await axios.post(
      route('operador.progreso.registrar', props.progreso.id),
      {
        cantidad_completada: form.value.cantidad_completada,
        cantidad_merma: form.value.cantidad_merma,
        cantidad_excedente: form.value.cantidad_excedente,
        notas: form.value.notas,
      }
    );

    // Recargar la página para ver los datos actualizados
    router.reload();
  } catch (error) {
    if (error.response?.status === 422) {
      form.value.errors = error.response.data.errors || {};
    } else {
      console.error('Error al registrar progreso:', error);
    }
  } finally {
    loading.value = false;
  }
};

const completarProceso = async () => {
  completingProcess.value = true;

  try {
    await axios.post(
      route('operador.progreso.completar', props.progreso.id)
    );

    router.visit(route('operador.dashboard'));
  } catch (error) {
    console.error('Error al completar proceso:', error);
  } finally {
    completingProcess.value = false;
  }
};
</script>
