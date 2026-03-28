<script setup>
import { Head, Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

defineProps({
    schedules: { type: Array, required: true },
    majors: { type: Array, required: true },
    auth: { type: Object, required: true },
});

const page = usePage();
const activeAcademicYear = computed(() => page.props.academicYear?.active ?? null);

const formatDate = (dateString) => {
    const date = new Date(dateString);
    return date.toLocaleDateString('id-ID', { year: 'numeric', month: 'long', day: 'numeric' });
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
    { num: 1, color: 'blue', title: 'Isi Formulir', desc: 'Lengkapi biodata, pilih jurusan (3 pilihan), dan upload berkas persyaratan.' },
    { num: 2, color: 'emerald', title: 'Verifikasi Berkas', desc: 'Panitia memverifikasi berkas dalam 1–3 hari kerja.' },
    { num: 3, color: 'violet', title: 'Cek Hasil Seleksi', desc: 'Cek hasil seleksi menggunakan nomor pendaftaran dan NIK.' },
    { num: 4, color: 'rose', title: 'Daftar Ulang', desc: 'Bawa berkas asli dan lengkapi administrasi sesuai jadwal.' },
];

const stepColors = {
    blue: { dot: 'bg-blue-600', card: 'border-blue-500', badge: 'bg-blue-600' },
    emerald: { dot: 'bg-emerald-500', card: 'border-emerald-500', badge: 'bg-emerald-500' },
    violet: { dot: 'bg-violet-600', card: 'border-violet-500', badge: 'bg-violet-600' },
    rose: { dot: 'bg-rose-500', card: 'border-rose-500', badge: 'bg-rose-500' },
};
</script>

<template>
    <Head title="Beranda - SPMB SMKN 8" />

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
                    <a href="#jadwal" class="hidden sm:block text-slate-600 hover:text-blue-600 px-3 py-2 text-sm font-medium transition">Jadwal</a>
                    <a href="#jurusan" class="hidden sm:block text-slate-600 hover:text-blue-600 px-3 py-2 text-sm font-medium transition">Jurusan</a>
                    <Link v-if="!auth.user" :href="route('student.login')" class="text-slate-600 hover:text-blue-600 px-3 py-2 text-sm font-medium transition">Login</Link>
                    <Link v-if="auth.user?.registration_number" :href="route('student.dashboard')" class="bg-blue-600 text-white px-4 py-2 rounded-lg text-sm font-semibold hover:bg-blue-700 transition">Dashboard</Link>
                    <Link v-if="auth.user && !auth.user?.registration_number" :href="route('dashboard')" class="bg-blue-600 text-white px-4 py-2 rounded-lg text-sm font-semibold hover:bg-blue-700 transition">Dashboard</Link>
                    <Link v-if="!auth.user" :href="route('student.register')" class="bg-blue-600 text-white px-4 py-2 rounded-lg text-sm font-semibold hover:bg-blue-700 transition shadow-sm">Daftar Sekarang</Link>
                </div>
            </div>
        </nav>

        <!-- Hero -->
        <section class="relative pt-16 overflow-hidden">
            <div class="absolute inset-0 bg-gradient-to-br from-blue-700 via-blue-600 to-indigo-700"></div>
            <!-- decorative circles -->
            <div class="absolute -top-24 -right-24 w-96 h-96 bg-white/5 rounded-full"></div>
            <div class="absolute bottom-0 -left-16 w-72 h-72 bg-white/5 rounded-full"></div>
            <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-28 text-center text-white">
                <span class="inline-block bg-white/20 text-white text-xs font-semibold px-4 py-1.5 rounded-full mb-6 tracking-wide uppercase">
                    {{ activeAcademicYear ? activeAcademicYear.name : 'Tahun Ajaran' }}
                </span>
                <h1 class="text-4xl md:text-6xl font-extrabold mb-4 leading-tight">
                    Penerimaan Peserta<br class="hidden md:block" /> Didik Baru
                </h1>
                <p class="text-lg md:text-xl text-blue-100 mb-10 max-w-xl mx-auto">
                    SMK Negeri 8 TIK Kota Jayapura — Wujudkan masa depanmu bersama kami.
                </p>
                <div class="flex flex-col sm:flex-row justify-center gap-4">
                    <template v-if="activeAcademicYear">
                        <Link :href="route('student.register')" class="bg-white text-blue-700 px-8 py-3.5 rounded-xl font-bold hover:bg-blue-50 transition shadow-lg text-base">
                            Daftar Sekarang
                        </Link>
                    </template>
                    <template v-else>
                        <span class="inline-block bg-white/20 text-white px-8 py-3.5 rounded-xl font-semibold text-base">
                            Pendaftaran belum dibuka saat ini
                        </span>
                    </template>
                    <a href="#jadwal" class="border-2 border-white/70 text-white px-8 py-3.5 rounded-xl font-semibold hover:bg-white/10 transition text-base">
                        Lihat Jadwal
                    </a>
                </div>
            </div>
            <!-- wave -->
            <div class="relative">
                <svg viewBox="0 0 1440 60" fill="none" xmlns="http://www.w3.org/2000/svg" class="w-full -mb-1">
                    <path d="M0 60L1440 60L1440 20C1200 60 960 0 720 20C480 40 240 0 0 20L0 60Z" fill="#f8fafc"/>
                </svg>
            </div>
        </section>

        <!-- Stats bar -->
        <section class="bg-slate-50 py-10">
            <div class="max-w-4xl mx-auto px-4 grid grid-cols-3 gap-4 text-center">
                <div class="bg-white rounded-2xl shadow-sm p-6 border border-slate-100">
                    <p class="text-3xl font-extrabold text-blue-600">{{ majors.length }}</p>
                    <p class="text-sm text-slate-500 mt-1">Kompetensi Keahlian</p>
                </div>
                <div class="bg-white rounded-2xl shadow-sm p-6 border border-slate-100">
                    <p class="text-3xl font-extrabold text-emerald-600">100%</p>
                    <p class="text-sm text-slate-500 mt-1">Online & Gratis</p>
                </div>
                <div class="bg-white rounded-2xl shadow-sm p-6 border border-slate-100">
                    <p class="text-3xl font-extrabold text-violet-600">{{ activeAcademicYear?.end_year ?? new Date().getFullYear() }}</p>
                    <p class="text-sm text-slate-500 mt-1">Tahun Ajaran Baru</p>
                </div>
            </div>
        </section>

        <!-- Jadwal -->
        <section id="jadwal" class="py-20 bg-slate-50">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center mb-12">
                    <span class="text-blue-600 font-semibold text-sm uppercase tracking-widest">Timeline</span>
                    <h2 class="text-3xl md:text-4xl font-bold text-slate-800 mt-2">Jadwal SPMB</h2>
                    <p class="text-slate-500 mt-3">Pantau setiap tahapan penerimaan peserta didik baru</p>
                </div>
                <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-6">
                    <div v-for="schedule in schedules" :key="schedule.id"
                        class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6 hover:shadow-md hover:-translate-y-1 transition-all duration-200">
                        <span :class="getStatusColor(schedule.status)" class="inline-block px-3 py-1 rounded-full text-xs font-semibold mb-4">
                            {{ getStatusLabel(schedule.status) }}
                        </span>
                        <h3 class="text-base font-bold text-slate-800 mb-2">{{ schedule.title }}</h3>
                        <p class="text-sm text-slate-500 mb-4 leading-relaxed">{{ schedule.description }}</p>
                        <div class="text-xs text-slate-400 border-t border-slate-100 pt-3 mt-auto">
                            <p class="font-semibold text-slate-600">{{ formatDate(schedule.start_date) }}</p>
                            <p v-if="schedule.start_date !== schedule.end_date">s/d {{ formatDate(schedule.end_date) }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Cek Kelulusan -->
        <section class="py-16 bg-gradient-to-r from-blue-600 to-indigo-600">
            <div class="max-w-3xl mx-auto px-4 text-center text-white">
                <h2 class="text-3xl font-bold mb-3">Cek Hasil Seleksi</h2>
                <p class="text-blue-100 mb-8">Masukkan nomor pendaftaran dan NIK untuk melihat hasil seleksi SPMB.</p>
                <Link :href="route('announcement.index')"
                    class="inline-flex items-center gap-2 bg-white text-blue-700 px-8 py-4 rounded-xl font-bold hover:bg-blue-50 transition shadow-lg text-base">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                    Cek Kelulusan
                </Link>
            </div>
        </section>

        <!-- Persyaratan -->
        <section class="py-20 bg-white">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center mb-12">
                    <span class="text-blue-600 font-semibold text-sm uppercase tracking-widest">Persyaratan</span>
                    <h2 class="text-3xl md:text-4xl font-bold text-slate-800 mt-2">Persyaratan Pendaftaran</h2>
                    <p class="text-slate-500 mt-3">Pastikan Anda memenuhi semua persyaratan sebelum mendaftar</p>
                </div>
                <div class="grid md:grid-cols-2 gap-8">
                    <!-- Persyaratan Umum -->
                    <div class="rounded-2xl border border-blue-100 bg-gradient-to-br from-blue-50 to-indigo-50 p-8">
                        <div class="flex items-center gap-4 mb-6">
                            <div class="w-12 h-12 bg-blue-600 rounded-xl flex items-center justify-center shadow">
                                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            </div>
                            <h3 class="text-lg font-bold text-slate-800">Persyaratan Umum</h3>
                        </div>
                        <ul class="space-y-3">
                            <li v-for="item in ['Lulusan SMP/MTs atau paket B tahun 2026','Memiliki NIK dan NISN yang valid','Sehat jasmani dan rohani','Tidak sedang terdaftar di sekolah lain','Berkelakuan baik dan tidak terlibat narkoba']" :key="item" class="flex items-start gap-3">
                                <svg class="w-5 h-5 text-blue-500 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                </svg>
                                <span class="text-slate-700 text-sm">{{ item }}</span>
                            </li>
                        </ul>
                    </div>
                    <!-- Berkas -->
                    <div class="rounded-2xl border border-emerald-100 bg-gradient-to-br from-emerald-50 to-green-50 p-8">
                        <div class="flex items-center gap-4 mb-6">
                            <div class="w-12 h-12 bg-emerald-600 rounded-xl flex items-center justify-center shadow">
                                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                            </div>
                            <h3 class="text-lg font-bold text-slate-800">Berkas yang Diperlukan</h3>
                        </div>
                        <ul class="space-y-3">
                            <li v-for="item in ['Scan Ijazah/SKL (PDF/JPG/PNG, max 2MB)','Scan Kartu Keluarga - KK (PDF/JPG/PNG, max 2MB)','Scan Akta Kelahiran (PDF/JPG/PNG, max 2MB)','Pas Foto 3x4 (JPG/PNG, background merah/biru, max 2MB)']" :key="item" class="flex items-start gap-3">
                                <svg class="w-5 h-5 text-emerald-500 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                </svg>
                                <span class="text-slate-700 text-sm">{{ item }}</span>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </section>

        <!-- Alur Pendaftaran -->
        <section class="py-20 bg-slate-50">
            <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center mb-14">
                    <span class="text-blue-600 font-semibold text-sm uppercase tracking-widest">Cara Daftar</span>
                    <h2 class="text-3xl md:text-4xl font-bold text-slate-800 mt-2">Alur Pendaftaran</h2>
                    <p class="text-slate-500 mt-3">Ikuti 4 langkah mudah untuk mendaftar</p>
                </div>
                <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-6">
                    <div v-for="step in steps" :key="step.num"
                        class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6 text-center hover:shadow-md hover:-translate-y-1 transition-all duration-200">
                        <div :class="stepColors[step.color].badge" class="w-12 h-12 rounded-full flex items-center justify-center mx-auto mb-4 text-white font-extrabold text-lg shadow">
                            {{ step.num }}
                        </div>
                        <h3 class="font-bold text-slate-800 mb-2">{{ step.title }}</h3>
                        <p class="text-sm text-slate-500 leading-relaxed">{{ step.desc }}</p>
                    </div>
                </div>
                <div class="text-center mt-12">
                    <Link :href="route('student.register')"
                        class="inline-flex items-center gap-2 bg-blue-600 text-white px-8 py-4 rounded-xl font-bold hover:bg-blue-700 transition shadow-lg text-base">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                        </svg>
                        Daftar Sekarang
                    </Link>
                </div>
            </div>
        </section>

        <!-- Jurusan -->
        <section id="jurusan" class="py-20 bg-white">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center mb-12">
                    <span class="text-blue-600 font-semibold text-sm uppercase tracking-widest">Program Studi</span>
                    <h2 class="text-3xl md:text-4xl font-bold text-slate-800 mt-2">Kompetensi Keahlian</h2>
                    <p class="text-slate-500 mt-3">Pilih jurusan yang sesuai dengan minat dan bakatmu</p>
                </div>
                <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-6">
                    <div v-for="major in majors" :key="major.id"
                        class="group bg-white rounded-2xl border border-slate-100 shadow-sm p-8 text-center hover:shadow-lg hover:-translate-y-1 transition-all duration-200 hover:border-blue-200">
                        <div class="w-16 h-16 bg-blue-50 group-hover:bg-blue-100 rounded-2xl flex items-center justify-center mx-auto mb-5 transition">
                            <div v-html="major.icon_svg" class="w-8 h-8 text-blue-600"></div>
                        </div>
                        <span class="inline-block bg-blue-100 text-blue-700 text-xs font-bold px-3 py-1 rounded-full mb-3">{{ major.code }}</span>
                        <h3 class="text-base font-bold text-slate-800 mb-2">{{ major.name }}</h3>
                        <p class="text-sm text-slate-500 leading-relaxed">{{ major.description }}</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Footer -->
        <footer class="bg-slate-900 text-white py-12">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex flex-col md:flex-row justify-between items-center gap-6">
                    <div class="flex items-center gap-3">
                        <img src="/logosmkn8.svg" alt="Logo" class="h-10 w-10 opacity-90" />
                        <div>
                            <p class="font-bold text-white">SMK Negeri 8 TIK Kota Jayapura</p>
                            <p class="text-slate-400 text-sm">JL. Gelanggan II RT 04 RW 01, Waena, Heram, Jayapura</p>
                        </div>
                    </div>
                    <div class="text-center md:text-right text-sm text-slate-400">
                        <p>Email: admin@smkn8tikjayapura.sch.id</p>
                        <p class="mt-1">© 2026 SPMB SMKN 8 TIK Jayapura</p>
                    </div>
                </div>
            </div>
        </footer>

    </div>
</template>
