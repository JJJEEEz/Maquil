<script setup>
import { computed } from 'vue';
import { useForm } from '@inertiajs/vue3';
import axios from 'axios';
import { Head } from '@inertiajs/vue3';

const incoming = defineProps({ orden: Object, tiposPrendas: Array });
const emit = defineEmits(['saved', 'close']);

const isEdit = computed(() => !!incoming.orden && incoming.orden !== null);

const tiposPrendasOptions = computed(() => {
  if (!incoming.tiposPrendas) return [];
  return incoming.tiposPrendas.map(tp => ({
    title: tp.nombre,
    value: tp.id
  }));
});

const form = useForm({
  name: incoming.orden ? incoming.orden.name : '',
  description: incoming.orden ? incoming.orden.description : '',
  client: incoming.orden ? incoming.orden.client : '',
  quality: incoming.orden ? incoming.orden.quality : '',
  status: incoming.orden ? incoming.orden.status : 'pending',
  target_quantity: incoming.orden ? incoming.orden.target_quantity : 0,
  target_date: incoming.orden ? (incoming.orden.target_date ? incoming.orden.target_date : '') : '',
  tipo_prenda_id: incoming.orden ? incoming.orden.tipo_prenda_id : null,
});

const todayStr = new Date().toISOString().slice(0,10);
const allowedDates = (val) => {
  if (!val) return true;
  return val >= todayStr;
};

function submit() {
  if (isEdit.value) {
    const payload = {
      name: form.name,
      description: form.description,
      client: form.client,
      quality: form.quality,
      status: form.status,
      target_quantity: (form.target_quantity === '' || form.target_quantity === null) ? null : Number(form.target_quantity),
      target_date: form.target_date && String(form.target_date).trim() !== '' ? form.target_date : null,
      tipo_prenda_id: form.tipo_prenda_id ? Number(form.tipo_prenda_id) : null,
    };

    axios.put(route('admin.ordenes.update', incoming.orden.id), payload)
      .then((res) => {
        emit('saved');
        emit('close');
      })
      .catch((err) => {
        console.error('Orden update error:', err.response ? err.response.data : err);
        if (err.response && err.response.status === 422) {
          const errors = err.response.data.errors || {};
          for (const k in form.errors) delete form.errors[k];
          Object.assign(form.errors, errors);
        }
      });
  } else {
    const payload = {
      name: form.name,
      description: form.description,
      client: form.client,
      quality: form.quality,
      status: form.status,
      target_quantity: form.target_quantity,
      target_date: form.target_date && String(form.target_date).trim() !== '' ? form.target_date : null,
      tipo_prenda_id: form.tipo_prenda_id ? Number(form.tipo_prenda_id) : null,
    };

    axios.post(route('admin.ordenes.store'), payload)
      .then((res) => {
        emit('saved');
        emit('close');
      })
      .catch((err) => {
        console.error('Orden store error:', err.response ? err.response.data : err);
        if (err.response && err.response.status === 422) {
          const errors = err.response.data.errors || {};
          for (const k in form.errors) delete form.errors[k];
          Object.assign(form.errors, errors);
        }
      });
  }
}
defineExpose({
  submitForm: submit,
  closeForm: () => emit('close'),
  processing: form.processing,
});
</script>

<template>
  <Head title="Ordenes" />
  <div class="p-6">
    <h2 class="text-lg font-semibold mb-6">{{ isEdit ? 'Editar Orden' : 'Crear Orden' }}</h2>
    <form @submit.prevent="submit">
      <v-row>
        <v-col cols="12" md="6">
          <v-text-field
            v-model="form.name"
            label="Nombre"
            outlined
            dense
            :error-messages="form.errors.name"
          />
        </v-col>
        <v-col cols="12" md="6">
          <label class="block text-sm font-medium mb-2">Tipo de Prenda</label>
          <select
            v-model="form.tipo_prenda_id"
            class="w-full px-3 py-2 border border-gray-300 rounded outline-none focus:ring-2 focus:ring-blue-500"
            :class="{ 'border-red-500': form.errors.tipo_prenda_id }"
          >
            <option v-for="item in tiposPrendasOptions" :key="item.value" :value="item.value">
              {{ item.title }}
            </option>
          </select>
          <div v-if="form.errors.tipo_prenda_id" class="text-red-600 text-sm mt-1">{{ form.errors.tipo_prenda_id }}</div>
        </v-col>
      </v-row>

      <v-row>
        <v-col cols="12" md="6">
          <v-text-field
            v-model="form.client"
            label="Cliente"
            outlined
            dense
            :error-messages="form.errors.client"
          />
        </v-col>
        <v-col cols="12" md="6">
          <v-text-field
            v-model="form.target_date"
            label="Fecha objetivo"
            type="date"
            outlined
            dense
            :error-messages="form.errors.target_date"
          />
        </v-col>
      </v-row>

      <v-row>
        <v-col cols="12" md="6">
          <v-text-field
            v-model.number="form.target_quantity"
            label="Cantidad objetivo"
            type="number"
            outlined
            dense
            :error-messages="form.errors.target_quantity"
          />
        </v-col>
        <v-col v-if="isEdit" cols="12" md="6">
          <v-text-field
            v-model="form.quality"
            label="Calidad"
            outlined
            dense
            :error-messages="form.errors.quality"
          />
        </v-col>
      </v-row>

      <v-row>
        <v-col cols="12">
          <v-textarea
            v-model="form.description"
            label="Descripción"
            outlined
            rows="4"
            :error-messages="form.errors.description"
          />
        </v-col>
      </v-row>
    </form>
  </div>
</template>
