<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    messages: {
        type: Object,
        required: true,
    },
    students: {
        type: Object,
        required: true,
    },
    filters: {
        type: Object,
        required: true,
    },
});

const searchForm = ref({
    search: props.filters.search || '',
    student_id: props.filters.student_id || '',
});

const search = () => {
    router.get(route('admin.inbox'), searchForm.value, {
        preserveState: true,
    });
};

const reset = () => {
    searchForm.value = {
        search: '',
        student_id: '',
    };
    router.get(route('admin.inbox'));
};

const deleteMessage = (messageId) => {
    if (confirm('Apakah Anda yakin ingin menghapus pesan ini?')) {
        router.delete(route('admin.inbox.destroy', messageId));
    }
};

const formatDate = (dateString) => {
    const date = new Date(dateString);
    return date.toLocaleDateString('id-ID', {
        day: 'numeric',
        month: 'short',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
};
</script>

<template>
    <Head title="Kelola Pesan - Admin SPMB" />

    <AdminLayout>
        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- Header -->
                <div class="flex justify-between items-center mb-6">
                    <div>
                        <h2 class="text-2xl font-bold text-gray-800">Kelola Pesan Inbox</h2>
                        <p class="text-sm text-gray-500 mt-1">Kirim dan kelola pesan ke siswa</p>
                    </div>
                    <Link :href="route('admin.inbox.compose')"
                        class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 font-medium flex items-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 4v16m8-8H4" />
                        </svg>
                        Kirim Pesan
                    </Link>
                </div>

                <!-- Filters -->
                <div class="bg-white rounded-lg shadow mb-6 p-4">
                    <form @submit.prevent="search" class="flex gap-4">
                        <div class="flex-1">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Cari Pesan</label>
                            <input v-model="searchForm.search" type="text"
                                class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                placeholder="Cari berdasarkan subjek atau isi pesan..." />
                        </div>
                        <div class="w-64">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Filter Siswa</label>
                            <select v-model="searchForm.student_id"
                                class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                <option value="">Semua Siswa</option>
                                <option v-for="student in students" :key="student.id" :value="student.id">
                                    {{ student.full_name }} ({{ student.registration_number }})
                                </option>
                            </select>
                        </div>
                        <div class="flex items-end gap-2">
                            <button type="submit"
                                class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 font-medium">
                                Cari
                            </button>
                            <button type="button" @click="reset"
                                class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 font-medium">
                                Reset
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Messages Table -->
                <div class="bg-white rounded-lg shadow overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Subjek
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Penerima
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Status
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Tanggal
                                    </th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Aksi
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <tr v-for="message in messages.data" :key="message.id"
                                    class="hover:bg-gray-50 transition">
                                    <td class="px-6 py-4">
                                        <div>
                                            <div class="font-medium text-gray-900">{{ message.subject }}</div>
                                            <div class="text-sm text-gray-500 line-clamp-1">{{ message.message }}</div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="text-sm text-gray-900">{{ message.student.full_name }}</div>
                                        <div class="text-sm text-gray-500">{{ message.student.registration_number }}</div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <span :class="message.is_read ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800'"
                                            class="px-2 py-1 text-xs rounded-full">
                                            {{ message.is_read ? 'Dibaca' : 'Belum Dibaca' }}
                                        </span>
                                        <span v-if="!message.is_system"
                                            class="ml-2 px-2 py-1 text-xs rounded-full bg-blue-100 text-blue-800">
                                            Dari Admin
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-500">
                                        {{ formatDate(message.created_at) }}
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        <div class="flex justify-end gap-2">
                                            <Link :href="route('admin.inbox.show', message.id)"
                                                class="text-blue-600 hover:text-blue-900 text-sm font-medium">
                                                Lihat
                                            </Link>
                                            <button @click="deleteMessage(message.id)"
                                                class="text-red-600 hover:text-red-900 text-sm font-medium">
                                                Hapus
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <tr v-if="messages.data.length === 0">
                                    <td colspan="5" class="px-6 py-12 text-center">
                                        <svg class="w-16 h-16 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                        </svg>
                                        <p class="text-gray-500 text-lg">Belum ada pesan</p>
                                        <p class="text-gray-400 text-sm mt-2">Kirim pesan pertama Anda ke siswa</p>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div v-if="messages.last_page > 1" class="px-6 py-4 border-t">
                        <div class="flex justify-center gap-2">
                            <Link v-for="page in messages.last_page" :key="page"
                                :href="messages.path + '?page=' + page"
                                :class="page === messages.current_page ? 'bg-blue-600 text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300'"
                                class="px-3 py-1 rounded text-sm font-medium">
                                {{ page }}
                            </Link>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
