<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, useForm } from '@inertiajs/vue3';

const form = useForm({
    password: '',
});

const submit = () => {
    form.post(route('password.confirm'), {
        onFinish: () => form.reset(),
    });
};
</script>

<template>
    <GuestLayout>
        <Head title="Confirm Password" />

        <v-card>
          <v-card-text>
            <div class="mb-4 text-sm text-gray-600">This is a secure area of the application. Please confirm your password before continuing.</div>

            <form @submit.prevent="submit">
              <Input v-model="form.password" label="Password" type="password" :error="!!form.errors.password" :error-messages="Array.isArray(form.errors.password) ? form.errors.password : (form.errors.password ? [form.errors.password] : [])" autofocus />

              <div class="mt-4 flex justify-end">
                <PrimaryButton :disabled="form.processing">Confirm</PrimaryButton>
              </div>
            </form>
          </v-card-text>
        </v-card>
    </GuestLayout>
</template>
