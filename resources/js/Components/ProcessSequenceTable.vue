<template>
  <div class="process-sequence-container">
    <div class="mb-4 p-3 bg-blue-50 dark:bg-blue-900 rounded-lg border border-blue-200 dark:border-blue-800">
      <p class="text-sm text-blue-700 dark:text-blue-300">
        💡 Arrastra los procesos para cambiar el orden de secuencia. Haz clic en cualquier proceso para editar.
      </p>
    </div>

    <div class="overflow-x-auto">
      <table class="w-full border-collapse bg-white dark:bg-gray-800 rounded-lg overflow-hidden shadow-sm">
        <thead>
          <tr class="bg-gray-100 dark:bg-gray-700 border-b border-gray-200 dark:border-gray-600">
            <th class="w-12 px-4 py-3 text-center text-sm font-semibold text-gray-700 dark:text-gray-300">#</th>
            <th class="px-4 py-3 text-left text-sm font-semibold text-gray-700 dark:text-gray-300">Proceso</th>
            <th class="px-4 py-3 text-left text-sm font-semibold text-gray-700 dark:text-gray-300">Tipo</th>
            <th class="px-4 py-3 text-center text-sm font-semibold text-gray-700 dark:text-gray-300">Entrada/Salida</th>
            <th class="px-4 py-3 text-center text-sm font-semibold text-gray-700 dark:text-gray-300">Tiempo (min)</th>
            <th class="px-4 py-3 text-left text-sm font-semibold text-gray-700 dark:text-gray-300">Dependencias</th>
            <th class="w-20 px-4 py-3 text-center text-sm font-semibold text-gray-700 dark:text-gray-300">Acciones</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
          <tr
            v-for="(nodo, index) in sortedNodos"
            :key="nodo.id"
            draggable="true"
            @dragstart="dragStart($event, index)"
            @dragover="dragOver($event)"
            @drop="drop($event, index)"
            @dragend="dragEnd"
            :class="[
              'hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors cursor-move',
              draggedIndex === index ? 'opacity-50 bg-gray-200 dark:bg-gray-600' : '',
              dropIndex === index ? 'border-t-2 border-blue-500' : '',
            ]"
          >
            <!-- Número de orden -->
            <td class="px-4 py-3 text-center font-semibold text-gray-900 dark:text-white bg-gray-50 dark:bg-gray-800">
              {{ index + 1 }}
            </td>

            <!-- Nombre del proceso -->
            <td class="px-4 py-3">
              <div class="group relative inline-block cursor-pointer" @click="$emit('edit', nodo)">
                <div class="font-semibold text-gray-900 dark:text-white hover:text-blue-600 dark:hover:text-blue-400">
                  {{ nodo.nombre }}
                </div>

                <!-- Tooltip -->
                <div class="absolute left-0 bottom-full mb-2 hidden group-hover:block z-50 w-72 bg-gray-900 dark:bg-gray-950 text-white text-sm p-3 rounded-lg shadow-lg">
                  <div class="font-semibold mb-2 border-b border-gray-700 pb-2">{{ nodo.nombre }}</div>
                  <div class="space-y-2 text-gray-200">
                    <div><strong>ID:</strong> {{ nodo.id }}</div>
                    <div><strong>Tipo:</strong> {{ nodo.tipo }}</div>
                    <div><strong>Entrada:</strong> {{ nodo.cantidad_entrada }} → <strong>Salida:</strong> {{ nodo.cantidad_salida }}</div>
                    <div><strong>Tiempo:</strong> {{ nodo.tiempo_estimado_minutos }} minutos</div>
                    <div v-if="nodo.descripcion">
                      <strong>Descripción:</strong><br />
                      {{ nodo.descripcion }}
                    </div>
                  </div>
                  <!-- Flecha del tooltip -->
                  <div class="absolute top-full left-6 w-0 h-0 border-l-4 border-r-4 border-t-4 border-l-transparent border-r-transparent border-t-gray-900 dark:border-t-gray-950"></div>
                </div>
              </div>
            </td>

            <!-- Tipo -->
            <td class="px-4 py-3">
              <v-chip :color="nodo.tipo === 'operacion' ? 'info' : 'warning'" variant="tonal" size="small">
                {{ nodo.tipo }}
              </v-chip>
            </td>

            <!-- Cantidad entrada/salida -->
            <td class="px-4 py-3 text-center text-sm text-gray-700 dark:text-gray-300">
              {{ nodo.cantidad_entrada }} → {{ nodo.cantidad_salida }}
            </td>

            <!-- Tiempo estimado -->
            <td class="px-4 py-3 text-center text-sm font-medium text-gray-700 dark:text-gray-300">
              {{ nodo.tiempo_estimado_minutos }}
            </td>

            <!-- Dependencias -->
            <td class="px-4 py-3">
              <div v-if="nodo.dependencias?.length" class="flex flex-wrap gap-1">
                <v-chip v-for="dep in nodo.dependencias" :key="dep.id" size="x-small" variant="outlined">
                  {{ dep.nombre }}
                </v-chip>
              </div>
              <span v-else class="text-gray-400 text-sm">—</span>
            </td>

            <!-- Acciones -->
            <td class="px-4 py-3 text-center">
              <div class="flex justify-center gap-2">
                <v-btn icon color="primary" size="small" @click.prevent="$emit('edit', nodo)">
                  <v-icon>mdi-pencil</v-icon>
                </v-btn>
                <v-btn icon color="error" size="small" @click.prevent="$emit('delete', nodo.id)">
                  <v-icon>mdi-delete</v-icon>
                </v-btn>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Mensaje cuando no hay procesos -->
    <div v-if="sortedNodos.length === 0" class="mt-6 p-6 text-center bg-gray-50 dark:bg-gray-800 rounded-lg border-2 border-dashed border-gray-300 dark:border-gray-600">
      <p class="text-gray-600 dark:text-gray-400">No hay procesos aún. Crea tu primer proceso para comenzar.</p>
    </div>
  </div>
</template>

<script setup>
import { computed, ref } from 'vue';

const props = defineProps({
  nodos: {
    type: Array,
    required: true,
  },
});

const emit = defineEmits(['edit', 'delete', 'reorder']);

const draggedIndex = ref(null);
const dropIndex = ref(null);
const localNodos = ref([...props.nodos].sort((a, b) => a.orden - b.orden));

// Computed property para obtener los nodos ordenados
const sortedNodos = computed(() => {
  return localNodos.value.sort((a, b) => a.orden - b.orden);
});

const dragStart = (event, index) => {
  draggedIndex.value = index;
  event.dataTransfer.effectAllowed = 'move';
};

const dragOver = (event) => {
  event.preventDefault();
  event.dataTransfer.dropEffect = 'move';
};

const drop = (event, dropIdx) => {
  event.preventDefault();
  if (draggedIndex.value !== null && draggedIndex.value !== dropIdx) {
    const draggedNodo = sortedNodos.value[draggedIndex.value];
    const droppedNodo = sortedNodos.value[dropIdx];

    // Intercambiar órdenes
    const tempOrden = draggedNodo.orden;
    draggedNodo.orden = droppedNodo.orden;
    droppedNodo.orden = tempOrden;

    // Reordenar el array local
    localNodos.value = [...localNodos.value];

    // Emitir evento con los nodos reordenados
    emit('reorder', sortedNodos.value);
  }
  draggedIndex.value = null;
  dropIndex.value = null;
};

const dragEnd = () => {
  draggedIndex.value = null;
  dropIndex.value = null;
};
</script>

<style scoped>
.process-sequence-container {
  animation: fadeIn 0.3s ease-in-out;
}

@keyframes fadeIn {
  from {
    opacity: 0;
    transform: translateY(10px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

/* Estilos para mejorar el feedback visual del drag */
tr[draggable='true'] {
  user-select: none;
}

tr[draggable='true']:hover {
  background-color: rgba(59, 130, 246, 0.05);
}

/* Modo dark para hover */
:global(.dark) tr[draggable='true']:hover {
  background-color: rgba(59, 130, 246, 0.1);
}
</style>
