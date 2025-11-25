<template>
  <div class="max-w-2xl mx-auto mt-8">
    <h1 class="text-2xl font-bold mb-4">Autenticación</h1>
    <div class="grid grid-cols-2 gap-6">
      <div class="p-4 border rounded bg-white">
        <h2 class="font-semibold mb-2">Login</h2>
        <LoginForm />
      </div>
      <div class="p-4 border rounded bg-white">
        <h2 class="font-semibold mb-2">Registrar</h2>
        <RegisterForm />
      </div>
    </div>
  </div>
</template>

<script setup>
import LoginForm from '@/Components/Auth/LoginForm.vue';
import RegisterForm from '@/Components/Auth/RegisterForm.vue';
</script>
