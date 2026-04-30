<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    academicYear: { type: Object, required: true },
    majors: { type: Array, required: true },
});

const page = usePage();
const flash = computed(() => page.props.flash ?? {});

const form = useForm({
    majors: props.majors.map((m) => ({
        id: m.id,
        quota: m.pivot_quota ?? m.quota,
        is_active: m.pivot_is_active ?? true,
    })),
});

const submit = () => {
    form.put(route('admin.academic-years.update-major-config', props.academicYear.id));
};
</script>

<template>

    <Head :title="`Konfigurasi Jurusan — ${academicYear.name}`" />
    <AdminLayout>
        <div class="mx-auto max-w-4xl space-y-6">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-xl font-semibold text-gray-800">Konfigurasi Jurusan</h1>
                    <p class="mt-0.5 text-sm text-gray-500">Tahun Ajaran: <span class="font-medium text-gray-700">{{
                        academicYear.name }}</span></p>
                </div>
                <Link :href="route('admin.academic-years')"
                    class="rounded-md border border-gray-300 bg-white px-3 py-1.5 text-sm text-gray-600 hover:bg-gray-50">
                    ← Kembali
                </Link>
            </div>

            <!-- Flash -->
            <div v-if="flash.success" class="rounded-md bg-green-50 p-4 text-sm text-green-700">
                {{ flash.success }}
            </div>

            <!-- Table -->
            <div class="rounded-lg bg-white shadow-sm">
                <form @submit.prevent="submit">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-medium uppercase text-gray-500">Jurusan
                                    </th>
                                    <th class="px-4 py-3 text-left text-xs font-medium uppercase text-gray-500">Kode
                                    </th>
                                    <th class="px-4 py-3 text-left text-xs font-medium uppercase text-gray-500">Kuota
                                        Default</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium uppercase text-gray-500">Kuota
                                        Tahun Ini</th>
                                    <th class="px-4 py-3 text-center text-xs font-medium uppercase text-gray-500">Aktif
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 bg-white">
                                <tr v-for="(row, index) in form.majors" :key="row.id" class="hover:bg-gray-50">
                                    <td class="px-4 py-3 text-sm font-medium text-gray-900">
                                        {{ majors[index].name }}
                                    </td>
                                    <td class="px-4 py-3 text-sm text-gray-600">
                                        {{ majors[index].code ?? '-' }}
                                    </td>
                                    <td class="px-4 py-3 text-sm text-gray-600">
                                        {{ majors[index].quota }}
                                    </td>
                                    <td class="px-4 py-3">
                                        <input v-model.number="row.quota" type="number" min="1"
                                            class="w-24 rounded-md border border-gray-300 px-2 py-1 text-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500" />
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        <input v-model="row.is_active" type="checkbox"
                                            class="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500" />
                                    </td>
                                </tr>
                                <tr v-if="majors.length === 0">
                                    <td colspan="5" class="px-4 py-8 text-center text-sm text-gray-500">
                                        Tidak ada jurusan.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="border-t border-gray-200 px-4 py-4">
                        <button type="submit" :disabled="form.processing"
                            class="rounded-md bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700 disabled:opacity-50">
                            Simpan Semua
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AdminLayout>
</template>
