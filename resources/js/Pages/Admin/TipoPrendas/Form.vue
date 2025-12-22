<script setup>
import { computed } from 'vue';
import { useForm } from '@inertiajs/vue3';
import axios from 'axios';
import Input from '@/Components/UI/Input.vue';

const props = defineProps({ tipoPrenda: Object });
const emit = defineEmits(['saved', 'close']);

const isEdit = computed(() => !!props.tipoPrenda && props.tipoPrenda !== null);

const form = useForm({
  nombre: props.tipoPrenda ? props.tipoPrenda.nombre : '',
  descripcion: props.tipoPrenda ? props.tipoPrenda.descripcion : '',
});

function submit() {
  if (isEdit.value) {
    const payload = {
      nombre: form.nombre,
      descripcion: form.descripcion,
    };

    axios.put(route('admin.tipos-prendas.update', props.tipoPrenda.id), payload)
      .then((res) => {
        emit('saved');
        emit('close');
      })
      .catch((err) => {
        console.error('TipoPrenda update error:', err.response ? err.response.data : err);
        if (err.response && err.response.status === 422) {
          const errors = err.response.data.errors || {};
          for (const k in form.errors) delete form.errors[k];
          Object.assign(form.errors, errors);
        }
      });
  } else {
    const payload = {
      nombre: form.nombre,
      descripcion: form.descripcion,
    };

    axios.post(route('admin.tipos-prendas.store'), payload)
      .then((res) => {
        emit('saved');
        emit('close');
      })
      .catch((err) => {
        console.error('TipoPrenda store error:', err.response ? err.response.data : err);
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
  <div class="p-6">
    <h2 class="text-lg font-semibold mb-4">{{ isEdit ? 'Editar Tipo de Prenda' : 'Crear Tipo de Prenda' }}</h2>
    <form @submit.prevent="submit">
      <Input v-model="form.nombre" label="Nombre" placeholder="Ej: Polo, Camisa Type 1" :error="!!form.errors.nombre" :error-messages="form.errors.nombre" />
      
      <label class="block font-medium mt-4">Descripción</label>
      <textarea
        v-model="form.descripcion"
        rows="4"
        class="w-full border border-gray-300 rounded px-3 py-2 mt-2"
        placeholder="Descripción opcional..."
      />
      <div v-if="form.errors.descripcion" class="text-red-600 text-sm mt-1">
        <div v-if="Array.isArray(form.errors.descripcion)" v-for="(err, i) in form.errors.descripcion" :key="i">{{ err }}</div>
        <div v-else>{{ form.errors.descripcion }}</div>
      </div>
    </form>
  </div>
</template>
