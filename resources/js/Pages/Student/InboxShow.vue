<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';

const props = defineProps({
    message: {
        type: Object,
        required: true,
    },
});

const formatDate = (dateString) => {
    return new Date(dateString).toLocaleDateString('id-ID', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
};

const form = useForm({});

const deleteMessage = () => {
    if (confirm('Apakah Anda yakin ingin menghapus pesan ini?')) {
        form.delete(route('student.inbox.destroy', props.message.id));
    }
};
</script>

<template>
    <Head :title="message.subject" />

    <div class="min-h-screen bg-gray-50">
        <!-- Navigation -->
        <nav class="bg-white shadow-md">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between h-16">
                    <div class="flex items-center">
                        <Link :href="route('home')" class="text-2xl font-bold text-blue-600">
                            SPMB SMKN 8
                        </Link>
                    </div>
                    <div class="flex items-center space-x-4">
                        <Link :href="route('student.dashboard')"
                            class="text-gray-700 hover:text-blue-600 px-3 py-2 rounded-md text-sm font-medium">
                            Dashboard
                        </Link>
                        <Link :href="route('student.inbox')"
                            class="text-gray-700 hover:text-blue-600 px-3 py-2 rounded-md text-sm font-medium">
                            Inbox
                        </Link>
                        <form @submit.prevent="$inertia.post(route('student.logout'))" class="inline">
                            <button type="submit" class="text-gray-700 hover:text-red-600 px-3 py-2 rounded-md text-sm font-medium">
                                Logout
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </nav>

        <!-- Main Content -->
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
            <div class="bg-white rounded-lg shadow-lg overflow-hidden">
                <!-- Header -->
                <div class="p-6 border-b">
                    <div class="flex items-start justify-between mb-4">
                        <div>
                            <h1 class="text-2xl font-bold text-gray-800 mb-2">
                                {{ message.subject }}
                            </h1>
                            <div class="flex items-center gap-3 text-sm text-gray-500">
                                <span>{{ formatDate(message.created_at) }}</span>
                                <span>•</span>
                                <span :class="message.is_read ? 'text-gray-500' : 'text-blue-600'" class="font-medium">
                                    {{ message.is_read ? 'Sudah dibaca' : 'Belum dibaca' }}
                                </span>
                            </div>
                        </div>
                        <span v-if="message.is_system"
                            class="bg-purple-100 text-purple-700 text-sm px-3 py-1 rounded-full font-medium">
                            Pesan Sistem
                        </span>
                    </div>
                </div>

                <!-- Message Content -->
                <div class="p-6">
                    <div class="prose max-w-none">
                        <p class="text-gray-700 whitespace-pre-line leading-relaxed">
                            {{ message.message }}
                        </p>
                    </div>
                </div>

                <!-- Footer Actions -->
                <div class="p-6 border-t bg-gray-50 flex items-center justify-between">
                    <Link :href="route('student.inbox')"
                        class="text-gray-600 hover:text-gray-800 text-sm font-medium flex items-center">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                        </svg>
                        Kembali ke Inbox
                    </Link>

                    <div class="flex gap-2">
                        <form v-if="!message.is_read" @submit.prevent="$inertia.put(route('student.inbox.mark-as-read', message.id))">
                            <button type="submit"
                                class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 text-sm font-medium">
                                Tandai Dibaca
                            </button>
                        </form>
                        <button @click="deleteMessage"
                            class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 text-sm font-medium">
                            Hapus
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
