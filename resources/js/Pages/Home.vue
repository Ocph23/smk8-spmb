<script setup>
import { Head, Link } from '@inertiajs/vue3';
import { computed } from 'vue';
import Footer from './Components/Footer.vue';

const props = defineProps({
    schedules: { type: Array, required: true },
    majors: { type: Array, required: true },
    auth: { type: Object, required: true },
    academicYear: { type: Object, default: () => ({}) },
    enrollmentWave: { type: Object, default: () => ({}) },
    announcements: { type: Array, default: () => [] },
    registrations: { type: Array, default: () => [] },
    documents: { type: Array, default: () => [] },
});

const activeAcademicYear = computed(() => props.academicYear?.active ?? null);
const announcementsData = computed(() => Array.isArray(props.announcements) ? props.announcements : []);
const schedulesData = computed(() => Array.isArray(props.schedules) ? props.schedules : []);
const openEnrollmentWave = computed(() => props.enrollmentWave?.open ?? null);

const formatDate = (dateString) => {
    const date = new Date(dateString);
    return date.toLocaleDateString('id-ID', { year: 'numeric', month: 'long', day: 'numeric' });
};

const formatDateTime = (value) => {
    if (!value) {
        return '-';
    }

    const date = new Date(value);
    if (Number.isNaN(date.getTime())) {
        return '-';
    }

    return date.toLocaleString('id-ID', {
        day: 'numeric',
        month: 'short',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
};

const getStatusColor = (status) => {
    const map = {
        active: 'bg-emerald-100 text-emerald-700 border border-emerald-300',
        inactive: 'bg-amber-100 text-amber-700 border border-amber-300',
        completed: 'bg-sky-100 text-sky-700 border border-sky-300',
    };
    return map[status] ?? 'bg-gray-100 text-gray-600 border border-gray-300';
};

const getStatusLabel = (status) => {
    const map = { active: 'Berlangsung', inactive: 'Akan Datang', completed: 'Selesai' };
    return map[status] ?? status;
};

const steps = [
    { num: 1, color: 'yellow', title: 'Buat Akun', desc: 'Buat akun menggunakan email yang aktif' },
    { num: 2, color: 'blue', title: 'Isi Formulir', desc: 'Lengkapi biodata, pilih jurusan (3 pilihan), dan unggah berkas persyaratan.' },
    { num: 3, color: 'emerald', title: 'Verifikasi Berkas', desc: 'Panitia memverifikasi berkas dalam 1-3 hari kerja.' },
    { num: 4, color: 'red', title: 'Tes', desc: 'Mengikuti tes sesuai jadwal.' },
    { num: 5, color: 'violet', title: 'Cek Hasil Seleksi', desc: 'Cek hasil seleksi menggunakan nomor pendaftaran dan NIK.' },
    { num: 6, color: 'rose', title: 'Daftar Ulang', desc: 'Bawa berkas asli dan lengkapi administrasi sesuai jadwal.' },
];

const stepColors = {
    yellow: { badge: 'bg-yellow-600' },
    blue: { badge: 'bg-blue-600' },
    emerald: { badge: 'bg-emerald-500' },
    red: { badge: 'bg-red-500' },
    violet: { badge: 'bg-violet-600' },
    rose: { badge: 'bg-rose-500' },
};

const toneClasses = {
    rose: 'bg-rose-50 text-rose-700 ring-1 ring-rose-200',
    blue: 'bg-blue-50 text-blue-700 ring-1 ring-blue-200',
    emerald: 'bg-emerald-50 text-emerald-700 ring-1 ring-emerald-200',
    amber: 'bg-amber-50 text-amber-700 ring-1 ring-amber-200',
};

const announcementItems = computed(() => {
    const items = [];
    const announcements = announcementsData.value;
    const wave = openEnrollmentWave.value;
    const schedules = schedulesData.value;

    if (announcements.length > 0) {
        announcements.forEach((announcement) => {
            const categoryMeta = {
                important: { label: 'Penting', tone: 'rose' },
                info: { label: 'Info', tone: 'blue' },
                schedule: { label: 'Jadwal', tone: 'amber' },
                result: { label: 'Hasil', tone: 'emerald' },
            }[announcement.category] ?? { label: 'Info', tone: 'blue' };

            items.push({
                tone: categoryMeta.tone,
                label: categoryMeta.label,
                title: announcement.title,
                description: announcement.content,
                meta: announcement.publish_at ? formatDateTime(announcement.publish_at) : 'Baru',
                link_text: announcement.link_text || 'Lihat Detail',
                link_url: announcement.link_url || '',
            });
        });

        return items.slice(0, 3);
    }

    if (wave) {
        const waveStatusLabel = {
            draft: 'Dipersiapkan',
            open: 'Sedang Dibuka',
            closed: 'Sudah Ditutup',
            announced: 'Sudah Diumumkan',
        }[wave.status] ?? wave.status;

        items.push({
            tone: wave.status === 'open' ? 'rose' : 'blue',
            label: 'Gelombang',
            title: `${wave.name} ${waveStatusLabel.toLowerCase()}`,
            description: wave.description?.trim() || 'Ikuti perkembangan gelombang pendaftaran melalui halaman utama.',
            meta: wave.open_date ? `Mulai ${formatDate(wave.open_date)}` : 'Update gelombang terbaru',
        });
    }

    const activeSchedule = schedules.find((schedule) => schedule.status === 'active');
    if (activeSchedule) {
        items.push({
            tone: 'emerald',
            label: 'Berlangsung',
            title: activeSchedule.title,
            description: activeSchedule.description?.trim() || 'Tahapan ini sedang berjalan dan perlu dipantau.',
            meta: activeSchedule.start_date
                ? `${formatDate(activeSchedule.start_date)}${activeSchedule.end_date ? ` s/d ${formatDate(activeSchedule.end_date)}` : ''}`
                : 'Jadwal aktif',
        });
    }

    const nextSchedule = schedules.find((schedule) => schedule.status === 'inactive');
    if (nextSchedule) {
        items.push({
            tone: 'amber',
            label: 'Berikutnya',
            title: nextSchedule.title,
            description: nextSchedule.description?.trim() || 'Agenda berikutnya akan segera dimulai.',
            meta: nextSchedule.start_date ? `Dimulai ${formatDate(nextSchedule.start_date)}` : 'Segera dimulai',
        });
    }

    if (items.length < 3 && activeAcademicYear.value) {
        items.push({
            tone: 'blue',
            label: 'Tahun Ajaran',
            title: activeAcademicYear.value.name,
            description: activeAcademicYear.value.description?.trim() || 'Tahun ajaran aktif yang menjadi acuan seluruh proses pendaftaran.',
            meta: `${activeAcademicYear.value.start_year} - ${activeAcademicYear.value.end_year}`,
        });
    }

    if (!items.length) {
        items.push({
            tone: 'blue',
            label: 'Info',
            title: 'Pantau halaman pengumuman',
            description: 'Informasi terbaru akan muncul di sini setelah panitia memperbarui data.',
            meta: 'Belum ada update',
        });
    }

    return items.slice(0, 3);
});

const statusChipClass = computed(() => (
    activeAcademicYear.value
        ? 'bg-emerald-500/15 text-emerald-700 ring-1 ring-emerald-200'
        : 'bg-amber-500/15 text-amber-700 ring-1 ring-amber-200'
));

const sidebarUpdatedAt = new Date().toLocaleDateString('id-ID', {
    day: 'numeric',
    month: 'long',
    year: 'numeric',
});
</script>

<template>

    <Head title="Beranda - SPMB SMKN 8" />

    <div class="min-h-screen bg-slate-50 font-sans">
        <nav class="fixed inset-x-0 top-0 z-50 border-b border-slate-200 bg-white/80 shadow-sm backdrop-blur-md">
            <div class="mx-auto flex h-16 max-w-7xl justify-between px-4 sm:px-6 lg:px-8">
                <div class="flex items-center gap-3">
                    <img src="/logosmkn8.svg" alt="Logo" class="h-9 w-9" />
                    <span class="text-sm font-bold leading-tight text-slate-800 sm:text-base">
                        SPMB <span class="text-blue-600">SMKN 8 TIK</span> Jayapura
                    </span>
                </div>
                <div class="flex items-center gap-2">
                    <a href="#jadwal"
                        class="hidden px-3 py-2 text-sm font-medium text-slate-600 transition hover:text-blue-600 sm:block">Jadwal</a>
                    <a href="#jurusan"
                        class="hidden px-3 py-2 text-sm font-medium text-slate-600 transition hover:text-blue-600 sm:block">Jurusan</a>
                    <Link :href="route('documents.indexx')"
                        class="hidden px-3 py-2 text-sm font-medium text-slate-600 transition hover:text-blue-600 sm:block">
                        Download</Link>
                    <Link :href="route('announcement.index')"
                        class="hidden px-3 py-2 text-sm font-medium text-slate-600 transition hover:text-blue-600 sm:block">
                        Cek Kelulusan</Link>
                    <Link v-if="!auth.user" :href="route('student.login')"
                        class="px-3 py-2 text-sm font-medium text-slate-600 transition hover:text-blue-600">Login</Link>
                    <Link v-if="auth.user?.registration_number" :href="route('student.dashboard')"
                        class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-blue-700">
                        Dashboard</Link>
                    <Link v-if="auth.user && !auth.user?.registration_number" :href="route('dashboard')"
                        class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-blue-700">
                        Dashboard</Link>
                    <Link v-if="!auth.user" :href="route('student.register')"
                        class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-700">
                        Daftar Sekarang</Link>
                </div>
            </div>
        </nav>

        <section class="relative overflow-hidden pt-16">
            <div class="absolute inset-0 bg-gradient-to-br from-blue-700 via-blue-600 to-indigo-700"></div>
            <div class="absolute -right-24 -top-24 h-96 w-96 rounded-full bg-white/5"></div>
            <div class="absolute -left-16 bottom-0 h-72 w-72 rounded-full bg-white/5"></div>
            <div class="relative mx-auto max-w-7xl px-4 py-28 text-center text-white sm:px-6 lg:px-8">
                <span
                    class="mb-6 inline-block rounded-full bg-white/20 px-4 py-1.5 text-xs font-semibold uppercase tracking-wide text-white">
                    {{ activeAcademicYear ? activeAcademicYear.name : 'Tahun Ajaran' }}
                </span>
                <h1 class="mb-4 text-4xl font-extrabold leading-tight md:text-6xl">
                    Penerimaan Peserta<br class="hidden md:block" /> Didik Baru
                </h1>
                <p class="mx-auto mb-10 max-w-xl text-lg text-blue-100 md:text-xl">
                    SMK Negeri 8 TIK Kota Jayapura - Wujudkan masa depanmu bersama kami.
                </p>
                <div class="flex flex-col justify-center gap-4 sm:flex-row">
                    <template v-if="activeAcademicYear">
                        <Link :href="route('student.register')"
                            class="rounded-xl bg-white px-8 py-3.5 text-base font-bold text-blue-700 shadow-lg transition hover:bg-blue-50">
                            Daftar Sekarang
                        </Link>
                    </template>
                    <template v-else>
                        <span
                            class="inline-block rounded-xl bg-white/20 px-8 py-3.5 text-base font-semibold text-white">
                            Pendaftaran belum dibuka saat ini
                        </span>
                    </template>
                    <a href="#jadwal"
                        class="rounded-xl border-2 border-white/70 px-8 py-3.5 text-base font-semibold text-white transition hover:bg-white/10">
                        Lihat Jadwal
                    </a>
                </div>
            </div>
            <div class="relative">
                <svg viewBox="0 0 1440 60" fill="none" xmlns="http://www.w3.org/2000/svg" class="-mb-1 w-full">
                    <path d="M0 60L1440 60L1440 20C1200 60 960 0 720 20C480 40 240 0 0 20L0 60Z" fill="#f8fafc" />
                </svg>
            </div>
        </section>

        <section class="bg-slate-50 py-10">
            <div class="mx-auto grid max-w-4xl grid-cols-3 gap-4 px-4 text-center">
                <div class="rounded-2xl border border-slate-100 bg-white p-6 shadow-sm">
                    <p class="text-3xl font-extrabold text-blue-600">{{ majors.length }}</p>
                    <p class="mt-1 text-sm text-slate-500">Kompetensi Keahlian</p>
                </div>
                <div class="rounded-2xl border border-slate-100 bg-white p-6 shadow-sm">
                    <p class="text-3xl font-extrabold text-emerald-600">100%</p>
                    <p class="mt-1 text-sm text-slate-500">Online & Gratis</p>
                </div>
                <div class="rounded-2xl border border-slate-100 bg-white p-6 shadow-sm">
                    <p class="text-3xl font-extrabold text-violet-600">{{ activeAcademicYear?.start_year ?? new
                        Date().getFullYear() }}</p>
                    <p class="mt-1 text-sm text-slate-500">Tahun Ajaran Baru</p>
                </div>
            </div>
        </section>

        <div class="bg-slate-50 py-20">
            <div
                class="mx-auto grid max-w-7xl gap-10 px-4 sm:px-6 lg:grid-cols-[minmax(0,1fr)_320px] lg:px-8 lg:items-start">
                <aside class="self-start lg:sticky lg:top-24 lg:col-start-2 lg:row-start-1">
                    <div
                        class="overflow-hidden rounded-3xl bg-white shadow-[0_24px_60px_rgba(15,23,42,0.12)] ring-1 ring-slate-200">
                        <div class="bg-gradient-to-r from-blue-600 to-indigo-700 px-5 py-4 text-white">
                            <p class="text-[11px] font-semibold uppercase tracking-[0.3em] text-blue-100">Papan
                                Pengumuman</p>
                            <h2 class="mt-2 text-xl font-bold leading-tight">Info Terbaru</h2>
                            <p class="mt-2 text-sm leading-relaxed text-blue-100/90">
                                Pantau update penting seputar pendaftaran, verifikasi, dan hasil seleksi.
                            </p>
                        </div>

                        <div class="space-y-3 p-4 sm:p-5">
                            <article v-for="item in announcementItems" :key="item.title"
                                class="rounded-2xl border border-slate-200 bg-slate-50/80 p-4 transition hover:-translate-y-0.5 hover:bg-white hover:shadow-md">
                                <div class="flex items-start justify-between gap-3">
                                    <span
                                        class="inline-flex shrink-0 rounded-full px-3 py-1 text-[11px] font-bold uppercase tracking-wide"
                                        :class="toneClasses[item.tone]">
                                        {{ item.label }}
                                    </span>
                                    <span class="text-[11px] font-medium text-slate-400">{{ item.meta }}</span>
                                </div>

                                <h3 class="mt-3 text-sm font-bold leading-snug text-slate-800">
                                    {{ item.title }}
                                </h3>
                                <p class="mt-2 text-sm leading-relaxed text-slate-500">
                                    {{ item.description }}
                                </p>
                                <a v-if="item.link_url" :href="item.link_url" target="_blank" rel="noopener noreferrer"
                                    class="mt-3 inline-flex items-center gap-1 text-sm font-semibold text-blue-600 hover:text-blue-700">
                                    {{ item.link_text }}
                                    <svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                    </svg>
                                </a>
                            </article>

                            <div class="rounded-2xl border border-slate-200 bg-white p-4">
                                <div class="flex items-center gap-3">
                                    <div
                                        class="flex size-11 items-center justify-center rounded-2xl bg-slate-100 text-slate-500">
                                        <svg class="size-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                    </div>
                                    <div>
                                        <p class="text-sm font-bold text-slate-800">Update terakhir</p>
                                        <p class="text-xs text-slate-500">{{ sidebarUpdatedAt }} WIT</p>
                                    </div>
                                </div>

                                <div class="mt-3 flex flex-wrap gap-2">
                                    <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold"
                                        :class="statusChipClass">
                                        {{ activeAcademicYear ? 'Tahun ajaran aktif' : 'Pendaftaran belum dibuka' }}
                                    </span>
                                    <span
                                        class="inline-flex items-center rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-600">
                                        {{ announcementItems.length }} pengumuman terbaru
                                    </span>
                                </div>
                            </div>

                            <div class="grid grid-cols-2 gap-3">
                                <Link :href="route('announcement.index')"
                                    class="inline-flex items-center justify-center rounded-2xl bg-blue-600 px-4 py-3 text-sm font-semibold text-white shadow-lg shadow-blue-600/20 transition hover:bg-blue-700">
                                    Cek Kelulusan
                                </Link>
                                <a href="#jadwal"
                                    class="inline-flex items-center justify-center rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm font-semibold text-slate-700 transition hover:border-blue-300 hover:text-blue-700">
                                    Lihat Jadwal
                                </a>
                            </div>
                        </div>
                    </div>
                </aside>

                <div class="space-y-0 lg:col-start-1 lg:row-start-1">
                    <section id="jadwal" class="py-20 bg-slate-50">
                        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                            <div class="mb-12 text-center">
                                <span
                                    class="text-sm font-semibold uppercase tracking-widest text-blue-600">Timeline</span>
                                <h2 class="mt-2 text-3xl font-bold text-slate-800 md:text-4xl">Jadwal SPMB</h2>
                                <p class="mt-3 text-slate-500">Pantau setiap tahapan penerimaan peserta didik baru</p>
                            </div>
                            <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
                                <div v-for="schedule in schedules" :key="schedule.id"
                                    class="rounded-2xl border border-slate-100 bg-white p-6 shadow-sm transition-all duration-200 hover:-translate-y-1 hover:shadow-md">
                                    <span :class="getStatusColor(schedule.status)"
                                        class="mb-4 inline-block rounded-full px-3 py-1 text-xs font-semibold">
                                        {{ getStatusLabel(schedule.status) }}
                                    </span>
                                    <h3 class="mb-2 text-base font-bold text-slate-800">{{ schedule.title }}</h3>
                                    <p class="mb-4 text-sm leading-relaxed text-slate-500">{{ schedule.description }}
                                    </p>
                                    <div class="mt-auto border-t border-slate-100 pt-3 text-xs text-slate-400">
                                        <p class="font-semibold text-slate-600">{{ formatDate(schedule.start_date) }}
                                        </p>
                                        <p v-if="schedule.start_date !== schedule.end_date">s/d {{
                                            formatDate(schedule.end_date)
                                            }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </section>

                    <section class="bg-gradient-to-r from-blue-600 to-indigo-600 py-16">
                        <div class="mx-auto max-w-3xl px-4 text-center text-white">
                            <h2 class="mb-3 text-3xl font-bold">Cek Hasil Seleksi</h2>
                            <p class="mb-8 text-blue-100">Masukkan nomor pendaftaran dan NIK untuk melihat hasil seleksi
                                SPMB.
                            </p>
                            <Link :href="route('announcement.index')"
                                class="inline-flex items-center gap-2 rounded-xl bg-white px-8 py-4 text-base font-bold text-blue-700 shadow-lg transition hover:bg-blue-50">
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                </svg>
                                Cek Kelulusan
                            </Link>
                        </div>
                    </section>

                    <section class="bg-white py-20">
                        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                            <div class="mb-12 text-center">
                                <span
                                    class="text-sm font-semibold uppercase tracking-widest text-blue-600">Persyaratan</span>
                                <h2 class="mt-2 text-3xl font-bold text-slate-800 md:text-4xl">Persyaratan Pendaftaran
                                </h2>
                                <p class="mt-3 text-slate-500">Pastikan Anda memenuhi semua persyaratan sebelum
                                    mendaftar</p>
                            </div>
                            <div class="grid gap-8 md:grid-cols-2">
                                <div
                                    class="rounded-2xl border border-blue-100 bg-gradient-to-br from-blue-50 to-indigo-50 p-8">
                                    <div class="mb-6 flex items-center gap-4">
                                        <div
                                            class="flex h-12 w-12 items-center justify-center rounded-xl bg-blue-600 shadow">
                                            <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            </svg>
                                        </div>
                                        <h3 class="text-lg font-bold text-slate-800">Persyaratan Umum</h3>
                                    </div>
                                    <ul class="space-y-3">
                                        <li v-for="item in ['Lulusan SMP/MTs atau paket B', 'Memiliki Email valid', 'Memiliki NIK dan NISN yang valid', 'Sehat jasmani dan rohani', 'Tidak sedang terdaftar di sekolah lain', 'Berkelakuan baik dan tidak terlibat narkoba']"
                                            :key="item" class="flex items-start gap-3">
                                            <svg class="mt-0.5 h-5 w-5 flex-shrink-0 text-blue-500" fill="currentColor"
                                                viewBox="0 0 20 20">
                                                <path fill-rule="evenodd"
                                                    d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                                    clip-rule="evenodd" />
                                            </svg>
                                            <span class="text-lg text-slate-700">{{ item }}</span>
                                        </li>
                                    </ul>
                                </div>

                                <div
                                    class="rounded-2xl border border-emerald-100 bg-gradient-to-br from-emerald-50 to-green-50 p-8">
                                    <div class="mb-6 flex items-center gap-4">
                                        <div
                                            class="flex h-12 w-12 items-center justify-center rounded-xl bg-emerald-600 shadow">
                                            <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                            </svg>
                                        </div>
                                        <h3 class="text-lg font-bold text-slate-800">Berkas yang Diperlukan</h3>
                                    </div>
                                    <ul class="space-y-3">
                                        <li v-for="item in registrations" :key="item.id" class="flex items-start gap-3">
                                            <svg class="mt-0.5 h-5 w-5 flex-shrink-0 text-emerald-500"
                                                fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd"
                                                    d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                                    clip-rule="evenodd" />
                                            </svg>
                                            <div class="flex flex-col">
                                                <span class="text-lg text-slate-700">{{ item.label }}</span>
                                                <span class="text-sm text-slate-700">{{ item.description }}</span>
                                            </div>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </section>

                    <section class="bg-slate-50 py-20">
                        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
                            <div class="mb-14 text-center">
                                <span class="text-sm font-semibold uppercase tracking-widest text-blue-600">Cara
                                    Daftar</span>
                                <h2 class="mt-2 text-3xl font-bold text-slate-800 md:text-4xl">Alur Pendaftaran</h2>
                                <p class="mt-3 text-slate-500">Ikuti 4 langkah mudah untuk mendaftar</p>
                            </div>
                            <div class="relative">
                                <div
                                    class="absolute left-8 top-0 bottom-0 hidden w-0.5 bg-gradient-to-b from-blue-500 via-emerald-400 via-violet-500 to-rose-400 sm:block">
                                </div>

                                <div class="space-y-8">
                                    <div v-for="step in steps" :key="step.num" class="relative flex items-start gap-6">
                                        <div :class="stepColors[step.color].badge"
                                            class="relative z-10 flex h-16 w-16 flex-shrink-0 items-center justify-center rounded-full text-xl font-extrabold text-white shadow-lg ring-4 ring-white">
                                            {{ step.num }}
                                        </div>
                                        <div
                                            class="flex-1 rounded-2xl border border-slate-100 bg-white p-6 shadow-sm transition-shadow duration-200 hover:shadow-md">
                                            <h3 class="mb-1 text-lg font-bold text-slate-800">{{ step.title }}</h3>
                                            <p class="text-sm leading-relaxed text-slate-500">{{ step.desc }}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="mt-12 text-center">
                                <Link :href="route('student.register')"
                                    class="inline-flex items-center gap-2 rounded-xl bg-blue-600 px-8 py-4 text-base font-bold text-white shadow-lg transition hover:bg-blue-700">
                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                    </svg>
                                    Daftar Sekarang
                                </Link>
                            </div>
                        </div>
                    </section>

                    <section id="jurusan" class="bg-white py-20">
                        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                            <div class="mb-12 text-center">
                                <span class="text-sm font-semibold uppercase tracking-widest text-blue-600">Program
                                    Studi</span>
                                <h2 class="mt-2 text-3xl font-bold text-slate-800 md:text-4xl">Kompetensi Keahlian</h2>
                                <p class="mt-3 text-slate-500">Pilih jurusan yang sesuai dengan minat dan bakatmu</p>
                            </div>
                            <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
                                <component v-for="major in majors" :key="major.id" :is="major.info_url ? 'a' : 'div'"
                                    :href="major.info_url || undefined" :target="major.info_url ? '_blank' : undefined"
                                    :rel="major.info_url ? 'noopener noreferrer' : undefined"
                                    class="group rounded-2xl border border-slate-100 bg-white p-8 text-center shadow-sm transition-all duration-200 hover:-translate-y-1 hover:border-blue-200 hover:shadow-lg"
                                    :class="{ 'cursor-pointer': major.info_url }">
                                    <div
                                        class="mx-auto mb-5 flex h-20 w-20 items-center justify-center rounded-2xl bg-blue-50 transition group-hover:bg-blue-100">
                                        <div v-html="major.icon_svg" class="text-blue-600"></div>
                                    </div>
                                    <span
                                        class="mb-3 inline-block rounded-full bg-blue-100 px-3 py-1 text-xs font-bold text-blue-700">{{
                                        major.code }}</span>
                                    <h3 class="mb-2 text-base font-bold text-slate-800">{{ major.name }}</h3>
                                    <p class="text-sm leading-relaxed text-slate-500">{{ major.description }}</p>
                                    <div v-if="major.info_url"
                                        class="mt-4 flex items-center justify-center gap-1 text-xs font-semibold text-blue-600">
                                        <span>Selengkapnya</span>
                                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                                        </svg>
                                    </div>
                                </component>
                            </div>
                        </div>
                    </section>
                </div>
            </div>
        </div>

        <Footer />
    </div>
</template>
