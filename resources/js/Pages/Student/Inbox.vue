<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';

const props = defineProps({
    messages: {
        type: Object,
        required: true,
    },
    unreadCount: {
        type: Number,
        required: true,
    },
});

const formatDate = (dateString) => {
    const date = new Date(dateString);
    const now = new Date();
    const diff = now - date;
    const days = Math.floor(diff / (1000 * 60 * 60 * 24));

    if (days === 0) {
        return date.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });
    } else if (days === 1) {
        return 'Kemarin';
    } else if (days < 7) {
        return date.toLocaleDateString('id-ID', { weekday: 'long' });
    } else {
        return date.toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' });
    }
};

const form = useForm({});

const markAllAsRead = () => {
    form.post(route('student.inbox.mark-all-read'));
};
</script>

<template>
    <Head title="Inbox - SPMB SMKN 8" />

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
                            class="relative text-gray-700 hover:text-blue-600 px-3 py-2 rounded-md text-sm font-medium">
                            Inbox
                            <span v-if="unreadCount > 0"
                                class="absolute top-1 right-1 bg-red-500 text-white text-xs rounded-full w-5 h-5 flex items-center justify-center">
                                {{ unreadCount }}
                            </span>
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
            <div class="bg-white rounded-lg shadow-lg">
                <!-- Header -->
                <div class="p-6 border-b flex items-center justify-between">
                    <div>
                        <h1 class="text-2xl font-bold text-gray-800">Inbox</h1>
                        <p class="text-sm text-gray-500 mt-1">
                            {{ unreadCount }} pesan belum dibaca
                        </p>
                    </div>
                    <button v-if="unreadCount > 0" @click="markAllAsRead"
                        class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 text-sm font-medium">
                        Tandai Semua Dibaca
                    </button>
                </div>

                <!-- Messages List -->
                <div v-if="messages.data.length > 0">
                    <div v-for="message in messages.data" :key="message.id"
                        :class="!message.is_read ? 'bg-blue-50 border-l-4 border-blue-500' : ''"
                        class="p-4 border-b hover:bg-gray-50 transition">
                        <Link :href="route('student.inbox.show', message.id)" class="block">
                            <div class="flex items-start justify-between mb-2">
                                <div class="flex-1">
                                    <div class="flex items-center gap-2 mb-1">
                                        <h3 :class="!message.is_read ? 'font-semibold text-gray-900' : 'font-medium text-gray-700'"
                                            class="text-lg">
                                            {{ message.subject }}
                                        </h3>
                                        <span v-if="message.is_system"
                                            class="bg-purple-100 text-purple-700 text-xs px-2 py-1 rounded-full">
                                            Sistem
                                        </span>
                                        <span v-if="!message.is_read"
                                            class="bg-blue-500 text-white text-xs px-2 py-1 rounded-full">
                                            Baru
                                        </span>
                                    </div>
                                    <p class="text-sm text-gray-500">
                                        {{ formatDate(message.created_at) }}
                                    </p>
                                </div>
                                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 5l7 7-7 7" />
                                </svg>
                            </div>
                            <p class="text-gray-600 text-sm line-clamp-2">
                                {{ message.message }}
                            </p>
                        </Link>
                    </div>

                    <!-- Pagination -->
                    <div v-if="messages.last_page > 1" class="p-4 border-t">
                        <div class="flex justify-center gap-2">
                            <template v-for="link in messages.links" :key="link.label">
                                <Link v-if="link.url"
                                    :href="link.url"
                                    v-html="link.label"
                                    :class="link.active ? 'bg-blue-600 text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300'"
                                    class="px-3 py-1 rounded text-sm font-medium" />
                                <span v-else
                                    v-html="link.label"
                                    class="px-3 py-1 rounded text-sm font-medium text-gray-400 cursor-not-allowed" />
                            </template>
                        </div>
                    </div>
                </div>
                <div v-else class="p-12 text-center">
                    <svg class="w-16 h-16 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                    </svg>
                    <p class="text-gray-500 text-lg">Belum ada pesan</p>
                    <p class="text-gray-400 text-sm mt-2">Pesan dari panitia akan muncul di sini</p>
                </div>
            </div>
        </div>
    </div>
</template>
