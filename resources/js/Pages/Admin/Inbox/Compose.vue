<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

const props = defineProps({
    students: {
        type: Object,
        required: true,
    },
});

const form = useForm({
    recipient_type: 'individual',
    student_id: '',
    subject: '',
    message: '',
    verification_status: '',
    major_id: '',
    is_accepted: '',
});

const studentSearch = ref('');
const filteredStudents = computed(() => {
    if (!studentSearch.value) {
        return props.students;
    }
    return props.students.filter(s => {
        const search = studentSearch.value.toLowerCase();
        return s.full_name.toLowerCase().includes(search) ||
            s.registration_number.toLowerCase().includes(search);
    });
});

const submit = () => {
    form.post(route('admin.inbox.send'), {
        onSuccess: () => {
            form.reset();
            studentSearch.value = '';
        },
    });
};

const goBack = () => {
    window.history.back();
};
</script>

<template>
    <Head title="Kirim Pesan - Admin SPMB" />

    <AdminLayout>
        <div class="py-12">
            <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
                <!-- Header -->
                <div class="flex justify-between items-center mb-6">
                    <div>
                        <h2 class="text-2xl font-bold text-gray-800">Kirim Pesan</h2>
                        <p class="text-sm text-gray-500 mt-1">Kirim pesan ke siswa (perorangan atau massal)</p>
                    </div>
                    <button @click="goBack"
                        class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 font-medium">
                        Kembali
                    </button>
                </div>

                <!-- Form -->
                <div class="bg-white rounded-lg shadow p-6">
                    <form @submit.prevent="submit">
                        <!-- Recipient Type -->
                        <div class="mb-6">
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Jenis Penerima <span class="text-red-500">*</span>
                            </label>
                            <div class="flex gap-4">
                                <label class="flex items-center">
                                    <input v-model="form.recipient_type" type="radio" value="individual"
                                        class="text-blue-600 focus:ring-blue-500" />
                                    <span class="ml-2 text-gray-700">Perorangan</span>
                                </label>
                                <label class="flex items-center">
                                    <input v-model="form.recipient_type" type="radio" value="all"
                                        class="text-blue-600 focus:ring-blue-500" />
                                    <span class="ml-2 text-gray-700">Semua Siswa</span>
                                </label>
                                <label class="flex items-center">
                                    <input v-model="form.recipient_type" type="radio" value="filtered"
                                        class="text-blue-600 focus:ring-blue-500" />
                                    <span class="ml-2 text-gray-700">Berdasarkan Filter</span>
                                </label>
                            </div>
                            <p v-if="form.errors.recipient_type" class="text-red-500 text-sm mt-1">
                                {{ form.errors.recipient_type }}
                            </p>
                        </div>

                        <!-- Individual Student Selection -->
                        <div v-if="form.recipient_type === 'individual'" class="mb-6">
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Pilih Siswa <span class="text-red-500">*</span>
                            </label>
                            <input v-model="studentSearch" type="text"
                                class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500 mb-2"
                                placeholder="Cari nama atau nomor pendaftaran..." />
                            <div class="border rounded-md max-h-60 overflow-y-auto">
                                <div v-for="student in filteredStudents" :key="student.id"
                                    class="p-3 hover:bg-gray-50 cursor-pointer border-b last:border-b-0"
                                    :class="{ 'bg-green-200': form.student_id == student.id }"
                                    @click="form.student_id = student.id">
                                    <div class="font-medium text-gray-900">{{ student.full_name }}</div>
                                    <div class="text-sm text-gray-500">
                                        {{ student.registration_number }} - 
                                        {{ student.accepted_major ? student.accepted_major.name : 'Belum ada jurusan' }}
                                    </div>
                                </div>
                                <div v-if="filteredStudents.length === 0" class="p-4 text-center text-gray-500">
                                    Tidak ada siswa yang ditemukan
                                </div>
                            </div>
                            <p v-if="form.errors.student_id" class="text-red-500 text-sm mt-1">
                                {{ form.errors.student_id }}
                            </p>
                        </div>

                        <!-- Filter Options -->
                        <div v-if="form.recipient_type === 'filtered'" class="mb-6 space-y-4">
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">
                                        Status Verifikasi
                                    </label>
                                    <select v-model="form.verification_status"
                                        class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                        <option value="">Semua Status</option>
                                        <option value="pending">Pending</option>
                                        <option value="verified">Terverifikasi</option>
                                        <option value="rejected">Ditolak</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">
                                        Jurusan
                                    </label>
                                    <select v-model="form.major_id"
                                        class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                        <option value="">Semua Jurusan</option>
                                        <option v-for="student in students" :key="student.id" :value="student.accepted_major_id">
                                            {{ student.accepted_major?.name }}
                                        </option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">
                                        Status Kelulusan
                                    </label>
                                    <select v-model="form.is_accepted"
                                        class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                        <option value="">Semua</option>
                                        <option value="true">Diterima</option>
                                        <option value="false">Belum Diterima</option>
                                    </select>
                                </div>
                            </div>
                            <p class="text-sm text-gray-500">
                                Pesan akan dikirim ke semua siswa yang memenuhi kriteria filter di atas.
                            </p>
                        </div>

                        <!-- Subject -->
                        <div class="mb-6">
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Subjek Pesan <span class="text-red-500">*</span>
                            </label>
                            <input v-model="form.subject" type="text"
                                class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                placeholder="Masukkan subjek pesan..." />
                            <p v-if="form.errors.subject" class="text-red-500 text-sm mt-1">
                                {{ form.errors.subject }}
                            </p>
                        </div>

                        <!-- Message -->
                        <div class="mb-6">
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Isi Pesan <span class="text-red-500">*</span>
                            </label>
                            <textarea v-model="form.message" rows="8"
                                class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                placeholder="Tulis pesan Anda di sini..."></textarea>
                            <p v-if="form.errors.message" class="text-red-500 text-sm mt-1">
                                {{ form.errors.message }}
                            </p>
                        </div>

                        <!-- Submit Button -->
                        <div class="flex gap-4">
                            <button type="submit" :disabled="form.processing"
                                class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 font-medium disabled:opacity-50">
                                <span v-if="form.processing">Mengirim...</span>
                                <span v-else>Kirim Pesan</span>
                            </button>
                            <button type="button" @click="goBack"
                                class="px-6 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 font-medium">
                                Batal
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
