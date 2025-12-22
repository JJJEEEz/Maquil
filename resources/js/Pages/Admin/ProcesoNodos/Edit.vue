<template>
  <AuthenticatedLayout>
    <template #header>
      <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
        Editar Proceso: {{ nodo.nombre }}
      </h2>
    </template>

    <div class="py-12">
      <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
        <div class="themed-panel overflow-hidden shadow-sm sm:rounded-lg">
          <div class="p-6 themed-text">
            <form @submit.prevent="submit" class="space-y-6">
              <div>
                <label class="block text-sm font-medium mb-2">Nombre del Proceso</label>
                <input
                  v-model="form.nombre"
                  type="text"
                  class="w-full px-4 py-2 border rounded-lg themed-input"
                  required
                />
                <div v-if="form.errors.nombre" class="text-red-500 text-sm mt-1">
                  {{ form.errors.nombre }}
                </div>
              </div>

              <div class="grid grid-cols-2 gap-4">
                <div>
                  <label class="block text-sm font-medium mb-2">Tipo</label>
                  <select
                    v-model="form.tipo"
                    class="w-full px-4 py-2 border rounded-lg themed-input"
                  >
                    <option value="operacion">Operación</option>
                    <option value="inspeccion">Inspección</option>
                  </select>
                </div>
              </div>

              <div class="grid grid-cols-2 gap-4">
                <div>
                  <label class="block text-sm font-medium mb-2">Cantidad Entrada</label>
                  <input
                    v-model.number="form.cantidad_entrada"
                    type="number"
                    min="1"
                    class="w-full px-4 py-2 border rounded-lg themed-input"
                    required
                  />
                </div>

                <div>
                  <label class="block text-sm font-medium mb-2">Cantidad Salida</label>
                  <input
                    v-model.number="form.cantidad_salida"
                    type="number"
                    min="1"
                    class="w-full px-4 py-2 border rounded-lg themed-input"
                    required
                  />
                </div>
              </div>

              <div>
                <label class="block text-sm font-medium mb-2">Tiempo Estimado (minutos)</label>
                <input
                  v-model.number="form.tiempo_estimado_minutos"
                  type="number"
                  min="1"
                  class="w-full px-4 py-2 border rounded-lg themed-input"
                />
              </div>

              <div>
                <label class="block text-sm font-medium mb-2">Procesos de los que depende</label>
                <div class="space-y-2">
                  <label v-for="nodoOpt in nodosDisponiblesFiltrados" :key="nodoOpt.id" class="flex items-center">
                    <input
                      type="checkbox"
                      :value="nodoOpt.id"
                      v-model="form.dependencias"
                      class="mr-2"
                    />
                    <span>{{ nodoOpt.nombre }} (Orden: {{ nodoOpt.orden }})</span>
                  </label>
                  <p v-if="nodosDisponiblesFiltrados.length === 0" class="text-gray-500 text-sm">
                    No hay procesos con orden menor disponibles
                  </p>
                </div>
              </div>

              <div class="flex gap-4">
                <button
                  type="submit"
                  :disabled="form.processing"
                  class="themed-action"
                >
                  Guardar Cambios
                </button>
                <Link
                  :href="route('admin.proceso-nodos.index', tipoPrenda.id)"
                  class="themed-cancel"
                >
                  Cancelar
                </Link>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>

<script setup>
import { computed } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Link, useForm } from '@inertiajs/vue3';

const props = defineProps({
  tipoPrenda: Object,
  nodo: Object,
  nodosDisponibles: Array,
});

const form = useForm({
  nombre: props.nodo.nombre,
  tipo: props.nodo.tipo,
  orden: props.nodo.orden,
  cantidad_entrada: props.nodo.cantidad_entrada,
  cantidad_salida: props.nodo.cantidad_salida,
  tiempo_estimado_minutos: props.nodo.tiempo_estimado_minutos,
  dependencias: props.nodo.dependencias.map(d => d.id),
});

// Filtrar solo procesos con orden menor al actual
const nodosDisponiblesFiltrados = computed(() => {
  const ordenActual = Number(form.orden);
  if (ordenActual <= 0) return [];
  return props.nodosDisponibles.filter(nodo => 
    nodo.id !== props.nodo.id && Number(nodo.orden) < ordenActual
  );
});

const submit = () => {
  form.put(route('admin.proceso-nodos.update', [props.tipoPrenda.id, props.nodo.id]));
};
</script>

<style scoped>
.themed-panel { background: var(--v-theme-surface, #FFFFFF); color: var(--v-theme-on-surface, #0B1220); }
.themed-text { color: var(--v-theme-on-surface, #0B1220); }
.themed-action { background: var(--v-theme-primary, #374151); color: var(--v-theme-on-primary, #FFFFFF); padding: 0.5rem 0.75rem; border-radius: 0.375rem; }
.themed-action:disabled { opacity: 0.6; }
.themed-cancel { background: var(--v-theme-secondary, #6B7280); color: var(--v-theme-on-primary, #FFFFFF); padding: 0.5rem 0.75rem; border-radius: 0.375rem; }
</style>
