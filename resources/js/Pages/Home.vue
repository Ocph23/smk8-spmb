<script setup>
import { Head, Link } from '@inertiajs/vue3';
import { computed } from 'vue';

defineProps({
    schedules: {
        type: Array,
        required: true,
    },
    majors: {
        type: Array,
        required: true,
    },

    auth: {
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

const getStatusColor = (status) => {
    switch (status) {
        case 'active':
            return 'bg-green-100 text-green-800';
        case 'inactive':
            return 'bg-gray-100 text-gray-800';
        case 'completed':
            return 'bg-blue-100 text-blue-800';
        default:
            return 'bg-gray-100 text-gray-800';
    }
};

const getStatusLabel = (status) => {
    switch (status) {
        case 'active':
            return 'Berlangsung';
        case 'inactive':
            return 'Akan Datang';
        case 'completed':
            return 'Selesai';
        default:
            return status;
    }
};
</script>

<template>

    <Head title="Beranda - SPMB SMKN 8" />

    <div class="min-h-screen bg-gradient-to-br from-blue-50 to-indigo-100">
        <!-- Navigation -->
        <nav class="bg-white shadow-md">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between h-16">
                    <div class="flex items-center">
                        <h1 class="text-2xl font-bold text-blue-600">SPMB SMKN 8 TIK KOTA JAYAPURA</h1>
                    </div>
                    <div class="flex items-center space-x-4">
                        <a href="#jadwal"
                            class="text-gray-700 hover:text-blue-600 px-3 py-2 rounded-md text-sm font-medium">
                            Jadwal
                        </a>
                        <Link v-if="auth.user" :href="route('dashboard')"
                            class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700">
                            Dashboard
                        </Link>
                        <Link v-else :href="route('login')"
                            class="text-gray-700 hover:text-blue-600 px-3 py-2 rounded-md text-sm font-medium">
                            Login
                        </Link>
                        <Link :href="route('student.register')"
                            class="bg-green-600 text-white px-4 py-2 rounded-md hover:bg-green-700 font-medium">
                            Daftar Sekarang
                        </Link>
                    </div>
                </div>
            </div>
        </nav>

        <!-- Hero Section -->
        <div class="bg-gradient-to-r from-blue-600 to-indigo-700 text-white py-20">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
                <h2 class="text-4xl md:text-5xl font-bold mb-4">
                    Penerimaan Peserta Didik Baru
                </h2>
                <p class="text-xl md:text-2xl mb-2">
                    SMK NEGERI 8 TIK KOTA JAYAPURA
                </p>
                <p class="text-lg opacity-90 mb-8">
                    Tahun Ajaran 2026/2027
                </p>
                <div class="flex justify-center space-x-4">
                    <Link :href="route('student.register')"
                        class="bg-white text-blue-600 px-8 py-3 rounded-lg font-semibold hover:bg-gray-100 transition">
                        Daftar Sekarang
                    </Link>
                    <a href="#jadwal"
                        class="border-2 border-white text-white px-8 py-3 rounded-lg font-semibold hover:bg-white hover:text-blue-600 transition">
                        Lihat Jadwal
                    </a>
                </div>
            </div>
        </div>

        <!-- Jadwal Section -->
        <div id="jadwal" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
            <h3 class="text-3xl font-bold text-center text-gray-800 mb-2">
                Jadwal SPMB
            </h3>
            <p class="text-center text-gray-600 mb-12">
                Timeline kegiatan penerimaan peserta didik baru
            </p>

            <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-6">
                <div v-for="schedule in schedules" :key="schedule.id"
                    class="bg-white rounded-lg shadow-lg p-6 border-t-4 border-blue-500 hover:shadow-xl transition">
                    <span :class="getStatusColor(schedule.status)"
                        class="inline-block px-3 py-1 rounded-full text-xs font-medium mb-3">
                        {{ getStatusLabel(schedule.status) }}
                    </span>
                    <h4 class="text-lg font-bold text-gray-800 mb-2">
                        {{ schedule.title }}
                    </h4>
                    <p class="text-sm text-gray-600 mb-4">
                        {{ schedule.description }}
                    </p>
                    <div class="text-sm text-gray-500">
                        <p>
                            <strong>{{ formatDate(schedule.start_date) }}</strong>
                        </p>
                        <p v-if="schedule.start_date !== schedule.end_date">
                            s/d {{ formatDate(schedule.end_date) }}
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Cek Kelulusan Section -->
        <div class="bg-white py-16">
            <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
                <h3 class="text-3xl font-bold text-gray-800 mb-4">
                    Cek Hasil Seleksi
                </h3>
                <p class="text-gray-600 mb-8">
                    Masukkan nomor pendaftaran dan NIK untuk melihat hasil seleksi SPMB
                </p>
                <Link :href="route('announcement.index')"
                    class="inline-flex items-center bg-blue-600 text-white px-8 py-4 rounded-lg font-semibold hover:bg-blue-700 transition text-lg">
                    <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                    Cek Kelulusan
                </Link>
            </div>
        </div>

        <!-- Persyaratan Section -->
        <div class="bg-white py-16">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <h3 class="text-3xl font-bold text-center text-gray-800 mb-4">
                    Persyaratan Pendaftaran
                </h3>
                <p class="text-center text-gray-600 mb-12">
                    Pastikan Anda memenuhi persyaratan berikut sebelum mendaftar
                </p>

                <div class="grid md:grid-cols-2 gap-8">
                    <div class="bg-gradient-to-br from-blue-50 to-indigo-100 rounded-lg shadow-lg p-8">
                        <div class="flex items-center mb-6">
                            <div class="w-12 h-12 bg-blue-600 rounded-full flex items-center justify-center mr-4">
                                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <h4 class="text-xl font-bold text-gray-800">Persyaratan Umum</h4>
                        </div>
                        <ul class="space-y-3">
                            <li class="flex items-start">
                                <svg class="w-5 h-5 text-green-500 mr-2 mt-0.5 flex-shrink-0" fill="currentColor"
                                    viewBox="0 0 20 20">
                                    <path fill-rule="evenodd"
                                        d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                        clip-rule="evenodd" />
                                </svg>
                                <span class="text-gray-700">Lulusan SMP/MTs atau paket B tahun 2026</span>
                            </li>
                            <li class="flex items-start">
                                <svg class="w-5 h-5 text-green-500 mr-2 mt-0.5 flex-shrink-0" fill="currentColor"
                                    viewBox="0 0 20 20">
                                    <path fill-rule="evenodd"
                                        d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                        clip-rule="evenodd" />
                                </svg>
                                <span class="text-gray-700">Memiliki NIK dan NISN yang valid</span>
                            </li>
                            <li class="flex items-start">
                                <svg class="w-5 h-5 text-green-500 mr-2 mt-0.5 flex-shrink-0" fill="currentColor"
                                    viewBox="0 0 20 20">
                                    <path fill-rule="evenodd"
                                        d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                        clip-rule="evenodd" />
                                </svg>
                                <span class="text-gray-700">Sehat jasmani dan rohani</span>
                            </li>
                            <li class="flex items-start">
                                <svg class="w-5 h-5 text-green-500 mr-2 mt-0.5 flex-shrink-0" fill="currentColor"
                                    viewBox="0 0 20 20">
                                    <path fill-rule="evenodd"
                                        d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                        clip-rule="evenodd" />
                                </svg>
                                <span class="text-gray-700">Tidak sedang terdaftar di sekolah lain</span>
                            </li>
                            <li class="flex items-start">
                                <svg class="w-5 h-5 text-green-500 mr-2 mt-0.5 flex-shrink-0" fill="currentColor"
                                    viewBox="0 0 20 20">
                                    <path fill-rule="evenodd"
                                        d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                        clip-rule="evenodd" />
                                </svg>
                                <span class="text-gray-700">Berkelakuan baik dan tidak terlibat narkoba</span>
                            </li>
                        </ul>
                    </div>

                    <div class="bg-gradient-to-br from-green-50 to-emerald-100 rounded-lg shadow-lg p-8">
                        <div class="flex items-center mb-6">
                            <div class="w-12 h-12 bg-green-600 rounded-full flex items-center justify-center mr-4">
                                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                            </div>
                            <h4 class="text-xl font-bold text-gray-800">Berkas yang Harus Dipersiapkan</h4>
                        </div>
                        <ul class="space-y-3">
                            <li class="flex items-start">
                                <svg class="w-5 h-5 text-green-500 mr-2 mt-0.5 flex-shrink-0" fill="currentColor"
                                    viewBox="0 0 20 20">
                                    <path fill-rule="evenodd"
                                        d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                        clip-rule="evenodd" />
                                </svg>
                                <span class="text-gray-700">Scan Ijazah/SKL (PDF/JPG/PNG, max 2MB)</span>
                            </li>
                            <li class="flex items-start">
                                <svg class="w-5 h-5 text-green-500 mr-2 mt-0.5 flex-shrink-0" fill="currentColor"
                                    viewBox="0 0 20 20">
                                    <path fill-rule="evenodd"
                                        d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                        clip-rule="evenodd" />
                                </svg>
                                <span class="text-gray-700">Scan Kartu Keluarga - KK (PDF/JPG/PNG, max 2MB)</span>
                            </li>
                            <li class="flex items-start">
                                <svg class="w-5 h-5 text-green-500 mr-2 mt-0.5 flex-shrink-0" fill="currentColor"
                                    viewBox="0 0 20 20">
                                    <path fill-rule="evenodd"
                                        d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                        clip-rule="evenodd" />
                                </svg>
                                <span class="text-gray-700">Scan Akta Kelahiran (PDF/JPG/PNG, max 2MB)</span>
                            </li>
                            <li class="flex items-start">
                                <svg class="w-5 h-5 text-green-500 mr-2 mt-0.5 flex-shrink-0" fill="currentColor"
                                    viewBox="0 0 20 20">
                                    <path fill-rule="evenodd"
                                        d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                        clip-rule="evenodd" />
                                </svg>
                                <span class="text-gray-700">Pas Foto 3x4 (JPG/PNG, background merah/biru, max 2MB)</span>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <!-- Alur Pendaftaran Section -->
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
            <h3 class="text-3xl font-bold text-center text-gray-800 mb-4">
                Alur Pendaftaran
            </h3>
            <p class="text-center text-gray-600 mb-12">
                Ikuti langkah-langkah berikut untuk melakukan pendaftaran
            </p>

            <div class="relative">
                <!-- Timeline Line -->
                <div class="hidden md:block absolute left-1/2 transform -translate-x-1/2 h-full w-1 bg-gradient-to-b from-blue-500 via-green-500 to-indigo-500"></div>

                <div class="space-y-12">
                    <!-- Step 1 -->
                    <div class="relative flex flex-col md:flex-row items-center">
                        <div class="md:w-1/2 md:pr-12 md:text-right">
                            <div class="bg-white rounded-lg shadow-lg p-6 border-l-4 border-blue-500">
                                <div class="flex items-center md:justify-end mb-3">
                                    <span
                                        class="bg-blue-500 text-white w-8 h-8 rounded-full flex items-center justify-center font-bold text-sm">
                                        1
                                    </span>
                                </div>
                                <h4 class="text-lg font-bold text-gray-800 mb-2">Isi Formulir Pendaftaran</h4>
                                <p class="text-gray-600 text-sm">
                                    Lengkapi biodata diri, pilih jurusan yang diminati (3 pilihan sesuai urutan prioritas),
                                    dan upload berkas persyaratan dalam format PDF atau gambar.
                                </p>
                            </div>
                        </div>
                        <div
                            class="absolute left-1/2 transform -translate-x-1/2 w-8 h-8 bg-blue-500 rounded-full border-4 border-white shadow-lg hidden md:flex items-center justify-center">
                            <svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                    d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                    clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="md:w-1/2 md:pl-12 mt-4 md:mt-0">
                            <div class="bg-blue-50 rounded-lg p-4 text-center md:text-left">
                                <p class="text-sm text-gray-600">
                                    <strong>Hasil:</strong> Anda akan mendapatkan nomor pendaftaran dan bukti pendaftaran
                                    yang dapat dicetak.
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Step 2 -->
                    <div class="relative flex flex-col md:flex-row items-center">
                        <div class="md:w-1/2 md:pr-12 md:text-right mt-4 md:mt-0 order-2 md:order-1">
                            <div class="bg-green-50 rounded-lg p-4 text-center md:text-right">
                                <p class="text-sm text-gray-600">
                                    <strong>Proses:</strong> Panitia akan memverifikasi berkas Anda dalam waktu 1-3 hari
                                    kerja.
                                </p>
                            </div>
                        </div>
                        <div
                            class="absolute left-1/2 transform -translate-x-1/2 w-8 h-8 bg-green-500 rounded-full border-4 border-white shadow-lg hidden md:flex items-center justify-center order-2 md:order-2">
                            <svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                    d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                    clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="md:w-1/2 md:pl-12 mt-4 md:mt-0 order-1 md:order-2">
                            <div class="bg-white rounded-lg shadow-lg p-6 border-r-4 border-green-500">
                                <div class="flex items-center md:justify-start mb-3">
                                    <span
                                        class="bg-green-500 text-white w-8 h-8 rounded-full flex items-center justify-center font-bold text-sm">
                                        2
                                    </span>
                                </div>
                                <h4 class="text-lg font-bold text-gray-800 mb-2">Verifikasi Berkas</h4>
                                <p class="text-gray-600 text-sm">
                                    Setelah mendaftar, tunggu proses verifikasi berkas oleh panitia.
                                    Anda dapat memantau status verifikasi melalui halaman pengumuman.
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Step 3 -->
                    <div class="relative flex flex-col md:flex-row items-center">
                        <div class="md:w-1/2 md:pr-12 md:text-right">
                            <div class="bg-white rounded-lg shadow-lg p-6 border-l-4 border-indigo-500">
                                <div class="flex items-center md:justify-end mb-3">
                                    <span
                                        class="bg-indigo-500 text-white w-8 h-8 rounded-full flex items-center justify-center font-bold text-sm">
                                        3
                                    </span>
                                </div>
                                <h4 class="text-lg font-bold text-gray-800 mb-2">Cek Hasil Seleksi</h4>
                                <p class="text-gray-600 text-sm">
                                    Setelah proses verifikasi dan seleksi selesai, cek hasil seleksi menggunakan
                                    nomor pendaftaran dan NIK Anda. Pengumuman akan ditampilkan secara online.
                                </p>
                            </div>
                        </div>
                        <div
                            class="absolute left-1/2 transform -translate-x-1/2 w-8 h-8 bg-indigo-500 rounded-full border-4 border-white shadow-lg hidden md:flex items-center justify-center">
                            <svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                    d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                    clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="md:w-1/2 md:pl-12 mt-4 md:mt-0">
                            <div class="bg-indigo-50 rounded-lg p-4 text-center md:text-left">
                                <p class="text-sm text-gray-600">
                                    <strong>Hasil:</strong> Jika dinyatakan lulus, Anda akan melihat jurusan yang
                                    dialokasikan.
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Step 4 -->
                    <div class="relative flex flex-col md:flex-row items-center">
                        <div class="md:w-1/2 md:pr-12 md:text-right mt-4 md:mt-0 order-2 md:order-1">
                            <div class="bg-purple-50 rounded-lg p-4 text-center md:text-right">
                                <p class="text-sm text-gray-600">
                                    <strong>Penting:</strong> Lakukan daftar ulang sesuai jadwal yang ditentukan.
                                </p>
                            </div>
                        </div>
                        <div
                            class="absolute left-1/2 transform -translate-x-1/2 w-8 h-8 bg-purple-500 rounded-full border-4 border-white shadow-lg hidden md:flex items-center justify-center order-2 md:order-2">
                            <svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                    d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                    clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="md:w-1/2 md:pl-12 mt-4 md:mt-0 order-1 md:order-2">
                            <div class="bg-white rounded-lg shadow-lg p-6 border-r-4 border-purple-500">
                                <div class="flex items-center md:justify-start mb-3">
                                    <span
                                        class="bg-purple-500 text-white w-8 h-8 rounded-full flex items-center justify-center font-bold text-sm">
                                        4
                                    </span>
                                </div>
                                <h4 class="text-lg font-bold text-gray-800 mb-2">Daftar Ulang</h4>
                                <p class="text-gray-600 text-sm">
                                    Bagi yang dinyatakan lulus seleksi, lakukan daftar ulang dengan membawa
                                    berkas asli dan melengkapi administrasi sesuai jadwal yang ditentukan.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- CTA Button -->
            <div class="text-center mt-12">
                <Link :href="route('student.register')"
                    class="inline-flex items-center bg-blue-600 text-white px-8 py-4 rounded-lg font-semibold hover:bg-blue-700 transition text-lg shadow-lg">
                    <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                    </svg>
                    Daftar Sekarang
                </Link>
            </div>
        </div>

        <!-- Jurusan Section -->
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
            <h3 class="text-3xl font-bold text-center text-gray-800 mb-4">
                Kompetensi Keahlian
            </h3>
            <p class="text-center text-gray-600 mb-12">
                Pilih jurusan yang sesuai dengan minat dan bakatmu
            </p>

            <div class="grid md:grid-cols-2 gap-8">
                <div v-for="major in majors" :key="major.id" class="bg-white rounded-lg shadow-lg p-6 text-center">
                    <div class="w-16 h-16 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <div v-html="major.icon_svg"></div>
                    </div>
                    <h4 class="text-xl font-bold text-gray-800 mb-2">{{ major.code }}</h4>
                    <p class="text-gray-600">{{ major.name }}</p>
                    <p class="text-sm text-gray-500 mt-2">
                        {{ major.description }}
                    </p>
                </div>


            </div>
        </div>

        <!-- Footer -->
        <footer class="bg-gray-800 text-white py-8">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
                <p class="mb-2">
                    <strong>SMK NEGERI 8 TIK KOTA JAYAPURA</strong>
                </p>
                <p class="text-sm text-gray-400">
                    JL. Gelanggan II RT 04 RW 01 Keluaran Waena, Distrik Heram, Kota Jayapura, Papua
                </p>
                <p class="text-sm text-gray-400 mt-2">
                    Email: admin@smkn8tikjayapura.sch.id
                </p>
            </div>
        </footer>
    </div>
</template>
