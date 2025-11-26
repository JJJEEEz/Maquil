<script setup>
import DangerButton from '@/Components/DangerButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import Input from '@/Components/UI/Input.vue';
import Modal from '@/Components/Modal.vue';
import { useForm } from '@inertiajs/vue3';
import { nextTick, ref } from 'vue';

const confirmingUserDeletion = ref(false);
const passwordInput = ref(null);

const form = useForm({
    password: '',
});

const confirmUserDeletion = () => {
    confirmingUserDeletion.value = true;

    nextTick(() => passwordInput.value.focus());
};

const deleteUser = () => {
    form.delete(route('profile.destroy'), {
        preserveScroll: true,
        onSuccess: () => closeModal(),
        onError: () => passwordInput.value.focus(),
        onFinish: () => form.reset(),
    });
};

const closeModal = () => {
    confirmingUserDeletion.value = false;

    form.clearErrors();
    form.reset();
};

function toArray(val) {
    if (!val) return [];
    return Array.isArray(val) ? val : [val];
}
</script>

<template>
    <v-card color="surface">
        <v-card-title>
            <div>
                <h2 class="text-lg font-medium" style="color: var(--v-theme-on-surface)">Eliminar cuenta</h2>
                    <p class="text-sm" style="color: var(--v-theme-on-surface)">Una vez eliminada tu cuenta, todos sus recursos y datos serán borrados permanentemente. Antes de eliminarla, descarga cualquier información que desees conservar.</p>
            </div>
        </v-card-title>

        <v-card-text>
            <DangerButton @click="confirmUserDeletion">Eliminar cuenta</DangerButton>

            <Modal :show="confirmingUserDeletion" @close="closeModal">
                <div class="p-6">
                    <h2 class="text-lg font-medium" style="color: var(--v-theme-on-surface)">¿Estás seguro de que deseas eliminar tu cuenta?</h2>

                    <p class="mt-1 text-sm" style="color: var(--v-theme-on-surface); opacity:.9">Introduce tu contraseña para confirmar que deseas eliminar tu cuenta de forma permanente.</p>

                    <div class="mt-6">
                        <Input v-model="form.password" label="Contraseña" type="password" ref="passwordInput" :error="!!form.errors.password" :error-messages="toArray(form.errors.password)" @keyup.enter="deleteUser" />
                    </div>

                    <div class="mt-6 flex justify-end">
                        <SecondaryButton @click="closeModal">Cancelar</SecondaryButton>

                        <DangerButton class="ms-3" :disabled="form.processing" @click="deleteUser">Eliminar cuenta</DangerButton>
                    </div>
                </div>
            </Modal>
        </v-card-text>
    </v-card>
</template>
