<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';

const props = defineProps({
    message: {
        type: Object,
        required: true,
    },
});

const formatDate = (dateString) => {
    const date = new Date(dateString);
    return date.toLocaleDateString('id-ID', {
        weekday: 'long',
        day: 'numeric',
        month: 'long',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
};

const deleteMessage = () => {
    if (confirm('Apakah Anda yakin ingin menghapus pesan ini?')) {
        router.delete(route('admin.inbox.destroy', props.message.id));
    }
};

const goBack = () => {
    window.history.back();
};
</script>

<template>
    <Head :title="message.subject + ' - Admin SPMB'" />

    <AdminLayout>
        <div class="py-12">
            <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
                <!-- Header -->
                <div class="flex justify-between items-center mb-6">
                    <div>
                        <button @click="goBack" class="text-blue-600 hover:text-blue-900 text-sm font-medium mb-2">
                            &larr; Kembali ke Inbox
                        </button>
                        <h2 class="text-2xl font-bold text-gray-800">Detail Pesan</h2>
                    </div>
                    <button @click="deleteMessage"
                        class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 font-medium">
                        Hapus Pesan
                    </button>
                </div>

                <!-- Message Content -->
                <div class="bg-white rounded-lg shadow overflow-hidden">
                    <!-- Message Header -->
                    <div class="p-6 border-b bg-gray-50">
                        <div class="flex justify-between items-start mb-4">
                            <h1 class="text-xl font-bold text-gray-900">{{ message.subject }}</h1>
                            <div class="flex gap-2">
                                <span :class="message.is_read ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800'"
                                    class="px-3 py-1 text-sm rounded-full">
                                    {{ message.is_read ? 'Dibaca' : 'Belum Dibaca' }}
                                </span>
                                <span v-if="!message.is_system"
                                    class="px-3 py-1 text-sm rounded-full bg-blue-100 text-blue-800">
                                    Dari Admin
                                </span>
                                <span v-else
                                    class="px-3 py-1 text-sm rounded-full bg-purple-100 text-purple-800">
                                    Pesan Sistem
                                </span>
                            </div>
                        </div>
                        <div class="grid grid-cols-2 gap-4 text-sm">
                            <div>
                                <span class="text-gray-500">Kepada:</span>
                                <p class="font-medium text-gray-900">{{ message.student.full_name }}</p>
                                <p class="text-gray-500">{{ message.student.registration_number }}</p>
                            </div>
                            <div>
                                <span class="text-gray-500">Tanggal:</span>
                                <p class="font-medium text-gray-900">{{ formatDate(message.created_at) }}</p>
                                <span v-if="message.sender" class="text-gray-500">
                                    Dikirim oleh: {{ message.sender.name }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Message Body -->
                    <div class="p-6">
                        <div class="prose max-w-none">
                            <p class="text-gray-800 whitespace-pre-line">{{ message.message }}</p>
                        </div>
                    </div>

                    <!-- Footer -->
                    <div class="p-4 border-t bg-gray-50 flex justify-end">
                        <Link :href="route('admin.students.show', message.student.id)"
                            class="text-blue-600 hover:text-blue-900 font-medium">
                            Lihat Profil Siswa &rarr;
                        </Link>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
