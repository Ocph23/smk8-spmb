<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    academicYears: { type: Array, required: true },
    majors: { type: Array, required: true },
});

const page = usePage();

const flash = computed(() => page.props.flash ?? {});
const errors = computed(() => page.props.errors ?? {});

const createForm = useForm({
    name: '',
    start_year: '',
    end_year: '',
    description: '',
});

const submitCreate = () => {
    createForm.post(route('admin.academic-years.store'), {
        onSuccess: () => createForm.reset(),
    });
};

const statusBadgeClass = (status) => {
    if (status === 'active') return 'bg-green-100 text-green-700';
    if (status === 'closed') return 'bg-blue-100 text-blue-700';
    return 'bg-gray-100 text-gray-600';
};

const statusLabel = (status) => {
    if (status === 'active') return 'Aktif';
    if (status === 'closed') return 'Tutup';
    return 'Draft';
};

const activate = (id) => {
    if (!confirm('Aktifkan tahun ajaran ini? Tahun ajaran yang sedang aktif akan ditutup.')) return;
    useForm({}).post(route('admin.academic-years.activate', id));
};

const close = (id) => {
    if (!confirm('Tutup tahun ajaran ini?')) return;
    useForm({}).post(route('admin.academic-years.close', id));
};

const destroy = (id) => {
    if (!confirm('Hapus tahun ajaran ini? Tindakan ini tidak dapat dibatalkan.')) return;
    useForm({}).delete(route('admin.academic-years.destroy', id));
};
</script>

<template>
    <Head title="Manajemen Tahun Ajaran" />

    <AdminLayout>
        <div class="mx-auto max-w-6xl space-y-6">
            <h1 class="text-xl font-semibold text-gray-800">Manajemen Tahun Ajaran</h1>

            <!-- Flash messages -->
            <div v-if="flash.success" class="rounded-md bg-green-50 p-4 text-sm text-green-700">
                {{ flash.success }}
            </div>
            <div v-if="errors.error" class="rounded-md bg-red-50 p-4 text-sm text-red-700">
                {{ errors.error }}
            </div>

            <!-- Create Form -->
            <div class="rounded-lg bg-white p-6 shadow-sm">
                <h2 class="mb-4 text-base font-medium text-gray-700">Tambah Tahun Ajaran Baru</h2>
                <form @submit.prevent="submitCreate" class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
                    <div>
                        <label class="mb-1 block text-xs font-medium text-gray-600">Nama <span class="text-red-500">*</span></label>
                        <input
                            v-model="createForm.name"
                            type="text"
                            placeholder="2025/2026"
                            class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500"
                        />
                        <p v-if="createForm.errors.name" class="mt-1 text-xs text-red-600">{{ createForm.errors.name }}</p>
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-medium text-gray-600">Tahun Mulai <span class="text-red-500">*</span></label>
                        <input
                            v-model="createForm.start_year"
                            type="number"
                            placeholder="2025"
                            class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500"
                        />
                        <p v-if="createForm.errors.start_year" class="mt-1 text-xs text-red-600">{{ createForm.errors.start_year }}</p>
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-medium text-gray-600">Tahun Selesai <span class="text-red-500">*</span></label>
                        <input
                            v-model="createForm.end_year"
                            type="number"
                            placeholder="2026"
                            class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500"
                        />
                        <p v-if="createForm.errors.end_year" class="mt-1 text-xs text-red-600">{{ createForm.errors.end_year }}</p>
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-medium text-gray-600">Deskripsi</label>
                        <input
                            v-model="createForm.description"
                            type="text"
                            placeholder="Opsional"
                            class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500"
                        />
                    </div>
                    <div class="sm:col-span-2 lg:col-span-4">
                        <button
                            type="submit"
                            :disabled="createForm.processing"
                            class="rounded-md bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700 disabled:opacity-50"
                        >
                            Tambah Tahun Ajaran
                        </button>
                    </div>
                </form>
            </div>

            <!-- Table -->
            <div class="rounded-lg bg-white shadow-sm">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium uppercase text-gray-500">Nama</th>
                                <th class="px-4 py-3 text-left text-xs font-medium uppercase text-gray-500">Tahun</th>
                                <th class="px-4 py-3 text-left text-xs font-medium uppercase text-gray-500">Status</th>
                                <th class="px-4 py-3 text-left text-xs font-medium uppercase text-gray-500">Siswa</th>
                                <th class="px-4 py-3 text-left text-xs font-medium uppercase text-gray-500">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 bg-white">
                            <tr v-if="academicYears.length === 0">
                                <td colspan="5" class="px-4 py-8 text-center text-sm text-gray-500">
                                    Belum ada tahun ajaran.
                                </td>
                            </tr>
                            <tr v-for="year in academicYears" :key="year.id" class="hover:bg-gray-50">
                                <td class="px-4 py-3 text-sm font-medium text-gray-900">{{ year.name }}</td>
                                <td class="px-4 py-3 text-sm text-gray-600">{{ year.start_year }} / {{ year.end_year }}</td>
                                <td class="px-4 py-3">
                                    <span :class="['rounded-full px-2 py-0.5 text-xs font-medium', statusBadgeClass(year.status)]">
                                        {{ statusLabel(year.status) }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-600">{{ year.students_count }}</td>
                                <td class="px-4 py-3">
                                    <div class="flex flex-wrap gap-2">
                                        <button
                                            v-if="year.status === 'draft' || year.status === 'closed'"
                                            @click="activate(year.id)"
                                            class="rounded bg-green-600 px-2 py-1 text-xs font-medium text-white hover:bg-green-700"
                                        >
                                            Aktifkan
                                        </button>
                                        <button
                                            v-if="year.status === 'active'"
                                            @click="close(year.id)"
                                            class="rounded bg-blue-600 px-2 py-1 text-xs font-medium text-white hover:bg-blue-700"
                                        >
                                            Tutup
                                        </button>
                                        <Link
                                            :href="route('admin.academic-years.major-config', year.id)"
                                            class="rounded bg-gray-600 px-2 py-1 text-xs font-medium text-white hover:bg-gray-700"
                                        >
                                            Konfigurasi Jurusan
                                        </Link>
                                        <button
                                            v-if="year.students_count === 0"
                                            @click="destroy(year.id)"
                                            class="rounded bg-red-600 px-2 py-1 text-xs font-medium text-white hover:bg-red-700"
                                        >
                                            Hapus
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
