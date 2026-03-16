<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    student: {
        type: Object,
        required: true,
    },
    majors: {
        type: Array,
        required: true,
    },
});

const currentStep = ref(1);

// Get major preferences
const getMajorPreference = (preference) => {
    const major = props.student.majors.find(m => m.pivot.preference === preference);
    return major ? major.id : '';
};

const form = useForm({
    full_name: props.student.full_name,
    nik: props.student.nik,
    nisn: props.student.nisn || '',
    place_of_birth: props.student.place_of_birth,
    date_of_birth: props.student.date_of_birth,
    gender: props.student.gender,
    religion: props.student.religion || '',
    // Address fields
    street: props.student.street || '',
    rt: props.student.rt || '',
    rw: props.student.rw || '',
    dusun: props.student.dusun || '',
    district: props.student.district || '',
    postal_code: props.student.postal_code || '',
    phone: props.student.phone,
    email: props.student.email,
    // Parent fields
    parent_name: props.student.parent_name,
    mother_name: props.student.mother_name || '',
    parent_phone: props.student.parent_phone,
    // Major preferences
    major_1: getMajorPreference(1),
    major_2: getMajorPreference(2),
    major_3: getMajorPreference(3),
    // Files (will be populated on change)
    file_ijazah: null,
    file_kk: null,
    file_akta: null,
    file_pas_photo: null,
});

const submit = () => {
    form.put(route('student.update', props.student.registration_number), {
        forceFormData: true,
        onSuccess: () => {
            // Stay on edit page or redirect to preview
        },
    });
};

const nextStep = () => {
    if (currentStep.value === 1 && validateStep1()) {
        currentStep.value = 2;
    } else if (currentStep.value === 2 && validateStep2()) {
        currentStep.value = 3;
    }
};

const prevStep = () => {
    if (currentStep.value > 1) {
        currentStep.value--;
    }
};

const validateStep1 = () => {
    const required = ['full_name', 'nik', 'place_of_birth', 'date_of_birth', 'gender', 'street', 'district', 'phone', 'email', 'parent_name', 'parent_phone'];
    for (const field of required) {
        if (!form[field]) {
            return false;
        }
    }
    // NIK must be 16 digits
    if (form.nik && form.nik.length !== 16) {
        return false;
    }
    // Phone validation
    if (form.phone && !/^08[0-9]{8,}$/.test(form.phone)) {
        return false;
    }
    if (form.parent_phone && !/^08[0-9]{8,}$/.test(form.parent_phone)) {
        return false;
    }
    return true;
};

const validateStep2 = () => {
    const required = ['major_1', 'major_2'];
    for (const field of required) {
        if (!form[field]) {
            return false;
        }
    }
    // Ensure major_1 and major_2 are different
    if (form.major_1 && form.major_2 && form.major_1 === form.major_2) {
        return false;
    }
    return true;
};

const handleFileChange = (field, event) => {
    const file = event.target.files[0];
    if (file) {
        // Validate file size (max 2MB for documents, 1MB for photo)
        const maxSize = field === 'file_pas_photo' ? 1024 * 1024 : 2048 * 1024;
        if (file.size > maxSize) {
            alert('Ukuran file terlalu besar. Maksimal ' + (field === 'file_pas_photo' ? '1MB' : '2MB'));
            event.target.value = '';
            return;
        }
        form[field] = file;
    }
};

const getFileName = (field, existingPath) => {
    // If new file selected, show its name
    if (form[field] && typeof form[field] === 'object') {
        return form[field].name;
    }
    // Otherwise show existing file indicator
    if (existingPath) {
        return '(File sudah ada)';
    }
    return null;
};

const hasExistingFile = (field) => {
    return props.student[field] && !form[field];
};
</script>

<template>

    <Head :title="`Edit Pendaftaran - ${student.registration_number}`" />

    <div class="min-h-screen bg-gray-50">
        <!-- Navigation -->
        <nav class="bg-white shadow-md">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between h-16">
                    <div class="flex items-center">
                        <Link :href="route('home')" class="text-2xl font-bold text-blue-600">
                            SPMB SMKN 8 TIK KOTA JAYAPURA
                        </Link>
                    </div>
                    <div class="flex items-center space-x-4">
                        <Link :href="route('home')"
                            class="text-gray-700 hover:text-blue-600 px-3 py-2 rounded-md text-sm font-medium">
                            Beranda
                        </Link>
                    </div>
                </div>
            </div>
        </nav>

        <!-- Main Content -->
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
            <div class="bg-white rounded-lg shadow-lg p-8">
                <div class="text-center mb-8">
                    <h1 class="text-3xl font-bold text-gray-800 mb-2">
                        Edit Data Pendaftaran
                    </h1>
                    <p class="text-gray-600">
                        No. Pendaftaran: <span class="font-bold text-blue-600">{{ student.registration_number }}</span>
                    </p>
                </div>

                <!-- Warning if verified -->
                <div v-if="student.verification_status !== 'pending'"
                    class="mb-6 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg">
                    <p class="font-semibold mb-1">⚠️ Data Sudah Diverifikasi</p>
                    <p class="text-sm">
                        Data pendaftaran Anda sudah diverifikasi. Perubahan data memerlukan persetujuan dari panitia.
                        Hubungi admin jika perlu mengubah data.
                    </p>
                </div>

                <!-- Error Messages -->
                <div v-if="$page.props.errors.error"
                    class="mb-6 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg">
                    {{ $page.props.errors.error }}
                </div>

                <form @submit.prevent="submit">
                    <!-- Progress Steps -->
                    <div class="mb-8">
                        <div class="flex items-center justify-center">
                            <div :class="currentStep >= 1 ? 'bg-blue-600' : 'bg-gray-300'"
                                class="w-10 h-10 rounded-full flex items-center justify-center text-white font-bold">
                                1
                            </div>
                            <div :class="currentStep >= 1 ? 'bg-blue-600' : 'bg-gray-300'" class="flex-1 h-1 mx-2" />
                            <div :class="currentStep >= 2 ? 'bg-blue-600' : 'bg-gray-300'"
                                class="w-10 h-10 rounded-full flex items-center justify-center text-white font-bold">
                                2
                            </div>
                            <div :class="currentStep >= 2 ? 'bg-blue-600' : 'bg-gray-300'" class="flex-1 h-1 mx-2" />
                            <div :class="currentStep >= 3 ? 'bg-blue-600' : 'bg-gray-300'"
                                class="w-10 h-10 rounded-full flex items-center justify-center text-white font-bold">
                                3
                            </div>
                        </div>
                        <div class="flex justify-between mt-2 text-sm text-gray-600">
                            <span>Biodata</span>
                            <span>Pilihan Jurusan</span>
                            <span>Upload Berkas</span>
                        </div>
                    </div>

                    <!-- Step 1: Biodata -->
                    <div v-show="currentStep === 1" class="space-y-6">
                        <h2 class="text-xl font-semibold text-gray-800 mb-4">Data Pribadi</h2>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Nama Lengkap <span class="text-red-500">*</span>
                            </label>
                            <input v-model="form.full_name" type="text"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                :class="{ 'border-red-500': $page.props.errors.full_name }"
                                placeholder="Masukkan nama lengkap" />
                            <p v-if="$page.props.errors.full_name" class="text-red-500 text-sm mt-1">
                                {{ $page.props.errors.full_name }}
                            </p>
                        </div>

                        <div class="grid md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">
                                    NIK <span class="text-red-500">*</span>
                                </label>
                                <input v-model="form.nik" type="text" maxlength="16"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                    :class="{ 'border-red-500': $page.props.errors.nik }" placeholder="16 digit NIK" />
                                <p v-if="$page.props.errors.nik" class="text-red-500 text-sm mt-1">
                                    {{ $page.props.errors.nik }}
                                </p>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">
                                    NISN
                                </label>
                                <input v-model="form.nisn" type="text" maxlength="10"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                    :class="{ 'border-red-500': $page.props.errors.nisn }"
                                    placeholder="10 digit NISN (opsional)" />
                                <p v-if="$page.props.errors.nisn" class="text-red-500 text-sm mt-1">
                                    {{ $page.props.errors.nisn }}
                                </p>
                            </div>
                        </div>

                        <div class="grid md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">
                                    Tempat Lahir <span class="text-red-500">*</span>
                                </label>
                                <input v-model="form.place_of_birth" type="text"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                    :class="{ 'border-red-500': $page.props.errors.place_of_birth }"
                                    placeholder="Kota/Kabupaten" />
                                <p v-if="$page.props.errors.place_of_birth" class="text-red-500 text-sm mt-1">
                                    {{ $page.props.errors.place_of_birth }}
                                </p>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">
                                    Tanggal Lahir <span class="text-red-500">*</span>
                                </label>
                                <input v-model="form.date_of_birth" type="date"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                    :class="{ 'border-red-500': $page.props.errors.date_of_birth }" />
                                <p v-if="$page.props.errors.date_of_birth" class="text-red-500 text-sm mt-1">
                                    {{ $page.props.errors.date_of_birth }}
                                </p>
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Jenis Kelamin <span class="text-red-500">*</span>
                            </label>
                            <div class="flex space-x-4">
                                <label class="flex items-center">
                                    <input v-model="form.gender" type="radio" value="male"
                                        class="text-blue-600 focus:ring-blue-500" />
                                    <span class="ml-2 text-gray-700">Laki-laki</span>
                                </label>
                                <label class="flex items-center">
                                    <input v-model="form.gender" type="radio" value="female"
                                        class="text-blue-600 focus:ring-blue-500" />
                                    <span class="ml-2 text-gray-700">Perempuan</span>
                                </label>
                            </div>
                            <p v-if="$page.props.errors.gender" class="text-red-500 text-sm mt-1">
                                {{ $page.props.errors.gender }}
                            </p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Agama
                            </label>
                            <select v-model="form.religion"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                <option value="">Pilih Agama</option>
                                <option value="Islam">Islam</option>
                                <option value="Kristen">Kristen</option>
                                <option value="Katolik">Katolik</option>
                                <option value="Hindu">Hindu</option>
                                <option value="Buddha">Buddha</option>
                                <option value="Konghucu">Konghucu</option>
                            </select>
                        </div>

                        <!-- Address Section -->
                        <div class="border-t pt-4">
                            <h3 class="text-lg font-semibold text-gray-800 mb-4">Alamat Lengkap</h3>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">
                                    Jalan/Gang <span class="text-red-500">*</span>
                                </label>
                                <input v-model="form.street" type="text"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                    :class="{ 'border-red-500': $page.props.errors.street }"
                                    placeholder="Nama jalan/gang" />
                                <p v-if="$page.props.errors.street" class="text-red-500 text-sm mt-1">
                                    {{ $page.props.errors.street }}
                                </p>
                            </div>

                            <div class="grid md:grid-cols-3 gap-4 mt-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">
                                        RT
                                    </label>
                                    <input v-model="form.rt" type="text"
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                        :class="{ 'border-red-500': $page.props.errors.rt }"
                                        placeholder="001" />
                                    <p v-if="$page.props.errors.rt" class="text-red-500 text-sm mt-1">
                                        {{ $page.props.errors.rt }}
                                    </p>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">
                                        RW
                                    </label>
                                    <input v-model="form.rw" type="text"
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                        :class="{ 'border-red-500': $page.props.errors.rw }"
                                        placeholder="002" />
                                    <p v-if="$page.props.errors.rw" class="text-red-500 text-sm mt-1">
                                        {{ $page.props.errors.rw }}
                                    </p>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">
                                        Dusun/Kampung
                                    </label>
                                    <input v-model="form.dusun" type="text"
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                        :class="{ 'border-red-500': $page.props.errors.dusun }"
                                        placeholder="Nama dusun/kampung" />
                                    <p v-if="$page.props.errors.dusun" class="text-red-500 text-sm mt-1">
                                        {{ $page.props.errors.dusun }}
                                    </p>
                                </div>
                            </div>

                            <div class="grid md:grid-cols-2 gap-4 mt-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">
                                        Kecamatan/Distrik <span class="text-red-500">*</span>
                                    </label>
                                    <input v-model="form.district" type="text"
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                        :class="{ 'border-red-500': $page.props.errors.district }"
                                        placeholder="Nama kecamatan/distrik" />
                                    <p v-if="$page.props.errors.district" class="text-red-500 text-sm mt-1">
                                        {{ $page.props.errors.district }}
                                    </p>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">
                                        Kode Pos
                                    </label>
                                    <input v-model="form.postal_code" type="text" maxlength="10"
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                        :class="{ 'border-red-500': $page.props.errors.postal_code }"
                                        placeholder="99xxx" />
                                    <p v-if="$page.props.errors.postal_code" class="text-red-500 text-sm mt-1">
                                        {{ $page.props.errors.postal_code }}
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div class="grid md:grid-cols-2 gap-4 mt-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">
                                    No. Telepon/HP <span class="text-red-500">*</span>
                                </label>
                                <input v-model="form.phone" type="text"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                    :class="{ 'border-red-500': $page.props.errors.phone }"
                                    placeholder="08xxxxxxxxxx" />
                                <p v-if="$page.props.errors.phone" class="text-red-500 text-sm mt-1">
                                    {{ $page.props.errors.phone }}
                                </p>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">
                                    Email <span class="text-red-500">*</span>
                                </label>
                                <input v-model="form.email" type="email"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                    :class="{ 'border-red-500': $page.props.errors.email }"
                                    placeholder="email@example.com" />
                                <p v-if="$page.props.errors.email" class="text-red-500 text-sm mt-1">
                                    {{ $page.props.errors.email }}
                                </p>
                            </div>
                        </div>

                        <!-- Parent Section -->
                        <div class="border-t pt-4">
                            <h3 class="text-lg font-semibold text-gray-800 mb-4">Data Orang Tua/Wali</h3>

                            <div class="grid md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">
                                        Nama Ayah/Wali <span class="text-red-500">*</span>
                                    </label>
                                    <input v-model="form.parent_name" type="text"
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                        :class="{ 'border-red-500': $page.props.errors.parent_name }"
                                        placeholder="Nama lengkap ayah/wali" />
                                    <p v-if="$page.props.errors.parent_name" class="text-red-500 text-sm mt-1">
                                        {{ $page.props.errors.parent_name }}
                                    </p>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">
                                        Nama Ibu <span class="text-red-500">*</span>
                                    </label>
                                    <input v-model="form.mother_name" type="text"
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                        :class="{ 'border-red-500': $page.props.errors.mother_name }"
                                        placeholder="Nama lengkap ibu" />
                                    <p v-if="$page.props.errors.mother_name" class="text-red-500 text-sm mt-1">
                                        {{ $page.props.errors.mother_name }}
                                    </p>
                                </div>
                            </div>

                            <div class="mt-4">
                                <label class="block text-sm font-medium text-gray-700 mb-1">
                                    No. Telepon Orang Tua/Wali <span class="text-red-500">*</span>
                                </label>
                                <input v-model="form.parent_phone" type="text"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                    :class="{ 'border-red-500': $page.props.errors.parent_phone }"
                                    placeholder="08xxxxxxxxxx" />
                                <p v-if="$page.props.errors.parent_phone" class="text-red-500 text-sm mt-1">
                                    {{ $page.props.errors.parent_phone }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Step 2: Pilihan Jurusan -->
                    <div v-show="currentStep === 2" class="space-y-6">
                        <h2 class="text-xl font-semibold text-gray-800 mb-4">Pilihan Jurusan</h2>
                        <p class="text-gray-600">
                            Pilih 3 jurusan yang Anda minati sesuai urutan prioritas.
                        </p>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Pilihan 1 <span class="text-red-500">*</span>
                            </label>
                            <select v-model="form.major_1"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                :class="{ 'border-red-500': $page.props.errors.major_1 }">
                                <option value="">Pilih Jurusan</option>
                                <option v-for="major in majors" :key="major.id" :value="major.id">
                                    {{ major.name }} ({{ major.code }}) - Kuota: {{ major.quota }}
                                </option>
                            </select>
                            <p v-if="$page.props.errors.major_1" class="text-red-500 text-sm mt-1">
                                {{ $page.props.errors.major_1 }}
                            </p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Pilihan 2 <span class="text-red-500">*</span>
                            </label>
                            <select v-model="form.major_2"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                :class="{ 'border-red-500': $page.props.errors.major_2 }">
                                <option value="">Pilih Jurusan</option>
                                <option v-for="major in majors" :key="major.id" :value="major.id"
                                    :disabled="major.id == form.major_1">
                                    {{ major.name }} ({{ major.code }}) - Kuota: {{ major.quota }}
                                </option>
                            </select>
                            <p v-if="$page.props.errors.major_2" class="text-red-500 text-sm mt-1">
                                {{ $page.props.errors.major_2 }}
                            </p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Pilihan 3
                            </label>
                            <select v-model="form.major_3"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                :class="{ 'border-red-500': $page.props.errors.major_3 }">
                                <option value="">Pilih Jurusan (Opsional)</option>
                                <option v-for="major in majors" :key="major.id" :value="major.id"
                                    :disabled="major.id == form.major_1 || major.id == form.major_2">
                                    {{ major.name }} ({{ major.code }}) - Kuota: {{ major.quota }}
                                </option>
                            </select>
                            <p v-if="$page.props.errors.major_3" class="text-red-500 text-sm mt-1">
                                {{ $page.props.errors.major_3 }}
                            </p>
                        </div>
                    </div>

                    <!-- Step 3: Upload Berkas -->
                    <div v-show="currentStep === 3" class="space-y-6">
                        <h2 class="text-xl font-semibold text-gray-800 mb-4">Upload Berkas</h2>
                        <p class="text-gray-600">
                            Upload ulang dokumen jika ingin mengganti file yang sudah ada.
                            Kosongkan jika tidak ingin mengubah.
                        </p>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Ijazah/SKL
                            </label>
                            <input type="file" @change="handleFileChange('file_ijazah', $event)"
                                accept=".pdf,.jpg,.jpeg,.png"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent" />
                            <p v-if="getFileName('file_ijazah', student.file_ijazah)" 
                                :class="hasExistingFile('file_ijazah') ? 'text-blue-600' : 'text-green-600'" 
                                class="text-sm mt-1">
                                {{ getFileName('file_ijazah', student.file_ijazah) }}
                            </p>
                            <p v-if="$page.props.errors.file_ijazah" class="text-red-500 text-sm mt-1">
                                {{ $page.props.errors.file_ijazah }}
                            </p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Kartu Keluarga (KK)
                            </label>
                            <input type="file" @change="handleFileChange('file_kk', $event)"
                                accept=".pdf,.jpg,.jpeg,.png"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent" />
                            <p v-if="getFileName('file_kk', student.file_kk)" 
                                :class="hasExistingFile('file_kk') ? 'text-blue-600' : 'text-green-600'" 
                                class="text-sm mt-1">
                                {{ getFileName('file_kk', student.file_kk) }}
                            </p>
                            <p v-if="$page.props.errors.file_kk" class="text-red-500 text-sm mt-1">
                                {{ $page.props.errors.file_kk }}
                            </p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Akta Kelahiran
                            </label>
                            <input type="file" @change="handleFileChange('file_akta', $event)"
                                accept=".pdf,.jpg,.jpeg,.png"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent" />
                            <p v-if="getFileName('file_akta', student.file_akta)" 
                                :class="hasExistingFile('file_akta') ? 'text-blue-600' : 'text-green-600'" 
                                class="text-sm mt-1">
                                {{ getFileName('file_akta', student.file_akta) }}
                            </p>
                            <p v-if="$page.props.errors.file_akta" class="text-red-500 text-sm mt-1">
                                {{ $page.props.errors.file_akta }}
                            </p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Pas Foto (3x4)
                            </label>
                            <input type="file" @change="handleFileChange('file_pas_photo', $event)"
                                accept=".jpg,.jpeg,.png"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent" />
                            <p v-if="getFileName('file_pas_photo', student.file_pas_photo)" 
                                :class="hasExistingFile('file_pas_photo') ? 'text-blue-600' : 'text-green-600'" 
                                class="text-sm mt-1">
                                {{ getFileName('file_pas_photo', student.file_pas_photo) }}
                            </p>
                            <p v-if="$page.props.errors.file_pas_photo" class="text-red-500 text-sm mt-1">
                                {{ $page.props.errors.file_pas_photo }}
                            </p>
                        </div>
                    </div>

                    <!-- Navigation Buttons -->
                    <div class="flex justify-between mt-8">
                        <Link :href="route('student.preview', student.registration_number)"
                            class="px-6 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">
                            Batal
                        </Link>

                        <div class="flex gap-2">
                            <button v-if="currentStep > 1" type="button" @click="prevStep"
                                class="px-6 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">
                                Kembali
                            </button>

                            <button v-if="currentStep < 3" type="button" @click="nextStep"
                                :disabled="!validateStep1() || (currentStep === 2 && !validateStep2())"
                                class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:bg-gray-400 disabled:cursor-not-allowed">
                                Lanjut
                            </button>

                            <button v-if="currentStep === 3" type="submit" :disabled="form.processing"
                                class="px-6 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 disabled:bg-gray-400 disabled:cursor-not-allowed">
                                <span v-if="form.processing">Menyimpan...</span>
                                <span v-else>Simpan Perubahan</span>
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>
