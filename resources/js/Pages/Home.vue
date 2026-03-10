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

    <Head title="Beranda - PPDB SMKN 8" />

    <div class="min-h-screen bg-gradient-to-br from-blue-50 to-indigo-100">
        <!-- Navigation -->
        <nav class="bg-white shadow-md">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between h-16">
                    <div class="flex items-center">
                        <h1 class="text-2xl font-bold text-blue-600">PPDB SMKN 8 TIK KOTA JAYAPURA</h1>
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
                Jadwal PPDB
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
                    Masukkan nomor pendaftaran dan NIK untuk melihat hasil seleksi PPDB
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
