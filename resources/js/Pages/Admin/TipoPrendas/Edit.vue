<template>
  <AuthenticatedLayout>
    <template #header>
      <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
        Editar Tipo de Prenda: {{ tipoPrenda.nombre }}
      </h2>
    </template>

    <div class="py-12">
      <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
        <v-card class="shadow-sm">
          <v-card-title>Editar Información</v-card-title>
          <v-card-text>
            <form @submit.prevent="submit" class="space-y-6">
              <div>
                <v-text-field
                  v-model="form.nombre"
                  label="Nombre"
                  variant="outlined"
                  :error-messages="form.errors.nombre ? [form.errors.nombre] : []"
                  required
                  class="mb-4"
                />
              </div>

              <div>
                <v-textarea
                  v-model="form.descripcion"
                  label="Descripción"
                  variant="outlined"
                  rows="4"
                  class="mb-4"
                />
              </div>

              <div class="flex gap-4">
                <v-btn
                  type="submit"
                  color="primary"
                  :loading="form.processing"
                  :disabled="form.processing"
                >
                  Guardar Cambios
                </v-btn>
                <v-btn
                  :href="route('admin.tipos-prendas.index')"
                  color="secondary"
                  variant="outlined"
                >
                  Cancelar
                </v-btn>
              </div>
            </form>
          </v-card-text>
        </v-card>
      </div>
    </div>
  </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { useForm, router } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
  tipoPrenda: Object,
});

const form = useForm({
  nombre: props.tipoPrenda.nombre,
  descripcion: props.tipoPrenda.descripcion,
});

const submit = () => {
  form.put(route('admin.tipos-prendas.update', props.tipoPrenda.id), {
    onSuccess: () => {
      router.visit(route('admin.tipos-prendas.index'));
    },
    onError: (errors) => {
      console.error('Error al guardar:', errors);
    }
  });
};
</script>
