<script setup>
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps({
    student: {
        type: Object,
        required: true,
    },
});

const formatGender = (gender) => {
    return gender === 'male' ? 'Laki-laki' : 'Perempuan';
};

const formatDate = (date) => {
    if (!date) return '-';
    return new Date(date).toLocaleDateString('id-ID', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
    });
};

const getStorageUrl = (path) => {
    if (!path) return null;
    return `/storage/${path}`;
};

const downloadFile = (path, filename) => {
    if (!path) return;
    const link = document.createElement('a');
    link.href = getStorageUrl(path);
    link.download = filename;
    link.target = '_blank';
    link.click();
};

// Helper: cari dokumen berdasarkan field_name dari relasi documents
const getDoc = (fieldName) => {
    return props.student.documents?.find(d => d.registration_document?.field_name === fieldName) ?? null;
};
</script>

<template>
    <Head :title="`Preview Pendaftaran - ${student.registration_number}`" />

    <div class="min-h-screen bg-gray-50">
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
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
            <!-- Success Message -->
            <div v-if="$page.props.session?.success"
                class="mb-6 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg">
                {{ $page.props.session.success }}
            </div>

            <div class="bg-white rounded-lg shadow-lg p-8">
                <div class="text-center mb-8">
                    <h1 class="text-3xl font-bold text-gray-800 mb-2">
                        Preview Pendaftaran
                    </h1>
                    <p class="text-gray-600">
                        No. Pendaftaran: <span class="font-bold text-blue-600">{{ student.registration_number }}</span>
                    </p>
                    <p class="text-sm text-gray-500 mt-2">
                        Tanggal Daftar: {{ formatDate(student.created_at) }}
                    </p>
                </div>

                <!-- Status Badge -->
                <div class="mb-6 flex justify-center">
                    <span :class="{
                        'bg-yellow-100 text-yellow-800': student.verification_status === 'pending',
                        'bg-green-100 text-green-800': student.verification_status === 'verified',
                        'bg-red-100 text-red-800': student.verification_status === 'rejected',
                    }" class="px-4 py-2 text-sm font-semibold rounded-full">
                        <span v-if="student.verification_status === 'pending'">⏳ Menunggu Verifikasi</span>
                        <span v-else-if="student.verification_status === 'verified'">✅ Terverifikasi</span>
                        <span v-else-if="student.verification_status === 'rejected'">❌ Ditolak</span>
                    </span>
                </div>

                <!-- Student Photo -->
                <div class="flex justify-center mb-6">
                    <div v-if="getDoc('file_pas_photo')" class="text-center">
                        <img :src="getStorageUrl(getDoc('file_pas_photo').file_path)" alt="Pas Foto"
                            class="w-32 h-40 object-cover border-4 border-gray-200 rounded-lg" />
                        <p class="text-sm text-gray-500 mt-2">Pas Foto</p>
                    </div>
                    <div v-else class="w-32 h-40 bg-gray-100 border-2 border-dashed border-gray-300 rounded-lg flex items-center justify-center">
                        <span class="text-gray-400 text-sm">Foto belum diupload</span>
                    </div>
                </div>

                <!-- Data Pribadi -->
                <div class="mb-8">
                    <h2 class="text-xl font-semibold text-gray-800 mb-4 border-b pb-2">
                        Data Pribadi
                    </h2>
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
                            <p class="text-sm text-gray-500">NISN</p>
                            <p class="font-medium text-gray-800">{{ student.nisn || '-' }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Tempat, Tanggal Lahir</p>
                            <p class="font-medium text-gray-800">
                                {{ student.place_of_birth }}, {{ formatDate(student.date_of_birth) }}
                            </p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Jenis Kelamin</p>
                            <p class="font-medium text-gray-800">{{ formatGender(student.gender) }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Agama</p>
                            <p class="font-medium text-gray-800">{{ student.religion || '-' }}</p>
                        </div>
                    </div>
                </div>

                <!-- Alamat -->
                <div class="mb-8">
                    <h2 class="text-xl font-semibold text-gray-800 mb-4 border-b pb-2">
                        Alamat
                    </h2>
                    <div class="space-y-2">
                        <div>
                            <p class="text-sm text-gray-500">Jalan/Gang</p>
                            <p class="font-medium text-gray-800">{{ student.street || '-' }}</p>
                        </div>
                        <div class="grid md:grid-cols-3 gap-4">
                            <div>
                                <p class="text-sm text-gray-500">RT</p>
                                <p class="font-medium text-gray-800">{{ student.rt ? 'RT ' + student.rt : '-' }}</p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500">RW</p>
                                <p class="font-medium text-gray-800">{{ student.rw ? 'RW ' + student.rw : '-' }}</p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500">Dusun/Kampung</p>
                                <p class="font-medium text-gray-800">{{ student.dusun || '-' }}</p>
                            </div>
                        </div>
                        <div class="grid md:grid-cols-2 gap-4">
                            <div>
                                <p class="text-sm text-gray-500">Kecamatan/Distrik</p>
                                <p class="font-medium text-gray-800">{{ student.district || '-' }}</p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500">Kode Pos</p>
                                <p class="font-medium text-gray-800">{{ student.postal_code || '-' }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Kontak -->
                <div class="mb-8">
                    <h2 class="text-xl font-semibold text-gray-800 mb-4 border-b pb-2">
                        Kontak
                    </h2>
                    <div class="grid md:grid-cols-2 gap-4">
                        <div>
                            <p class="text-sm text-gray-500">No. Telepon/HP</p>
                            <p class="font-medium text-gray-800">{{ student.phone }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Email</p>
                            <p class="font-medium text-gray-800">{{ student.email }}</p>
                        </div>
                    </div>
                </div>

                <!-- Data Orang Tua -->
                <div class="mb-8">
                    <h2 class="text-xl font-semibold text-gray-800 mb-4 border-b pb-2">
                        Data Orang Tua/Wali
                    </h2>
                    <div class="grid md:grid-cols-2 gap-4">
                        <div>
                            <p class="text-sm text-gray-500">Nama Ayah/Wali</p>
                            <p class="font-medium text-gray-800">{{ student.parent_name }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Nama Ibu</p>
                            <p class="font-medium text-gray-800">{{ student.mother_name || '-' }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">No. Telepon Orang Tua/Wali</p>
                            <p class="font-medium text-gray-800">{{ student.parent_phone }}</p>
                        </div>
                    </div>
                </div>

                <!-- Pilihan Jurusan -->
                <div class="mb-8">
                    <h2 class="text-xl font-semibold text-gray-800 mb-4 border-b pb-2">
                        Pilihan Jurusan
                    </h2>
                    <div class="space-y-2">
                        <div v-for="(major, index) in student.majors" :key="major.id"
                            class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                            <div>
                                <p class="font-medium text-gray-800">
                                    Pilihan {{ index + 1 }}: {{ major.name }} ({{ major.code }})
                                </p>
                            </div>
                            <span class="text-sm text-gray-500">Kuota: {{ major.quota }}</span>
                        </div>
                    </div>
                </div>

                <!-- Berkas Upload -->
                <div class="mb-8">
                    <h2 class="text-xl font-semibold text-gray-800 mb-4 border-b pb-2">
                        Berkas Upload
                    </h2>
                    <div v-if="student.documents && student.documents.length > 0" class="grid md:grid-cols-2 gap-4">
                        <div v-for="doc in student.documents" :key="doc.id" class="p-4 border rounded-lg">
                            <p class="text-sm text-gray-500 mb-2">{{ doc.registration_document?.label ?? doc.file_name }}</p>
                            <div class="flex items-center justify-between">
                                <span class="text-green-600 text-sm">✓ File terupload</span>
                                <button @click="downloadFile(doc.file_path, doc.file_name)"
                                    class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                                    Download
                                </button>
                            </div>
                        </div>
                    </div>
                    <p v-else class="text-gray-400 text-sm">Belum ada berkas yang diupload.</p>
                </div>

                <!-- Action Buttons -->
                <div class="flex flex-col md:flex-row gap-4 justify-center mt-8 pt-6 border-t">
                    <Link :href="route('student.edit', student.registration_number)"
                        class="inline-flex justify-center items-center px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                        </svg>
                        Edit Data Pendaftaran
                    </Link>
                    <Link :href="route('student.certificate', student.registration_number)"
                        class="inline-flex justify-center items-center px-6 py-3 bg-green-600 text-white rounded-lg hover:bg-green-700">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        Cetak Bukti Pendaftaran
                    </Link>
                </div>

                <!-- Important Note -->
                <div v-if="student.verification_status === 'pending'"
                    class="mt-6 bg-yellow-50 border border-yellow-200 text-yellow-800 px-4 py-3 rounded-lg">
                    <p class="font-semibold mb-1">⚠️ Catatan Penting:</p>
                    <p class="text-sm">
                        Pendaftaran Anda sedang menunggu verifikasi dari panitia. Anda masih dapat mengedit data
                        jika terdapat kesalahan. Setelah diverifikasi, data tidak dapat diubah lagi.
                    </p>
                </div>
            </div>
        </div>
    </div>
</template>
