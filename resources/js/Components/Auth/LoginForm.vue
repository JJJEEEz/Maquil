<template>
  <form @submit.prevent="submit">
    <Input v-model="form.email" label="Email" type="email" :error="errors.email" />
    <Input v-model="form.password" label="Password" type="password" :error="errors.password" />
    <div class="mt-4">
      <Button type="submit" :disabled="processing">Entrar</Button>
    </div>
  </form>
</template>

<script setup>
import { useForm } from '@inertiajs/vue3';
import Input from '@/Components/UI/Input.vue';
import Button from '@/Components/UI/Button.vue';

const form = useForm({ email: '', password: '' });
const processing = form.processing;
const errors = form.errors;

function submit() {
  form.post(route('login'));
}
</script>
