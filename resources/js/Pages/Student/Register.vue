<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    majors: {
        type: Array,
        required: true,
    },
    errors: {
        type: Object,
        required: true,
    },
});

const currentStep = ref(1);

const form = useForm({
    full_name: '',
    nik: '',
    nisn: '',
    place_of_birth: '',
    date_of_birth: '',
    gender: '',
    religion: '',
    address: '',
    phone: '',
    email: '',
    parent_name: '',
    parent_phone: '',
    major_1: '',
    major_2: '',
    major_3: '',
    file_ijazah: null,
    file_kk: null,
    file_akta: null,
    file_pas_photo: null,
});

const submit = () => {
    form.post(route('student.register.store'), {
        forceFormData: true,
        onSuccess: () => {
            currentStep.value = 1;
        },
    });
};

const nextStep = () => {
    if (currentStep.value < 3) {
        currentStep.value++;
    }
};

const prevStep = () => {
    if (currentStep.value > 1) {
        currentStep.value--;
    }
};

const validateStep1 = () => {
    const required = ['full_name', 'nik', 'place_of_birth', 'date_of_birth', 'gender', 'address', 'phone', 'email', 'parent_name', 'parent_phone'];
    for (const field of required) {
        if (!form[field]) {
            return false;
        }
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
    return true;
};

const handleFileChange = (field, event) => {
    form[field] = event.target.files[0];
};

const formatDateForInput = (date) => {
    if (!date) return '';
    const d = new Date(date);
    return d.toISOString().split('T')[0];
};
</script>

<template>

    <Head title="Pendaftaran - PPDB SMKN 8" />

    <div class="min-h-screen bg-gray-50">
        <!-- Navigation -->
        <nav class="bg-white shadow-md">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between h-16">
                    <div class="flex items-center">
                        <Link :href="route('home')" class="text-2xl font-bold text-blue-600">
                            PPDB SMKN 8 TIK KOTA JAYAPURA
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
                <h1 class="text-3xl font-bold text-gray-800 mb-2 text-center">
                    Formulir Pendaftaran PPDB
                </h1>
                <p class="text-center text-gray-600 mb-8">
                    Tahun Ajaran 2026/2027
                </p>

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

                <!-- Error Messages -->
                <div v-if="$page.props.errors.error"
                    class="mb-6 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg">
                    {{ $page.props.errors.error }}
                </div>

                <form @submit.prevent="submit">
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

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Alamat Lengkap <span class="text-red-500">*</span>
                            </label>
                            <textarea v-model="form.address" rows="3"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                :class="{ 'border-red-500': $page.props.errors.address }"
                                placeholder="Jalan, RT/RW, Kelurahan, Kecamatan"></textarea>
                            <p v-if="$page.props.errors.address" class="text-red-500 text-sm mt-1">
                                {{ $page.props.errors.address }}
                            </p>
                        </div>

                        <div class="grid md:grid-cols-2 gap-4">
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

                        <div class="grid md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">
                                    Nama Orang Tua/Wali <span class="text-red-500">*</span>
                                </label>
                                <input v-model="form.parent_name" type="text"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                    :class="{ 'border-red-500': $page.props.errors.parent_name }"
                                    placeholder="Nama lengkap orang tua/wali" />
                                <p v-if="$page.props.errors.parent_name" class="text-red-500 text-sm mt-1">
                                    {{ $page.props.errors.parent_name }}
                                </p>
                            </div>

                            <div>
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
                            Upload dokumen-dokumen berikut dalam format PDF atau gambar (JPG/PNG).
                            Maksimal ukuran file: 2MB.
                        </p>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Ijazah/SKL
                            </label>
                            <input type="file" @change="handleFileChange('file_ijazah', $event)"
                                accept=".pdf,.jpg,.jpeg,.png"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent" />
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
                            <p v-if="$page.props.errors.file_pas_photo" class="text-red-500 text-sm mt-1">
                                {{ $page.props.errors.file_pas_photo }}
                            </p>
                        </div>
                    </div>

                    <!-- Navigation Buttons -->
                    <div class="flex justify-between mt-8">
                        <button v-if="currentStep > 1" type="button" @click="prevStep"
                            class="px-6 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">
                            Kembali
                        </button>
                        <div v-else></div>

                        <button v-if="currentStep < 3" type="button" @click="nextStep"
                            :disabled="!validateStep1() || (currentStep === 1 && !validateStep1()) || (currentStep === 2 && !validateStep2())"
                            class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:bg-gray-400 disabled:cursor-not-allowed">
                            Lanjut
                        </button>

                        <button v-if="currentStep === 3" type="submit" :disabled="form.processing"
                            class="px-6 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 disabled:bg-gray-400 disabled:cursor-not-allowed">
                            <span v-if="form.processing">Menyimpan...</span>
                            <span v-else>Daftar Sekarang</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>
