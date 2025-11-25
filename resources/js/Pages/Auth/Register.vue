<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const form = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
});

const submit = () => {
    form.post(route('register'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};
</script>

<template>
    <GuestLayout>
        <Head title="Register" />

        <v-card>
          <v-card-text>
            <form @submit.prevent="submit">
              <Input v-model="form.name" label="Name" :error="!!form.errors.name" :error-messages="Array.isArray(form.errors.name) ? form.errors.name : (form.errors.name ? [form.errors.name] : [])" />

              <Input v-model="form.email" label="Email" type="email" :error="!!form.errors.email" :error-messages="Array.isArray(form.errors.email) ? form.errors.email : (form.errors.email ? [form.errors.email] : [])" />

              <Input v-model="form.password" label="Password" type="password" :error="!!form.errors.password" :error-messages="Array.isArray(form.errors.password) ? form.errors.password : (form.errors.password ? [form.errors.password] : [])" />

              <Input v-model="form.password_confirmation" label="Confirm Password" type="password" :error="!!form.errors.password_confirmation" :error-messages="Array.isArray(form.errors.password_confirmation) ? form.errors.password_confirmation : (form.errors.password_confirmation ? [form.errors.password_confirmation] : [])" />

              <div class="mt-4 flex items-center justify-end">
                <Link :href="route('login')">Already registered?</Link>

                <PrimaryButton class="ms-4" :disabled="form.processing">Register</PrimaryButton>
              </div>
            </form>
          </v-card-text>
        </v-card>
    </GuestLayout>
</template>
