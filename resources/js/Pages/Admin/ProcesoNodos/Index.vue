
<script setup>
import { ref } from 'vue';
import { Head } from '@inertiajs/vue3';
import { Link } from '@inertiajs/vue3';
import { Inertia } from '@inertiajs/inertia';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Modal from '@/Components/Modal.vue';
import ProcesoNodoForm from '@/Pages/Admin/ProcesoNodos/Form.vue';
import ProcessTree from '@/Components/ProcessTree.vue';

const props = defineProps({
  tipoPrenda: Object,
  nodos: Array,
});

const showModal = ref(false);
const editItem = ref(null);
const procesoNodoFormRef = ref(null);

const headers = [
  { title: 'Nombre', value: 'nombre' },
  { title: 'Tipo', value: 'tipo' },
  { title: 'Entrada/Salida', value: 'cantidad' },
  { title: 'Tiempo Est.', value: 'tiempo' },
  { title: 'Dependencias', value: 'dependencias', sortable: false },
  { title: 'Acciones', value: 'actions', sortable: false, width: 100, align: 'center' },
];

// Available nodes for dependencies (exclude self)
const nodosDisponibles = ref(props.nodos.filter(n => true));

const buildTree = () => {
  const map = new Map();
  props.nodos.forEach(nodo => map.set(nodo.id, { ...nodo, hijos: [] }));

  const tree = [];
  props.nodos.forEach(nodo => {
    const node = map.get(nodo.id);
    if (nodo.parent_id) {
      const parent = map.get(nodo.parent_id);
      if (parent) parent.hijos.push(node);
    } else {
      tree.push(node);
    }
  });

  return tree.sort((a, b) => a.orden - b.orden);
};

function openCreate() {
  editItem.value = null;
  showModal.value = true;
}

function openEdit(nodo) {
  editItem.value = nodo;
  showModal.value = true;
}

function close() {
  showModal.value = false;
}

function onSaved() {
  window.location.reload();
}

function remove(id) {
  if (!confirm('Eliminar proceso?')) return;
  Inertia.delete(route('admin.proceso-nodos.destroy', [props.tipoPrenda.id, id]), {}, { preserveState: true });
}
</script>


<template>
  <Head title="Procesos" />
  <AuthenticatedLayout>
    <v-card class="p-6">
      <v-card-title class="d-flex justify-space-between align-center">
        <v-col cols="12" md="12">
          <v-row>
            <div>
              <h1 class="text-xl mb-1 font-semibold">Procesos: {{ tipoPrenda.nombre }}</h1>
            </div>
          </v-row>
          <v-row class="justify-space-between align-center">
            <div>
              <Link :href="route('admin.tipos-prendas.index')" class="text-blue-500 hover:underline">
                ← Volver a Tipos de Prendas
              </Link>
            </div>
            <div>
              <v-btn color="primary" variant="elevated" @click="openCreate">Crear Proceso</v-btn>
            </div>
          </v-row>
        </v-col>
      </v-card-title>

      <v-card-text>
        <div v-if="$page.props.flash?.success" class="mb-4 p-4 bg-green-100 text-green-700 rounded">
          {{ $page.props.flash?.success }}
        </div>

        <!-- Visualización de Árbol de Procesos -->
        <div class="themed-panel p-4 rounded-lg overflow-x-auto mb-6">
          <h3 class="text-lg font-semibold mb-4">Diagrama de Flujo de Procesos</h3>
          <ProcessTree :nodos="nodos" @edit="openEdit" />
        </div>

        <!-- Tabla de Procesos -->
        <v-data-table
          :items="nodos"
          :headers="headers"
          item-key="id"
          dense
          class="elevation-1"
        >
          <template #item.tipo="{ item }">
            <v-chip :color="item.tipo === 'operacion' ? 'info' : 'warning'" variant="tonal" size="small">
              {{ item.tipo }}
            </v-chip>
          </template>

          <template #item.cantidad="{ item }">
            {{ item.cantidad_entrada }} → {{ item.cantidad_salida }}
          </template>

          <template #item.tiempo="{ item }">
            {{ item.tiempo_estimado_minutos }} min
          </template>

          <template #item.dependencias="{ item }">
            <div v-if="item.dependencias?.length">
              <v-chip v-for="dep in item.dependencias" :key="dep.id" size="small" class="mr-1">
                {{ dep.nombre }}
              </v-chip>
            </div>
            <span v-else class="text-gray-400 text-sm">-</span>
          </template>

          <template #item.actions="{ item }">
            <div class="actions-cell">
              <v-btn icon color="primary" size="small" @click.prevent="openEdit(item)">
                <v-icon>mdi-pencil</v-icon>
              </v-btn>
              <v-btn icon color="error" size="small" @click.prevent="remove(item.id)">
                <v-icon>mdi-delete</v-icon>
              </v-btn>
            </div>
          </template>
        </v-data-table>
      </v-card-text>
    </v-card>

    <Modal :show="showModal" @close="close">
      <ProcesoNodoForm ref="procesoNodoFormRef" :nodo="editItem" :nodosDisponibles="nodosDisponibles" :tipoPrenda="tipoPrenda" @saved="onSaved" @close="close" />

      <template #footer>
        <div class="modal-actions mt-4 d-flex justify-space-between px-6">
          <v-btn variant="outlined" @click="close">Cerrar</v-btn>
          <v-btn 
            color="primary" 
            @click="procesoNodoFormRef && procesoNodoFormRef.submitForm()" 
            :disabled="procesoNodoFormRef && procesoNodoFormRef.processing"
            :loading="procesoNodoFormRef && procesoNodoFormRef.processing"
          >
            Guardar
          </v-btn>
        </div>
      </template>
    </Modal>
  </AuthenticatedLayout>
</template>

<style scoped>
.actions-cell { display: flex; gap: 6px; align-items: center; }

/* Theme helpers */
.themed-panel {
  background: var(--v-theme-background, #F8FAFC);
  color: var(--v-theme-on-surface, #0B1220);
}

.themed-action {
  background: var(--v-theme-primary, #374151);
  color: var(--v-theme-on-primary, #FFFFFF);
  padding: 0.5rem 0.75rem;
  border-radius: 0.375rem;
}

.themed-cancel {
  background: var(--v-theme-secondary, #6B7280);
  color: var(--v-theme-on-primary, #FFFFFF);
  padding: 0.5rem 0.75rem;
  border-radius: 0.375rem;
}
</style>
