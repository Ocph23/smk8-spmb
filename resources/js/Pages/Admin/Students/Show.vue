<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    student: {
        type: Object,
        required: true,
    },
});

const showPreview = ref(false);
const previewFile = ref('');
const previewType = ref('');
const previewTitle = ref('');

const openPreview = (file, title) => {
    if (!file) return;

    previewTitle.value = title;
    const filePath = `/storage/${file}`;

    if (file.endsWith('.pdf')) {
        previewType.value = 'pdf';
    } else if (file.match(/\.(jpg|jpeg|png)$/i)) {
        previewType.value = 'image';
    }

    previewFile.value = filePath;
    showPreview.value = true;
};

const closePreview = () => {
    showPreview.value = false;
    previewFile.value = '';
};

const verifyForm = useForm({
    status: props.student.verification_status,
    note: props.student.verification_note || '',
});

const allocateForm = useForm({
    major_id: props.student.accepted_major_id || '',
    is_accepted: props.student.is_accepted ? 1 : 0,
});

const submitVerification = () => {
    verifyForm.post(route('admin.students.verify', props.student.id));
};

const submitAllocation = () => {
    allocateForm.post(route('admin.students.allocate', props.student.id));
};

const deleteStudent = () => {
    if (!confirm('Apakah Anda yakin ingin menghapus data pendaftar ini?')) {
        return;
    }
    router.delete(route('admin.students.destroy', props.student.id));
};

const formatDate = (dateString) => {
    return new Date(dateString).toLocaleDateString('id-ID', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
    });
};
</script>

<template>
    <Head :title="`Detail ${student.registration_number}`" />

    <AdminLayout>
        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8 space-y-6">
                <!-- Student Info -->
                <div class="bg-white shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="text-lg font-semibold text-gray-800">
                                Informasi Pribadi
                            </h3>
                            <Link
                                :href="route('admin.students')"
                                class="text-blue-600 hover:text-blue-800"
                            >
                                ← Kembali
                            </Link>
                        </div>
                        <div class="grid md:grid-cols-2 gap-4">
                            <div>
                                <p class="text-sm text-gray-500">Nomor Pendaftaran</p>
                                <p class="font-semibold text-blue-600">{{ student.registration_number }}</p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500">NIK</p>
                                <p class="font-medium">{{ student.nik }}</p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500">NISN</p>
                                <p class="font-medium">{{ student.nisn || '-' }}</p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500">Nama Lengkap</p>
                                <p class="font-medium">{{ student.full_name }}</p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500">Tempat, Tanggal Lahir</p>
                                <p class="font-medium">{{ student.place_of_birth }}, {{ formatDate(student.date_of_birth) }}</p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500">Jenis Kelamin</p>
                                <p class="font-medium">{{ student.gender === 'male' ? 'Laki-laki' : 'Perempuan' }}</p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500">Agama</p>
                                <p class="font-medium">{{ student.religion || '-' }}</p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500">Email</p>
                                <p class="font-medium">{{ student.email }}</p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500">No. Telepon</p>
                                <p class="font-medium">{{ student.phone }}</p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500">Nama Orang Tua/Wali</p>
                                <p class="font-medium">{{ student.parent_name }}</p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500">No. Telepon Orang Tua</p>
                                <p class="font-medium">{{ student.parent_phone }}</p>
                            </div>
                            <div class="md:col-span-2">
                                <p class="text-sm text-gray-500">Alamat</p>
                                <p class="font-medium">{{ student.address }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Major Preferences -->
                <div class="bg-white shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-gray-800 mb-4">
                            Pilihan Jurusan
                        </h3>
                        <div class="space-y-2">
                            <div
                                v-for="major in student.majors"
                                :key="major.id"
                                class="flex items-center justify-between bg-gray-50 rounded px-4 py-3"
                            >
                                <div class="flex items-center">
                                    <span
                                        :class="{
                                            'bg-blue-100 text-blue-800': major.pivot.preference === 1,
                                            'bg-gray-200 text-gray-800': major.pivot.preference !== 1,
                                        }"
                                        class="inline-flex items-center justify-center w-8 h-8 rounded-full text-sm font-bold mr-3"
                                    >
                                        {{ major.pivot.preference }}
                                    </span>
                                    <div>
                                        <p class="font-medium">{{ major.name }}</p>
                                        <p class="text-sm text-gray-500">{{ major.description }}</p>
                                    </div>
                                </div>
                                <span class="text-sm text-gray-500">Kuota: {{ major.quota }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Verification -->
                <div class="bg-white shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-gray-800 mb-4">
                            Verifikasi
                        </h3>
                        <form @submit.prevent="submitVerification" class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">
                                    Status Verifikasi
                                </label>
                                <select
                                    v-model="verifyForm.status"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                                >
                                    <option value="pending">Pending</option>
                                    <option value="verified">Verified</option>
                                    <option value="rejected">Rejected</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">
                                    Catatan
                                </label>
                                <textarea
                                    v-model="verifyForm.note"
                                    rows="3"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                                    placeholder="Catatan verifikasi (opsional)"
                                ></textarea>
                            </div>
                            <button
                                type="submit"
                                :disabled="verifyForm.processing"
                                class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:bg-gray-400"
                            >
                                Simpan Verifikasi
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Allocation -->
                <div class="bg-white shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-gray-800 mb-4">
                            Alokasi Jurusan
                        </h3>
                        <form @submit.prevent="submitAllocation" class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">
                                    Status Kelulusan
                                </label>
                                <div class="flex items-center space-x-4">
                                    <label class="flex items-center">
                                        <input
                                            v-model="allocateForm.is_accepted"
                                            type="radio"
                                            :value="1"
                                            class="text-green-600 focus:ring-green-500"
                                        />
                                        <span class="ml-2 text-gray-700">Diterima</span>
                                    </label>
                                    <label class="flex items-center">
                                        <input
                                            v-model="allocateForm.is_accepted"
                                            type="radio"
                                            :value="0"
                                            class="text-red-600 focus:ring-red-500"
                                        />
                                        <span class="ml-2 text-gray-700">Tidak Diterima</span>
                                    </label>
                                </div>
                            </div>
                            <div v-if="allocateForm.is_accepted == 1">
                                <label class="block text-sm font-medium text-gray-700 mb-1">
                                    Jurusan yang Diterima
                                </label>
                                <select
                                    v-model="allocateForm.major_id"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                                >
                                    <option value="">Pilih Jurusan</option>
                                    <option
                                        v-for="major in student.majors"
                                        :key="major.id"
                                        :value="major.id"
                                    >
                                        {{ major.name }}
                                    </option>
                                </select>
                            </div>
                            <button
                                type="submit"
                                :disabled="allocateForm.processing"
                                class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 disabled:bg-gray-400"
                            >
                                Simpan Alokasi
                            </button>
                        </form>

                        <div v-if="student.is_accepted && student.accepted_major" class="mt-4 p-4 bg-green-50 border border-green-200 rounded-lg">
                            <p class="text-sm text-green-800">
                                <strong>Diterima di:</strong> {{ student.accepted_major.name }}
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Delete Button -->
                <div class="flex justify-end">
                    <button
                        @click="deleteStudent"
                        class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700"
                    >
                        Hapus Pendaftar
                    </button>
                </div>

                <!-- Documents Preview -->
                <div class="bg-white shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-gray-800 mb-4">
                            Berkas Upload
                        </h3>
                        <div v-if="student.documents && student.documents.length > 0" class="grid md:grid-cols-2 gap-4">
                            <div v-for="doc in student.documents" :key="doc.id" class="border rounded-lg p-4">
                                <div class="flex items-center justify-between mb-2">
                                    <h4 class="font-medium text-gray-700">
                                        {{ doc.registration_document?.label ?? doc.file_name }}
                                    </h4>
                                    <span class="px-2 py-1 text-xs bg-green-100 text-green-800 rounded">✓ Uploaded</span>
                                </div>
                                <button
                                    @click="openPreview(doc.file_path, doc.registration_document?.label ?? doc.file_name)"
                                    class="text-blue-600 hover:text-blue-800 text-sm flex items-center"
                                >
                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                    </svg>
                                    Preview
                                </button>
                            </div>
                        </div>
                        <p v-else class="text-sm text-gray-500">Belum ada berkas yang diupload.</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Preview Modal -->
        <div
            v-if="showPreview"
            class="fixed inset-0 bg-black bg-opacity-75 flex items-center justify-center z-50 p-4"
            @click="closePreview"
        >
            <div
                class="bg-white rounded-lg max-w-4xl w-full max-h-[90vh] overflow-auto"
                @click.stop
            >
                <div class="flex justify-between items-center p-4 border-b">
                    <h3 class="text-lg font-semibold text-gray-800">
                        Preview: {{ previewTitle }}
                    </h3>
                    <button
                        @click="closePreview"
                        class="text-gray-500 hover:text-gray-700"
                    >
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                <div class="p-4">
                    <img
                        v-if="previewType === 'image'"
                        :src="previewFile"
                        :alt="previewTitle"
                        class="max-w-full h-auto mx-auto"
                    />
                    <iframe
                        v-else-if="previewType === 'pdf'"
                        :src="previewFile"
                        class="w-full h-[70vh] border rounded"
                    ></iframe>
                    <div
                        v-else
                        class="text-center py-12 text-gray-500"
                    >
                        <p>Preview tidak tersedia untuk format ini.</p>
                        <a
                            :href="previewFile"
                            download
                            class="text-blue-600 hover:underline mt-2 inline-block"
                        >
                            Download file
                        </a>
                    </div>
                </div>
                <div class="p-4 border-t flex justify-end">
                    <a
                        :href="previewFile"
                        download
                        class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 inline-flex items-center"
                    >
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                        </svg>
                        Download
                    </a>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
