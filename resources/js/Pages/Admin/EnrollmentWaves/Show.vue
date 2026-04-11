<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, useForm, usePage } from '@inertiajs/vue3';
import { computed, reactive } from 'vue';

const props = defineProps({
    wave:       { type: Object, required: true },
    quotaStats: { type: Array,  required: true },
});

const page   = usePage();
const flash  = computed(() => page.props.flash ?? {});
const errors = computed(() => page.props.errors ?? {});

const statusBadge = (status) => ({
    draft:     'bg-gray-100 text-gray-600',
    open:      'bg-green-100 text-green-700',
    closed:    'bg-yellow-100 text-yellow-700',
    announced: 'bg-blue-100 text-blue-700',
}[status] ?? 'bg-gray-100 text-gray-600');

const statusLabel = (status) => ({
    draft: 'Draft', open: 'Dibuka', closed: 'Ditutup', announced: 'Diumumkan',
}[status] ?? status);

// --- Edit detail gelombang (hanya draft) ---
const editForm = useForm({
    name:        props.wave.name,
    open_date:   props.wave.open_date  ?? '',
    close_date:  props.wave.close_date ?? '',
    description: props.wave.description ?? '',
});

const submitEdit = () => {
    editForm.put(route('admin.enrollment-waves.update', props.wave.id));
};

// --- Edit kuota (hanya draft) ---
const quotaValues = reactive(
    Object.fromEntries(props.quotaStats.map(s => [s.major_id, s.quota]))
);

const quotaForm = useForm({});

const submitQuotas = () => {
    const quotas = Object.entries(quotaValues).map(([major_id, quota]) => ({
        major_id: parseInt(major_id),
        quota:    parseInt(quota) || 0,
    }));
    quotaForm.transform(() => ({ quotas }))
        .put(route('admin.enrollment-waves.update-quotas', props.wave.id));
};

const totalQuota    = computed(() => props.quotaStats.reduce((s, q) => s + q.quota, 0));
const totalAccepted = computed(() => props.quotaStats.reduce((s, q) => s + q.accepted, 0));
const totalRemaining = computed(() => props.quotaStats.reduce((s, q) => s + q.remaining, 0));

// --- Transisi status ---
const openWave = () => {
    if (!confirm('Buka gelombang ini untuk pendaftaran?')) return;
    useForm({}).post(route('admin.enrollment-waves.open', props.wave.id));
};
const closeWave = () => {
    if (!confirm('Tutup gelombang ini?')) return;
    useForm({}).post(route('admin.enrollment-waves.close', props.wave.id));
};
const announceWave = () => {
    if (!confirm('Umumkan hasil seleksi gelombang ini?')) return;
    useForm({}).post(route('admin.enrollment-waves.announce', props.wave.id));
};
</script>

<template>
    <Head :title="`Gelombang: ${wave.name}`" />

    <AdminLayout>
        <div class="mx-auto max-w-4xl space-y-6">

            <!-- Header -->
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div class="flex items-center gap-3">
                    <a :href="route('admin.enrollment-waves.index')"
                        class="text-sm text-indigo-600 hover:underline">← Kembali</a>
                    <h1 class="text-xl font-semibold text-gray-800">{{ wave.name }}</h1>
                    <span :class="['rounded-full px-2.5 py-0.5 text-xs font-medium', statusBadge(wave.status)]">
                        {{ statusLabel(wave.status) }}
                    </span>
                </div>
                <div class="flex gap-2">
                    <button v-if="wave.status === 'draft'" @click="openWave"
                        class="rounded-md bg-green-600 px-3 py-1.5 text-sm font-medium text-white hover:bg-green-700">
                        Buka Gelombang
                    </button>
                    <button v-if="wave.status === 'open'" @click="closeWave"
                        class="rounded-md bg-yellow-500 px-3 py-1.5 text-sm font-medium text-white hover:bg-yellow-600">
                        Tutup Gelombang
                    </button>
                    <button v-if="wave.status === 'closed'" @click="announceWave"
                        class="rounded-md bg-blue-600 px-3 py-1.5 text-sm font-medium text-white hover:bg-blue-700">
                        Umumkan Hasil
                    </button>
                </div>
            </div>

            <!-- Flash / Error -->
            <div v-if="flash.success" class="rounded-md bg-green-50 p-4 text-sm text-green-700">{{ flash.success }}</div>
            <div v-if="errors.error"  class="rounded-md bg-red-50 p-4 text-sm text-red-700">{{ errors.error }}</div>

            <!-- Detail / Edit Form -->
            <div class="rounded-lg bg-white p-6 shadow-sm">
                <h2 class="mb-4 text-base font-semibold text-gray-700">Detail Gelombang</h2>

                <form v-if="wave.status === 'draft'" @submit.prevent="submitEdit"
                    class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <label class="mb-1 block text-xs font-medium text-gray-600">
                            Nama Gelombang <span class="text-red-500">*</span>
                        </label>
                        <input v-model="editForm.name" type="text"
                            class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500" />
                        <p v-if="editForm.errors.name" class="mt-1 text-xs text-red-600">{{ editForm.errors.name }}</p>
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-medium text-gray-600">Tanggal Buka</label>
                        <input v-model="editForm.open_date" type="date"
                            class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm" />
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-medium text-gray-600">Tanggal Tutup</label>
                        <input v-model="editForm.close_date" type="date"
                            class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm" />
                    </div>
                    <div class="sm:col-span-2">
                        <label class="mb-1 block text-xs font-medium text-gray-600">Deskripsi</label>
                        <textarea v-model="editForm.description" rows="2"
                            class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm" />
                    </div>
                    <div class="sm:col-span-2">
                        <button type="submit" :disabled="editForm.processing"
                            class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700 disabled:opacity-50">
                            {{ editForm.processing ? 'Menyimpan...' : 'Simpan Perubahan' }}
                        </button>
                    </div>
                </form>

                <div v-else class="grid grid-cols-2 gap-4 text-sm text-gray-700 sm:grid-cols-4">
                    <div>
                        <span class="block text-xs text-gray-500">Tanggal Buka</span>
                        {{ wave.open_date ?? '-' }}
                    </div>
                    <div>
                        <span class="block text-xs text-gray-500">Tanggal Tutup</span>
                        {{ wave.close_date ?? '-' }}
                    </div>
                    <div class="col-span-2">
                        <span class="block text-xs text-gray-500">Deskripsi</span>
                        {{ wave.description ?? '-' }}
                    </div>
                </div>
            </div>

            <!-- Quota Table -->
            <div class="rounded-lg bg-white p-6 shadow-sm">
                <div class="mb-4 flex items-center justify-between">
                    <h2 class="text-base font-semibold text-gray-700">Kuota per Jurusan</h2>
                    <div v-if="wave.status === 'draft'"
                        class="rounded-md bg-yellow-50 px-3 py-1 text-xs text-yellow-700 border border-yellow-200">
                        ✏️ Kuota dapat diedit karena status masih Draft
                    </div>
                    <div v-else class="rounded-md bg-gray-50 px-3 py-1 text-xs text-gray-500 border border-gray-200">
                        Kuota hanya dapat diedit saat status Draft
                    </div>
                </div>

                <div v-if="quotaStats.length === 0"
                    class="rounded-md bg-yellow-50 p-4 text-sm text-yellow-700">
                    Belum ada jurusan yang dikonfigurasi untuk tahun ajaran ini.
                    Silakan konfigurasi jurusan di halaman Tahun Ajaran terlebih dahulu.
                </div>

                <template v-else>
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b text-left text-xs text-gray-500">
                                <th class="pb-2 font-medium">Jurusan</th>
                                <th class="pb-2 text-right font-medium">Kuota Gelombang</th>
                                <th class="pb-2 text-right font-medium">Diterima</th>
                                <th class="pb-2 text-right font-medium">Sisa</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="stat in quotaStats" :key="stat.major_id"
                                class="border-b last:border-0 hover:bg-gray-50">
                                <td class="py-3 font-medium text-gray-800">{{ stat.major_name }}</td>
                                <td class="py-3 text-right">
                                    <input
                                        v-if="wave.status === 'draft'"
                                        v-model.number="quotaValues[stat.major_id]"
                                        type="number" min="0" max="999"
                                        class="w-24 rounded-md border border-indigo-300 px-2 py-1 text-right text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500" />
                                    <span v-else class="font-medium">{{ stat.quota }}</span>
                                </td>
                                <td class="py-3 text-right text-gray-600">{{ stat.accepted }}</td>
                                <td class="py-3 text-right font-medium"
                                    :class="stat.remaining === 0 ? 'text-red-600' : 'text-green-700'">
                                    {{ stat.remaining }}
                                </td>
                            </tr>
                        </tbody>
                        <tfoot>
                            <tr class="border-t-2 border-gray-300 bg-gray-50 text-xs font-semibold text-gray-600">
                                <td class="py-2">Total</td>
                                <td class="py-2 text-right">{{ totalQuota }}</td>
                                <td class="py-2 text-right">{{ totalAccepted }}</td>
                                <td class="py-2 text-right"
                                    :class="totalRemaining === 0 ? 'text-red-600' : 'text-green-700'">
                                    {{ totalRemaining }}
                                </td>
                            </tr>
                        </tfoot>
                    </table>

                    <div v-if="wave.status === 'draft'" class="mt-4 flex items-center gap-3">
                        <button @click="submitQuotas" :disabled="quotaForm.processing"
                            class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700 disabled:opacity-50">
                            {{ quotaForm.processing ? 'Menyimpan...' : 'Simpan Kuota' }}
                        </button>
                        <span class="text-xs text-gray-500">
                            Kuota hanya dapat diubah sebelum gelombang dibuka.
                        </span>
                    </div>
                </template>
            </div>

        </div>
    </AdminLayout>
</template>
