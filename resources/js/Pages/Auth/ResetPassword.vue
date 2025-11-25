<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, useForm } from '@inertiajs/vue3';

const props = defineProps({
    email: {
        type: String,
        required: true,
    },
    token: {
        type: String,
        required: true,
    },
});

const form = useForm({
    token: props.token,
    email: props.email,
    password: '',
    password_confirmation: '',
});

const submit = () => {
    form.post(route('password.store'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};
</script>

<template>
    <GuestLayout>
        <Head title="Reset Password" />

        <v-card>
          <v-card-text>
            <form @submit.prevent="submit">
              <Input v-model="form.email" label="Email" type="email" :error="!!form.errors.email" :error-messages="Array.isArray(form.errors.email) ? form.errors.email : (form.errors.email ? [form.errors.email] : [])" />

              <Input v-model="form.password" label="Password" type="password" :error="!!form.errors.password" :error-messages="Array.isArray(form.errors.password) ? form.errors.password : (form.errors.password ? [form.errors.password] : [])" />

              <Input v-model="form.password_confirmation" label="Confirm Password" type="password" :error="!!form.errors.password_confirmation" :error-messages="Array.isArray(form.errors.password_confirmation) ? form.errors.password_confirmation : (form.errors.password_confirmation ? [form.errors.password_confirmation] : [])" />

              <div class="mt-4 flex items-center justify-end">
                <PrimaryButton :disabled="form.processing">Reset Password</PrimaryButton>
              </div>
            </form>
          </v-card-text>
        </v-card>
    </GuestLayout>
</template>
