<script setup>
import { ref } from 'vue';
import { usePage } from '@inertiajs/vue3';
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';
import AcademicYearSelector from '@/Components/AcademicYearSelector.vue';
import { Link } from '@inertiajs/vue3';

const sidebarOpen = ref(false);
const page = usePage();

const isAdmin = () => page.props.auth.user?.role === 'admin';

const menuItems = [
    {
        name: 'Dashboard',
        href: 'dashboard',
        icon: 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6',
    },
    {
        name: 'Pendaftar',
        href: 'admin.students',
        icon: 'M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z',
    },
    {
        name: 'Jurusan',
        href: 'admin.majors',
        icon: 'M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h2M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4',
        adminOnly: true,
    },
    {
        name: 'Jadwal',
        href: 'admin.schedules',
        icon: 'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z',
        adminOnly: true,
    },
    {
        name: 'Kelola Pesan',
        href: 'admin.inbox',
        icon: 'M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z',
    },
    {
        name: 'Pengumuman',
        href: '/admin/pengumuman',
        useUrl: true,
        icon: 'M4 4h16v10H7l-3 4V4z',
        adminOnly: true,
    },
    {
        name: 'Laporan',
        href: 'admin.reports',
        icon: 'M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z',
    },
    {
        name: 'Tahun Ajaran',
        href: 'admin.academic-years',
        icon: 'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z',
        adminOnly: true,
    },
    {
        name: 'Gelombang',
        href: 'admin.enrollment-waves.index',
        icon: 'M3 4h13M3 8h9m-9 4h9m5-4v12m0 0l-4-4m4 4l4-4',
        adminOnly: true,
    },
    {
        name: 'Dokumen Template',
        href: 'admin.documents',
        icon: 'M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z',
        adminOnly: true,
    },
    {
        name: 'Berkas Pendaftaran',
        href: 'admin.registration-documents',
        icon: 'M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z',
        adminOnly: true,
    },
    {
        name: 'Manajemen Panitia',
        href: 'admin.panitia',
        icon: 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z',
        adminOnly: true,
    },
    {
        name: 'Lihat Website',
        href: 'home',
        icon: 'M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14',
        external: true,
    },
];

const isActive = (routeName) => {
    if (routeName.startsWith('/')) {
        return window.location.pathname === routeName;
    }

    return route().current(routeName) || route().current(`${routeName}.*`);
};
</script>

<template>
    <div class="flex h-screen bg-gray-100">
        <!-- Mobile sidebar overlay -->
        <div
            v-if="sidebarOpen"
            class="fixed inset-0 z-40 bg-gray-600 bg-opacity-75 sm:hidden"
            @click="sidebarOpen = false"
        ></div>

        <!-- Sidebar -->
        <aside
            :class="[
                'fixed inset-y-0 left-0 z-50 flex w-64 flex-col transform bg-gray-800 transition-transform duration-300 ease-in-out sm:static sm:translate-x-0',
                sidebarOpen ? 'translate-x-0' : '-translate-x-full',
            ]"
        >
            <!-- Logo -->
            <div class="flex h-16 items-center justify-center border-b border-gray-700 bg-gray-900 px-4">
                <Link :href="route('dashboard')">
                    <ApplicationLogo class="h-10 w-auto fill-current text-white" />
                </Link>
            </div>

            <!-- Academic Year Selector -->
            <div class="border-b border-gray-700 px-3 py-3">
                <AcademicYearSelector />
            </div>

            <!-- Sidebar Menu -->
            <nav class="flex-1 space-y-1 overflow-y-auto px-3 py-4">
                <template v-for="item in menuItems" :key="item.name">
                    <Link
                        v-if="!item.adminOnly || $page.props.auth.user.role === 'admin'"
                        :href="item.useUrl ? item.href : route(item.href)"
                        :target="item.external ? '_blank' : undefined"
                        :class="[
                            'group flex items-center rounded-lg px-4 py-3 text-sm font-medium transition-colors',
                            isActive(item.href)
                                ? 'bg-gray-700 text-white'
                                : 'text-gray-300 hover:bg-gray-700 hover:text-white',
                        ]"
                    >
                        <svg
                            :class="[
                                'mr-3 h-5 w-5 flex-shrink-0',
                                isActive(item.href) ? 'text-white' : 'text-gray-400 group-hover:text-white',
                            ]"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke="currentColor"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                :d="item.icon"
                            />
                        </svg>
                        {{ item.name }}
                    </Link>
                </template>
            </nav>

            <!-- User Profile -->
            <div class="border-t border-gray-700 p-4">
                <Dropdown align="top" width="48">
                    <template #trigger>
                        <button
                            type="button"
                            class="flex w-full items-center rounded-lg px-3 py-2 text-sm font-medium text-gray-300 transition-colors hover:bg-gray-700 hover:text-white"
                        >
                            <svg
                                class="mr-3 h-5 w-5 flex-shrink-0 text-gray-400"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke="currentColor"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"
                                />
                            </svg>
                            <div class="flex flex-1 flex-col text-left">
                                <span class="text-sm font-medium">{{ $page.props.auth.user.name }}</span>
                                <span class="text-xs capitalize text-gray-400">{{ $page.props.auth.user.role }}</span>
                            </div>
                            <svg
                                class="ml-2 h-4 w-4 text-gray-400"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke="currentColor"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M19 9l-7 7-7-7"
                                />
                            </svg>
                        </button>
                    </template>

                    <template #content>
                        <DropdownLink :href="route('profile.edit')">
                            Profile
                        </DropdownLink>
                        <DropdownLink :href="route('logout')" method="post" as="button">
                            Log Out
                        </DropdownLink>
                    </template>
                </Dropdown>
            </div>
        </aside>

        <!-- Main Content -->
        <div class="flex flex-1 flex-col overflow-hidden">
            <!-- Top Header (Mobile) -->
            <header class="flex h-16 items-center justify-between border-b border-gray-200 bg-white px-4 sm:hidden">
                <button
                    @click="sidebarOpen = true"
                    class="rounded-md p-2 text-gray-400 hover:bg-gray-100 hover:text-gray-500 focus:outline-none"
                >
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M4 6h16M4 12h16M4 18h16"
                        />
                    </svg>
                </button>
                <div class="text-sm font-medium text-gray-500">
                    {{ $page.props.auth.user.name }}
                </div>
            </header>

            <!-- Page Content -->
            <main class="flex-1 overflow-y-auto p-6">
                <slot />
            </main>
        </div>
    </div>
</template>
