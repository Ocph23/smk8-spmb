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
        hour: '2-digit',
        minute: '2-digit',
    });
};
</script>

<template>

    <Head :title="`Pendaftaran Berhasil - ${student.registration_number}`" />

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
                </div>
            </div>
        </nav>

        <!-- Main Content -->
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
            <!-- Success Banner -->
            <div class="bg-green-50 border border-green-200 rounded-lg p-6 mb-8">
                <div class="flex items-center">
                    <svg class="w-12 h-12 text-green-500 mr-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <div>
                        <h1 class="text-2xl font-bold text-green-800">
                            Pendaftaran Berhasil!
                        </h1>
                        <p class="text-green-700">
                            Data Anda telah tersimpan. Simpan nomor pendaftaran ini dengan baik.
                        </p>
                    </div>
                </div>
            </div>

            <!-- Registration Number Card -->
            <div class="bg-white rounded-lg shadow-lg p-8 mb-8">
                <div class="text-center mb-6">
                    <p class="text-gray-600 mb-2">Nomor Pendaftaran Anda</p>
                    <p class="text-4xl font-bold text-blue-600 tracking-wider">
                        {{ student.registration_number }}
                    </p>
                </div>

                <div class="border-t pt-6">
                    <h2 class="text-lg font-semibold text-gray-800 mb-4">Informasi Pendaftaran</h2>

                    <div class="grid md:grid-cols-2 gap-4">
                        <div>
                            <p class="text-sm text-gray-500">Nama Lengkap</p>
                            <p class="font-medium text-gray-800">{{ student.full_name }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">NIK</p>
                            <p class="font-medium text-gray-800">{{ student.nik }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Email</p>
                            <p class="font-medium text-gray-800">{{ student.email }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">No. Telepon</p>
                            <p class="font-medium text-gray-800">{{ student.phone }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Tanggal Daftar</p>
                            <p class="font-medium text-gray-800">{{ formatDate(student.created_at) }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Status Verifikasi</p>
                            <p :class="{
                                'text-yellow-600': student.verification_status === 'pending',
                                'text-green-600': student.verification_status === 'verified',
                                'text-red-600': student.verification_status === 'rejected',
                            }" class="font-medium">
                                {{ student.verification_status === 'pending' ? 'Menunggu Verifikasi' :
                                    student.verification_status === 'verified' ? 'Terverifikasi' : 'Ditolak' }}
                            </p>
                        </div>
                    </div>
                </div>

                <div class="border-t pt-6 mt-6">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4">Pilihan Jurusan</h3>
                    <div class="space-y-2">
                        <div v-for="major in student.majors" :key="major.id"
                            class="flex items-center justify-between bg-gray-50 rounded px-4 py-2">
                            <span class="text-gray-700">
                                Pilihan {{ major.pivot.preference }}: {{ major.name }}
                            </span>
                            <span class="text-sm text-gray-500">{{ major.code }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="flex flex-col md:flex-row gap-4 justify-center">
                <Link :href="route('student.preview', student.registration_number)"
                    class="inline-flex items-center justify-center bg-green-600 text-white px-6 py-3 rounded-lg hover:bg-green-700 font-medium">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                    </svg>
                    Lihat Detail Pendaftaran
                </Link>

                <a :href="route('student.certificate.print', student.registration_number)" target="_blank"
                    class="inline-flex items-center justify-center bg-blue-600 text-white px-6 py-3 rounded-lg hover:bg-blue-700 font-medium">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                    </svg>
                    Cetak Bukti Pendaftaran (PDF)
                </a>

                <Link :href="route('home')"
                    class="inline-flex items-center justify-center bg-gray-600 text-white px-6 py-3 rounded-lg hover:bg-gray-700 font-medium">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                    </svg>
                    Kembali ke Beranda
                </Link>
            </div>

            <!-- Info Box -->
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-6 mt-8">
                <h3 class="font-semibold text-blue-800 mb-2">Langkah Selanjutnya:</h3>
                <ul class="space-y-2 text-blue-700">
                    <li class="flex items-start">
                        <span class="text-blue-500 mr-2">1.</span>
                        <span>Tunggu proses verifikasi dokumen (1-3 hari kerja)</span>
                    </li>
                    <li class="flex items-start">
                        <span class="text-blue-500 mr-2">2.</span>
                        <span>Pantau status verifikasi melalui halaman Cek Kelulusan</span>
                    </li>
                    <li class="flex items-start">
                        <span class="text-blue-500 mr-2">3.</span>
                        <span>Jika diterima, lakukan daftar ulang sesuai jadwal yang ditentukan</span>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</template>
