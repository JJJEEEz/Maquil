<template>
  <Head title="Tipos de Prendas" />
  <AuthenticatedLayout>
    <v-card class="p-6">
      <v-card-title class="d-flex justify-space-between align-center">
        <v-col cols="12" md="12">
          <v-row>
            <div>
              <h1 class="text-xl mb-1 font-semibold">Tipos de Prendas</h1>
            </div>
          </v-row>
          <v-row class="justify-end">
            <div>
              <v-btn color="primary" variant="elevated" @click="openCreate">Crear Tipo de Prenda</v-btn>
            </div>
          </v-row>
        </v-col>
      </v-card-title>

      <v-card-text>
        <div v-if="$page.props.flash?.success" class="mb-4 p-4 bg-green-100 text-green-700 rounded">
          {{ $page.props.flash?.success }}
        </div>

        <v-data-table
          :items="tiposPrendas.data"
          :headers="headers"
          item-key="id"
          dense
          class="elevation-1"
        >
          <template #item.procesos="{ item }">
            <Link :href="route('admin.proceso-nodos.index', item.id)" class="text-blue-500 hover:underline">
              Ver Procesos ({{ item.proceso_nodos?.length || 0 }})
            </Link>
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

        <div class="d-flex justify-center mt-4">
          <v-pagination
            v-if="tiposPrendas.last_page > 1"
            v-model="pageNumber"
            :length="tiposPrendas.last_page"
            @update:model-value="onPageChange"
          />
        </div>
      </v-card-text>
    </v-card>

    <Modal :show="showModal" @close="close">
      <TipoPrendaForm ref="tipoPrendaFormRef" :tipoPrenda="editItem" @saved="onSaved" @close="close" />

      <template #footer>
        <div class="modal-actions mt-4 flex justify-between px-6">
          <div>
            <SecondaryButton type="button" @click.prevent="close">Cerrar</SecondaryButton>
          </div>
          <div>
            <Button type="button" @click.prevent="tipoPrendaFormRef && tipoPrendaFormRef.submitForm()" :disabled="tipoPrendaFormRef && tipoPrendaFormRef.processing">Guardar</Button>
          </div>
        </div>
      </template>
    </Modal>
  </AuthenticatedLayout>
</template>

<script setup>
import { ref } from 'vue';
import { Head } from '@inertiajs/vue3';
import { Link, usePage } from '@inertiajs/vue3';
import { Inertia } from '@inertiajs/inertia';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Modal from '@/Components/Modal.vue';
import TipoPrendaForm from '@/Pages/Admin/TipoPrendas/Form.vue';
import Button from '@/Components/UI/Button.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';

const props = defineProps({
  tiposPrendas: Object,
});

const page = usePage();
const pageNumber = ref(props.tiposPrendas?.current_page || 1);
const showModal = ref(false);
const editItem = ref(null);
const tipoPrendaFormRef = ref(null);

const headers = [
  { title: 'Nombre', value: 'nombre' },
  { title: 'Descripción', value: 'descripcion' },
  { title: 'Procesos', value: 'procesos', sortable: false },
  { title: 'Acciones', value: 'actions', sortable: false, width: 100, align: 'center' },
];

function openCreate() {
  editItem.value = null;
  showModal.value = true;
}

function openEdit(tipo) {
  editItem.value = tipo;
  showModal.value = true;
}

function close() {
  showModal.value = false;
}

function onSaved() {
  window.location.reload();
}

function remove(id) {
  if (!confirm('Eliminar tipo de prenda?')) return;
  Inertia.delete(route('admin.tipos-prendas.destroy', id), {}, { preserveState: true });
}

function onPageChange(newPage) {
  window.location.href = `${route('admin.tipos-prendas.index')}?page=${newPage}`;
}
</script>

<style scoped>
.actions-cell { display: flex; gap: 6px; align-items: center; }
</style>
