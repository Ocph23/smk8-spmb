<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';

const form = useForm({
    registration_number: '',
    nik: '',
});

const submit = () => {
    form.post(route('announcement.check'));
};
</script>

<template>

    <Head title="Cek Kelulusan - SPMB SMKN 8" />

    <div class="min-h-screen bg-gradient-to-br from-blue-50 to-indigo-100">
        <!-- Navigation -->
        <nav class="bg-white shadow-md">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between h-16">
                    <div class="flex items-center">
                        <Link :href="route('home')" class="text-2xl font-bold text-blue-600">
                            SPMB SMKN 8 TIK KOTA JAYAPURA
                        </Link>
                    </div>
                    <div class="flex items-center space-x-4">
                        <Link :href="route('home')"
                            class="text-gray-700 hover:text-blue-600 px-3 py-2 rounded-md text-sm font-medium">
                            Beranda
                        </Link>
                    </div>
                </div>
            </div>
        </nav>

        <!-- Main Content -->
        <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
            <div class="bg-white rounded-lg shadow-lg p-8">
                <div class="text-center mb-8">
                    <h1 class="text-3xl font-bold text-gray-800 mb-2">
                        Cek Hasil Seleksi SPMB
                    </h1>
                    <p class="text-gray-600">
                        Masukkan Nomor Pendaftaran dan NIK untuk melihat hasil seleksi
                    </p>
                </div>

                <!-- Error Message -->
                <div v-if="$page.props.errors.error"
                    class="mb-6 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg">
                    {{ $page.props.errors.error }}
                </div>

                <form @submit.prevent="submit" class="space-y-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Nomor Pendaftaran
                        </label>
                        <input v-model="form.registration_number" type="text"
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent text-lg"
                            :class="{ 'border-red-500': $page.props.errors.registration_number }"
                            placeholder="Contoh: SPMB-2026-0001" />
                        <p v-if="$page.props.errors.registration_number" class="text-red-500 text-sm mt-1">
                            {{ $page.props.errors.registration_number }}
                        </p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            NIK
                        </label>
                        <input v-model="form.nik" type="text" maxlength="16"
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent text-lg"
                            :class="{ 'border-red-500': $page.props.errors.nik }" placeholder="16 digit NIK" />
                        <p v-if="$page.props.errors.nik" class="text-red-500 text-sm mt-1">
                            {{ $page.props.errors.nik }}
                        </p>
                    </div>

                    <button type="submit" :disabled="form.processing"
                        class="w-full bg-blue-600 text-white py-3 rounded-lg hover:bg-blue-700 font-semibold disabled:bg-gray-400 disabled:cursor-not-allowed transition">
                        <span v-if="form.processing">Memeriksa...</span>
                        <span v-else>Cek Sekarang</span>
                    </button>
                </form>

                <div class="mt-8 pt-6 border-t">
                    <p class="text-sm text-gray-600 text-center">
                        Pastikan Nomor Pendaftaran dan NIK yang Anda masukkan sudah benar.
                        Hubungi panitia jika mengalami kesulitan.
                    </p>
                </div>
            </div>
        </div>
    </div>
</template>
