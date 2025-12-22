<script setup>
import { computed } from 'vue';
import { useForm } from '@inertiajs/vue3';
import axios from 'axios';
import Input from '@/Components/UI/Input.vue';
import Button from '@/Components/UI/Button.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import DatePicker from '@/Components/DatePicker.vue';
import { Head } from '@inertiajs/vue3';

const incoming = defineProps({ orden: Object, tiposPrendas: Array });
const emit = defineEmits(['saved', 'close']);

const isEdit = computed(() => !!incoming.orden && incoming.orden !== null);

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
    // Use axios for XHR to avoid Inertia redirect issues
    const payload = {
      name: form.name,
      description: form.description,
      client: form.client,
      quality: form.quality,
      status: form.status,
      // coerce quantity to integer or null to satisfy integer|min:0 validation
      target_quantity: (form.target_quantity === '' || form.target_quantity === null) ? null : Number(form.target_quantity),
      // send null when empty to satisfy nullable|date rules on server
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
// Expose methods and state so parent (Modal) can render footer buttons
defineExpose({
  submitForm: submit,
  closeForm: () => emit('close'),
  processing: form.processing,
});
</script>

<template>
  <Head title="Ordenes" />
  <div class="p-6">
    <h2 class="text-lg font-semibold mb-4">{{ isEdit ? 'Editar Orden' : 'Crear Orden' }}</h2>
    <form @submit.prevent="submit">
      <Input v-model="form.name" label="Nombre" :error="!!form.errors.name" :error-messages="form.errors.name" />
      <div class="mt-4">
        <label class="block font-medium">Tipo de Prenda</label>
        <select
          v-model="form.tipo_prenda_id"
          class="w-full border border-gray-300 rounded px-3 py-2 mt-2"
          :class="{ 'border-red-500': form.errors.tipo_prenda_id }"
        >
          <option value="">Seleccionar tipo de prenda...</option>
          <option v-for="tp in tiposPrendas" :key="tp.id" :value="tp.id">{{ tp.nombre }}</option>
        </select>
        <div v-if="form.errors.tipo_prenda_id" class="text-red-600 text-sm mt-1">
          <div v-if="Array.isArray(form.errors.tipo_prenda_id)" v-for="(err, i) in form.errors.tipo_prenda_id" :key="i">{{ err }}</div>
          <div v-else>{{ form.errors.tipo_prenda_id }}</div>
        </div>
      </div>
      <Input v-model="form.client" label="Cliente" :error="!!form.errors.client" :error-messages="form.errors.client" />
      <div>
        <DatePicker
          v-model="form.target_date"
          :allowed-dates="allowedDates"
          label="Fecha objetivo"
          :error="!!form.errors.target_date"
          :error-messages="form.errors.target_date"
        />
      </div>
      <Input v-model="form.target_quantity" type="number" label="Cantidad objetivo" :error="!!form.errors.target_quantity" :error-messages="form.errors.target_quantity" />
      <Input v-if="isEdit" v-model="form.quality" label="Calidad" :error="!!form.errors.quality" :error-messages="form.errors.quality" />

      <label class="block font-medium">Descripción</label>
      <textarea
        v-model="form.description"
        rows="4"
        class="w-full border border-gray-300 rounded px-3 py-2 mt-2"
        placeholder="Descripción"
      />
      <div v-if="form.errors.description" class="text-red-600 text-sm mt-1">
        <div v-if="Array.isArray(form.errors.description)" v-for="(err, i) in form.errors.description" :key="i">{{ err }}</div>
        <div v-else>{{ form.errors.description }}</div>
      </div>

      <!-- footer moved to Modal's footer slot -->
    </form>
  </div>
</template>
