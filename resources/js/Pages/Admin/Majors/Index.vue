<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import { ref } from 'vue';

defineProps({
    majors: {
        type: Array,
        required: true,
    },
});

const showForm = ref(false);
const editingMajor = ref(null);

const form = useForm({
    name: '',
    code: '',
    description: '',
    quota: 30,
    icon_svg: '',
});

const openCreate = () => {
    editingMajor.value = null;
    form.reset();
    form.name = '';
    form.code = '';
    form.description = '';
    form.quota = 30;
    form.icon_svg = '';
    showForm.value = true;
};

const openEdit = (major) => {
    editingMajor.value = major;
    form.name = major.name;
    form.code = major.code;
    form.description = major.description || '';
    form.quota = major.quota;
    form.icon_svg = major.icon_svg || '';
    showForm.value = true;
};

const submit = () => {
    if (editingMajor.value) {
        form.put(route('admin.majors.update', editingMajor.value.id), {
            onSuccess: () => {
                showForm.value = false;
            },
            onError: () => {
                // Handle errors
            },
        });
    } else {
        form.post(route('admin.majors.store'), {
            onSuccess: () => {
                showForm.value = false;
            },
            onError: () => {
                // Handle errors
            },
        });
    }
};

const destroy = (major) => {
    if (!confirm(`Apakah Anda yakin ingin menghapus jurusan ${major.name}?`)) {
        return;
    }
    router.delete(route('admin.majors.destroy', major.id));
};
</script>

<template>
    <Head title="Kelola Jurusan - Admin" />

    <AdminLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="text-xl font-semibold leading-tight text-gray-800">
                    Kelola Kompetensi Keahlian
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
                        + Tambah Jurusan
                    </button>
                </div>
            </div>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <!-- Majors Grid -->
                <div class="bg-white shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
                            <div
                                v-for="major in majors"
                                :key="major.id"
                                class="border rounded-lg p-4 hover:shadow-md transition"
                            >
                                <div class="flex justify-between items-start mb-2">
                                    <div class="flex items-center gap-3">
                                        <span v-if="major.icon_svg" class="inline-flex items-center justify-center w-12 h-12 bg-blue-100 text-blue-800 rounded-lg">
                                            <span v-html="major.icon_svg"></span>
                                        </span>
                                        <span v-else class="inline-flex items-center justify-center w-12 h-12 bg-blue-100 text-blue-800 rounded-lg font-bold text-lg">
                                            {{ major.code }}
                                        </span>
                                        <div>
                                            <h3 class="text-lg font-semibold text-gray-800">{{ major.name }}</h3>
                                            <p class="text-sm text-gray-500">{{ major.code }}</p>
                                        </div>
                                    </div>
                                    <div class="flex gap-2">
                                        <button
                                            @click="openEdit(major)"
                                            class="text-blue-600 hover:text-blue-800 text-sm"
                                        >
                                            Edit
                                        </button>
                                        <button
                                            @click="destroy(major)"
                                            class="text-red-600 hover:text-red-800 text-sm"
                                        >
                                            Hapus
                                        </button>
                                    </div>
                                </div>
                                <div class="flex items-center justify-between">
                                    <div class="text-sm text-gray-500">
                                        <p>
                                            <strong>Kuota:</strong> {{ major.quota }} siswa
                                        </p>
                                    </div>
                                    <div class="flex gap-2">
                                        <span class="px-2 py-1 text-xs bg-gray-100 text-gray-800 rounded-full" :title="`${major.students_count || 0} pendaftar memilih jurusan ini`">
                                            📋 {{ major.students_count || 0 }} Pendaftar
                                        </span>
                                        <span 
                                            v-if="major.accepted_count > 0"
                                            class="px-2 py-1 text-xs bg-green-100 text-green-800 rounded-full"
                                            :title="`${major.accepted_count} siswa diterima di jurusan ini`"
                                        >
                                            ✓ {{ major.accepted_count }} Diterima
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div v-if="majors.length === 0" class="text-center py-12 text-gray-500">
                            Belum ada kompetensi keahlian. Klik "Tambah Jurusan" untuk menambahkan.
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
                    {{ editingMajor ? 'Edit Jurusan' : 'Tambah Jurusan Baru' }}
                </h3>

                <form @submit.prevent="submit" class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Nama Jurusan
                        </label>
                        <input
                            v-model="form.name"
                            type="text"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                            placeholder="Contoh: Rekayasa Perangkat Lunak"
                            required
                        />
                        <p v-if="$page.props.errors.name" class="text-red-500 text-sm mt-1">
                            {{ $page.props.errors.name }}
                        </p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Kode Jurusan
                        </label>
                        <input
                            v-model="form.code"
                            type="text"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                            placeholder="Contoh: RPL"
                            maxlength="10"
                            required
                        />
                        <p v-if="$page.props.errors.code" class="text-red-500 text-sm mt-1">
                            {{ $page.props.errors.code }}
                        </p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Deskripsi
                        </label>
                        <textarea
                            v-model="form.description"
                            rows="3"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                            placeholder="Deskripsi kompetensi keahlian..."
                        ></textarea>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Icon SVG (Opsional)
                        </label>
                        <textarea
                            v-model="form.icon_svg"
                            rows="4"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 font-mono text-sm"
                            placeholder='<svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">...</svg>'
                        ></textarea>
                        <p class="text-xs text-gray-500 mt-1">
                            Masukkan kode SVG untuk icon jurusan. Kosongkan untuk menampilkan kode jurusan.
                        </p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Kuota Siswa
                        </label>
                        <input
                            v-model.number="form.quota"
                            type="number"
                            min="1"
                            max="100"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                            required
                        />
                        <p v-if="$page.props.errors.quota" class="text-red-500 text-sm mt-1">
                            {{ $page.props.errors.quota }}
                        </p>
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
    </AdminLayout>
</template>

<style scoped>
.line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
</style>
