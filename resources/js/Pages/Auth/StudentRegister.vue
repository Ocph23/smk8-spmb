<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';

const form = useForm({
    email: '',
    phone: '',
    password: '',
    password_confirmation: '',
});

const submit = () => {
    form.post(route('student.register'));
};
</script>

<template>
    <Head title="Daftar Akun - SPMB SMKN 8" />

    <div class="min-h-screen bg-gradient-to-br from-blue-50 to-blue-100 flex items-center justify-center py-12 px-4">
        <div class="max-w-md w-full">
            <!-- Logo/Header -->
            <div class="text-center mb-8">
                <Link :href="route('home')" class="text-3xl font-bold text-blue-600">
                    SPMB SMKN 8
                </Link>
                <p class="text-gray-600 mt-2">Sistem Penerimaan Siswa Baru</p>
            </div>

            <!-- Register Card -->
            <div class="bg-white rounded-lg shadow-xl p-8">
                <h2 class="text-2xl font-bold text-gray-800 mb-6 text-center">Buat Akun</h2>
                <p class="text-gray-600 text-sm mb-6 text-center">
                    Buat akun untuk memulai pendaftaran
                </p>

                <!-- Error Messages -->
                <div v-if="$page.props.errors.error"
                    class="mb-4 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg">
                    {{ $page.props.errors.error }}
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

                    <div class="mb-4">
                        <label for="phone" class="block text-sm font-medium text-gray-700 mb-2">
                            Nomor Telepon
                        </label>
                        <input
                            id="phone"
                            v-model="form.phone"
                            type="tel"
                            inputmode="tel"
                            required
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                            placeholder="08xxxxxxxxxx"
                        />
                        <p class="mt-1 text-xs text-gray-500">
                            Gunakan nomor aktif yang bisa dihubungi panitia.
                        </p>
                        <p v-if="$page.props.errors.phone" class="text-red-500 text-sm mt-1">
                            {{ $page.props.errors.phone }}
                        </p>
                    </div>

                    <div class="mb-4">
                        <label for="password" class="block text-sm font-medium text-gray-700 mb-2">
                            Password
                        </label>
                        <input
                            id="password"
                            v-model="form.password"
                            type="password"
                            required
                            minlength="6"
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                            placeholder="Minimal 6 karakter"
                        />
                        <p v-if="$page.props.errors.password" class="text-red-500 text-sm mt-1">
                            {{ $page.props.errors.password }}
                        </p>
                    </div>

                    <div class="mb-6">
                        <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-2">
                            Konfirmasi Password
                        </label>
                        <input
                            id="password_confirmation"
                            v-model="form.password_confirmation"
                            type="password"
                            required
                            minlength="6"
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                            placeholder="Ulangi password"
                        />
                    </div>

                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="w-full bg-blue-600 text-white py-3 rounded-lg font-semibold hover:bg-blue-700 disabled:bg-gray-400 disabled:cursor-not-allowed transition"
                    >
                        <span v-if="form.processing">Loading...</span>
                        <span v-else>Daftar</span>
                    </button>
                </form>

                <div class="mt-6 text-center">
                    <p class="text-gray-600">
                        Sudah punya akun?
                        <Link :href="route('student.login')" class="text-blue-600 hover:text-blue-700 font-semibold">
                            Login
                        </Link>
                    </p>
                </div>

                <div class="mt-4 text-center">
                    <Link :href="route('home')" class="text-gray-500 hover:text-gray-700 text-sm">
                        ← Kembali ke Beranda
                    </Link>
                </div>
            </div>
        </div>
    </div>
</template>
