<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, useForm, usePage, router } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

const props = defineProps({
    waves: { type: Array, required: true },
    academicYear: { type: Object, default: null },
});

const page = usePage();
const flash = computed(() => page.props.flash ?? {});
const errors = computed(() => page.props.errors ?? {});

const showCreateForm = ref(false);

const createForm = useForm({
    name: '',
    open_date: '',
    close_date: '',
    description: '',
});

const submitCreate = () => {
    createForm.post(route('admin.enrollment-waves.store'), {
        onSuccess: () => {
            createForm.reset();
            showCreateForm.value = false;
        },
    });
};

const statusBadge = (status) => {
    const map = {
        draft:      'bg-gray-100 text-gray-600',
        open:       'bg-green-100 text-green-700',
        closed:     'bg-yellow-100 text-yellow-700',
        announced:  'bg-blue-100 text-blue-700',
    };
    return map[status] ?? 'bg-gray-100 text-gray-600';
};

const statusLabel = (status) => {
    const map = { draft: 'Draft', open: 'Buka', closed: 'Tutup', announced: 'Diumumkan' };
    return map[status] ?? status;
};

const openWave = (id) => {
    if (!confirm('Buka gelombang ini untuk pendaftaran?')) return;
    useForm({}).post(route('admin.enrollment-waves.open', id));
};

const closeWave = (id) => {
    if (!confirm('Tutup gelombang ini?')) return;
    useForm({}).post(route('admin.enrollment-waves.close', id));
};

const announceWave = (id) => {
    if (!confirm('Umumkan hasil seleksi gelombang ini?')) return;
    useForm({}).post(route('admin.enrollment-waves.announce', id));
};

const destroyWave = (id) => {
    if (!confirm('Hapus gelombang ini? Tindakan ini tidak dapat dibatalkan.')) return;
    useForm({}).delete(route('admin.enrollment-waves.destroy', id));
};
</script>

<template>
    <Head title="Manajemen Gelombang Pendaftaran" />

    <AdminLayout>
        <div class="mx-auto max-w-6xl space-y-6">
            <div class="flex items-center justify-between">
                <h1 class="text-xl font-semibold text-gray-800">Gelombang Pendaftaran</h1>
                <button
                    @click="showCreateForm = !showCreateForm"
                    class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700"
                >
                    + Buat Gelombang
                </button>
            </div>

            <div v-if="academicYear" class="text-sm text-gray-500">
                Tahun Ajaran: <span class="font-medium text-gray-700">{{ academicYear.name }}</span>
            </div>

            <!-- Flash messages -->
            <div v-if="flash.success" class="rounded-md bg-green-50 p-4 text-sm text-green-700">{{ flash.success }}</div>
            <div v-if="errors.error" class="rounded-md bg-red-50 p-4 text-sm text-red-700">{{ errors.error }}</div>

            <!-- Create Form -->
            <div v-if="showCreateForm" class="rounded-lg bg-white p-6 shadow-sm">
                <h2 class="mb-4 text-base font-medium text-gray-700">Buat Gelombang Baru</h2>
                <form @submit.prevent="submitCreate" class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <label class="mb-1 block text-xs font-medium text-gray-600">Nama Gelombang <span class="text-red-500">*</span></label>
                        <input v-model="createForm.name" type="text" placeholder="Gelombang I"
                            class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500" />
                        <p v-if="createForm.errors.name" class="mt-1 text-xs text-red-600">{{ createForm.errors.name }}</p>
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-medium text-gray-600">Tanggal Buka</label>
                        <input v-model="createForm.open_date" type="date"
                            class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500" />
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-medium text-gray-600">Tanggal Tutup</label>
                        <input v-model="createForm.close_date" type="date"
                            class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500" />
                    </div>
                    <div class="sm:col-span-2">
                        <label class="mb-1 block text-xs font-medium text-gray-600">Deskripsi</label>
                        <textarea v-model="createForm.description" rows="2"
                            class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500" />
                    </div>
                    <div class="sm:col-span-2 flex gap-2">
                        <button type="submit" :disabled="createForm.processing"
                            class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700 disabled:opacity-50">
                            Simpan
                        </button>
                        <button type="button" @click="showCreateForm = false"
                            class="rounded-md border border-gray-300 px-4 py-2 text-sm text-gray-600 hover:bg-gray-50">
                            Batal
                        </button>
                    </div>
                </form>
            </div>

            <!-- Waves List -->
            <div v-if="waves.length === 0" class="rounded-lg bg-white p-8 text-center text-sm text-gray-500 shadow-sm">
                Belum ada gelombang pendaftaran untuk tahun ajaran ini.
            </div>

            <div v-else class="space-y-4">
                <div v-for="wave in waves" :key="wave.id" class="rounded-lg bg-white p-5 shadow-sm">
                    <div class="flex flex-wrap items-start justify-between gap-3">
                        <div>
                            <div class="flex items-center gap-2">
                                <span class="text-base font-semibold text-gray-800">{{ wave.name }}</span>
                                <span :class="['rounded-full px-2 py-0.5 text-xs font-medium', statusBadge(wave.status)]">
                                    {{ statusLabel(wave.status) }}
                                </span>
                            </div>
                            <div class="mt-1 flex flex-wrap gap-4 text-xs text-gray-500">
                                <span v-if="wave.open_date">Buka: {{ wave.open_date }}</span>
                                <span v-if="wave.close_date">Tutup: {{ wave.close_date }}</span>
                                <span>Pendaftar: <strong>{{ wave.students_count ?? 0 }}</strong></span>
                                <span>Diterima: <strong>{{ wave.accepted_count ?? 0 }}</strong></span>
                            </div>
                        </div>

                        <div class="flex flex-wrap gap-2">
                            <a :href="route('admin.enrollment-waves.show', wave.id)"
                                class="rounded-md border border-gray-300 px-3 py-1.5 text-xs text-gray-600 hover:bg-gray-50">
                                Detail
                            </a>
                            <button v-if="wave.status === 'draft'" @click="openWave(wave.id)"
                                class="rounded-md bg-green-600 px-3 py-1.5 text-xs font-medium text-white hover:bg-green-700">
                                Buka
                            </button>
                            <button v-if="wave.status === 'open'" @click="closeWave(wave.id)"
                                class="rounded-md bg-yellow-500 px-3 py-1.5 text-xs font-medium text-white hover:bg-yellow-600">
                                Tutup
                            </button>
                            <button v-if="wave.status === 'closed'" @click="announceWave(wave.id)"
                                class="rounded-md bg-blue-600 px-3 py-1.5 text-xs font-medium text-white hover:bg-blue-700">
                                Umumkan
                            </button>
                            <button v-if="wave.status === 'draft'" @click="destroyWave(wave.id)"
                                class="rounded-md bg-red-50 px-3 py-1.5 text-xs font-medium text-red-600 hover:bg-red-100">
                                Hapus
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
