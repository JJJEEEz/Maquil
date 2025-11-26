<script setup>
import PrimaryButton from '@/Components/PrimaryButton.vue';
import Input from '@/Components/UI/Input.vue';
import { Link, useForm, usePage } from '@inertiajs/vue3';

defineProps({
    mustVerifyEmail: {
        type: Boolean,
    },
    status: {
        type: String,
    },
});

const user = usePage().props.auth.user;

const form = useForm({
    name: user.name,
    email: user.email,
});

function toArray(val) {
    if (!val) return [];
    return Array.isArray(val) ? val : [val];
}
</script>

<template>
    <v-card>
        <v-card-title>
            <div>
                    <h2 class="text-lg font-medium">Información del perfil</h2>
                    <p class="text-sm">Actualiza la información de tu cuenta y tu dirección de correo electrónico.</p>
            </div>
        </v-card-title>

        <v-card-text>
            <form @submit.prevent="form.patch(route('profile.update'))">
                <Input v-model="form.name" label="Nombre" :error="!!form.errors.name" :error-messages="toArray(form.errors.name)" />

                <Input v-model="form.email" label="Correo electrónico" type="email" :error="!!form.errors.email" :error-messages="toArray(form.errors.email)" />

                <div v-if="mustVerifyEmail && user.email_verified_at === null" class="my-2">
                    <p class="text-sm">Tu dirección de correo electrónico no está verificada.</p>
                    <Link :href="route('verification.send')" method="post" as="button">
                        <v-btn text>Haz clic aquí para reenviar el correo de verificación.</v-btn>
                    </Link>

                    <div v-show="status === 'verification-link-sent'" class="mt-2 text-sm text-success">
                        Se ha enviado un nuevo enlace de verificación a tu correo electrónico.
                    </div>
                </div>

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
