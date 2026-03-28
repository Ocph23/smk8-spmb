<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';

defineProps({ status: String });

const form = useForm({ email: '' });

const submit = () => form.post(route('student.forgot-password.send'));
</script>

<template>
    <Head title="Lupa Password - SPMB SMKN 8" />

    <div class="min-h-screen bg-gradient-to-br from-blue-50 to-blue-100 flex items-center justify-center py-12 px-4">
        <div class="max-w-md w-full">
            <div class="text-center mb-8">
                <Link :href="route('home')" class="text-3xl font-bold text-blue-600">SPMB SMKN 8</Link>
                <p class="text-gray-600 mt-2">Sistem Penerimaan Siswa Baru</p>
            </div>

            <div class="bg-white rounded-lg shadow-xl p-8">
                <h2 class="text-2xl font-bold text-gray-800 mb-2 text-center">Lupa Password</h2>
                <p class="text-sm text-gray-600 text-center mb-6">
                    Masukkan email Anda dan kami akan mengirimkan link untuk membuat password baru.
                </p>

                <div v-if="status" class="mb-4 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg text-sm">
                    {{ status }}
                </div>

                <form @submit.prevent="submit">
                    <div class="mb-4">
                        <label for="email" class="block text-sm font-medium text-gray-700 mb-2">Email</label>
                        <input
                            id="email"
                            v-model="form.email"
                            type="email"
                            required
                            autofocus
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                            placeholder="email@example.com"
                        />
                        <p v-if="form.errors.email" class="text-red-500 text-sm mt-1">{{ form.errors.email }}</p>
                    </div>

                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="w-full bg-blue-600 text-white py-3 rounded-lg font-semibold hover:bg-blue-700 disabled:bg-gray-400 disabled:cursor-not-allowed transition"
                    >
                        <span v-if="form.processing">Mengirim...</span>
                        <span v-else>Kirim Link Reset Password</span>
                    </button>
                </form>

                <div class="mt-6 text-center">
                    <Link :href="route('student.login')" class="text-blue-600 hover:text-blue-700 text-sm">
                        ← Kembali ke Login
                    </Link>
                </div>
            </div>
        </div>
    </div>
</template>
