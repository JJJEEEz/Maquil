<template>
  <AuthenticatedLayout>
    <template #header>
      <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
        Crear Tipo de Prenda
      </h2>
    </template>

    <div class="py-12">
      <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
          <div class="p-6 text-gray-900 dark:text-gray-100">
            <form @submit.prevent="submit" class="space-y-6">
              <div>
                <label class="block text-sm font-medium mb-2">Nombre</label>
                <input
                  v-model="form.nombre"
                  type="text"
                  class="w-full px-4 py-2 border rounded-lg dark:bg-gray-700 dark:border-gray-600"
                  placeholder="Ej: Polo, Camisa Type 1"
                  required
                />
                <div v-if="form.errors.nombre" class="text-red-500 text-sm mt-1">
                  {{ form.errors.nombre }}
                </div>
              </div>

              <div>
                <label class="block text-sm font-medium mb-2">Descripción</label>
                <textarea
                  v-model="form.descripcion"
                  class="w-full px-4 py-2 border rounded-lg dark:bg-gray-700 dark:border-gray-600"
                  rows="4"
                  placeholder="Descripción opcional..."
                ></textarea>
              </div>

              <div class="flex gap-4">
                <button
                  type="submit"
                  :disabled="form.processing"
                  class="bg-blue-500 hover:bg-blue-700 disabled:opacity-50 text-white font-bold py-2 px-4 rounded"
                >
                  Guardar
                </button>
                <Link
                  :href="route('admin.tipos-prendas.index')"
                  class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded"
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
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Link, useForm } from '@inertiajs/vue3';

const form = useForm({
  nombre: '',
  descripcion: '',
});

const submit = () => {
  form.post(route('admin.tipos-prendas.store'));
};
</script>
