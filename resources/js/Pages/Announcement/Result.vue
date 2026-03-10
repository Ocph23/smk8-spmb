<script setup>
import { Head, Link } from '@inertiajs/vue3';

defineProps({
    student: {
        type: Object,
        required: true,
    },
});

const formatDate = (dateString) => {
    const date = new Date(dateString);
    return date.toLocaleDateString('id-ID', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
    });
};
</script>

<template>

    <Head title="Hasil Seleksi - PPDB SMKN 8" />

    <div class="min-h-screen bg-gradient-to-br from-blue-50 to-indigo-100">
        <!-- Navigation -->
        <nav class="bg-white shadow-md">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between h-16">
                    <div class="flex items-center">
                        <Link :href="route('home')" class="text-2xl font-bold text-blue-600">
                            PPDB SMKN 8 TIK KOTA JAYAPURA
                        </Link>
                    </div>
                </div>
            </div>
        </nav>

        <!-- Main Content -->
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
            <!-- Result Card -->
            <div class="bg-white rounded-lg shadow-lg p-8 mb-8">
                <div class="text-center mb-8">
                    <h1 class="text-3xl font-bold text-gray-800 mb-2">
                        Hasil Seleksi PPDB
                    </h1>
                    <p class="text-gray-600">
                        Tahun Ajaran 2026/2027
                    </p>
                </div>

                <!-- Student Info -->
                <div class="border-b pb-6 mb-6">
                    <div class="grid md:grid-cols-2 gap-4">
                        <div>
                            <p class="text-sm text-gray-500">Nomor Pendaftaran</p>
                            <p class="font-semibold text-lg text-blue-600">{{ student.registration_number }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Nama Lengkap</p>
                            <p class="font-medium text-gray-800">{{ student.full_name }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">NIK</p>
                            <p class="font-medium text-gray-800">{{ student.nik }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Tanggal Daftar</p>
                            <p class="font-medium text-gray-800">{{ formatDate(student.created_at) }}</p>
                        </div>
                    </div>
                </div>

                <!-- Result Status -->
                <div v-if="student.is_accepted && student.accepted_major"
                    class="bg-green-50 border-2 border-green-500 rounded-lg p-8 text-center mb-6">
                    <svg class="w-20 h-20 text-green-500 mx-auto mb-4" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <h2 class="text-3xl font-bold text-green-700 mb-2">
                        SELAMAT! ANDA DINYATAKAN LULUS
                    </h2>
                    <p class="text-green-600 mb-4">
                        Anda diterima di kompetensi keahlian:
                    </p>
                    <div class="bg-white rounded-lg px-6 py-4 inline-block">
                        <p class="text-2xl font-bold text-blue-600">
                            {{ student.accepted_major.name }}
                        </p>
                        <p class="text-gray-500">{{ student.accepted_major.code }}</p>
                    </div>
                </div>

                <div v-else-if="student.verification_status === 'rejected'"
                    class="bg-red-50 border-2 border-red-500 rounded-lg p-8 text-center mb-6">
                    <svg class="w-20 h-20 text-red-500 mx-auto mb-4" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <h2 class="text-2xl font-bold text-red-700 mb-2">
                        MAaf, ANDA BELUM LULUS SELEKSI
                    </h2>
                    <p v-if="student.verification_note" class="text-red-600 mt-4">
                        Catatan: {{ student.verification_note }}
                    </p>
                </div>

                <div v-else class="bg-yellow-50 border-2 border-yellow-500 rounded-lg p-8 text-center mb-6">
                    <svg class="w-20 h-20 text-yellow-500 mx-auto mb-4" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <h2 class="text-2xl font-bold text-yellow-700 mb-2">
                        HASIL SELEKSI BELUM DIUMUMKAN
                    </h2>
                    <p class="text-yellow-600">
                        Status verifikasi Anda saat ini:
                        <span class="font-semibold">{{ student.verification_status }}</span>
                    </p>
                    <p class="text-sm text-yellow-600 mt-2">
                        Hasil seleksi akan diumumkan sesuai jadwal yang telah ditentukan.
                    </p>
                </div>

                <!-- Major Preferences -->
                <div class="border-t pt-6">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4">
                        Pilihan Jurusan
                    </h3>
                    <div class="space-y-2">
                        <div v-for="major in student.majors" :key="major.id"
                            class="flex items-center justify-between bg-gray-50 rounded px-4 py-3">
                            <div class="flex items-center">
                                <span :class="{
                                    'bg-blue-100 text-blue-800': major.pivot.preference === 1,
                                    'bg-gray-200 text-gray-800': major.pivot.preference !== 1,
                                }"
                                    class="inline-flex items-center justify-center w-8 h-8 rounded-full text-sm font-bold mr-3">
                                    {{ major.pivot.preference }}
                                </span>
                                <span class="text-gray-700">{{ major.name }}</span>
                            </div>
                            <span class="text-sm text-gray-500">{{ major.code }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="flex justify-center">
                <Link :href="route('announcement.index')"
                    class="inline-flex items-center bg-gray-600 text-white px-6 py-3 rounded-lg hover:bg-gray-700 font-medium">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    Cek Lagi
                </Link>
            </div>
        </div>
    </div>
</template>
