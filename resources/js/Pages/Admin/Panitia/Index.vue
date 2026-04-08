<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import { ref } from 'vue';

defineProps({
    panitiaList: {
        type: Array,
        required: true,
    },
});

const showForm = ref(false);
const editingPanitia = ref(null);

const form = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
});

const openCreate = () => {
    editingPanitia.value = null;
    form.reset();
    showForm.value = true;
};

const openEdit = (panitia) => {
    editingPanitia.value = panitia;
    form.name = panitia.name;
    form.email = panitia.email;
    form.password = '';
    form.password_confirmation = '';
    showForm.value = true;
};

const submit = () => {
    if (editingPanitia.value) {
        form.put(route('admin.panitia.update', editingPanitia.value.id), {
            onSuccess: () => {
                showForm.value = false;
            },
        });
    } else {
        form.post(route('admin.panitia.store'), {
            onSuccess: () => {
                showForm.value = false;
            },
        });
    }
};

const destroy = (panitia) => {
    if (!confirm(`Apakah Anda yakin ingin menghapus akun panitia ${panitia.name}?`)) {
        return;
    }
    router.delete(route('admin.panitia.destroy', panitia.id));
};

const formatDate = (dateString) => {
    return new Date(dateString).toLocaleDateString('id-ID', {
        day: '2-digit',
        month: 'long',
        year: 'numeric',
    });
};
</script>

<template>
    <Head title="Kelola Panitia - Admin" />

    <AdminLayout>
        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <!-- Page header -->
                <div class="mb-6 flex items-center justify-between">
                    <h2 class="text-2xl font-bold text-gray-800">Kelola Akun Panitia</h2>
                    <button
                        @click="openCreate"
                        class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700"
                    >
                        + Tambah Panitia
                    </button>
                </div>

                <!-- Panitia Table -->
                <div class="bg-white shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <table v-if="panitiaList.length > 0" class="w-full text-sm text-left text-gray-700">
                            <thead class="text-xs text-gray-500 uppercase bg-gray-50">
                                <tr>
                                    <th class="px-4 py-3">Nama</th>
                                    <th class="px-4 py-3">Email</th>
                                    <th class="px-4 py-3">Tanggal Dibuat</th>
                                    <th class="px-4 py-3 text-right">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                <tr v-for="panitia in panitiaList" :key="panitia.id" class="hover:bg-gray-50">
                                    <td class="px-4 py-3 font-medium text-gray-900">{{ panitia.name }}</td>
                                    <td class="px-4 py-3 text-gray-600">{{ panitia.email }}</td>
                                    <td class="px-4 py-3 text-gray-500">{{ formatDate(panitia.created_at) }}</td>
                                    <td class="px-4 py-3 text-right">
                                        <div class="flex justify-end gap-3">
                                            <button
                                                @click="openEdit(panitia)"
                                                class="text-blue-600 hover:text-blue-800 text-sm"
                                            >
                                                Edit
                                            </button>
                                            <button
                                                @click="destroy(panitia)"
                                                class="text-red-600 hover:text-red-800 text-sm"
                                            >
                                                Hapus
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>

                        <div v-else class="text-center py-12 text-gray-500">
                            Belum ada akun panitia. Klik "Tambah Panitia" untuk menambahkan.
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
                    {{ editingPanitia ? 'Edit Akun Panitia' : 'Tambah Akun Panitia Baru' }}
                </h3>

                <form @submit.prevent="submit" class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Nama</label>
                        <input
                            v-model="form.name"
                            type="text"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                            placeholder="Nama lengkap"
                            required
                        />
                        <p v-if="form.errors.name" class="text-red-500 text-sm mt-1">{{ form.errors.name }}</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                        <input
                            v-model="form.email"
                            type="email"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                            placeholder="email@example.com"
                            required
                        />
                        <p v-if="form.errors.email" class="text-red-500 text-sm mt-1">{{ form.errors.email }}</p>
                    </div>

                    <template v-if="!editingPanitia">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Password</label>
                            <input
                                v-model="form.password"
                                type="password"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                                placeholder="Minimal 8 karakter"
                                required
                            />
                            <p v-if="form.errors.password" class="text-red-500 text-sm mt-1">{{ form.errors.password }}</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Konfirmasi Password</label>
                            <input
                                v-model="form.password_confirmation"
                                type="password"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                                placeholder="Ulangi password"
                                required
                            />
                        </div>
                    </template>

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
