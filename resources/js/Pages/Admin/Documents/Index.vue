<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    documents: { type: Array, required: true },
});

const showForm = ref(false);
const editingDoc = ref(null);

const form = useForm({
    name: '',
    description: '',
    file: null,
    is_active: true,
});

const fileInput = ref(null);

const openCreate = () => {
    editingDoc.value = null;
    form.reset();
    form.is_active = true;
    if (fileInput.value) fileInput.value.value = '';
    showForm.value = true;
};

const openEdit = (doc) => {
    editingDoc.value = doc;
    form.name = doc.name;
    form.description = doc.description || '';
    form.is_active = doc.is_active;
    form.file = null;
    if (fileInput.value) fileInput.value.value = '';
    showForm.value = true;
};

const submit = () => {
    if (editingDoc.value) {
        form.post(route('admin.documents.update', editingDoc.value.id), {
            onSuccess: () => { showForm.value = false; },
        });
    } else {
        form.post(route('admin.documents.store'), {
            onSuccess: () => { showForm.value = false; },
        });
    }
};

const toggleActive = (doc) => {
    router.patch(route('admin.documents.toggle', doc.id));
};

const destroy = (doc) => {
    if (!confirm(`Hapus dokumen "${doc.name}"?`)) return;
    router.delete(route('admin.documents.destroy', doc.id));
};

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
    <Head title="Dokumen Template - Admin" />

    <AdminLayout>
        <div class="py-12">
            <div class="mx-auto max-w-5xl sm:px-6 lg:px-8">
                <!-- Header -->
                <div class="mb-6 flex items-center justify-between">
                    <h2 class="text-2xl font-bold text-gray-800">Dokumen Template Persyaratan</h2>
                    <button @click="openCreate"
                        class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700">
                        + Upload Dokumen
                    </button>
                </div>

                <!-- Flash -->
                <div v-if="$page.props.flash?.success"
                    class="mb-4 rounded-lg bg-green-50 border border-green-200 px-4 py-3 text-green-700 text-sm">
                    {{ $page.props.flash.success }}
                </div>

                <!-- Table -->
                <div class="bg-white shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div v-if="documents.length === 0" class="text-center py-12 text-gray-500">
                            Belum ada dokumen template. Klik "Upload Dokumen" untuk menambahkan.
                        </div>

                        <table v-else class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Dokumen</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Ukuran</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                <tr v-for="doc in documents" :key="doc.id">
                                    <td class="px-4 py-4">
                                        <div class="flex items-center gap-3">
                                            <span class="text-2xl">{{ fileIcon(doc.file_name) }}</span>
                                            <div>
                                                <p class="font-medium text-gray-900">{{ doc.name }}</p>
                                                <p class="text-xs text-gray-500">{{ doc.file_name }}</p>
                                                <p v-if="doc.description" class="text-xs text-gray-400 mt-0.5">{{ doc.description }}</p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-4 py-4 text-sm text-gray-500">{{ doc.file_size }}</td>
                                    <td class="px-4 py-4">
                                        <button @click="toggleActive(doc)"
                                            :class="doc.is_active
                                                ? 'bg-green-100 text-green-800 hover:bg-green-200'
                                                : 'bg-gray-100 text-gray-600 hover:bg-gray-200'"
                                            class="px-2 py-1 text-xs font-semibold rounded-full transition">
                                            {{ doc.is_active ? 'Aktif' : 'Nonaktif' }}
                                        </button>
                                    </td>
                                    <td class="px-4 py-4">
                                        <div class="flex gap-3 text-sm">
                                            <a :href="route('documents.download', doc.id)"
                                                class="text-blue-600 hover:text-blue-800">
                                                Download
                                            </a>
                                            <button @click="openEdit(doc)" class="text-yellow-600 hover:text-yellow-800">
                                                Edit
                                            </button>
                                            <button @click="destroy(doc)" class="text-red-600 hover:text-red-800">
                                                Hapus
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal -->
        <div v-if="showForm" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
            <div class="bg-white rounded-lg p-6 w-full max-w-md">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">
                    {{ editingDoc ? 'Edit Dokumen' : 'Upload Dokumen Baru' }}
                </h3>

                <form @submit.prevent="submit" class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Nama Dokumen</label>
                        <input v-model="form.name" type="text" required
                            placeholder="Contoh: Formulir Pendaftaran"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500" />
                        <p v-if="form.errors.name" class="text-red-500 text-xs mt-1">{{ form.errors.name }}</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Deskripsi (opsional)</label>
                        <input v-model="form.description" type="text"
                            placeholder="Keterangan singkat dokumen"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500" />
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            File {{ editingDoc ? '(kosongkan jika tidak diganti)' : '' }}
                        </label>
                        <input ref="fileInput" type="file"
                            accept=".pdf,.doc,.docx,.xls,.xlsx,.zip"
                            :required="!editingDoc"
                            @change="form.file = $event.target.files[0]"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm" />
                        <p class="text-xs text-gray-400 mt-1">PDF, Word, Excel, ZIP — maks. 10 MB</p>
                        <p v-if="form.errors.file" class="text-red-500 text-xs mt-1">{{ form.errors.file }}</p>
                    </div>

                    <div class="flex items-center gap-2">
                        <input v-model="form.is_active" type="checkbox" id="is_active"
                            class="rounded border-gray-300 text-blue-600" />
                        <label for="is_active" class="text-sm text-gray-700">Aktif (dapat didownload siswa)</label>
                    </div>

                    <div class="flex justify-end gap-2 pt-2">
                        <button type="button" @click="showForm = false"
                            class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300">
                            Batal
                        </button>
                        <button type="submit" :disabled="form.processing"
                            class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:bg-gray-400">
                            {{ form.processing ? 'Menyimpan...' : 'Simpan' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AdminLayout>
</template>
