<script setup>
import { ref, computed, onMounted, watch } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import { Inertia } from '@inertiajs/inertia';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Modal from '@/Components/Modal.vue';
import OrdenForm from '@/Pages/Admin/Ordenes/Form.vue';
import Button from '@/Components/UI/Button.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import Breadcrumbs from '@/Components/Breadcrumbs.vue';
import { Head } from '@inertiajs/vue3';

const props = defineProps({ ordenes: Object });
const ordenes = props.ordenes;

const page = usePage();

const authPermissions = computed(() => {
  try {
    return (props.authPermissions && props.authPermissions.length) ? props.authPermissions : (page.props && page.props.authPermissions ? page.props.authPermissions : []);
  } catch (e) {
    return [];
  }
});

const canCreate = computed(() => authPermissions.value.includes('ordenes.create'));
const canEdit = computed(() => authPermissions.value.includes('ordenes.edit'));
const canDelete = computed(() => authPermissions.value.includes('ordenes.delete'));
const canViewLotes = computed(() => authPermissions.value.includes('lotes.view'));

onMounted(() => {
  try {
    console.log('authPermissions (shared):', JSON.parse(JSON.stringify(authPermissions.value || [])));
    watch(authPermissions, (val) => {
      try {
        console.log('authPermissions (changed):', JSON.parse(JSON.stringify(val || [])));
      } catch (e) {
        console.log('authPermissions (changed):', val);
      }
    });
  } catch (e) {
    // ignore
  }
});


const headers = [
  { title: 'ID', value: 'id', width: 60, align: 'center' },
  { title: 'Nombre', value: 'name' },
  { title: 'Descripción', value: 'description' },
  { title: 'Cliente', value: 'client' },
  { title: 'Calidad', value: 'quality' },
  { title: 'Estatus', value: 'status' },
  { title: 'Cantidad', value: 'target_quantity', align: 'right' },
  { title: 'Fecha target', value: 'target_date' },
  { title: 'Acciones', value: 'actions', sortable: false, width: 140, align: 'center' },
];

const showModal = ref(false);
const editItem = ref(null);
const ordenFormRef = ref(null);

function openCreate() {
  editItem.value = null;
  showModal.value = true;
}

function openEdit(orden) {
  editItem.value = orden;
  showModal.value = true;
}

function close() {
  showModal.value = false;
}

function onSaved() {
  // refresh page after save
  window.location.reload();
}

function remove(id) {
if (!confirm('Eliminar orden?')) return;
  Inertia.delete(route('admin.ordenes.destroy', id), {}, { preserveState: true });
}

function formatDateLocal(input) {
  if (!input) return '-';
  if (input instanceof Date) return input.toLocaleDateString();

  const s = String(input);
  const m = s.match(/^(\d{4})-(\d{2})-(\d{2})/);
  if (m) {
    const y = Number(m[1]);
    const mo = Number(m[2]) - 1;
    const d = Number(m[3]);
    return new Date(y, mo, d).toLocaleDateString();
  }

  const parsed = new Date(input);
  if (!isNaN(parsed.getTime())) return parsed.toLocaleDateString();

  return s;
}

function formatStatus(status) {
  const map = {
    pending: 'Pendiente',
    in_progress: 'En proceso',
    completed: 'Completado',
    cancelled: 'Cancelado',
  };
  return map[status] ?? String(status);
}

function statusColor(status) {
  switch (status) {
    case 'in_progress':
      return 'info';
    case 'completed':
      return 'success';
    case 'cancelled':
      return 'error';
    default:
      return 'grey';
  }
}
</script>

<template>
    <Head title="Ordenes" />
    <AuthenticatedLayout>
    <v-card class="p-6">
        <v-card-title class="d-flex justify-space-between align-center">
        <v-col cols="12" md="12">
          <v-row>
            <div>
              <h1 class="text-xl mb-1 font-semibold">Ordenes</h1>
              <Breadcrumbs :items="[{ text: 'Panel ', href: route('welcome') }, { text: 'Ordenes' }]" />
            </div>
          </v-row>
          <v-row class="justify-end">
            <div v-if="canCreate">
              <v-btn color="primary" variant="elevated" @click="openCreate">Crear</v-btn>
            </div>
          </v-row>
        </v-col>
      </v-card-title>

      <v-card-text>
        <v-data-table
          :items="ordenes.data"
          :headers="headers"
          item-key="id"
          dense
          class="elevation-1"
        >
          <template #item.target_date="{ item }">
            {{ item.target_date ? formatDateLocal(item.target_date) : '-' }}
          </template>

          <template #item.status="{ item }">
            <v-chip :color="statusColor(item.status)" variant="tonal" size="small">{{ formatStatus(item.status) }}</v-chip>
          </template>
          <template #item.actions="{ item }">
            <div class="actions-cell">
              <v-btn v-if="canEdit" icon color="primary" size="small" @click.prevent="openEdit(item)">
                <v-icon>mdi-pencil</v-icon>
              </v-btn>
              <v-btn v-if="canDelete" icon color="error" size="small" @click.prevent="remove(item.id)">
                <v-icon>mdi-delete</v-icon>
              </v-btn>
              <Link v-if="canViewLotes" :href="route('admin.ordenes.lotes.index', item.id)">
                <v-btn icon color="secondary" size="small" title="Lotes">
                  <v-icon>mdi-format-list-bulleted</v-icon>
                </v-btn>
              </Link>
            </div>
          </template>
        </v-data-table>
      </v-card-text>
    </v-card>

    <Modal :show="showModal" @close="close">
      <OrdenForm ref="ordenFormRef" :orden="editItem" @saved="onSaved" @close="close" />

      <template #footer>
        <div class="modal-actions mt-4 flex justify-between px-6">
          <div>
            <SecondaryButton type="button" @click.prevent="close">Cerrar</SecondaryButton>
          </div>
          <div>
            <Button type="button" @click.prevent="ordenFormRef && ordenFormRef.submitForm()" :disabled="ordenFormRef && ordenFormRef.processing">Guardar</Button>
          </div>
        </div>
      </template>
    </Modal>
  </AuthenticatedLayout>
</template>

<style scoped>
.actions-cell{ display:flex; gap:6px; align-items:center }
</style>
