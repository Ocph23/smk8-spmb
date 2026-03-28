<script setup>
import { computed, ref } from 'vue';
import { usePage, useForm } from '@inertiajs/vue3';

const page = usePage();

const allYears = computed(() => page.props.academicYear?.all ?? []);
const currentYear = computed(() => page.props.academicYear?.current);

const open = ref(false);

const form = useForm({ academic_year_id: null });

const statusBadge = (status) => {
    if (status === 'active') return 'bg-green-100 text-green-700';
    if (status === 'closed') return 'bg-blue-100 text-blue-700';
    return 'bg-gray-100 text-gray-600';
};

const statusLabel = (status) => {
    if (status === 'active') return 'Aktif';
    if (status === 'closed') return 'Tutup';
    return 'Draft';
};

const select = (id) => {
    open.value = false;
    form.academic_year_id = id;
    form.post(route('admin.academic-years.set-context'));
};
</script>

<template>
    <div class="relative">
        <button
            type="button"
            @click="open = !open"
            class="flex items-center gap-1.5 rounded-md bg-gray-700 px-3 py-1.5 text-xs font-medium text-gray-200 hover:bg-gray-600 focus:outline-none"
        >
            <svg class="h-3.5 w-3.5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
            </svg>
            <span>{{ currentYear ? currentYear.name : 'Pilih Tahun Ajaran' }}</span>
            <svg class="h-3 w-3 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
            </svg>
        </button>

        <div
            v-if="open"
            class="absolute left-0 z-50 mt-1 w-52 rounded-md bg-white shadow-lg ring-1 ring-black ring-opacity-5"
        >
            <div class="py-1">
                <div v-if="allYears.length === 0" class="px-3 py-2 text-xs text-gray-500">
                    Tidak ada tahun ajaran
                </div>
                <button
                    v-for="year in allYears"
                    :key="year.id"
                    type="button"
                    @click="select(year.id)"
                    class="flex w-full items-center justify-between px-3 py-2 text-left text-xs hover:bg-gray-50"
                    :class="currentYear && currentYear.id === year.id ? 'bg-gray-50 font-semibold text-gray-900' : 'text-gray-700'"
                >
                    <span>{{ year.name }}</span>
                    <span :class="['rounded-full px-1.5 py-0.5 text-xs font-medium', statusBadge(year.status)]">
                        {{ statusLabel(year.status) }}
                    </span>
                </button>
            </div>
        </div>

        <!-- Backdrop -->
        <div v-if="open" class="fixed inset-0 z-40" @click="open = false" />
    </div>
</template>
