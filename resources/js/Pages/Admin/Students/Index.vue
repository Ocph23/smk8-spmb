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
    filters: {
        type: Object,
        required: true,
    },
});

const searchForm = ref({
    search: props.filters.search || '',
    status: props.filters.status || '',
    major: props.filters.major || '',
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
</script>

<template>
    <Head title="Kelola Pendaftar - Admin" />

    <AdminLayout>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
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
                                            <div class="flex gap-2">
                                                <Link
                                                    :href="route('admin.students.show', student.id)"
                                                    class="text-blue-600 hover:text-blue-800"
                                                >
                                                    Detail
                                                </Link>
                                                <button
                                                    v-if="student.verification_status === 'pending'"
                                                    @click="verifyStudent(student.id, 'verified')"
                                                    class="text-green-600 hover:text-green-800"
                                                >
                                                    ✓
                                                </button>
                                                <button
                                                    v-if="student.verification_status === 'pending'"
                                                    @click="verifyStudent(student.id, 'rejected')"
                                                    class="text-red-600 hover:text-red-800"
                                                >
                                                    ✗
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
