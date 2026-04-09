<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    students: {
        type: Object,
        required: true,
    },
    majors: {
        type: Array,
        required: true,
    },
    waves: {
        type: Array,
        default: () => [],
    },
    filters: {
        type: Object,
        required: true,
    },
    currentAcademicYear: {
        type: Object,
        default: null,
    },
});

const searchForm = ref({
    search: props.filters.search || '',
    status: props.filters.status || '',
    major: props.filters.major || '',
    wave: props.filters.wave || '',
});

const search = () => {
    router.get(route('admin.students'), searchForm.value, {
        preserveState: true,
    });
};

const reset = () => {
    searchForm.value = {
        search: '',
        status: '',
        major: '',
        wave: '',
    };
    router.get(route('admin.students'));
};

const formatDate = (dateString) => {
    return new Date(dateString).toLocaleDateString('id-ID');
};

const verifyStudent = (studentId, status) => {
    if (!confirm(`Apakah Anda yakin ingin ${status === 'verified' ? 'menerima' : 'menolak'} pendaftar ini?`)) {
        return;
    }

    router.post(route('admin.students.verify', studentId), {
        status,
    });
};

const deleteStudent = (studentId) => {
    if (!confirm('Apakah Anda yakin ingin menghapus data pendaftar ini? Tindakan ini tidak dapat dibatalkan.')) {
        return;
    }
    router.delete(route('admin.students.destroy', studentId));
};
</script>

<template>
    <Head title="Kelola Pendaftar - Admin" />

    <AdminLayout>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <!-- Read-only banner -->
                <div v-if="currentAcademicYear?.status === 'closed'"
                    class="mb-4 rounded-lg border border-amber-300 bg-amber-50 px-4 py-3 flex items-center gap-2">
                    <svg class="h-5 w-5 text-amber-500 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z" />
                    </svg>
                    <span class="text-sm font-medium text-amber-800">Mode Hanya-Baca — Tahun ajaran ini sudah ditutup.</span>
                </div>

                <!-- Page header -->
                <div class="mb-6 flex items-center gap-3">
                    <h2 class="text-2xl font-bold text-gray-800">Kelola Pendaftar</h2>
                    <span v-if="currentAcademicYear"
                        class="inline-flex items-center rounded-full bg-blue-100 px-3 py-1 text-xs font-semibold text-blue-700">
                        {{ currentAcademicYear.name }}
                    </span>
                </div>

                <!-- Filters -->
                <div class="bg-white shadow-sm sm:rounded-lg mb-6">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-gray-800 mb-4">Filter</h3>
                        <form @submit.prevent="search" class="grid md:grid-cols-4 gap-4">
                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-gray-700 mb-1">
                                    Cari
                                </label>
                                <input
                                    v-model="searchForm.search"
                                    type="text"
                                    placeholder="Nama, No. Pendaftaran, atau NIK"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                />
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">
                                    Status Verifikasi
                                </label>
                                <select
                                    v-model="searchForm.status"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                >
                                    <option value="">Semua Status</option>
                                    <option value="pending">Pending</option>
                                    <option value="verified">Verified</option>
                                    <option value="rejected">Rejected</option>
                                </select>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">
                                    Jurusan
                                </label>
                                <select
                                    v-model="searchForm.major"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                >
                                    <option value="">Semua Jurusan</option>
                                    <option
                                        v-for="major in majors"
                                        :key="major.id"
                                        :value="major.id"
                                    >
                                        {{ major.name }}
                                    </option>
                                </select>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">
                                    Gelombang
                                </label>
                                <select
                                    v-model="searchForm.wave"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                >
                                    <option value="">Semua Gelombang</option>
                                    <option v-for="wave in waves" :key="wave.id" :value="wave.id">
                                        {{ wave.name }}
                                    </option>
                                </select>
                            </div>

                            <div class="md:col-span-4 flex gap-2">
                                <button
                                    type="submit"
                                    class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700"
                                >
                                    Filter
                                </button>
                                <button
                                    type="button"
                                    @click="reset"
                                    class="px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700"
                                >
                                    Reset
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Students Table -->
                <div class="bg-white shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                            No. Pendaftaran
                                        </th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                            Nama
                                        </th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                            Jurusan (Pilihan)
                                        </th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                            Gelombang
                                        </th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                            Status
                                        </th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                            Tanggal
                                        </th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                            Aksi
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <tr v-for="student in students.data" :key="student.id">
                                        <td class="px-4 py-4 text-sm font-medium text-blue-600">
                                            <Link
                                                :href="route('admin.students.show', student.id)"
                                                class="hover:underline"
                                            >
                                                {{ student.registration_number }}
                                            </Link>
                                        </td>
                                        <td class="px-4 py-4 text-sm text-gray-900">
                                            <div>
                                                <div class="font-medium">{{ student.full_name }}</div>
                                                <div class="text-gray-500 text-xs">{{ student.nik }}</div>
                                            </div>
                                        </td>
                                        <td class="px-4 py-4 text-sm text-gray-500">
                                            <div v-for="major in student.majors" :key="major.id" class="text-xs">
                                                <span class="font-medium">{{ major.pivot.preference === 1 ? 'P1:' : major.pivot.preference === 2 ? 'P2:' : 'P3:' }}</span>
                                                {{ major.name }}
                                            </div>
                                        </td>
                                        <td class="px-4 py-4 text-xs text-gray-500">
                                            {{ student.enrollment_wave?.name ?? '-' }}
                                        </td>
                                        <td class="px-4 py-4 text-sm">
                                            <span
                                                :class="{
                                                    'bg-yellow-100 text-yellow-800': student.verification_status === 'pending',
                                                    'bg-green-100 text-green-800': student.verification_status === 'verified',
                                                    'bg-red-100 text-red-800': student.verification_status === 'rejected',
                                                }"
                                                class="px-2 py-1 text-xs font-semibold rounded-full"
                                            >
                                                {{ student.verification_status }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-4 text-sm text-gray-500">
                                            {{ formatDate(student.created_at) }}
                                        </td>
                                        <td class="px-4 py-4 text-sm">
                                            <div class="flex gap-2 items-center">
                                                <Link
                                                    :href="route('admin.students.show', student.id)"
                                                    class="text-blue-600 hover:text-blue-800"
                                                >
                                                    Detail
                                                </Link>
                                                <button
                                                    v-if="student.verification_status !== 'verified'"
                                                    @click="verifyStudent(student.id, 'verified')"
                                                    :disabled="currentAcademicYear?.status === 'closed'"
                                                    title="Verifikasi"
                                                    class="text-green-600 hover:text-green-800 disabled:opacity-40 disabled:cursor-not-allowed"
                                                >
                                                    ✓
                                                </button>
                                                <button
                                                    v-if="student.verification_status !== 'rejected'"
                                                    @click="verifyStudent(student.id, 'rejected')"
                                                    :disabled="currentAcademicYear?.status === 'closed'"
                                                    title="Tolak"
                                                    class="text-red-600 hover:text-red-800 disabled:opacity-40 disabled:cursor-not-allowed"
                                                >
                                                    ✗
                                                </button>
                                                <button
                                                    @click="deleteStudent(student.id)"
                                                    :disabled="currentAcademicYear?.status === 'closed'"
                                                    title="Hapus"
                                                    class="text-gray-400 hover:text-red-600 disabled:opacity-40 disabled:cursor-not-allowed"
                                                >
                                                    🗑
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <div class="mt-4">
                            <div class="flex justify-between items-center">
                                <div class="text-sm text-gray-700">
                                    Menampilkan {{ students.from }} - {{ students.to }} dari {{ students.total }} data
                                </div>
                                <div class="flex gap-2">
                                    <Link
                                        v-for="link in students.links"
                                        :key="link.label"
                                        :href="link.url || ''"
                                        v-html="link.label"
                                        class="px-3 py-1 rounded"
                                        :class="{
                                            'bg-blue-600 text-white': link.active,
                                            'bg-gray-200 hover:bg-gray-300': !link.active && link.url,
                                            'bg-gray-100 text-gray-400 cursor-not-allowed': !link.url,
                                        }"
                                    />
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
