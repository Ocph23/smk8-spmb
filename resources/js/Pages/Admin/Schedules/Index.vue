<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

defineProps({
    schedules: {
        type: Array,
        required: true,
    },
});

const showForm = ref(false);
const editingSchedule = ref(null);

const form = useForm({
    title: '',
    description: '',
    start_date: '',
    end_date: '',
    status: 'inactive',
});

const openCreate = () => {
    editingSchedule.value = null;
    form.reset();
    form.title = '';
    form.description = '';
    form.start_date = '';
    form.end_date = '';
    form.status = 'inactive';
    showForm.value = true;
};

const openEdit = (schedule) => {
    editingSchedule.value = schedule;
    form.title = schedule.title;
    form.description = schedule.description || '';
    form.start_date = schedule.start_date.split('T')[0];
    form.end_date = schedule.end_date.split('T')[0];
    form.status = schedule.status;
    showForm.value = true;
};

const submit = () => {
    if (editingSchedule.value) {
        form.put(route('admin.schedules.update', editingSchedule.value.id), {
            onSuccess: () => {
                showForm.value = false;
            },
        });
    } else {
        form.post(route('admin.schedules.store'), {
            onSuccess: () => {
                showForm.value = false;
            },
        });
    }
};

const destroy = (schedule) => {
    if (!confirm('Apakah Anda yakin ingin menghapus jadwal ini?')) {
        return;
    }
    router.delete(route('admin.schedules.destroy', schedule.id));
};

const formatDate = (dateString) => {
    return new Date(dateString).toLocaleDateString('id-ID', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
    });
};

const getStatusColor = (status) => {
    switch (status) {
        case 'active':
            return 'bg-green-100 text-green-800';
        case 'inactive':
            return 'bg-gray-100 text-gray-800';
        case 'completed':
            return 'bg-blue-100 text-blue-800';
    }
};
</script>

<template>
    <Head title="Kelola Jadwal - Admin" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="text-xl font-semibold leading-tight text-gray-800">
                    Kelola Jadwal PPDB
                </h2>
                <div class="flex gap-2">
                    <Link
                        :href="route('dashboard')"
                        class="text-blue-600 hover:text-blue-800"
                    >
                        ← Kembali ke Dashboard
                    </Link>
                    <button
                        @click="openCreate"
                        class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700"
                    >
                        + Tambah Jadwal
                    </button>
                </div>
            </div>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <!-- Schedule List -->
                <div class="bg-white shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
                            <div
                                v-for="schedule in schedules"
                                :key="schedule.id"
                                class="border rounded-lg p-4 hover:shadow-md transition"
                            >
                                <div class="flex justify-between items-start mb-2">
                                    <span
                                        :class="getStatusColor(schedule.status)"
                                        class="px-2 py-1 text-xs font-semibold rounded-full"
                                    >
                                        {{ schedule.status }}
                                    </span>
                                    <div class="flex gap-2">
                                        <button
                                            @click="openEdit(schedule)"
                                            class="text-blue-600 hover:text-blue-800 text-sm"
                                        >
                                            Edit
                                        </button>
                                        <button
                                            @click="destroy(schedule)"
                                            class="text-red-600 hover:text-red-800 text-sm"
                                        >
                                            Hapus
                                        </button>
                                    </div>
                                </div>
                                <h3 class="text-lg font-semibold text-gray-800 mb-2">
                                    {{ schedule.title }}
                                </h3>
                                <p class="text-sm text-gray-600 mb-3">
                                    {{ schedule.description || 'Tidak ada deskripsi' }}
                                </p>
                                <div class="text-sm text-gray-500">
                                    <p>
                                        <strong>Mulai:</strong> {{ formatDate(schedule.start_date) }}
                                    </p>
                                    <p v-if="schedule.start_date !== schedule.end_date">
                                        <strong>Selesai:</strong> {{ formatDate(schedule.end_date) }}
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div v-if="schedules.length === 0" class="text-center py-12 text-gray-500">
                            Belum ada jadwal PPDB.
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal Form -->
        <div
            v-if="showForm"
            class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50"
        >
            <div class="bg-white rounded-lg p-6 w-full max-w-md">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">
                    {{ editingSchedule ? 'Edit Jadwal' : 'Tambah Jadwal Baru' }}
                </h3>

                <form @submit.prevent="submit" class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Judul Jadwal
                        </label>
                        <input
                            v-model="form.title"
                            type="text"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                            required
                        />
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Deskripsi
                        </label>
                        <textarea
                            v-model="form.description"
                            rows="3"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                        ></textarea>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Tanggal Mulai
                            </label>
                            <input
                                v-model="form.start_date"
                                type="date"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                                required
                            />
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Tanggal Selesai
                            </label>
                            <input
                                v-model="form.end_date"
                                type="date"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                                required
                            />
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Status
                        </label>
                        <select
                            v-model="form.status"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                        >
                            <option value="inactive">Inactive</option>
                            <option value="active">Active</option>
                            <option value="completed">Completed</option>
                        </select>
                    </div>

                    <div class="flex justify-end gap-2 mt-6">
                        <button
                            type="button"
                            @click="showForm = false"
                            class="px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700"
                        >
                            Batal
                        </button>
                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:bg-gray-400"
                        >
                            Simpan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
