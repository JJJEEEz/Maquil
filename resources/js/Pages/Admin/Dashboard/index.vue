<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
</script>

<template>
    <Head title="Dashboard" />

    <AuthenticatedLayout>
        <v-card>
            <v-card-title>
                <h1 class="text-lg font-semibold mb-0">Dashboard</h1>
            </v-card-title>

            <v-card-text>
                Bienvenido al panel de administración.
            </v-card-text>
        </v-card>
    </AuthenticatedLayout>
</template>
