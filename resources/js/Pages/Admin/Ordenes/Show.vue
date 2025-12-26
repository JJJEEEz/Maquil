<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Link, usePage } from '@inertiajs/vue3';
import { computed, ref, reactive } from 'vue';
import Modal from '@/Components/Modal.vue';
import Button from '@/Components/UI/Button.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import DatePicker from '@/Components/DatePicker.vue';
import { Head } from '@inertiajs/vue3';

const props = defineProps({
  orden: { type: Object, default: null },
  authPermissions: { type: Array, default: () => [] },
});
const orden = computed(() => props.orden);
// local mutable copy of orden so we can update lotes without reloading
const ordenLocal = ref(orden.value ? JSON.parse(JSON.stringify(orden.value)) : null);
// table headers for lotes (Vuetify 3 expects `title` and `key`)
const loteHeaders = [
  { title: 'ID', key: 'id', width: 60, align: 'center' },
  { title: 'Fecha', key: 'fecha' },
  { title: 'Estado', key: 'estado_trabajo' },
  { title: 'Prendas', key: 'total_prendas_terminadas', align: 'right' },
  { title: 'Mermas', key: 'total_mermas', align: 'right' },
  { title: 'Acciones', key: 'actions', sortable: false, width: 200, align: 'center' },
];


// computed sorted list (force sort by id desc so newest/highest id first)
const sortedLotes = computed(() => {
  const list = (ordenLocal.value && Array.isArray(ordenLocal.value.lotes)) ? [...ordenLocal.value.lotes] : [];
  return list.sort((a, b) => {
    const ai = Number(a && a.id ? a.id : 0);
    const bi = Number(b && b.id ? b.id : 0);
    return bi - ai;
  });
});

function loteStatus(item) {
  // derive a simple status from dates if explicit status not provided
  if (!item) return '-';
  if (item.status) return item.status;
  if (item.ended_at) return 'Completado';
  if (item.started_at) return 'En progreso';
  return 'Pendiente';
}

const loteModalOpen = ref(false);
const editingLote = ref(null);
const processing = ref(false);

const authPermissions = props.authPermissions || [];
const canCreateLote = authPermissions.includes('lotes.create');
const canEditLote = authPermissions.includes('lotes.edit');
const canDeleteLote = authPermissions.includes('lotes.delete');
const canViewLote = authPermissions.includes('lotes.view');
const canRegistrarProcesos = authPermissions.includes('procesos.registrar');

const loteForm = reactive({
  fecha: null,
  estado_trabajo: 'no_trabajado',
  razon_interrupcion: null,
});

const estadoTrabajoOptions = [
  { title: 'No trabajado', value: 'no_trabajado' },
  { title: 'Trabajado', value: 'trabajado' },
  { title: 'Interrumpido', value: 'interrumpido' },
];
const statusProcessing = ref({});

function updateLoteStatus(lote, newStatus) {
  if (!lote || !lote.id) return;
  const id = lote.id;
  const prev = lote.status;
  // optimistic update
  lote.status = newStatus;
  statusProcessing.value[id] = true;

  window.axios.put(route('admin.ordenes.lotes.update', id), { status: newStatus })
    .then((res) => {
      // if server returned updated lote, sync fields
      if (res.data && res.data.lote) {
        const updated = res.data.lote;
        // find in local list and replace fields
        if (ordenLocal.value && Array.isArray(ordenLocal.value.lotes)) {
          const idx = ordenLocal.value.lotes.findIndex(l => l.id === id);
          if (idx !== -1) {
            ordenLocal.value.lotes.splice(idx, 1, { ...ordenLocal.value.lotes[idx], ...updated });
          }
        }
      }
    })
    .catch((err) => {
      // rollback on error
      lote.status = prev;
    })
    .finally(() => {
      statusProcessing.value[id] = false;
    });
}

function openCreateLote() {
  editingLote.value = null;
  // Set today's date by default
  const today = new Date();
  const year = today.getFullYear();
  const month = String(today.getMonth() + 1).padStart(2, '0');
  const day = String(today.getDate()).padStart(2, '0');
  loteForm.fecha = `${year}-${month}-${day}`;
  loteForm.estado_trabajo = 'no_trabajado';
  loteForm.razon_interrupcion = null;
  loteModalOpen.value = true;
}

function openEditLote(lote) {
  editingLote.value = lote;
  loteForm.fecha = lote.fecha ?? null;
  loteForm.estado_trabajo = lote.estado_trabajo ?? 'no_trabajado';
  loteForm.razon_interrupcion = lote.razon_interrupcion ?? null;
  loteModalOpen.value = true;
}

function closeLoteModal() {
  loteModalOpen.value = false;
}

function submitLote() {
  if (!orden.value || !orden.value.id) return;
  const payload = {
    fecha: loteForm.fecha || null,
    estado_trabajo: loteForm.estado_trabajo || 'no_trabajado',
    razon_interrupcion: loteForm.razon_interrupcion || null,
  };

  processing.value = true;
  localErrors.value = {};

  // Use axios XHR requests (no X-Inertia header) so we can receive plain JSON and just close the modal
  if (editingLote.value && editingLote.value.id) {
    window.axios.put(route('admin.ordenes.lotes.update', editingLote.value.id), payload)
      .then((res) => {
        // replace updated lote in local list
        if (ordenLocal.value && ordenLocal.value.lotes) {
          const idx = ordenLocal.value.lotes.findIndex(l => l.id === editingLote.value.id);
          if (idx !== -1) ordenLocal.value.lotes.splice(idx, 1, res.data.lote ?? res.data);
        }
        closeLoteModal();
      })
      .catch((err) => {
        if (err.response && err.response.status === 422) {
          localErrors.value = err.response.data.errors || {};
        }
      })
      .finally(() => (processing.value = false));
  } else {
    window.axios.post(route('admin.ordenes.lotes.store', orden.value.id), payload)
      .then((res) => {
        // push new lote into local list
        if (ordenLocal.value) {
          ordenLocal.value.lotes = ordenLocal.value.lotes || [];
          ordenLocal.value.lotes.push(res.data.lote ?? res.data);
        }
        closeLoteModal();
      })
      .catch((err) => {
        if (err.response && err.response.status === 422) {
          localErrors.value = err.response.data.errors || {};
        }
      })
      .finally(() => (processing.value = false));
  }
}

function deleteLote(loteId) {
  if (!confirm('¿Eliminar lote? Esta acción no se puede deshacer.')) return;
  processing.value = true;
  // Use axios delete to avoid X-Inertia navigation and update local list
  window.axios.delete(route('admin.ordenes.lotes.destroy', loteId))
    .then(() => {
      if (ordenLocal.value && ordenLocal.value.lotes) {
        ordenLocal.value.lotes = ordenLocal.value.lotes.filter(l => l.id !== loteId);
      }
    })
    .catch(() => {
      // ignore for now or show a toast
    })
    .finally(() => (processing.value = false));
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
  return String(input);
}

function formatEstadoTrabajo(estado) {
  const map = {
    trabajado: 'Trabajado',
    no_trabajado: 'No trabajado',
    interrumpido: 'Interrumpido',
  };
  return map[estado] || estado;
}

function getEstadoColor(estado) {
  switch (estado) {
    case 'trabajado':
      return 'success';
    case 'interrumpido':
      return 'error';
    case 'no_trabajado':
    default:
      return 'grey';
  }
}

// expose errors from server-side validation
const page = usePage();
const localErrors = ref({});
const errors = computed(() => {
  // prefer local validation errors from axios requests, fallback to server-side errors in page props
  if (localErrors.value && Object.keys(localErrors.value).length) return localErrors.value;
  return (page.props.value && page.props.value.errors) ? page.props.value.errors : {};
});
</script>

<template>
  <Head title="Lotes" />
  <AuthenticatedLayout>
    <v-card class="p-6">
      <v-card-title class="d-flex justify-space-between align-center">
        <div>
          <h1 class="text-xl mb-1 font-semibold">Lotes de la Orden: {{ orden ? orden.name : '—' }}</h1>
          <div class="text-sm">Cliente: {{ orden && orden.client ? orden.client : '-' }}</div>
        <Breadcrumbs :items="[{ text: 'Panel ', href: route('welcome') }, { text: 'Ordenes', href: route('admin.ordenes.index') }, { text: (orden ? orden.name : '') }]" />
        </div>
        <div>
          <Link :href="route('admin.ordenes.index')">
            <v-btn color="secondary" variant="outlined">Volver</v-btn>
          </Link>
        </div>
      </v-card-title>

      <v-card-text>
        <div v-if="!orden">
          <p>No se encontró la orden.</p>
        </div>
        <div v-else>
          <div class="d-flex justify-space-between items-center mb-2">
            <div class="d-flex flex-column">
              <div class="text-sm">Fecha de entrega esperada: {{ ordenLocal && ordenLocal.target_date ? formatDateLocal(ordenLocal.target_date) : '-' }}</div>
            </div>
            <v-btn v-if="canCreateLote" color="primary" variant="elevated" size="small" @click="openCreateLote" :disabled="processing">Nuevo Lote</v-btn>
          </div>

          <v-data-table :headers="loteHeaders" :items="sortedLotes" item-key="id" dense :sort-by="['id']" :sort-desc="[true]">
            <template #no-data>
              <div class="text-center pa-6">No hay lotes disponibles para esta orden.</div>
            </template>
            <template #item.id="{ item }">{{ item.id }}</template>
            <template #item.fecha="{ item }">{{ item.fecha ? formatDateLocal(item.fecha) : '-' }}</template>
            <template #item.estado_trabajo="{ item }">
              <v-chip :color="getEstadoColor(item.estado_trabajo)" variant="tonal" size="small">
                {{ formatEstadoTrabajo(item.estado_trabajo) }}
              </v-chip>
            </template>
            <template #item.total_prendas_terminadas="{ item }">{{ item.total_prendas_terminadas || 0 }}</template>
            <template #item.total_mermas="{ item }">{{ item.total_mermas || 0 }}</template>
            <template #item.actions="{ item }">
              <div style="display:flex; gap:6px; align-items:center">
                  <Link v-if="canViewLote" :href="route('admin.lotes.show', item.id)">
                    <v-btn icon color="info" size="small" title="Ver Detalle">
                      <v-icon>mdi-eye</v-icon>
                    </v-btn>
                  </Link>
                  <v-btn v-if="canEditLote" icon color="primary" size="small" @click.prevent="openEditLote(item)">
                    <v-icon>mdi-pencil</v-icon>
                  </v-btn>
                  <v-btn v-if="canDeleteLote" icon color="error" size="small" @click.prevent="deleteLote(item.id)">
                    <v-icon>mdi-delete</v-icon>
                  </v-btn>
              </div>
            </template>
          </v-data-table>
        </div>
      </v-card-text>
    </v-card>
    <Modal :show="loteModalOpen" @close="closeLoteModal">
      <div class="p-6">
        <h3 class="text-lg font-semibold mb-4">{{ editingLote ? 'Editar Lote' : 'Crear Lote' }}</h3>
        <div class="grid grid-cols-1 gap-4">
          <div>
            <label class="block text-sm mb-1">Fecha</label>
            <DatePicker v-model="loteForm.fecha" />
            <div v-if="errors.fecha" class="text-red-600 text-sm mt-1">{{ errors.fecha[0] }}</div>
          </div>

          <div>
            <label class="block text-sm mb-1">Estado de Trabajo</label>
            <select 
              v-model="loteForm.estado_trabajo" 
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
            >
              <option value="no_trabajado">No trabajado</option>
              <option value="trabajado">Trabajado</option>
              <option value="interrumpido">Interrumpido</option>
            </select>
            <div v-if="errors.estado_trabajo" class="text-red-600 text-sm mt-1">{{ errors.estado_trabajo[0] }}</div>
          </div>

          <div v-if="loteForm.estado_trabajo === 'interrumpido'">
            <label class="block text-sm mb-1">Razón de Interrupción</label>
            <v-textarea 
              v-model="loteForm.razon_interrupcion" 
              rows="3" 
              density="compact"
              placeholder="Explique la razón de la interrupción..."
            />
            <div v-if="errors.razon_interrupcion" class="text-red-600 text-sm mt-1">{{ errors.razon_interrupcion[0] }}</div>
          </div>
        </div>
      </div>

      <template #footer>
        <div class="flex justify-end gap-3 px-6">
          <SecondaryButton type="button" @click.prevent="closeLoteModal" :disabled="processing">Cancelar</SecondaryButton>
          <Button type="button" @click.prevent="submitLote" :disabled="processing">{{ processing ? 'Guardando...' : 'Guardar' }}</Button>
        </div>
      </template>
    </Modal>
  </AuthenticatedLayout>
</template>
