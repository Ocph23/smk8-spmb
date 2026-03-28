<script setup>
import { Head, Link } from '@inertiajs/vue3';
import Footer from '../Components/Footer.vue';

defineProps({
    documents: { type: Array, required: true },
});

const fileIcon = (fileName) => {
    const ext = fileName?.split('.').pop()?.toLowerCase();
    if (ext === 'pdf') return '📄';
    if (['doc', 'docx'].includes(ext)) return '📝';
    if (['xls', 'xlsx'].includes(ext)) return '📊';
    if (ext === 'zip') return '🗜️';
    return '📎';
};
</script>

<template>
    <Head title="Dokumen Persyaratan - SPMB SMKN 8" />

    <div class="min-h-screen bg-slate-50 font-sans">
        <!-- Navbar -->
        <nav class="fixed top-0 inset-x-0 z-50 bg-white/80 backdrop-blur-md shadow-sm border-b border-slate-200">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex justify-between items-center h-16">
                <div class="flex items-center gap-3">
                    <img src="/logosmkn8.svg" alt="Logo" class="h-9 w-9" />
                    <span class="font-bold text-slate-800 text-sm sm:text-base leading-tight">
                        SPMB <span class="text-blue-600">SMKN 8 TIK</span> Jayapura
                    </span>
                </div>
                <div class="flex items-center gap-2">
                    <Link :href="route('home')" class="text-slate-600 hover:text-blue-600 px-3 py-2 text-sm font-medium transition">Beranda</Link>
                </div>
            </div>
        </nav>

        <!-- Header -->
        <div class="pt-16 bg-gradient-to-br from-blue-700 via-blue-600 to-indigo-700">
            <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-16 text-center text-white">
                <span class="inline-block bg-white/20 text-white text-xs font-semibold px-4 py-1.5 rounded-full mb-4 tracking-wide uppercase">Unduhan</span>
                <h1 class="text-3xl md:text-4xl font-extrabold mb-3">Dokumen Template Persyaratan</h1>
                <p class="text-blue-100 text-base">Download, isi, dan upload dokumen berikut saat melengkapi formulir pendaftaran.</p>
            </div>
            <svg viewBox="0 0 1440 40" fill="none" xmlns="http://www.w3.org/2000/svg" class="w-full -mb-1">
                <path d="M0 40L1440 40L1440 10C1200 40 960 0 720 10C480 20 240 0 0 10L0 40Z" fill="#f8fafc"/>
            </svg>
        </div>

        <!-- Content -->
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
            <!-- Empty state -->
            <div v-if="documents.length === 0"
                class="text-center py-20 bg-white rounded-2xl border border-slate-100 shadow-sm">
                <div class="text-5xl mb-4">📂</div>
                <p class="text-slate-500 text-lg font-medium">Belum ada dokumen tersedia</p>
                <p class="text-slate-400 text-sm mt-2">Silakan cek kembali nanti.</p>
            </div>

            <!-- Document list -->
            <div v-else class="grid sm:grid-cols-2 gap-4">
                <a v-for="doc in documents" :key="doc.id"
                    :href="route('documents.download', doc.id)"
                    class="flex items-center gap-4 bg-white rounded-2xl border border-slate-100 shadow-sm p-5 hover:shadow-md hover:border-blue-300 hover:-translate-y-0.5 transition-all duration-200 group">
                    <div class="w-14 h-14 bg-blue-50 group-hover:bg-blue-100 rounded-xl flex items-center justify-center flex-shrink-0 transition text-2xl">
                        {{ fileIcon(doc.file_name) }}
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="font-semibold text-slate-800 truncate">{{ doc.name }}</p>
                        <p v-if="doc.description" class="text-xs text-slate-500 truncate mt-0.5">{{ doc.description }}</p>
                        <p class="text-xs text-slate-400 mt-1">{{ doc.file_name }} &bull; {{ doc.file_size }}</p>
                    </div>
                    <div class="flex-shrink-0 w-9 h-9 rounded-lg bg-blue-600 group-hover:bg-blue-700 flex items-center justify-center transition">
                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                        </svg>
                    </div>
                </a>
            </div>

            <!-- Back link -->
            <div class="mt-10 text-center">
                <Link :href="route('home')" class="text-blue-600 hover:text-blue-700 text-sm font-medium">
                    ← Kembali ke Beranda
                </Link>
            </div>
        </div>

        <!-- Footer -->
        <Footer/>
    </div>
</template>
