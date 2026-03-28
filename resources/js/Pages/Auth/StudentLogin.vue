<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';

const form = useForm({
    email: '',
    password: '',
});

const submit = () => {
    form.post(route('student.login'));
};
</script>

<template>
    <Head title="Login Siswa - SPMB SMKN 8" />

    <div class="min-h-screen bg-gradient-to-br from-blue-50 to-blue-100 flex items-center justify-center py-12 px-4">
        <div class="max-w-md w-full">
            <!-- Logo/Header -->
            <div class="text-center mb-8">
                <Link :href="route('home')" class="text-3xl font-bold text-blue-600">
                    SPMB SMKN 8
                </Link>
                <p class="text-gray-600 mt-2">Sistem Penerimaan Siswa Baru</p>
            </div>

            <!-- Login Card -->
            <div class="bg-white rounded-lg shadow-xl p-8">
                <h2 class="text-2xl font-bold text-gray-800 mb-6 text-center">Login Siswa</h2>

                <!-- Error Messages -->
                <div v-if="$page.props.errors.email"
                    class="mb-4 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg">
                    {{ $page.props.errors.email }}
                </div>

                <form @submit.prevent="submit">
                    <div class="mb-4">
                        <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
                            Email
                        </label>
                        <input
                            id="email"
                            v-model="form.email"
                            type="email"
                            required
                            autofocus
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                            placeholder="email@example.com"
                        />
                        <p v-if="$page.props.errors.email" class="text-red-500 text-sm mt-1">
                            {{ $page.props.errors.email }}
                        </p>
                    </div>

                    <div class="mb-6">
                        <div class="flex justify-between items-center mb-2">
                            <label for="password" class="block text-sm font-medium text-gray-700">
                                Password
                            </label>
                            <Link :href="route('student.forgot-password')" class="text-sm text-blue-600 hover:text-blue-700">
                                Lupa Password?
                            </Link>
                        </div>
                        <input
                            id="password"
                            v-model="form.password"
                            type="password"
                            required
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                            placeholder="••••••••"
                        />
                        <p v-if="$page.props.errors.password" class="text-red-500 text-sm mt-1">
                            {{ $page.props.errors.password }}
                        </p>
                    </div>

                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="w-full bg-blue-600 text-white py-3 rounded-lg font-semibold hover:bg-blue-700 disabled:bg-gray-400 disabled:cursor-not-allowed transition"
                    >
                        <span v-if="form.processing">Loading...</span>
                        <span v-else>Login</span>
                    </button>
                </form>

                <div class="mt-6 text-center">
                    <p class="text-gray-600">
                        Belum punya akun?
                        <Link :href="route('student.register')" class="text-blue-600 hover:text-blue-700 font-semibold">
                            Daftar Sekarang
                        </Link>
                    </p>
                </div>

                <div class="mt-4 text-center">
                    <Link :href="route('home')" class="text-gray-500 hover:text-gray-700 text-sm">
                        ← Kembali ke Beranda
                    </Link>
                </div>
            </div>

            <!-- Info Card -->
            <div class="mt-6 bg-white rounded-lg shadow p-6">
                <h3 class="font-semibold text-gray-800 mb-3">Informasi Login</h3>
                <ul class="space-y-2 text-sm text-gray-600">
                    <li class="flex items-start">
                        <span class="text-blue-500 mr-2">•</span>
                        <span>Gunakan email yang Anda daftarkan</span>
                    </li>
                    <li class="flex items-start">
                        <span class="text-blue-500 mr-2">•</span>
                        <span>Password telah dikirim ke email Anda setelah pendaftaran</span>
                    </li>
                    <li class="flex items-start">
                        <span class="text-blue-500 mr-2">•</span>
                        <span>Hubungi panitia jika lupa password</span>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</template>
