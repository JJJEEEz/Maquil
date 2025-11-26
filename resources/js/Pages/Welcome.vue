<script setup>
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';</script>

<template>
    <AuthenticatedLayout>
        <div class="dashboard-logo-wrapper">
            <ApplicationLogo class="dashboard-logo" />
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
.dashboard-logo-wrapper{
    display:flex;
    justify-content:center;
    align-items:center;
    min-height:50vh;
}
.dashboard-logo{
    height:auto;
    width:35vh;
    opacity:0.6;
    max-width:90%;
}
@media (max-width:600px){
    .dashboard-logo{ width:50vw; }
}
</style>
