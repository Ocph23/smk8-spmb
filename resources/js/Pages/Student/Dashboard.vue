<script setup>
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps({
    student: {
        type: Object,
        required: true,
    },
    unreadCount: {
        type: Number,
        required: true,
    },
    recentInbox: {
        type: Array,
        required: true,
    },
    documents: {
        type: Array,
        default: () => [],
    },
});

const formatDate = (dateString) => {
    return new Date(dateString).toLocaleDateString('id-ID', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
    });
};

const getStatusBadge = (status) => {
    const badges = {
        pending: { class: 'bg-yellow-100 text-yellow-800', label: 'Menunggu Verifikasi' },
        verified: { class: 'bg-green-100 text-green-800', label: 'Terverifikasi' },
        rejected: { class: 'bg-red-100 text-red-800', label: 'Ditolak' },
    };
    return badges[status] || badges.pending;
};
</script>

<template>

    <Head title="Dashboard - SPMB SMKN 8" />

    <div class="min-h-screen bg-gray-50">
        <!-- Navigation -->
        <nav class="bg-white shadow-md">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between h-16">
                    <div class="flex items-center">
                        <Link :href="route('home')" class="text-2xl font-bold text-blue-600">
                            SPMB SMKN 8
                        </Link>
                    </div>
                    <div class="flex items-center space-x-4">
                        <Link :href="route('student.dashboard')"
                            class="text-gray-700 hover:text-blue-600 px-3 py-2 rounded-md text-sm font-medium">
                            Dashboard
                        </Link>
                        <Link :href="route('student.inbox')"
                            class="relative text-gray-700 hover:text-blue-600 px-3 py-2 rounded-md text-sm font-medium">
                            Inbox
                            <span v-if="unreadCount > 0"
                                class="absolute top-1 right-1 bg-red-500 text-white text-xs rounded-full w-5 h-5 flex items-center justify-center">
                                {{ unreadCount }}
                            </span>
                        </Link>
                        <form @submit.prevent="$inertia.post(route('student.logout'))" class="inline">
                            <button type="submit"
                                class="text-gray-700 hover:text-red-600 px-3 py-2 rounded-md text-sm font-medium">
                                Logout
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </nav>

        <!-- Main Content -->
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
            <!-- Welcome Banner -->
            <div class="bg-gradient-to-r from-blue-600 to-blue-700 rounded-lg shadow-lg p-8 mb-8 text-white">
                <h1 class="text-3xl font-bold mb-2">Selamat Datang, {{ student.full_name || 'Siswa Baru' }}!</h1>
                <p class="text-blue-100">Dashboard Pendaftaran SMKN 8 TIK KOTA JAYAPURA</p>
            </div>

            <!-- Alert jika belum lengkapi data -->
            <div v-if="!student.full_name || !student.street"
                class="bg-yellow-50 border border-yellow-200 text-yellow-800 px-6 py-4 rounded-lg mb-8">
                <div class="flex items-start">
                    <svg class="w-6 h-6 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                    <div class="flex-1">
                        <h3 class="font-semibold mb-2">⚠️ Data Pendaftaran Belum Lengkap</h3>
                        <p class="text-sm mb-3">
                            Anda belum melengkapi data pendaftaran. Silakan lengkapi data Anda untuk melanjutkan proses
                            pendaftaran.
                        </p>
                        <Link :href="route('student.register.form')"
                            class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 text-sm font-medium">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                            </svg>
                            Lengkapi Data Sekarang
                        </Link>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <!-- Left Column - Registration Status -->
                <div class="lg:col-span-2 space-y-6">
                    <!-- Registration Status Card -->
                    <div class="bg-white rounded-lg shadow-lg p-6">
                        <h2 class="text-xl font-semibold text-gray-800 mb-4 flex items-center">
                            <svg class="w-6 h-6 mr-2 text-blue-600" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            Status Pendaftaran
                        </h2>

                        <div v-if="student.registration_number" class="space-y-4">
                            <div class="bg-blue-50 rounded-lg p-4">
                                <p class="text-sm text-gray-600">Nomor Pendaftaran</p>
                                <p class="text-2xl font-bold text-blue-600">{{ student.registration_number }}</p>
                            </div>

                            <div class="flex items-center justify-between">
                                <p class="text-gray-600">Status Verifikasi</p>
                                <span :class="getStatusBadge(student.verification_status).class"
                                    class="px-4 py-2 rounded-full text-sm font-semibold">
                                    {{ getStatusBadge(student.verification_status).label }}
                                </span>
                            </div>

                            <div v-if="student.verification_note"
                                class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                                <p class="text-sm text-yellow-800">
                                    <strong>Catatan:</strong> {{ student.verification_note }}
                                </p>
                            </div>

                            <div class="border-t pt-4">
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <p class="text-sm text-gray-500">Email</p>
                                        <p class="font-medium text-gray-800">{{ student.email }}</p>
                                    </div>
                                    <div>
                                        <p class="text-sm text-gray-500">No. Telepon</p>
                                        <p class="font-medium text-gray-800">{{ student.phone }}</p>
                                    </div>
                                </div>
                            </div>

                            <!-- Action Buttons -->
                            <div class="flex flex-wrap gap-3 pt-4">
                                <Link v-if="student.registration_number"
                                    :href="route('student.preview', student.registration_number)"
                                    class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 text-sm font-medium">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                    </svg>
                                    Lihat Detail
                                </Link>
                                <Link v-if="student.verification_status === 'pending'"
                                    :href="route('student.edit', student.registration_number)"
                                    class="inline-flex items-center px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 text-sm font-medium">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                    </svg>
                                    Edit Data
                                </Link>
                            </div>
                        </div>
                        <div v-else class="text-center py-8">
                            <p class="text-gray-500 mb-4">Anda belum memiliki pendaftaran</p>
                            <Link :href="route('student.register')"
                                class="inline-flex items-center px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 font-medium">
                                Daftar Sekarang
                            </Link>
                        </div>
                    </div>

                    <!-- Jurusan Preferences -->
                    <div v-if="student.majors && student.majors.length > 0" class="bg-white rounded-lg shadow-lg p-6">
                        <h2 class="text-xl font-semibold text-gray-800 mb-4">Pilihan Jurusan</h2>
                        <div class="space-y-3">
                            <div v-for="(major, index) in student.majors" :key="major.id"
                                class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                                <div class="flex items-center">
                                    <span
                                        class="flex items-center justify-center w-8 h-8 bg-blue-100 text-blue-600 rounded-full font-bold text-sm mr-3">
                                        {{ index + 1 }}
                                    </span>
                                    <div>
                                        <p class="font-medium text-gray-800">{{ major.name }}</p>
                                        <p class="text-sm text-gray-500">{{ major.code }}</p>
                                    </div>
                                </div>
                                <span class="text-sm text-gray-500">Kuota: {{ major.quota }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right Column - Inbox & Info -->
                <div class="space-y-6">
                    <!-- Inbox Card -->
                    <div class="bg-white rounded-lg shadow-lg p-6">
                        <div class="flex items-center justify-between mb-4">
                            <h2 class="text-xl font-semibold text-gray-800">Inbox</h2>
                            <Link :href="route('student.inbox')"
                                class="text-blue-600 hover:text-blue-700 text-sm font-medium">
                                Lihat Semua
                            </Link>
                        </div>

                        <div v-if="recentInbox.length > 0" class="space-y-3">
                            <div v-for="message in recentInbox" :key="message.id"
                                :class="!message.is_read ? 'bg-blue-50 border-l-4 border-blue-500' : 'bg-gray-50'"
                                class="p-3 rounded-lg">
                                <Link :href="route('student.inbox.show', message.id)" class="block">
                                    <div class="flex items-start justify-between">
                                        <div class="flex-1">
                                            <p :class="!message.is_read ? 'font-semibold text-gray-800' : 'text-gray-600'"
                                                class="text-sm mb-1">
                                                {{ message.subject }}
                                            </p>
                                            <p class="text-xs text-gray-500">{{ formatDate(message.created_at) }}</p>
                                        </div>
                                        <span v-if="!message.is_read"
                                            class="bg-blue-500 text-white text-xs px-2 py-1 rounded-full">BARU</span>
                                    </div>
                                </Link>
                            </div>
                        </div>
                        <div v-else class="text-center py-8">
                            <p class="text-gray-400">Belum ada pesan</p>
                        </div>
                    </div>

                    <!-- Info Card -->
                    <div class="bg-gradient-to-br from-green-50 to-blue-50 rounded-lg shadow-lg p-6">
                        <h3 class="font-semibold text-gray-800 mb-3">Langkah Selanjutnya</h3>
                        <ul class="space-y-3 text-sm text-gray-700">
                            <li class="flex items-start">
                                <span
                                    class="flex items-center justify-center w-6 h-6 bg-green-500 text-white rounded-full text-xs font-bold mr-2 flex-shrink-0">1</span>
                                <span>Lengkapi data pendaftaran dengan benar</span>
                            </li>
                            <li class="flex items-start">
                                <span
                                    class="flex items-center justify-center w-6 h-6 bg-green-500 text-white rounded-full text-xs font-bold mr-2 flex-shrink-0">2</span>
                                <span>Upload semua berkas yang diperlukan</span>
                            </li>
                            <li class="flex items-start">
                                <span
                                    class="flex items-center justify-center w-6 h-6 bg-green-500 text-white rounded-full text-xs font-bold mr-2 flex-shrink-0">3</span>
                                <span>Tunggu verifikasi dari panitia</span>
                            </li>
                            <li class="flex items-start">
                                <span
                                    class="flex items-center justify-center w-6 h-6 bg-green-500 text-white rounded-full text-xs font-bold mr-2 flex-shrink-0">4</span>
                                <span>Pantau status melalui inbox</span>
                            </li>
                        </ul>
                    </div>

                    <!-- Help Card -->
                    <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-6">
                        <h3 class="font-semibold text-yellow-800 mb-2">Butuh Bantuan?</h3>
                        <p class="text-sm text-yellow-700 mb-3">
                            Jika mengalami kendala, hubungi panitia SPMB:
                        </p>
                        <div class="space-y-2 text-sm text-yellow-800">
                            <p>📞 +62 853-2004-3617</p>
                            <p>📧 info@smkn8jayapura.sch.id</p>
                        </div>
                    </div>

                    <!-- Dokumen Template -->
                    <div v-if="documents.length > 0" class="bg-white rounded-lg shadow-lg p-6">
                        <h3 class="font-semibold text-gray-800 mb-3 flex items-center gap-2">
                            <span>📂</span> Dokumen Template Persyaratan
                        </h3>
                        <p class="text-xs text-gray-500 mb-3">Download dan isi dokumen berikut sebagai kelengkapan berkas pendaftaran.</p>
                        <div class="space-y-2">
                            <a v-for="doc in documents" :key="doc.id"
                                :href="route('documents.download', doc.id)"
                                class="flex items-center justify-between p-3 rounded-lg border border-gray-200 hover:border-blue-400 hover:bg-blue-50 transition group">
                                <div class="flex items-center gap-2 min-w-0">
                                    <span class="text-lg flex-shrink-0">📄</span>
                                    <div class="min-w-0">
                                        <p class="text-sm font-medium text-gray-800 truncate">{{ doc.name }}</p>
                                        <p v-if="doc.description" class="text-xs text-gray-400 truncate">{{ doc.description }}</p>
                                    </div>
                                </div>
                                <span class="text-xs text-blue-600 group-hover:text-blue-700 flex-shrink-0 ml-2 font-medium">
                                    ↓ {{ doc.file_size }}
                                </span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
