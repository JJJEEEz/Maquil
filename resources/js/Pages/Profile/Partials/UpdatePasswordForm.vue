<script setup>
import PrimaryButton from '@/Components/PrimaryButton.vue';
import Input from '@/Components/UI/Input.vue';
import { useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const passwordInput = ref(null);
const currentPasswordInput = ref(null);

const form = useForm({
    current_password: '',
    password: '',
    password_confirmation: '',
});

const updatePassword = () => {
    form.put(route('password.update'), {
        preserveScroll: true,
        onSuccess: () => form.reset(),
        onError: () => {
            if (form.errors.password) {
                form.reset('password', 'password_confirmation');
                passwordInput.value.focus();
            }
            if (form.errors.current_password) {
                form.reset('current_password');
                currentPasswordInput.value.focus();
            }
        },
    });
};

function toArray(val) {
    if (!val) return [];
    return Array.isArray(val) ? val : [val];
}
</script>

<template>
    <v-card>
        <v-card-title>
            <div>
                <h2 class="text-lg font-medium">Actualizar contraseña</h2>
                <p class="text-sm">Asegúrate de que tu cuenta use una contraseña larga y segura.</p>
            </div>
        </v-card-title>

        <v-card-text>
            <form @submit.prevent="updatePassword">
                <Input v-model="form.current_password" label="Contraseña actual" type="password" :error="!!form.errors.current_password" :error-messages="toArray(form.errors.current_password)" ref="currentPasswordInput" />

                <Input v-model="form.password" label="Nueva contraseña" type="password" :error="!!form.errors.password" :error-messages="toArray(form.errors.password)" ref="passwordInput" />

                <Input v-model="form.password_confirmation" label="Confirmar contraseña" type="password" :error="!!form.errors.password_confirmation" :error-messages="toArray(form.errors.password_confirmation)" />

                <v-card-actions class="mt-4">

                    <PrimaryButton :disabled="form.processing">Guardar</PrimaryButton>

                    <Transition>
                        <p v-if="form.recentlySuccessful" class="text-sm text-muted">Guardado.</p>
                    </Transition>
                </v-card-actions>
            </form>
        </v-card-text>
    </v-card>
</template>
