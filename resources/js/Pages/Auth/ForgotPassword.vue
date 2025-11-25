<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, useForm } from '@inertiajs/vue3';

defineProps({
    status: {
        type: String,
    },
});

const form = useForm({
    email: '',
});

const submit = () => {
    form.post(route('password.email'));
};
</script>

<template>
    <GuestLayout>
        <Head title="Forgot Password" />

        <v-card>
          <v-card-text>
            <div class="mb-4 text-sm text-gray-600">Forgot your password? No problem. Just let us know your email address and we will email you a password reset link that will allow you to choose a new one.</div>

            <div v-if="status" class="mb-4 text-sm font-medium text-success">{{ status }}</div>

            <form @submit.prevent="submit">
              <Input v-model="form.email" label="Email" type="email" :error="!!form.errors.email" :error-messages="Array.isArray(form.errors.email) ? form.errors.email : (form.errors.email ? [form.errors.email] : [])" />

              <div class="mt-4 flex items-center justify-end">
                <PrimaryButton :disabled="form.processing">Email Password Reset Link</PrimaryButton>
              </div>
            </form>
          </v-card-text>
        </v-card>
    </GuestLayout>
</template>
