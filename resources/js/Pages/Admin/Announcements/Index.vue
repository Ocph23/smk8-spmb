<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    announcements: {
        type: Object,
        required: true,
    },
});

const showForm = ref(false);
const editingAnnouncement = ref(null);

const form = useForm({
    title: '',
    category: 'info',
    content: '',
    link_text: '',
    link_url: '',
    is_pinned: false,
    is_active: true,
    publish_at: '',
    expires_at: '',
});

const categoryMeta = {
    important: { label: 'Penting', class: 'bg-rose-100 text-rose-700' },
    info: { label: 'Info', class: 'bg-blue-100 text-blue-700' },
    schedule: { label: 'Jadwal', class: 'bg-amber-100 text-amber-700' },
    result: { label: 'Hasil', class: 'bg-emerald-100 text-emerald-700' },
};

const openCreate = () => {
    editingAnnouncement.value = null;
    form.reset();
    form.title = '';
    form.category = 'info';
    form.content = '';
    form.link_text = '';
    form.link_url = '';
    form.is_pinned = false;
    form.is_active = true;
    form.publish_at = '';
    form.expires_at = '';
    showForm.value = true;
};

const toDateTimeLocal = (value) => {
    if (!value) {
        return '';
    }

    const date = new Date(value);
    if (Number.isNaN(date.getTime())) {
        return '';
    }

    const offset = date.getTimezoneOffset() * 60000;
    return new Date(date.getTime() - offset).toISOString().slice(0, 16);
};

const openEdit = (announcement) => {
    editingAnnouncement.value = announcement;
    form.title = announcement.title;
    form.category = announcement.category;
    form.content = announcement.content;
    form.link_text = announcement.link_text || '';
    form.link_url = announcement.link_url || '';
    form.is_pinned = !!announcement.is_pinned;
    form.is_active = !!announcement.is_active;
    form.publish_at = toDateTimeLocal(announcement.publish_at);
    form.expires_at = toDateTimeLocal(announcement.expires_at);
    showForm.value = true;
};

const submit = () => {
    if (editingAnnouncement.value) {
        form.put(route('admin.announcements.update', editingAnnouncement.value.id), {
            preserveScroll: true,
            onSuccess: () => {
                showForm.value = false;
            },
        });
        return;
    }

    form.post(route('admin.announcements.store'), {
        preserveScroll: true,
        onSuccess: () => {
            showForm.value = false;
        },
    });
};

const destroy = (announcement) => {
    if (!confirm(`Hapus pengumuman "${announcement.title}"?`)) {
        return;
    }

    router.delete(route('admin.announcements.destroy', announcement.id), {
        preserveScroll: true,
    });
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

const excerpt = (text, length = 120) => {
    if (!text) {
        return '-';
    }

    return text.length > length ? `${text.slice(0, length)}...` : text;
};
</script>

<template>
    <Head title="Kelola Pengumuman - Admin" />

    <AdminLayout>
        <div class="mx-auto max-w-7xl py-12 sm:px-6 lg:px-8">
            <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <h2 class="text-2xl font-bold text-gray-800">Kelola Pengumuman</h2>
                    <p class="mt-1 text-sm text-gray-500">Atur isi papan pengumuman yang tampil di beranda.</p>
                </div>

                <button
                    @click="openCreate"
                    class="inline-flex items-center justify-center gap-2 rounded-lg bg-blue-600 px-4 py-2 font-semibold text-white transition hover:bg-blue-700"
                >
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Tambah Pengumuman
                </button>
            </div>

            <div class="overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-gray-200">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Judul</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Kategori</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Jadwal</th>
                                <th class="px-6 py-3 text-right text-xs font-semibold uppercase tracking-wider text-gray-500">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 bg-white">
                            <tr v-for="announcement in announcements.data" :key="announcement.id" class="align-top hover:bg-gray-50">
                                <td class="px-6 py-4">
                                    <div class="max-w-xl">
                                        <div class="text-sm font-semibold text-gray-900">{{ announcement.title }}</div>
                                        <p class="mt-1 text-sm text-gray-500">{{ excerpt(announcement.content) }}</p>
                                        <div v-if="announcement.link_url" class="mt-2 text-xs text-blue-600">
                                            {{ announcement.link_text || announcement.link_url }}
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="inline-flex rounded-full px-3 py-1 text-xs font-semibold" :class="categoryMeta[announcement.category]?.class || 'bg-gray-100 text-gray-700'">
                                        {{ categoryMeta[announcement.category]?.label || announcement.category }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-sm">
                                    <div class="flex flex-col gap-2">
                                        <span class="inline-flex w-fit rounded-full px-3 py-1 text-xs font-semibold" :class="announcement.is_active ? 'bg-emerald-100 text-emerald-700' : 'bg-gray-100 text-gray-600'">
                                            {{ announcement.is_active ? 'Aktif' : 'Nonaktif' }}
                                        </span>
                                        <span v-if="announcement.is_pinned" class="inline-flex w-fit rounded-full bg-amber-100 px-3 py-1 text-xs font-semibold text-amber-700">
                                            Disematkan
                                        </span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-500">
                                    <p>
                                        <span class="font-medium text-gray-700">Tayang:</span> {{ formatDateTime(announcement.publish_at) }}
                                    </p>
                                    <p>
                                        <span class="font-medium text-gray-700">Berakhir:</span> {{ formatDateTime(announcement.expires_at) }}
                                    </p>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <div class="flex justify-end gap-3">
                                        <button
                                            @click="openEdit(announcement)"
                                            class="text-sm font-medium text-blue-600 hover:text-blue-800"
                                        >
                                            Edit
                                        </button>
                                        <button
                                            @click="destroy(announcement)"
                                            class="text-sm font-medium text-red-600 hover:text-red-800"
                                        >
                                            Hapus
                                        </button>
                                    </div>
                                </td>
                            </tr>

                            <tr v-if="announcements.data.length === 0">
                                <td colspan="5" class="px-6 py-12 text-center">
                                    <p class="text-sm text-gray-500">Belum ada pengumuman. Klik tombol tambah untuk membuat yang pertama.</p>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div v-if="announcements.last_page > 1" class="border-t border-gray-200 bg-gray-50 px-6 py-4">
                    <div class="flex flex-wrap gap-2">
                        <Link
                            v-for="link in announcements.links"
                            :key="link.label"
                            :href="link.url || ''"
                            :class="[
                                'rounded-md px-3 py-2 text-sm font-medium',
                                link.active
                                    ? 'bg-blue-600 text-white'
                                    : 'bg-white text-gray-700 ring-1 ring-gray-200 hover:bg-gray-50',
                                !link.url ? 'pointer-events-none opacity-50' : '',
                            ]"
                            v-html="link.label"
                        />
                    </div>
                </div>
            </div>
        </div>

        <div
            v-if="showForm"
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 px-4 py-8"
        >
            <div class="max-h-[90vh] w-full max-w-3xl overflow-y-auto rounded-2xl bg-white shadow-2xl">
                <div class="border-b border-gray-200 px-6 py-4">
                    <h3 class="text-lg font-bold text-gray-900">
                        {{ editingAnnouncement ? 'Edit Pengumuman' : 'Tambah Pengumuman Baru' }}
                    </h3>
                    <p class="mt-1 text-sm text-gray-500">
                        Atur isi pengumuman yang akan tampil di sidebar beranda.
                    </p>
                </div>

                <form @submit.prevent="submit" class="space-y-5 px-6 py-5">
                    <div class="grid gap-5 md:grid-cols-2">
                        <div class="md:col-span-2">
                            <label class="mb-1 block text-sm font-medium text-gray-700">Judul</label>
                            <input
                                v-model="form.title"
                                type="text"
                                class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                placeholder="Contoh: Pendaftaran Gelombang 1 Dibuka"
                            />
                            <p v-if="form.errors.title" class="mt-1 text-sm text-red-600">{{ form.errors.title }}</p>
                        </div>

                        <div>
                            <label class="mb-1 block text-sm font-medium text-gray-700">Kategori</label>
                            <select
                                v-model="form.category"
                                class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                            >
                                <option value="important">Penting</option>
                                <option value="info">Info</option>
                                <option value="schedule">Jadwal</option>
                                <option value="result">Hasil</option>
                            </select>
                            <p v-if="form.errors.category" class="mt-1 text-sm text-red-600">{{ form.errors.category }}</p>
                        </div>

                        <div class="md:col-span-2">
                            <label class="mb-1 block text-sm font-medium text-gray-700">Isi Pengumuman</label>
                            <textarea
                                v-model="form.content"
                                rows="5"
                                class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                placeholder="Tulis isi pengumuman yang akan tampil di beranda."
                            ></textarea>
                            <p v-if="form.errors.content" class="mt-1 text-sm text-red-600">{{ form.errors.content }}</p>
                        </div>

                        <div>
                            <label class="mb-1 block text-sm font-medium text-gray-700">Teks Tombol (Opsional)</label>
                            <input
                                v-model="form.link_text"
                                type="text"
                                class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                placeholder="Contoh: Baca Detail"
                            />
                            <p v-if="form.errors.link_text" class="mt-1 text-sm text-red-600">{{ form.errors.link_text }}</p>
                        </div>

                        <div>
                            <label class="mb-1 block text-sm font-medium text-gray-700">Link Tombol (Opsional)</label>
                            <input
                                v-model="form.link_url"
                                type="url"
                                class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                placeholder="https://"
                            />
                            <p v-if="form.errors.link_url" class="mt-1 text-sm text-red-600">{{ form.errors.link_url }}</p>
                        </div>

                        <div>
                            <label class="mb-1 block text-sm font-medium text-gray-700">Tayang Mulai</label>
                            <input
                                v-model="form.publish_at"
                                type="datetime-local"
                                class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                            />
                            <p v-if="form.errors.publish_at" class="mt-1 text-sm text-red-600">{{ form.errors.publish_at }}</p>
                        </div>

                        <div>
                            <label class="mb-1 block text-sm font-medium text-gray-700">Berakhir</label>
                            <input
                                v-model="form.expires_at"
                                type="datetime-local"
                                class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                            />
                            <p v-if="form.errors.expires_at" class="mt-1 text-sm text-red-600">{{ form.errors.expires_at }}</p>
                        </div>

                        <div class="flex items-center gap-6 md:col-span-2">
                            <label class="inline-flex items-center gap-2 text-sm font-medium text-gray-700">
                                <input v-model="form.is_active" type="checkbox" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500" />
                                Aktif
                            </label>
                            <label class="inline-flex items-center gap-2 text-sm font-medium text-gray-700">
                                <input v-model="form.is_pinned" type="checkbox" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500" />
                                Sematkan di atas
                            </label>
                        </div>
                    </div>

                    <div class="flex items-center justify-end gap-3 border-t border-gray-200 pt-4">
                        <button
                            type="button"
                            @click="showForm = false"
                            class="rounded-lg bg-gray-100 px-4 py-2 font-semibold text-gray-700 transition hover:bg-gray-200"
                        >
                            Batal
                        </button>
                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="rounded-lg bg-blue-600 px-4 py-2 font-semibold text-white transition hover:bg-blue-700 disabled:cursor-not-allowed disabled:bg-gray-400"
                        >
                            Simpan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AdminLayout>
</template>
