<script setup>
import { computed } from 'vue';
import { useForm } from '@inertiajs/vue3';
import axios from 'axios';
import Input from '@/Components/UI/Input.vue';

const props = defineProps({
  nodo: Object,
  nodosDisponibles: Array,
  tipoPrenda: Object,
});
const emit = defineEmits(['saved', 'close']);

const isEdit = computed(() => !!props.nodo && props.nodo !== null);

const form = useForm({
  nombre: props.nodo ? props.nodo.nombre : '',
  tipo: props.nodo ? props.nodo.tipo : 'operacion',
  orden: props.nodo ? props.nodo.orden : 0,
  cantidad_entrada: props.nodo ? props.nodo.cantidad_entrada : 1,
  cantidad_salida: props.nodo ? props.nodo.cantidad_salida : 1,
  tiempo_estimado_minutos: props.nodo ? props.nodo.tiempo_estimado_minutos : null,
  dependencias: props.nodo && props.nodo.dependencias ? props.nodo.dependencias.map(d => d.id) : [],
});

function submit() {
  const payload = {
    nombre: form.nombre,
    tipo: form.tipo,
    orden: Number(form.orden),
    cantidad_entrada: Number(form.cantidad_entrada),
    cantidad_salida: Number(form.cantidad_salida),
    tiempo_estimado_minutos: form.tiempo_estimado_minutos ? Number(form.tiempo_estimado_minutos) : null,
    dependencias: form.dependencias,
  };

  if (isEdit.value) {
    axios.put(route('admin.proceso-nodos.update', [props.tipoPrenda.id, props.nodo.id]), payload)
      .then((res) => {
        emit('saved');
        emit('close');
      })
      .catch((err) => {
        console.error('ProcesoNodo update error:', err.response ? err.response.data : err);
        if (err.response && err.response.status === 422) {
          const errors = err.response.data.errors || {};
          for (const k in form.errors) delete form.errors[k];
          Object.assign(form.errors, errors);
        }
      });
  } else {
    axios.post(route('admin.proceso-nodos.store', props.tipoPrenda.id), payload)
      .then((res) => {
        emit('saved');
        emit('close');
      })
      .catch((err) => {
        console.error('ProcesoNodo store error:', err.response ? err.response.data : err);
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
    <h2 class="text-lg font-semibold mb-4">{{ isEdit ? 'Editar Proceso' : 'Crear Proceso' }}</h2>
    <form @submit.prevent="submit" class="space-y-4">
      <Input v-model="form.nombre" label="Nombre del Proceso" placeholder="Ej: Corte Puño, Costura Manga" :error="!!form.errors.nombre" :error-messages="form.errors.nombre" />

      <div class="grid grid-cols-2 gap-4">
        <div>
          <label class="block font-medium">Tipo</label>
          <select v-model="form.tipo" class="w-full border border-gray-300 rounded px-3 py-2 mt-2" :class="{ 'border-red-500': form.errors.tipo }">
            <option value="operacion">Operación</option>
            <option value="inspeccion">Inspección</option>
          </select>
          <div v-if="form.errors.tipo" class="text-red-600 text-sm mt-1">{{ form.errors.tipo }}</div>
        </div>

        <div>
          <label class="block font-medium">Orden (Secuencia)</label>
          <input v-model.number="form.orden" type="number" class="w-full border border-gray-300 rounded px-3 py-2 mt-2" :class="{ 'border-red-500': form.errors.orden }" />
          <div v-if="form.errors.orden" class="text-red-600 text-sm mt-1">{{ form.errors.orden }}</div>
        </div>
      </div>

      <div class="grid grid-cols-2 gap-4">
        <div>
          <label class="block font-medium">Cantidad Entrada</label>
          <input v-model.number="form.cantidad_entrada" type="number" min="1" class="w-full border border-gray-300 rounded px-3 py-2 mt-2" :class="{ 'border-red-500': form.errors.cantidad_entrada }" />
          <p class="text-xs text-gray-500 mt-1">Ej: 2 si necesita 2 piezas de entrada</p>
          <div v-if="form.errors.cantidad_entrada" class="text-red-600 text-sm mt-1">{{ form.errors.cantidad_entrada }}</div>
        </div>

        <div>
          <label class="block font-medium">Cantidad Salida</label>
          <input v-model.number="form.cantidad_salida" type="number" min="1" class="w-full border border-gray-300 rounded px-3 py-2 mt-2" :class="{ 'border-red-500': form.errors.cantidad_salida }" />
          <p class="text-xs text-gray-500 mt-1">Ej: 1 si produce 1 pieza de salida</p>
          <div v-if="form.errors.cantidad_salida" class="text-red-600 text-sm mt-1">{{ form.errors.cantidad_salida }}</div>
        </div>
      </div>

      <div>
        <label class="block font-medium">Tiempo Estimado (minutos)</label>
        <input v-model.number="form.tiempo_estimado_minutos" type="number" min="1" class="w-full border border-gray-300 rounded px-3 py-2 mt-2" :class="{ 'border-red-500': form.errors.tiempo_estimado_minutos }" />
        <div v-if="form.errors.tiempo_estimado_minutos" class="text-red-600 text-sm mt-1">{{ form.errors.tiempo_estimado_minutos }}</div>
      </div>

      <div>
        <label class="block font-medium">Procesos de los que depende (Padres)</label>
        <div class="space-y-2 mt-2 max-h-48 overflow-y-auto border border-gray-300 rounded p-3">
          <label v-for="nodoOpt in nodosDisponibles" :key="nodoOpt.id" class="flex items-center">
            <input type="checkbox" :value="nodoOpt.id" v-model="form.dependencias" class="mr-2" />
            <span class="text-sm">{{ nodoOpt.nombre }}</span>
          </label>
          <div v-if="nodosDisponibles.length === 0" class="text-gray-500 text-sm p-2">
            No hay procesos disponibles
          </div>
        </div>
        <p class="text-xs text-gray-500 mt-2">
          Selecciona los procesos cuyo resultado es necesario para este proceso.<br>
          Ej: "Costura Manga Frente" depende de "Corte Manga" y "Corte Frente"
        </p>
        <div v-if="form.errors.dependencias" class="text-red-600 text-sm mt-1">{{ form.errors.dependencias }}</div>
      </div>
    </form>
  </div>
</template>
