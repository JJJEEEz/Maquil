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
    <v-card>
        <v-card-title>
            <div>
                <h2 class="text-lg font-medium">Delete Account</h2>
                <p class="text-sm">Once your account is deleted, all of its resources and data will be permanently deleted. Before deleting your account, please download any data or information that you wish to retain.</p>
            </div>
        </v-card-title>

        <v-card-text>
            <DangerButton @click="confirmUserDeletion">Delete Account</DangerButton>

            <Modal :show="confirmingUserDeletion" @close="closeModal">
                <div class="p-6">
                    <h2 class="text-lg font-medium">Are you sure you want to delete your account?</h2>

                    <p class="mt-1 text-sm text-gray-600">Please enter your password to confirm you would like to permanently delete your account.</p>

                    <div class="mt-6">
                        <Input v-model="form.password" label="Password" type="password" ref="passwordInput" :error="!!form.errors.password" :error-messages="toArray(form.errors.password)" @keyup.enter="deleteUser" />
                    </div>

                    <div class="mt-6 flex justify-end">
                        <SecondaryButton @click="closeModal">Cancel</SecondaryButton>

                        <DangerButton class="ms-3" :disabled="form.processing" @click="deleteUser">Delete Account</DangerButton>
                    </div>
                </div>
            </Modal>
        </v-card-text>
    </v-card>
</template>
