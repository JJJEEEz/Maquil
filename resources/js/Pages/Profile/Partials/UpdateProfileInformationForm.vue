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
                <h2 class="text-lg font-medium">Profile Information</h2>
                <p class="text-sm">Update your account's profile information and email address.</p>
            </div>
        </v-card-title>

        <v-card-text>
            <form @submit.prevent="form.patch(route('profile.update'))">
                <Input v-model="form.name" label="Name" :error="!!form.errors.name" :error-messages="toArray(form.errors.name)" />

                <Input v-model="form.email" label="Email" type="email" :error="!!form.errors.email" :error-messages="toArray(form.errors.email)" />

                <div v-if="mustVerifyEmail && user.email_verified_at === null" class="my-2">
                    <p class="text-sm">Your email address is unverified.</p>
                    <Link :href="route('verification.send')" method="post" as="button">
                        <v-btn text>Click here to re-send the verification email.</v-btn>
                    </Link>

                    <div v-show="status === 'verification-link-sent'" class="mt-2 text-sm text-success">
                        A new verification link has been sent to your email address.
                    </div>
                </div>

                <v-card-actions class="mt-4">
                    <PrimaryButton :disabled="form.processing">Save</PrimaryButton>

                    <Transition>
                        <p v-if="form.recentlySuccessful" class="text-sm text-muted">Saved.</p>
                    </Transition>
                </v-card-actions>
            </form>
        </v-card-text>
    </v-card>
</template>
